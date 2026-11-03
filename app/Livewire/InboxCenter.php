<?php

namespace App\Livewire;

use App\Models\AppNotification;
use App\Models\Community;
use App\Models\CommunityConversation;
use App\Models\CommunityMessage;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class InboxCenter extends Component
{
    #[Locked]
    public int $communityId;

    #[Locked]
    public ?int $selectedConversationId = null;

    #[Locked]
    public array $notices = [];

    #[Locked]
    public array $threads = [];

    #[Locked]
    public int $notificationCount = 0;

    #[Locked]
    public int $messageCount = 0;

    #[Locked]
    public bool $available = true;

    #[Locked]
    public array $announcedNoticeIds = [];

    #[Locked]
    public int $latestMessageId = 0;

    public function mount(Community $community): void
    {
        $this->communityId = $community->id;
        $this->refreshInbox(false);
    }

    private function community(): ?Community
    {
        $actor = auth()->user();
        if (! $actor || ! $actor->hasVerifiedEmail()) {
            return null;
        }

        return Community::query()->active()->accessibleTo($actor)->find($this->communityId);
    }

    #[On('inbox-refresh')]
    public function refreshInbox(bool $announce = true): void
    {
        $community = $this->community();
        $this->available = $community !== null;
        if (! $community) {
            $this->notices = $this->threads = [];
            $this->notificationCount = $this->messageCount = 0;
            $this->selectedConversationId = null;

            return;
        }

        $actor = auth()->user();
        $notifications = AppNotification::query()->visibleFor($actor, $community)->latest()->orderByDesc('id')->limit(8)->get();
        $previousNotificationCount = $this->notificationCount;
        $this->notificationCount = AppNotification::unreadCountFor($actor, $community);
        $this->notices = $notifications->map(fn ($notice) => [
            'id' => $notice->id, 'summary' => $notice->summary(), 'unread' => $notice->read_at === null,
            'time' => $notice->created_at->diffForHumans(),
            'url' => route('communities.notifications.follow', [$community, $notice]),
        ])->all();

        $visible = CommunityConversation::query()->visibleTo($actor, $community);
        $unread = CommunityMessage::query()->whereIn('conversation_id', (clone $visible)->select('id'))
            ->where('sender_id', '!=', $actor->id)->whereNull('read_at');
        $this->messageCount = (clone $unread)->count();
        $messageId = (int) ((clone $unread)->max('id') ?? 0);
        $noticeIds = $notifications->whereNull('read_at')->pluck('id')->all();
        if ($announce && (($messageId > $this->latestMessageId && $messageId > 0)
            || ($this->notificationCount > $previousNotificationCount && array_diff($noticeIds, $this->announcedNoticeIds)))) {
            $this->dispatch('inbox-arrived', message: $messageId > $this->latestMessageId && $messageId > 0);
        }
        $this->latestMessageId = max($this->latestMessageId, $messageId);
        $this->announcedNoticeIds = $noticeIds;

        $this->threads = (clone $visible)->with(['userOne', 'userTwo', 'latestMessage'])
            ->withCount(['messages as unread_count' => fn ($q) => $q->where('sender_id', '!=', $actor->id)->whereNull('read_at')])
            ->orderByDesc('last_message_at')->orderByDesc('id')->limit(8)->get()
            ->map(function ($thread) use ($actor) {
                $other = $thread->getOtherParticipant($actor);

                return ['id' => $thread->id, 'name' => $other->name,
                    'initial' => mb_substr($other->name, 0, 1), 'body' => $thread->latestMessage?->body ?? '',
                    'time' => $thread->latestMessage?->created_at->diffForHumans() ?? '',
                    'unread' => $thread->unread_count];
            })->all();
        if ($this->selectedConversationId !== null && ! (clone $visible)->whereKey($this->selectedConversationId)->exists()) {
            $this->selectedConversationId = null;
        }
    }

    public function openConversation(int $id): void
    {
        $community = $this->community();
        abort_unless($community, 404);
        abort_unless(CommunityConversation::query()->visibleTo(auth()->user(), $community)->whereKey($id)->exists(), 404);
        $this->selectedConversationId = $id;
    }

    public function backToInbox(): void
    {
        $this->selectedConversationId = null;
        $this->refreshInbox(false);
    }

    public function markAllRead(): void
    {
        $community = $this->community();
        abort_unless($community, 404);
        AppNotification::query()->visibleFor(auth()->user(), $community)->whereNull('read_at')->update(['read_at' => now('UTC')]);
        $this->refreshInbox(false);
    }

    public function render()
    {
        $community = $this->community();

        return view('livewire.inbox-center', [
            'community' => $community,
            'activeConversation' => $community && $this->selectedConversationId !== null
                ? CommunityConversation::query()->visibleTo(auth()->user(), $community)->find($this->selectedConversationId) : null,
        ]);
    }
}
