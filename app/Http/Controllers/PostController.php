<?php

namespace App\Http\Controllers;

use App\Actions\CommunityContent\FeedMutation;
use App\Models\Community;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Display a listing of posts for the community feed.
     */
    public function index(Request $request, Community $community): View
    {
        Gate::authorize('viewAny', [Post::class, $community]);

        $currentCategorySlug = $request->query('category');
        $currentSort = $request->query('sort', 'newest');

        if ($currentSort !== null && ! in_array($currentSort, ['newest', 'most_commented'], true)) {
            throw ValidationException::withMessages([
                'sort' => 'The selected sort is invalid.',
            ]);
        }

        $query = $community->posts()
            ->with([
                'author:id,name',
                'category',
            ])
            ->withCount('comments');

        if (is_string($currentCategorySlug) && $currentCategorySlug !== '' && $currentCategorySlug !== 'all') {
            if ($currentCategorySlug === 'uncategorized') {
                $query->whereNull('category_id');
            } else {
                $category = $community->categories()->where('slug', $currentCategorySlug)->first();
                if (! $category) {
                    throw ValidationException::withMessages([
                        'category' => 'The selected category does not exist in this community.',
                    ]);
                }
                $query->where('category_id', $category->id);
            }
        }

        // Pinned posts always appear first, followed by requested sort with id tie-breaker
        $query->orderByRaw('pinned_at IS NULL, pinned_at DESC');

        if ($currentSort === 'most_commented') {
            $query->orderByDesc('comments_count')
                ->orderByDesc('created_at')
                ->orderByDesc('id');
        } else {
            $query->orderByDesc('created_at')
                ->orderByDesc('id');
        }

        $posts = $query->paginate(15)->withQueryString();
        $categories = $community->categories()->get();

        return view('communities.posts.index', [
            'community' => $community,
            'posts' => $posts,
            'categories' => $categories,
            'currentCategory' => $currentCategorySlug,
            'currentSort' => $currentSort,
            'isCreator' => $community->isCreator($request->user()),
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
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('post_categories', 'id')->where('community_id', $community->id),
            ],
        ]);

        if (! empty($validated['category_id'])) {
            $category = $community->categories()->findOrFail($validated['category_id']);
            if ($category->isAnnouncements() && ! $community->isCreator($request->user())) {
                throw ValidationException::withMessages([
                    'category_id' => 'Only the community creator can post in Announcements.',
                ]);
            }
        }

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

        $post->load([
            'author:id,name',
            'category',
        ]);

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

        $categories = $community->categories()->get();

        return view('communities.posts.edit', [
            'community' => $community,
            'post' => $post,
            'categories' => $categories,
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
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('post_categories', 'id')->where('community_id', $community->id),
            ],
        ]);

        if (! empty($validated['category_id'])) {
            $category = $community->categories()->findOrFail($validated['category_id']);
            if ($category->isAnnouncements() && ! $community->isCreator($request->user())) {
                throw ValidationException::withMessages([
                    'category_id' => 'Only the community creator can post in Announcements.',
                ]);
            }
        }

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

    /**
     * Pin the specified post (creator only, idempotent).
     */
    public function pin(Request $request, Community $community, Post $post): RedirectResponse
    {
        if (! $community->isActive()) {
            abort(404);
        }

        if (! $community->isCreator($request->user())) {
            abort(403);
        }

        app(FeedMutation::class)->run($request->user(), $community, $post->id, null, function (Community $current, Post $currentPost) use ($request): void {
            if (! $current->isActive()) {
                abort(404);
            }

            if (! $current->isCreator($request->user())) {
                abort(403);
            }

            if (! $currentPost->pinned_at) {
                $currentPost->update(['pinned_at' => now()]);
            }
        });

        return back()->with('status', 'post-pinned');
    }

    /**
     * Unpin the specified post (creator only, idempotent).
     */
    public function unpin(Request $request, Community $community, Post $post): RedirectResponse
    {
        if (! $community->isActive()) {
            abort(404);
        }

        if (! $community->isCreator($request->user())) {
            abort(403);
        }

        app(FeedMutation::class)->run($request->user(), $community, $post->id, null, function (Community $current, Post $currentPost) use ($request): void {
            if (! $current->isActive()) {
                abort(404);
            }

            if (! $current->isCreator($request->user())) {
                abort(403);
            }

            if ($currentPost->pinned_at) {
                $currentPost->update(['pinned_at' => null]);
            }
        });

        return back()->with('status', 'post-unpinned');
    }
}
