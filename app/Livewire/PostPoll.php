<?php

namespace App\Livewire;

use App\Models\Community;
use App\Models\Post;
use App\Services\PostPolls;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class PostPoll extends Component
{
    #[Locked]
    public int $communityId;

    #[Locked]
    public int $postId;

    public function mount(Community $community, Post $post): void
    {
        $this->communityId = $community->id;
        $this->postId = $post->id;
    }

    public function vote(int $optionId): void
    {
        app(PostPolls::class)->vote(auth()->user(), Community::findOrFail($this->communityId), $this->postId, $optionId);
        $this->dispatch('social-poll-updated.'.$this->postId);
    }

    public function close(): void
    {
        app(PostPolls::class)->close(auth()->user(), Community::findOrFail($this->communityId), $this->postId);
        $this->dispatch('social-poll-updated.'.$this->postId);
    }

    #[On('social-poll-updated.{postId}')]
    public function refreshPoll(): void {}

    public function render()
    {
        $community = Community::findOrFail($this->communityId);
        $post = $community->posts()->findOrFail($this->postId);
        Gate::authorize('view', $post);
        $poll = $post->poll()->with('options')->first();
        $snapshot = $poll ? PostPolls::snapshot($poll, auth()->user()) : null;

        return view('livewire.post-poll', compact('community', 'post', 'snapshot'));
    }
}
