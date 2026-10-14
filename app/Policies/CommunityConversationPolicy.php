<?php

namespace App\Policies;

use App\Models\CommunityConversation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CommunityConversationPolicy
{
    /**
     * Determine whether the user can view the conversation.
     */
    public function view(User $user, CommunityConversation $conversation): Response
    {
        $community = $conversation->community;

        if (! $community || ! $community->isActive()) {
            return Response::denyAsNotFound();
        }

        if (! $conversation->isParticipant($user)) {
            return Response::denyAsNotFound();
        }

        $userOne = $conversation->userOne;
        $userTwo = $conversation->userTwo;

        if (! $userOne || ! $userTwo) {
            return Response::denyAsNotFound();
        }

        $userOneAccess = $community->isCreator($userOne)
            || ($community->memberships()->where('user_id', $userOne->id)->effective()->exists());

        $userTwoAccess = $community->isCreator($userTwo)
            || ($community->memberships()->where('user_id', $userTwo->id)->effective()->exists());

        if (! $userOneAccess || ! $userTwoAccess) {
            return Response::denyAsNotFound();
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can send a message in the conversation.
     */
    public function sendMessage(User $user, CommunityConversation $conversation): Response
    {
        return $this->view($user, $conversation);
    }
}
