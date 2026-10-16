<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProofOfWork;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ProofOfWorkController extends Controller
{
    public function store(Request $request, Community $community, Course $course, Lesson $lesson): RedirectResponse
    {
        $this->validateLessonContext($community, $course, $lesson);

        $user = $request->user();
        if (! $course->hasMemberAccess($user)) {
            abort(404);
        }

        $action = $request->validate(['action' => ['sometimes', 'required', 'in:draft,submit']])['action'] ?? 'submit';

        if ($action === 'draft') {
            return $this->handleDraft($request, $community, $course, $lesson);
        }

        return $this->handleSubmit($request, $community, $course, $lesson);
    }

    private function handleDraft(Request $request, Community $community, Course $course, Lesson $lesson): RedirectResponse
    {
        $validated = $request->validate([
            'summary' => ['nullable', 'string', 'max:5000'],
            'url' => ['nullable', 'string', 'max:2048'],
            'attachment' => [
                'nullable',
                File::image()->dimensions(Rule::dimensions()->maxWidth(4096)->maxHeight(4096)),
                File::types(['jpg', 'jpeg', 'png', 'webp'])->extensions(['jpg', 'jpeg', 'png', 'webp'])->max(5120),
            ],
        ]);

        $url = $validated['url'] ?? null;
        if (is_string($url) && trim($url) !== '') {
            if (preg_match('/[\x00-\x1F\x7F]/', $url) === 1) {
                throw ValidationException::withMessages(['url' => 'The link cannot contain control characters.']);
            }
            $url = trim($url);
            if (! $this->isValidHttpsUrl($url)) {
                throw ValidationException::withMessages([
                    'url' => 'The link must be a valid HTTPS URL without credentials.',
                ]);
            }
        } else {
            $url = null;
        }

        $user = $request->user();
        $disk = Storage::disk('community_media');
        $newPath = null;
        $oldPath = null;

        try {
            DB::transaction(function () use ($request, $community, $course, $lesson, $user, $validated, $url, $disk, &$newPath, &$oldPath): void {
                [$community, $course, $lesson] = LessonProofOfWork::lockAccessibleContext($community, $course, $lesson, $user);
                $current = LessonProofOfWork::query()
                    ->where('lesson_id', $lesson->id)
                    ->where('user_id', $user->id)
                    ->where('is_current', true)
                    ->lockForUpdate()
                    ->first();

                if ($current && $current->isTerminal()) {
                    throw ValidationException::withMessages([
                        'summary' => 'This submission has reached a terminal state and cannot be modified.',
                    ]);
                }

                if ($current && $current->isWaiting()) {
                    throw ValidationException::withMessages([
                        'summary' => 'A submission is already awaiting review. You cannot edit it now.',
                    ]);
                }

                // A reviewed revision is never edited, including intermediate draft saves.
                if ($current?->isNeedsRevision()) {
                    $current->update(['is_current' => false]);
                    $current = LessonProofOfWork::create([
                        'community_id' => $community->id,
                        'course_id' => $course->id,
                        'lesson_id' => $lesson->id,
                        'user_id' => $user->id,
                        'revision' => $current->revision + 1,
                        'is_current' => true,
                        'status' => LessonProofOfWork::STATUS_DRAFT,
                        'summary' => $current->summary,
                        'url' => $current->url,
                        'attachment_path' => $current->attachment_path,
                    ]);
                }

                $attachmentPath = $current?->attachment_path;
                if ($request->hasFile('attachment')) {
                    $newPath = $disk->putFile((string) $community->id.'/proof-of-work/'.(string) $user->id, $request->file('attachment'));
                    if (! is_string($newPath)) {
                        throw new \RuntimeException('Failed to store evidence image.');
                    }
                    $oldPath = $attachmentPath;
                    $attachmentPath = $newPath;
                }

                if (! $current) {
                    LessonProofOfWork::create([
                        'community_id' => $community->id,
                        'course_id' => $course->id,
                        'lesson_id' => $lesson->id,
                        'user_id' => $user->id,
                        'revision' => 1,
                        'is_current' => true,
                        'status' => LessonProofOfWork::STATUS_DRAFT,
                        'summary' => $validated['summary'] ?? null,
                        'url' => $url,
                        'attachment_path' => $attachmentPath,
                    ]);
                } else {
                    $current->update([
                        'summary' => $validated['summary'] ?? null,
                        'url' => $url,
                        'attachment_path' => $attachmentPath,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            if (is_string($newPath)) {
                $this->cleanupFile($newPath, $community->id, $user->id);
            }
            throw $exception;
        }

        if (is_string($oldPath) && $oldPath !== $newPath) {
            $this->cleanupFile($oldPath, $community->id, $user->id);
        }

        return back()->with('status', 'Draft saved successfully.');
    }

    private function handleSubmit(Request $request, Community $community, Course $course, Lesson $lesson): RedirectResponse
    {
        $validated = $request->validate([
            'summary' => ['required', 'string', 'min:10', 'max:5000'],
            'url' => ['required', 'string', 'max:2048'],
            'attachment' => [
                'nullable',
                File::image()->dimensions(Rule::dimensions()->maxWidth(4096)->maxHeight(4096)),
                File::types(['jpg', 'jpeg', 'png', 'webp'])->extensions(['jpg', 'jpeg', 'png', 'webp'])->max(5120),
            ],
        ]);

        if (preg_match('/[\x00-\x1F\x7F]/', $validated['url']) === 1) {
            throw ValidationException::withMessages(['url' => 'The link cannot contain control characters.']);
        }
        $url = trim($validated['url']);
        if (! $this->isValidHttpsUrl($url)) {
            throw ValidationException::withMessages([
                'url' => 'The link must be an absolute HTTPS URL without credentials.',
            ]);
        }

        $user = $request->user();
        $disk = Storage::disk('community_media');
        $newPath = null;
        $oldPath = null;

        try {
            DB::transaction(function () use ($request, $community, $course, $lesson, $user, $validated, $url, $disk, &$newPath, &$oldPath): void {
                [$community, $course, $lesson] = LessonProofOfWork::lockAccessibleContext($community, $course, $lesson, $user);
                $current = LessonProofOfWork::query()
                    ->where('lesson_id', $lesson->id)
                    ->where('user_id', $user->id)
                    ->where('is_current', true)
                    ->lockForUpdate()
                    ->first();

                if ($current && $current->isTerminal()) {
                    throw ValidationException::withMessages([
                        'summary' => 'This submission has reached a terminal state and cannot be modified.',
                    ]);
                }

                if ($current && $current->isWaiting()) {
                    throw ValidationException::withMessages([
                        'summary' => 'A submission is already awaiting review.',
                    ]);
                }

                $attachmentPath = $current?->attachment_path;
                if ($request->hasFile('attachment')) {
                    $newPath = $disk->putFile((string) $community->id.'/proof-of-work/'.(string) $user->id, $request->file('attachment'));
                    if (! is_string($newPath)) {
                        throw new \RuntimeException('Failed to store evidence image.');
                    }
                    if ($current && $current->isDraft()) {
                        $oldPath = $current->attachment_path;
                    }
                    $attachmentPath = $newPath;
                }

                if ($current && $current->isNeedsRevision()) {
                    // Mark old revision as no longer current
                    $current->update(['is_current' => false]);

                    // Create new revision in WAITING state
                    LessonProofOfWork::create([
                        'community_id' => $community->id,
                        'course_id' => $course->id,
                        'lesson_id' => $lesson->id,
                        'user_id' => $user->id,
                        'revision' => $current->revision + 1,
                        'is_current' => true,
                        'status' => LessonProofOfWork::STATUS_WAITING,
                        'summary' => $validated['summary'],
                        'url' => $url,
                        'attachment_path' => $attachmentPath,
                        'submitted_at' => now(),
                    ]);
                } elseif ($current && $current->isDraft()) {
                    $current->update([
                        'status' => LessonProofOfWork::STATUS_WAITING,
                        'summary' => $validated['summary'],
                        'url' => $url,
                        'attachment_path' => $attachmentPath,
                        'submitted_at' => now(),
                    ]);
                } else {
                    LessonProofOfWork::create([
                        'community_id' => $community->id,
                        'course_id' => $course->id,
                        'lesson_id' => $lesson->id,
                        'user_id' => $user->id,
                        'revision' => 1,
                        'is_current' => true,
                        'status' => LessonProofOfWork::STATUS_WAITING,
                        'summary' => $validated['summary'],
                        'url' => $url,
                        'attachment_path' => $attachmentPath,
                        'submitted_at' => now(),
                    ]);
                }
            });
        } catch (Throwable $exception) {
            if (is_string($newPath)) {
                $this->cleanupFile($newPath, $community->id, $user->id);
            }
            throw $exception;
        }

        if (is_string($oldPath) && $oldPath !== $newPath) {
            $this->cleanupFile($oldPath, $community->id, $user->id);
        }

        return back()->with('status', 'Proof of work submitted. Waiting for feedback.');
    }

    public function image(Request $request, Community $community, LessonProofOfWork $proofOfWork): StreamedResponse
    {
        $proofOfWork = LessonProofOfWork::query()->eligible()->whereKey($proofOfWork->id)->firstOrFail();
        if (! $community->isActive() || $proofOfWork->community_id !== $community->id) {
            abort(404);
        }

        $user = $request->user();
        if (! $user) {
            abort(404);
        }

        $isOwner = $proofOfWork->user_id === $user->id;
        $isCreator = $community->isCreator($user);

        // Recheck effective membership or creator access
        if ($isOwner) {
            $isActiveMember = $community->memberships()->where('user_id', $user->id)->effective()->exists();
            abort_unless($isActiveMember || $isCreator, 404);
        } else {
            abort_unless($isCreator && ! $proofOfWork->isDraft(), 404);
        }

        $path = $proofOfWork->attachment_path;
        abort_unless(
            is_string($path) && preg_match('/\A'.preg_quote((string) $community->id, '/').'\/proof-of-work\/'.preg_quote((string) $proofOfWork->user_id, '/').'\/[a-zA-Z0-9_\-]+\.(?:jpg|jpeg|png|webp)\z/D', $path) === 1,
            404
        );

        $disk = Storage::disk('community_media');
        abort_unless($disk->exists($path), 404);

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $type = in_array($extension, ['jpg', 'jpeg'], true) ? 'image/jpeg' : 'image/'.$extension;

        return $disk->response($path, 'evidence.'.$extension, [
            'Content-Type' => $type,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }

    private function validateLessonContext(Community $community, Course $course, Lesson $lesson): void
    {
        if (! $community->isActive()
            || $course->community_id !== $community->id
            || ! $course->isPublished()
            || $lesson->section?->course_id !== $course->id
            || ! $lesson->isPublished()
            || ! $lesson->isPractical()) {
            abort(404);
        }
    }

    private function isValidHttpsUrl(string $url): bool
    {
        if (mb_strlen($url) > 2048) {
            return false;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $parsed = parse_url($url);
        if (! is_array($parsed)) {
            return false;
        }

        if (($parsed['scheme'] ?? '') !== 'https') {
            return false;
        }

        if (isset($parsed['user']) || isset($parsed['pass'])) {
            return false;
        }

        $host = $parsed['host'] ?? '';
        if ($host === '' || preg_match('/[\x00-\x1F\x7F]/', $url) === 1) {
            return false;
        }

        return true;
    }

    private function cleanupFile(string $path, int $communityId, int $userId): void
    {
        if (preg_match('/\A'.preg_quote((string) $communityId, '/').'\/proof-of-work\/'.preg_quote((string) $userId, '/').'\/[a-zA-Z0-9_\-]+\.(?:jpg|jpeg|png|webp)\z/D', $path) !== 1) {
            Log::warning('Proof of work cleanup rejected invalid reference.', ['path' => $path]);

            return;
        }

        try {
            // A later draft may share an image with a retained reviewed revision.
            if (LessonProofOfWork::query()->where('attachment_path', $path)->exists()) {
                return;
            }
            Storage::disk('community_media')->delete($path);
        } catch (Throwable) {
            Log::warning('Proof of work cleanup failed.', ['path' => $path]);
        }
    }
}
