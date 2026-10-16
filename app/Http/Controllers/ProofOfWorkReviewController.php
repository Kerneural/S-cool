<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\LessonProofOfWork;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProofOfWorkReviewController extends Controller
{
    public function index(Request $request, Community $community): View
    {
        $this->authorizeCreator($community, $request->user());

        $validStatuses = [
            LessonProofOfWork::STATUS_WAITING,
            LessonProofOfWork::STATUS_NEEDS_REVISION,
            LessonProofOfWork::STATUS_VERIFIED,
            LessonProofOfWork::STATUS_REJECTED,
        ];
        $status = $request->validate(['status' => ['sometimes', 'required', 'string', Rule::in([...$validStatuses, 'all'])]])['status'] ?? 'WAITING';

        $query = $community->proofOfWorks()
            ->eligible()
            ->with(['lesson.section.course', 'user', 'reviewer'])
            ->whereIn('status', $validStatuses)
            ->where('is_current', true);

        if ($status !== 'all' && in_array($status, $validStatuses, true)) {
            $query->where('status', $status);
        }

        $submissions = $query->orderByDesc('submitted_at')->orderByDesc('id')->paginate(15)->withQueryString();

        $counts = [
            'WAITING' => $community->proofOfWorks()->eligible()->where('is_current', true)->where('status', LessonProofOfWork::STATUS_WAITING)->count(),
            'NEEDS_REVISION' => $community->proofOfWorks()->eligible()->where('is_current', true)->where('status', LessonProofOfWork::STATUS_NEEDS_REVISION)->count(),
            'VERIFIED' => $community->proofOfWorks()->eligible()->where('is_current', true)->where('status', LessonProofOfWork::STATUS_VERIFIED)->count(),
            'REJECTED' => $community->proofOfWorks()->eligible()->where('is_current', true)->where('status', LessonProofOfWork::STATUS_REJECTED)->count(),
        ];

        return view('communities.manage.proof-of-work.index', compact('community', 'submissions', 'status', 'counts'));
    }

    public function show(Request $request, Community $community, LessonProofOfWork $proofOfWork): View
    {
        $this->authorizeCreator($community, $request->user());

        $proofOfWork = LessonProofOfWork::query()->eligible()->where('status', '!=', LessonProofOfWork::STATUS_DRAFT)->whereKey($proofOfWork->id)->firstOrFail();
        if ($proofOfWork->community_id !== $community->id) {
            abort(404);
        }

        $proofOfWork->load(['lesson.section.course', 'user', 'reviewer']);

        $revisions = LessonProofOfWork::query()
            ->eligible()
            ->where('community_id', $community->id)
            ->where('course_id', $proofOfWork->course_id)
            ->where('lesson_id', $proofOfWork->lesson_id)
            ->where('user_id', $proofOfWork->user_id)
            ->where('status', '!=', LessonProofOfWork::STATUS_DRAFT)
            ->with('reviewer')
            ->orderBy('revision', 'asc')
            ->get();

        return view('communities.manage.proof-of-work.show', compact('community', 'proofOfWork', 'revisions'));
    }

    public function review(Request $request, Community $community, LessonProofOfWork $proofOfWork): RedirectResponse
    {
        $this->authorizeCreator($community, $request->user());

        if ($proofOfWork->community_id !== $community->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:VERIFIED,NEEDS_REVISION,REJECTED'],
            'feedback' => ['nullable', 'string', 'max:5000'],
        ]);

        $newStatus = $validated['status'];
        $feedback = trim((string) ($validated['feedback'] ?? ''));

        if (in_array($newStatus, [LessonProofOfWork::STATUS_NEEDS_REVISION, LessonProofOfWork::STATUS_REJECTED], true) && $feedback === '') {
            throw ValidationException::withMessages([
                'feedback' => 'Actionable feedback is required when requesting revision or rejecting work.',
            ]);
        }

        $user = $request->user();

        DB::transaction(function () use ($community, $proofOfWork, $newStatus, $feedback, $user): void {
            [$community, , $lesson] = LessonProofOfWork::lockAccessibleContext(
                $community, $proofOfWork->course, $proofOfWork->lesson, User::query()->findOrFail($proofOfWork->user_id)
            );
            $this->authorizeCreator($community, $user);
            $locked = $community->proofOfWorks()->where('course_id', $proofOfWork->course_id)
                ->where('lesson_id', $lesson->id)->where('user_id', $proofOfWork->user_id)
                ->whereKey($proofOfWork->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isWaiting() || ! $locked->is_current) {
                throw ValidationException::withMessages([
                    'status' => 'This submission is not awaiting review. It may have already been reviewed.',
                ]);
            }

            $skillSnapshot = $locked->skill_label_snapshot;
            if ($newStatus === LessonProofOfWork::STATUS_VERIFIED) {
                $skillSnapshot = trim((string) $lesson->skill_label);
                if ($skillSnapshot === '' || mb_strlen($skillSnapshot) > 80) {
                    throw ValidationException::withMessages(['status' => 'Configure a nonempty skill label before verifying this work.']);
                }
            }

            $locked->update([
                'status' => $newStatus,
                'feedback' => $feedback !== '' ? $feedback : null,
                'reviewer_id' => $user->id,
                'reviewed_at' => now(),
                'skill_label_snapshot' => $skillSnapshot,
            ]);
        });

        return redirect()->route('communities.manage.proof-of-work.index', $community)
            ->with('status', 'Review submitted successfully.');
    }

    private function authorizeCreator(Community $community, $user): void
    {
        if (! $community->isActive() || ! $user || ! $community->isCreator($user)) {
            abort(404);
        }
    }
}
