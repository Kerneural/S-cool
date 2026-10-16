<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Community;
use App\Models\HelpfulAnswerAction;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class HelpfulAnswerService
{
    /**
     * Accept a comment as the creator-accepted helpful answer for a contextual post.
     */
    public function accept(Community $community, Post $post, Comment $comment, User $actor): void
    {
        DB::transaction(function () use ($community, $post, $comment, $actor): void {
            // Match classroom/access lock order. Route-bound models may be stale.
            $community = $this->lockCreatorCommunity($community, $actor);
            $currentPost = $community->posts()->findOrFail($post->id);
            abort_unless($currentPost->hasLearningContext(), 422, 'Only contextual posts can have helpful answers.');
            $course = $community->courses()->lockForUpdate()->findOrFail($currentPost->course_id);
            abort_unless($course->isPublished(), 404);
            if ($currentPost->lesson_id !== null) {
                $lesson = $currentPost->lesson()->firstOrFail();
                $section = $course->sections()->lockForUpdate()->findOrFail($lesson->course_section_id);
                $lesson = $section->lessons()->lockForUpdate()->findOrFail($lesson->id);
                abort_unless($lesson->isPublished(), 404);
            }

            $currentComment = $currentPost->comments()->findOrFail($comment->id);
            abort_if((int) $currentComment->user_id === (int) $actor->id, 403, 'Creators cannot accept their own answers.');
            $membership = $community->memberships()->where('user_id', $currentComment->user_id)->lockForUpdate()->first();
            $entitlement = $course->isOneTime()
                ? $course->entitlements()->where('user_id', $currentComment->user_id)->lockForUpdate()->first()
                : null;
            abort_unless($membership?->hasEffectiveAccess() && ($course->isIncluded() || $entitlement !== null), 404);

            $lockedPost = $community->posts()->lockForUpdate()->findOrFail($post->id);
            $lockedComment = $lockedPost->comments()->lockForUpdate()->findOrFail($comment->id);
            abort_unless((int) $lockedPost->course_id === (int) $currentPost->course_id
                && $lockedPost->lesson_id === $currentPost->lesson_id
                && (int) $lockedComment->user_id === (int) $currentComment->user_id, 409);

            // Idempotent: if already accepted, do nothing
            if ((int) $lockedPost->accepted_comment_id === (int) $lockedComment->id) {
                return;
            }

            // If another comment was previously accepted, record replacement
            if ($lockedPost->accepted_comment_id !== null) {
                $prevComment = $lockedPost->comments()->withTrashed()->lockForUpdate()->find($lockedPost->accepted_comment_id);
                if ($prevComment) {
                    $prevComment->update(['is_helpful' => false]);
                    HelpfulAnswerAction::create([
                        'community_id' => $community->id,
                        'post_id' => $lockedPost->id,
                        'comment_id' => $prevComment->id,
                        'author_id' => $prevComment->user_id,
                        'actor_id' => $actor->id,
                        'action' => HelpfulAnswerAction::ACTION_REPLACED,
                        'created_at' => now(),
                    ]);
                }
            }

            $lockedComment->update(['is_helpful' => true]);
            $lockedPost->update(['accepted_comment_id' => $lockedComment->id]);

            HelpfulAnswerAction::create([
                'community_id' => $community->id,
                'post_id' => $lockedPost->id,
                'comment_id' => $lockedComment->id,
                'author_id' => $lockedComment->user_id,
                'actor_id' => $actor->id,
                'action' => HelpfulAnswerAction::ACTION_ACCEPTED,
                'created_at' => now(),
            ]);
        }, 3);
    }

    /**
     * Revoke an accepted helpful answer.
     */
    public function revoke(Community $community, Post $post, Comment $comment, User $actor): void
    {
        DB::transaction(function () use ($community, $post, $comment, $actor): void {
            $community = $this->lockCreatorCommunity($community, $actor);
            $lockedPost = $community->posts()->lockForUpdate()->findOrFail($post->id);
            // Revocation also cleans up retained, soft-deleted accepted sources.
            $lockedComment = $lockedPost->comments()->withTrashed()->lockForUpdate()->findOrFail($comment->id);

            if ((int) $lockedPost->accepted_comment_id !== (int) $lockedComment->id) {
                abort_if($lockedPost->accepted_comment_id !== null, 409, 'The accepted answer changed. Refresh before retrying.');

                return; // Repeated revocation is idempotent.
            }

            $lockedComment->update(['is_helpful' => false]);
            $lockedPost->update(['accepted_comment_id' => null]);

            HelpfulAnswerAction::create([
                'community_id' => $community->id,
                'post_id' => $lockedPost->id,
                'comment_id' => $lockedComment->id,
                'author_id' => $lockedComment->user_id,
                'actor_id' => $actor->id,
                'action' => HelpfulAnswerAction::ACTION_REVOKED,
                'created_at' => now(),
            ]);
        }, 3);
    }

    private function lockCreatorCommunity(Community $community, User $actor): Community
    {
        $current = Community::query()->lockForUpdate()->findOrFail($community->id);
        abort_unless($current->isActive(), 404);
        abort_unless($current->isCreator($actor), 403, 'Only the community creator can manage helpful answers.');

        return $current;
    }
}
