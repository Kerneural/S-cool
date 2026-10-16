<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CourseCoverController extends Controller
{
    public function store(Request $request, Community $community, Course $course): RedirectResponse
    {
        if ($course->community_id !== $community->id) {
            abort(404);
        }

        Gate::authorize('update', $course);

        $request->validate(['cover' => [
            'required',
            File::image()->dimensions(Rule::dimensions()->maxWidth(4096)->maxHeight(4096)),
            File::types(['jpg', 'jpeg', 'png', 'webp'])->extensions(['jpg', 'jpeg', 'png', 'webp'])->max(5120),
        ]]);

        $disk = Storage::disk('community_media');
        $newPath = null;
        $oldPath = null;

        try {
            DB::transaction(function () use ($request, $community, $course, $disk, &$newPath, &$oldPath): void {
                $current = Course::query()->whereKey($course->id)->lockForUpdate()->firstOrFail();
                Gate::forUser($request->user())->authorize('update', $current);

                $oldPath = $current->cover_path;
                $targetDir = (string) $community->id.'/courses';
                $newPath = $disk->putFile($targetDir, $request->file('cover'));

                if (! is_string($newPath)) {
                    throw new \RuntimeException('Course cover storage failed.');
                }

                $current->cover_path = $newPath;
                $current->save();
            });
        } catch (Throwable $exception) {
            if (is_string($newPath)) {
                $this->cleanup($newPath, $community->id);
            }
            throw $exception;
        }

        if (is_string($oldPath)) {
            $this->cleanup($oldPath, $community->id);
        }

        return back()->with('status', 'Course cover updated successfully.');
    }

    public function show(Request $request, Community $community, Course $course): StreamedResponse
    {
        if (! $community->isActive() || $course->community_id !== $community->id) {
            abort(404);
        }

        $user = $request->user();
        $isCreator = $user !== null && $community->isCreator($user);
        $isActiveMember = $user !== null && $community->memberships()->where('user_id', $user->id)->effective()->exists();

        // Draft covers are creator-only; published course covers can be viewed by active community members
        if ($course->isDraft()) {
            abort_unless($isCreator, 404);
        } else {
            abort_unless($isCreator || $isActiveMember, 404);
        }

        $path = $course->cover_path;
        abort_unless(
            is_string($path) && preg_match('/\A'.preg_quote((string) $community->id, '/').'\/courses\/[a-zA-Z0-9_\-]+\.(?:jpg|jpeg|png|webp)\z/D', $path) === 1,
            404
        );

        $disk = Storage::disk('community_media');
        abort_unless($disk->exists($path), 404);

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $type = in_array($extension, ['jpg', 'jpeg'], true) ? 'image/jpeg' : 'image/'.$extension;

        return $disk->response($path, 'cover.'.$extension, [
            'Content-Type' => $type,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }

    public function destroy(Request $request, Community $community, Course $course): RedirectResponse
    {
        if ($course->community_id !== $community->id) {
            abort(404);
        }

        Gate::authorize('update', $course);

        $oldPath = null;
        DB::transaction(function () use ($request, $course, &$oldPath): void {
            $current = Course::query()->whereKey($course->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($request->user())->authorize('update', $current);

            $oldPath = $current->cover_path;
            $current->cover_path = null;
            $current->save();
        });

        if (is_string($oldPath)) {
            $this->cleanup($oldPath, $community->id);
        }

        return back()->with('status', 'Course cover removed.');
    }

    private function cleanup(string $path, int $communityId): void
    {
        if (preg_match('/\A'.preg_quote((string) $communityId, '/').'\/courses\/[a-zA-Z0-9_\-]+\.(?:jpg|jpeg|png|webp)\z/D', $path) !== 1) {
            Log::warning('Course cover cleanup rejected an invalid reference.', ['community_id' => $communityId, 'path' => $path]);

            return;
        }

        try {
            if (! Storage::disk('community_media')->delete($path)) {
                Log::warning('Course cover cleanup failed.', ['community_id' => $communityId, 'path' => $path]);
            }
        } catch (Throwable) {
            Log::warning('Course cover cleanup failed.', ['community_id' => $communityId, 'path' => $path]);
        }
    }
}
