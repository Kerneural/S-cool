<?php

namespace App\Livewire;

use App\Actions\CommunityContent\FeedMutation;
use App\Models\Community;
use App\Models\Post;
use App\Models\PostBookmark;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PostSaveButton extends Component
{
    #[Locked]
    public int $communityId;

    #[Locked]
    public string $communitySlug;

    #[Locked]
    public int $postId;

    public bool $isSaved = false;

    public ?string $errorMessage = null;

    public function mount(Community $community, Post $post): void
    {
        $this->communityId = $community->id;
        $this->communitySlug = $community->slug;
        $this->postId = $post->id;

        $user = auth()->user();
        $this->isSaved = $user ? $post->isBookmarkedBy($user) : false;
    }

    /**
     * @return array{isSaved: bool, errorMessage: string|null}
     */
    public function toggleSave(): array
    {
        $user = auth()->user();
        if (! $user) {
            $this->errorMessage = __('Please sign in to save posts.');

            return $this->state();
        }

        $prevSaved = $this->isSaved;

        try {
            $community = Community::findOrFail($this->communityId);

            app(FeedMutation::class)->run($user, $community, $this->postId, null, function (Community $currentCommunity, Post $post) use ($user): void {
                if (! $currentCommunity->isActive()) {
                    abort(404);
                }

                $isCreator = $currentCommunity->isCreator($user);
                $isMember = $currentCommunity->memberships()->where('user_id', $user->id)->effective()->exists();

                if (! $isCreator && ! $isMember) {
                    abort(404);
                }

                $bookmark = PostBookmark::query()
                    ->where('post_id', $post->id)
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if ($bookmark) {
                    $bookmark->delete();
                    $this->isSaved = false;
                } else {
                    PostBookmark::create([
                        'community_id' => $currentCommunity->id,
                        'post_id' => $post->id,
                        'user_id' => $user->id,
                    ]);
                    $this->isSaved = true;
                }

                $this->errorMessage = null;
            });

            $this->dispatch('post-bookmark-updated', postId: $this->postId, saved: $this->isSaved);
        } catch (\Throwable $e) {
            report($e);
            $this->isSaved = $prevSaved;
            $this->errorMessage = 'Unable to update bookmark. Please try again.';
        }

        return $this->state();
    }

    /**
     * @return array{isSaved: bool, errorMessage: string|null}
     */
    private function state(): array
    {
        return [
            'isSaved' => $this->isSaved,
            'errorMessage' => $this->errorMessage,
        ];
    }

    public function render(): View
    {
        return view('livewire.post-save-button');
    }
}
