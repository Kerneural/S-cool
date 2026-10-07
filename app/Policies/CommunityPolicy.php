<?php

namespace App\Policies;

use App\Models\Community;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CommunityPolicy
{
    /**
     * Determine whether the user can view the community.
     */
    public function view(User $user, Community $community): Response
    {
        return $community->isActive() && ($community->isCreator($user)
            || $community->memberships()->where('user_id', $user->id)->where('status', 'ACTIVE')->exists())
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can create communities.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the community.
     */
    public function update(User $user, Community $community): Response
    {
        return $community->isActive() && $community->isCreator($user)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Hard deletion is not supported; archiving needs a separate state action.
     */
    public function delete(User $user, Community $community): bool
    {
        return false;
    }
}
