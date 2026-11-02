<?php

namespace App\Http\Controllers;

use App\Actions\CommunityContent\FeedMutation;
use App\Models\Comment;
use App\Models\Community;
use App\Models\Post;
use App\Services\SocialAttachments;
use App\Services\SocialPublishing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CommentController extends Controller
{
    /**
     * Store a newly created comment in storage.
     */
    public function store(Request $request, Community $community, Post $post): RedirectResponse
    {
        Gate::authorize('create', [Comment::class, $post]);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'content_format' => ['sometimes', 'in:plain,markdown'],
            'parent_id' => ['nullable', 'integer', 'min:1'],
            ...SocialAttachments::rules(),
        ]);
        app(SocialPublishing::class)->comment(
            $request->user(), $community, $post->id, $data,
            $request->file('images', []), $request->file('documents', []),
        );

        return redirect()->route('communities.posts.show', [$community, $post])->with('status', 'comment-created');
    }

    /**
     * Remove the specified comment from storage (soft delete).
     */
    public function edit(Community $community, Post $post, Comment $comment): View
    {
        Gate::authorize('update', $comment);

        return view('communities.posts.comments.edit', compact('community', 'post', 'comment'));
    }

    public function update(Request $request, Community $community, Post $post, Comment $comment): RedirectResponse
    {
        Gate::authorize('update', $comment);
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'content_format' => ['sometimes', 'in:plain,markdown'],
            ...SocialAttachments::rules(),
        ]);
        unset($validated['images'], $validated['documents']);
        $media = app(SocialAttachments::class);
        $staged = $media->stage($request->file('images', []), $request->file('documents', []));
        try {
            app(FeedMutation::class)->run($request->user(), $community, $post->id, $comment->id, function (Community $current, Post $currentPost, Comment $currentComment) use ($request, $validated, $media, $staged): void {
                Gate::authorize('update', $currentComment);
                $currentComment->update($validated);
                $media->publish($staged, $currentPost, $request->user(), $currentComment);
            });
        } catch (\Throwable $exception) {
            $media->discard($staged);
            throw $exception;
        }

        return redirect()->route('communities.posts.show', [$community, $post])->with('status', 'comment-updated');
    }

    public function destroy(Request $request, Community $community, Post $post, Comment $comment): RedirectResponse
    {
        Gate::authorize('delete', $comment);

        app(FeedMutation::class)->run($request->user(), $community, $post->id, $comment->id, function (Community $current, Post $currentPost, Comment $currentComment): void {
            Gate::authorize('delete', $currentComment);
            $currentComment->delete();
        });

        return redirect()->route('communities.posts.show', [$community, $post])->with('status', 'comment-deleted');
    }
}
