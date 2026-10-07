<?php

namespace App\Policies;

use App\Models\Community;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    /**
     * Determine whether the user can view the posts of a community.
     */
    public function viewAny(User $user, Community $community): Response
    {
        return $this->canAccessCommunity($user, $community)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can view the post.
     */
    public function view(User $user, Post $post): Response
    {
        return $this->canAccessCommunity($user, $post->community)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can create posts in the community.
     */
    public function create(User $user, Community $community): Response
    {
        return $this->canAccessCommunity($user, $community)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can update the post (author only).
     */
    public function update(User $user, Post $post): Response
    {
        return $post->community->isActive() && (int) $post->user_id === (int) $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can delete the post (author or creator).
     */
    public function delete(User $user, Post $post): Response
    {
        $canDelete = $post->community->isActive()
            && ((int) $post->user_id === (int) $user->id || $post->community->isCreator($user));

        return $canDelete
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Check if user is creator or active member of an active community.
     */
    protected function canAccessCommunity(User $user, Community $community): bool
    {
        if (! $community->isActive()) {
            return false;
        }

        return $community->isCreator($user)
            || $community->memberships()->where('user_id', $user->id)->where('status', 'ACTIVE')->exists();
    }
}
