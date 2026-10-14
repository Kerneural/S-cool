<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityConversation;
use App\Models\CommunityMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CommunityMessageController extends Controller
{
    /**
     * Display the member's community-scoped inbox.
     */
    public function index(Request $request, Community $community): View
    {
        $actor = $request->user();
        abort_unless($community->isActive(), 404);

        $actorHasAccess = $community->isCreator($actor)
            || ($community->memberships()->where('user_id', $actor->id)->effective()->exists());

        abort_unless($actorHasAccess, 404);

        $visible = CommunityConversation::query()->visibleTo($actor, $community);
        $conversations = (clone $visible)
            ->with(['userOne', 'userTwo', 'latestMessage'])
            ->withCount(['messages as unread_count' => fn ($query) => $query
                ->where('sender_id', '!=', $actor->id)->whereNull('read_at')])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')->paginate(15);

        $totalUnread = CommunityMessage::whereIn('conversation_id', $visible->select('id'))
            ->where('sender_id', '!=', $actor->id)->whereNull('read_at')->count();

        return view('communities.messages.index', [
            'community' => $community,
            'conversations' => $conversations,
            'totalUnread' => $totalUnread,
        ]);
    }

    /**
     * Start or open a conversation with an eligible member.
     */
    public function start(Request $request, Community $community): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($community->isActive(), 404);

        $actorHasAccess = $community->isCreator($actor)
            || ($community->memberships()->where('user_id', $actor->id)->effective()->exists());

        abort_unless($actorHasAccess, 404);

        $validated = $request->validate([
            'recipient_id' => ['required', 'integer'],
        ]);

        $recipient = User::findOrFail($validated['recipient_id']);

        if ((int) $recipient->id === (int) $actor->id) {
            throw ValidationException::withMessages([
                'recipient_id' => 'You cannot message yourself.',
            ]);
        }

        $recipientHasAccess = $community->isCreator($recipient)
            || ($community->memberships()->where('user_id', $recipient->id)->effective()->exists());

        abort_unless($recipientHasAccess, 404);

        [$userOneId, $userTwoId] = CommunityConversation::canonicalParticipants($actor->id, $recipient->id);

        $conversation = DB::transaction(function () use ($community, $userOneId, $userTwoId): CommunityConversation {
            $currentCommunity = Community::whereKey($community->id)->lockForUpdate()->firstOrFail();
            $this->requireLockedParticipants($currentCommunity, [$userOneId, $userTwoId]);

            return CommunityConversation::firstOrCreate([
                'community_id' => $currentCommunity->id,
                'user_one_id' => $userOneId,
                'user_two_id' => $userTwoId,
            ]);
        }, 3);

        return redirect()->route('communities.messages.show', [$community, $conversation]);
    }

    /**
     * Display the paginated messages in a conversation.
     */
    public function show(Request $request, Community $community, CommunityConversation $conversation): View
    {
        abort_if((int) $conversation->community_id !== (int) $community->id, 404);
        Gate::authorize('view', $conversation);

        $actor = $request->user();

        [$community, $conversation, $messages] = DB::transaction(function () use ($community, $conversation, $actor): array {
            $currentCommunity = Community::whereKey($community->id)->lockForUpdate()->firstOrFail();
            $currentConversation = CommunityConversation::whereKey($conversation->id)
                ->where('community_id', $currentCommunity->id)->lockForUpdate()->firstOrFail();
            abort_unless($currentConversation->isParticipant($actor), 404);
            $this->requireLockedParticipants($currentCommunity, [$currentConversation->user_one_id, $currentConversation->user_two_id]);

            // Newest page first, chronological order within each page.
            $messages = $currentConversation->messages()->with('sender')->orderByDesc('id')->paginate(30);
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

        $otherUser = $conversation->getOtherParticipant($actor);

        return view('communities.messages.show', [
            'community' => $community,
            'conversation' => $conversation,
            'messages' => $messages,
            'otherUser' => $otherUser,
        ]);
    }

    /**
     * Send a plain-text message in the conversation.
     */
    public function store(Request $request, Community $community, CommunityConversation $conversation): RedirectResponse
    {
        abort_if((int) $conversation->community_id !== (int) $community->id, 404);
        Gate::authorize('sendMessage', $conversation);

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:2000'],
            'client_send_id' => ['required', 'string', 'min:1', 'max:64'],
        ]);

        $body = trim($validated['body']);
        if ($body === '') {
            throw ValidationException::withMessages([
                'body' => 'Message cannot be empty.',
            ]);
        }

        $actor = $request->user();
        $clientSendId = $validated['client_send_id'];

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

        return redirect()->route('communities.messages.show', [$community, $conversation]);
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
