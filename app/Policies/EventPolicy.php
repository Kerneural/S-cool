<?php

namespace App\Policies;

use App\Models\Community;
use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EventPolicy
{
    /**
     * Determine whether the user can view the event calendar/list.
     */
    public function viewAny(User $user, Community $community): Response
    {
        return $this->canAccessCommunity($user, $community)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can view the event.
     */
    public function view(User $user, Event $event): Response
    {
        return $this->canAccessCommunity($user, $event->community)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can create an event (Creator only).
     */
    public function create(User $user, Community $community): Response
    {
        return $community->isActive() && $community->isCreator($user)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can update the event (Creator only, active community).
     */
    public function update(User $user, Event $event): Response
    {
        return $event->community->isActive() && $event->community->isCreator($user)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can cancel the event (Creator only).
     */
    public function cancel(User $user, Event $event): Response
    {
        return $event->community->isActive() && $event->community->isCreator($user)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Hard deletion is excluded in MVP.
     */
    public function delete(User $user, Event $event): bool
    {
        return false;
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
