<?php

namespace App\Actions\CommunityContent;

use App\Models\Community;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\DB;

class FeedMutation
{
    /**
     * Lock order: community -> membership -> post -> comment.
     * Re-read state and authorize in the callback, never on route-bound snapshots.
     */
    public function run(User $user, Community $community, ?int $postId, ?int $commentId, Closure $mutate): mixed
    {
        return DB::transaction(function () use ($user, $community, $postId, $commentId, $mutate) {
            $currentCommunity = Community::query()->lockForUpdate()->findOrFail($community->id);
            $currentCommunity->memberships()->where('user_id', $user->id)->lockForUpdate()->first();

            $post = $postId === null ? null : $currentCommunity->posts()->lockForUpdate()->findOrFail($postId);
            $post?->setRelation('community', $currentCommunity);
            $comment = $commentId === null ? null : $post->comments()->lockForUpdate()->findOrFail($commentId);
            $comment?->setRelation('post', $post);

            return $mutate($currentCommunity, $post, $comment);
        }, 3);
    }
}
