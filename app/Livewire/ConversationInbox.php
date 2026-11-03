<?php

namespace App\Livewire;

use App\Models\Community;
use App\Services\CommunityMessaging;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ConversationInbox extends Component
{
    #[Locked]
    public int $communityId;

    #[Locked]
    public int $page = 1;

    public function mount(Community $community): void
    {
        $this->communityId = $community->id;
        $this->page = max(1, (int) filter_var(request()->query('page', 1), FILTER_VALIDATE_INT));
    }

    public function render()
    {
        return view('livewire.conversation-inbox', app(CommunityMessaging::class)->inbox(
            Community::findOrFail($this->communityId), auth()->user(), $this->page,
        ));
    }
}
