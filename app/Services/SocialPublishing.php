<?php

namespace App\Services;

use App\Actions\CommunityContent\FeedMutation;
use App\Jobs\SendPostReplyNotification;
use App\Models\Comment;
use App\Models\Community;
use App\Models\OnboardingStepCompletion;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SocialPublishing
{
    public function comment(User $actor, Community $community, int $postId, array $data, array $images = [], array $documents = []): Comment
    {
        $attributes = Validator::make($data, [
            'body' => ['required', 'string', 'max:2000'],
            'content_format' => ['sometimes', 'in:plain,markdown'],
            'parent_id' => ['nullable', 'integer', 'min:1'],
        ])->validate();
        $staged = app(SocialAttachments::class)->stage($images, $documents);
        try {
            return app(FeedMutation::class)->run($actor, $community, $postId, null, function (Community $current, Post $post) use ($actor, $attributes, $staged): Comment {
                Gate::forUser($actor)->authorize('create', [Comment::class, $post]);
                if (! empty($attributes['parent_id'])) {
                    $parent = $post->comments()->whereKey($attributes['parent_id'])->lockForUpdate()->first();
                    if (! $parent || $parent->parent_id !== null) {
                        throw ValidationException::withMessages(['parent_id' => __('Reply to an available top-level comment.')]);
                    }
                }
                $comment = $post->comments()->create(['user_id' => $actor->id, ...$attributes]);
                app(SocialAttachments::class)->publish($staged, $post, $actor, $comment);
                if ((int) $post->user_id !== (int) $actor->id) {
                    SendPostReplyNotification::dispatch($comment->id)->afterCommit();
                }
                OnboardingStepCompletion::markStep($current, $actor, 'comment');

                return $comment;
            });
        } catch (\Throwable $exception) {
            app(SocialAttachments::class)->discard($staged);
            throw $exception;
        }
    }
}
