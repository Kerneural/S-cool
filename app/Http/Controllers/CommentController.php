<?php

namespace App\Http\Controllers;

use App\Actions\CommunityContent\FeedMutation;
use App\Models\Comment;
use App\Models\Community;
use App\Models\Post;
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

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        app(FeedMutation::class)->run($request->user(), $community, $post->id, null, function (Community $current, Post $currentPost) use ($request, $validated): void {
            Gate::authorize('create', [Comment::class, $currentPost]);
            $currentPost->comments()->create(['user_id' => $request->user()->id, 'body' => $validated['body']]);
        });

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
        $validated = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        app(FeedMutation::class)->run($request->user(), $community, $post->id, $comment->id, function (Community $current, Post $currentPost, Comment $currentComment) use ($validated): void {
            Gate::authorize('update', $currentComment);
            $currentComment->update($validated);
        });

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
