<?php

namespace App\Services;

use App\Models\Community;
use App\Models\CommunityConversation;
use App\Models\CommunityMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CommunityMessaging
{
    public function inbox(Community $community, User $actor, ?int $page = null): array
    {
        abort_unless($community->isActive(), 404);
        abort_unless($community->isCreator($actor)
            || $community->memberships()->where('user_id', $actor->id)->effective()->exists(), 404);
        $visible = CommunityConversation::query()->visibleTo($actor, $community);
        $conversations = (clone $visible)->with(['userOne', 'userTwo', 'latestMessage'])
            ->withCount(['messages as unread_count' => fn ($query) => $query
                ->where('sender_id', '!=', $actor->id)->whereNull('read_at')])
            ->orderByDesc('last_message_at')->orderByDesc('id')->paginate(15, ['*'], 'page', $page)
            ->withPath(route('communities.messages.index', $community));
        $totalUnread = CommunityMessage::whereIn('conversation_id', $visible->select('id'))
            ->where('sender_id', '!=', $actor->id)->whereNull('read_at')->count();

        return compact('community', 'conversations', 'totalUnread');
    }

    public function snapshot(Community $community, CommunityConversation $conversation, User $actor, string $pageName = 'page'): array
    {
        abort_unless((int) $conversation->community_id === (int) $community->id, 404);
        Gate::forUser($actor)->authorize('view', $conversation);

        return DB::transaction(function () use ($community, $conversation, $actor, $pageName): array {
            $currentCommunity = Community::whereKey($community->id)->lockForUpdate()->firstOrFail();
            $currentConversation = CommunityConversation::whereKey($conversation->id)
                ->where('community_id', $currentCommunity->id)->lockForUpdate()->firstOrFail();
            abort_unless($currentConversation->isParticipant($actor), 404);
            $this->requireLockedParticipants($currentCommunity, [$currentConversation->user_one_id, $currentConversation->user_two_id]);

            // Newest page first, chronological order within each page.
            $messages = $currentConversation->messages()->with('sender')->orderByDesc('id')->paginate(30, ['*'], $pageName);
            $messages->setCollection($messages->getCollection()->reverse()->values());
            $readAt = now('UTC');
            $currentConversation->messages()->whereIn('id', $messages->pluck('id'))
                ->where('sender_id', '!=', $actor->id)->whereNull('read_at')->update(['read_at' => $readAt]);
            foreach ($messages as $message) {
                if ((int) $message->sender_id !== (int) $actor->id && $message->read_at === null) {
                    $message->read_at = $readAt;
                }
            }

            return [$currentCommunity, $currentConversation, $messages];
        }, 3);
    }

    public function send(Community $community, CommunityConversation $conversation, User $actor, string $body, string $clientSendId): void
    {
        abort_unless((int) $conversation->community_id === (int) $community->id, 404);
        Gate::forUser($actor)->authorize('sendMessage', $conversation);
        DB::transaction(function () use ($community, $conversation, $actor, $body, $clientSendId): void {
            $currCommunity = Community::whereKey($community->id)->lockForUpdate()->firstOrFail();
            abort_unless($currCommunity->isActive(), 404);

            $currConv = CommunityConversation::whereKey($conversation->id)
                ->where('community_id', $currCommunity->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($currConv->isParticipant($actor), 404);
            $this->requireLockedParticipants($currCommunity, [$currConv->user_one_id, $currConv->user_two_id]);

            // Idempotency check with client_send_id
            $existing = CommunityMessage::where('conversation_id', $currConv->id)
                ->where('sender_id', $actor->id)
                ->where('client_send_id', $clientSendId)
                ->first();

            if ($existing !== null) {
                return;
            }

            CommunityMessage::create([
                'conversation_id' => $currConv->id,
                'sender_id' => $actor->id,
                'body' => $body,
                'client_send_id' => $clientSendId,
            ]);

            $currConv->forceFill(['last_message_at' => now('UTC')])->save();
        }, 3);
    }

    /** Call only after locking the current community, then conversation if present. */
    private function requireLockedParticipants(Community $community, array $participantIds): void
    {
        abort_unless($community->isActive(), 404);
        $memberships = $community->memberships()->whereIn('user_id', $participantIds)
            ->orderBy('user_id')->lockForUpdate()->get()->keyBy('user_id');
        foreach ($participantIds as $id) {
            if ((int) $id === (int) $community->creator_id) {
                continue;
            }
            $membership = $memberships->get($id);
            $membership?->setRelation('community', $community);
            abort_unless($membership?->hasEffectiveAccess() ?? false, 404);
        }
    }
}
