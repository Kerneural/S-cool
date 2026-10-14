<?php

namespace App\Jobs;

use App\Models\AppNotification;
use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPostReplyNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $commentId,
    ) {
        $this->afterCommit();
    }

    public function handle(): void
    {
        $comment = Comment::query()->find($this->commentId);
        if (! $comment || $comment->trashed()) {
            return;
        }

        $post = $comment->post;
        if (! $post || $post->trashed()) {
            return;
        }

        // Own reply sends none (AC-01)
        if ($comment->user_id === $post->user_id) {
            return;
        }

        $community = $post->community;
        if (! $community || ! $community->isActive()) {
            return;
        }

        $author = $post->author;
        if (! $author || ! $author->notify_replies) {
            return;
        }

        $authorId = $post->user_id;
        $isCreator = (int) $community->creator_id === (int) $authorId;
        $isActiveMember = $community->memberships()
            ->where('user_id', $authorId)
            ->effective()
            ->exists();

        if (! $isCreator && ! $isActiveMember) {
            return;
        }

        $dedupeKey = "post_reply:comment_{$comment->id}:recipient_{$authorId}";

        AppNotification::createNotification(
            userId: $authorId,
            communityId: $community->id,
            type: 'post_reply',
            dedupeKey: $dedupeKey,
            data: [
                'post_id' => $post->id,
                'comment_id' => $comment->id,
                'commenter_id' => $comment->user_id,
            ],
        );
    }
}
