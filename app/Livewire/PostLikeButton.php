<?php

namespace App\Livewire;

use App\Actions\CommunityContent\FeedMutation;
use App\Models\Community;
use App\Models\PointContribution;
use App\Models\Post;
use App\Models\PostLike;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PostLikeButton extends Component
{
    #[Locked]
    public int $communityId;

    #[Locked]
    public string $communitySlug;

    #[Locked]
    public int $postId;

    public int $likesCount = 0;

    public bool $isLiked = false;

    public bool $isOwnPost = false;

    public ?string $errorMessage = null;

    public function mount(Community $community, Post $post): void
    {
        $this->communityId = $community->id;
        $this->communitySlug = $community->slug;
        $this->postId = $post->id;

        $user = auth()->user();
        $this->isOwnPost = $user && (int) $post->user_id === (int) $user->id;
        $this->likesCount = $post->active_likes_count ?? $post->activeLikes()->count();
        $this->isLiked = $user ? $post->isLikedBy($user) : false;
    }

    /**
     * @return array{isLiked: bool, likesCount: int, errorMessage: string|null}
     */
    public function toggleLike(): array
    {
        $user = auth()->user();
        if (! $user) {
            $this->errorMessage = __('Please sign in to like posts.');

            return $this->state();
        }

        $prevLiked = $this->isLiked;
        $prevCount = $this->likesCount;

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

                if ((int) $post->user_id === (int) $user->id) {
                    $this->errorMessage = __('You cannot like your own post.');

                    return;
                }

                $like = PostLike::query()
                    ->where('post_id', $post->id)
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if ($like) {
                    $newActive = ! $like->is_active;
                    $like->update(['is_active' => $newActive]);

                    PointContribution::where('source_type', 'post_like')
                        ->where('source_id', $like->id)
                        ->update(['is_active' => $newActive]);

                    $this->isLiked = $newActive;
                } else {
                    $like = PostLike::create([
                        'community_id' => $currentCommunity->id,
                        'post_id' => $post->id,
                        'user_id' => $user->id,
                        'is_active' => true,
                    ]);

                    PointContribution::updateOrCreate(
                        [
                            'source_type' => 'post_like',
                            'source_id' => $like->id,
                        ],
                        [
                            'community_id' => $currentCommunity->id,
                            'beneficiary_id' => $post->user_id,
                            'actor_id' => $user->id,
                            'points' => 1,
                            'is_active' => true,
                            'contributed_at' => $like->created_at ?? now(),
                        ]
                    );

                    $this->isLiked = true;
                }

                $this->likesCount = $post->activeLikes()->count();
                $this->errorMessage = null;
            });
        } catch (\Throwable $e) {
            report($e);
            $this->isLiked = $prevLiked;
            $this->likesCount = $prevCount;
            $this->errorMessage = 'Unable to update like. Please try again.';
        }

        return $this->state();
    }

    /**
     * @return array{isLiked: bool, likesCount: int, errorMessage: string|null}
     */
    private function state(): array
    {
        return [
            'isLiked' => $this->isLiked,
            'likesCount' => $this->likesCount,
            'errorMessage' => $this->errorMessage,
        ];
    }

    public function render(): View
    {
        return view('livewire.post-like-button');
    }
}
