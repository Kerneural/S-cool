<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CommentPolicy
{
    /**
     * Determine whether the user can create a comment on the post.
     */
    public function create(User $user, Post $post): Response
    {
        return $this->canAccessPost($user, $post)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can update the comment (author only).
     */
    public function update(User $user, Comment $comment): Response
    {
        return $comment->post->community->isActive() && (int) $comment->user_id === (int) $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can delete the comment (author or creator moderation).
     */
    public function delete(User $user, Comment $comment): Response
    {
        $canDelete = $comment->post->community->isActive()
            && ((int) $comment->user_id === (int) $user->id || $comment->post->community->isCreator($user));

        return $canDelete
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Check if user is creator or active member of the post's community.
     */
    protected function canAccessPost(User $user, Post $post): bool
    {
        $community = $post->community;

        if (! $community || ! $community->isActive()) {
            return false;
        }

        return $community->isCreator($user)
            || $community->memberships()->where('user_id', $user->id)->where('status', 'ACTIVE')->exists();
    }
}
