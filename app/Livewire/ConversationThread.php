<?php

namespace App\Livewire;

use App\Models\Community;
use App\Models\CommunityConversation;
use App\Services\CommunityMessaging;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class ConversationThread extends Component
{
    use WithPagination;

    #[Locked]
    public int $communityId;

    #[Locked]
    public int $conversationId;

    #[Locked]
    public string $clientSendId;

    public string $body = '';

    public function mount(Community $community, CommunityConversation $conversation): void
    {
        $this->communityId = $community->id;
        $this->conversationId = $conversation->id;
        $retryId = old('client_send_id');
        $this->clientSendId = is_string($retryId) && trim($retryId) !== '' && Str::length($retryId) <= 64
            ? $retryId : (string) Str::uuid();
        $draft = old('body', '');
        $this->body = is_string($draft) ? $draft : '';
        foreach (session('errors')?->get('client_send_id') ?? [] as $error) {
            $this->addError('client_send_id', $error);
        }
    }

    public function send(): void
    {
        $this->validate(['body' => ['required', 'string', 'min:1', 'max:2000']]);
        $body = trim($this->body);
        if ($body === '') {
            $this->addError('body', __('Message cannot be empty.'));

            return;
        }
        $key = 'conversation-send:'.auth()->id();
        if (RateLimiter::tooManyAttempts($key, 30)) {
            $this->addError('body', 'Bạn gửi quá nhanh. Chờ một phút rồi thử lại.');

            return;
        }
        RateLimiter::hit($key, 60);
        app(CommunityMessaging::class)->send(
            Community::findOrFail($this->communityId),
            CommunityConversation::findOrFail($this->conversationId),
            auth()->user(), $body, $this->clientSendId,
        );
        $this->body = '';
        $this->clientSendId = (string) Str::uuid();
        $this->resetPage();
        $this->dispatch('conversation-sent');
    }

    public function render()
    {
        [$community, $conversation, $messages] = app(CommunityMessaging::class)->snapshot(
            Community::findOrFail($this->communityId),
            CommunityConversation::findOrFail($this->conversationId),
            auth()->user(),
        );

        return view('livewire.conversation-thread', [
            'community' => $community, 'conversation' => $conversation,
            'messages' => $messages, 'otherUser' => $conversation->getOtherParticipant(auth()->user()),
        ]);
    }
}
