<?php

namespace App\Http\Controllers;

use App\Actions\CommunityContent\FeedMutation;
use App\Models\Community;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Display a listing of posts for the community feed.
     */
    public function index(Request $request, Community $community): View
    {
        Gate::authorize('viewAny', [Post::class, $community]);

        $posts = $community->posts()
            ->with('author:id,name')
            ->withCount('comments')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('communities.posts.index', [
            'community' => $community,
            'posts' => $posts,
        ]);
    }

    /**
     * Store a newly created post in storage.
     */
    public function store(Request $request, Community $community): RedirectResponse
    {
        Gate::authorize('create', [Post::class, $community]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
        ]);

        app(FeedMutation::class)->run($request->user(), $community, null, null, function (Community $current) use ($request, $validated): void {
            Gate::authorize('create', [Post::class, $current]);
            $current->posts()->create(['user_id' => $request->user()->id, ...$validated]);
        });

        return redirect()->route('communities.posts.index', $community)->with('status', 'post-created');
    }

    /**
     * Display the specified post.
     */
    public function show(Community $community, Post $post): View
    {
        Gate::authorize('view', $post);

        $post->load('author:id,name');
        $comments = $post->comments()->with('author:id,name')->orderBy('created_at')->orderBy('id')->paginate(20);
        $comments->each(fn ($comment) => $comment->setRelation('post', $post));

        return view('communities.posts.show', [
            'community' => $community,
            'post' => $post,
            'comments' => $comments,
        ]);
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Community $community, Post $post): View
    {
        Gate::authorize('update', $post);

        return view('communities.posts.edit', [
            'community' => $community,
            'post' => $post,
        ]);
    }

    /**
     * Update the specified post in storage.
     */
    public function update(Request $request, Community $community, Post $post): RedirectResponse
    {
        Gate::authorize('update', $post);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
        ]);

        app(FeedMutation::class)->run($request->user(), $community, $post->id, null, function (Community $current, Post $currentPost) use ($validated): void {
            Gate::authorize('update', $currentPost);
            $currentPost->update($validated);
        });

        return redirect()->route('communities.posts.index', $community)->with('status', 'post-updated');
    }

    /**
     * Remove the specified post from storage (soft delete).
     */
    public function destroy(Request $request, Community $community, Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);

        app(FeedMutation::class)->run($request->user(), $community, $post->id, null, function (Community $current, Post $currentPost): void {
            Gate::authorize('delete', $currentPost);
            $currentPost->delete();
        });

        return redirect()->route('communities.posts.index', $community)->with('status', 'post-deleted');
    }
}
