<?php

namespace App\Services;

use App\Actions\CommunityContent\FeedMutation;
use App\Models\Community;
use App\Models\Post;
use App\Models\PostPoll;
use App\Models\PostPollVote;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PostPolls
{
    public function vote(User $actor, Community $community, int $postId, int $optionId): void
    {
        app(FeedMutation::class)->run($actor, $community, $postId, null, function (Community $current, Post $post) use ($actor, $optionId): void {
            Gate::forUser($actor)->authorize('view', $post);
            $poll = $post->poll()->lockForUpdate()->firstOrFail();
            if ($poll->closed_at !== null) {
                throw ValidationException::withMessages(['poll' => __('This poll is closed.')]);
            }
            abort_unless($poll->options()->whereKey($optionId)->exists(), 404);
            PostPollVote::updateOrCreate(
                ['post_poll_id' => $poll->id, 'user_id' => $actor->id],
                ['post_poll_option_id' => $optionId],
            );
        });
    }

    public function close(User $actor, Community $community, int $postId): void
    {
        app(FeedMutation::class)->run($actor, $community, $postId, null, function (Community $current, Post $post) use ($actor): void {
            Gate::forUser($actor)->authorize('delete', $post);
            $poll = $post->poll()->lockForUpdate()->firstOrFail();
            if ($poll->closed_at === null) {
                $poll->update(['closed_at' => now()]);
            }
        });
    }

    public static function snapshot(PostPoll $poll, User $actor): array
    {
        $counts = $poll->votes()->selectRaw('post_poll_option_id, COUNT(*) AS aggregate')->groupBy('post_poll_option_id')->pluck('aggregate', 'post_poll_option_id');
        $total = (int) $counts->sum();

        return [
            'options' => $poll->options->map(fn ($option) => [
                'id' => $option->id, 'label' => $option->label,
                'count' => (int) ($counts[$option->id] ?? 0),
                'percent' => $total ? round((int) ($counts[$option->id] ?? 0) * 100 / $total) : 0,
            ])->all(),
            'total' => $total, 'closed' => $poll->closed_at !== null,
            'selected' => $poll->votes()->where('user_id', $actor->id)->value('post_poll_option_id'),
        ];
    }
}
