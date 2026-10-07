<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Community;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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

        $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return redirect()->route('communities.posts.show', [$community, $post])->with('status', 'comment-created');
    }

    /**
     * Remove the specified comment from storage (soft delete).
     */
    public function destroy(Community $community, Post $post, Comment $comment): RedirectResponse
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        return redirect()->route('communities.posts.show', [$community, $post])->with('status', 'comment-deleted');
    }
}
