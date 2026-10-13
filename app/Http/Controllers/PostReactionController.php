<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Post;
use App\Models\PostBookmark;
use App\Models\PostLike;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PostReactionController extends Controller
{
    /**
     * Authorize user access to active community and target post.
     */
    protected function authorizeAccess(User $user, Community $community, ?Post $post = null): void
    {
        if (! $community->isActive()) {
            abort(404);
        }

        $isCreator = $community->isCreator($user);
        $isMember = $community->memberships()->where('user_id', $user->id)->where('status', 'ACTIVE')->exists();

        if (! $isCreator && ! $isMember) {
            abort(404);
        }

        if ($post !== null) {
            if ((int) $post->community_id !== (int) $community->id || $post->trashed()) {
                abort(404);
            }
        }
    }

    /**
     * Like a post (idempotent, no self-like).
     */
    public function like(Request $request, Community $community, Post $post): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeAccess($user, $community, $post);

        if ((int) $post->user_id === (int) $user->id) {
            throw ValidationException::withMessages([
                'like' => 'You cannot like your own post.',
            ]);
        }

        DB::transaction(function () use ($user, $community, $post) {
            $lockedCommunity = Community::query()->lockForUpdate()->findOrFail($community->id);
            $lockedPost = $lockedCommunity->posts()->lockForUpdate()->findOrFail($post->id);

            $this->authorizeAccess($user, $lockedCommunity, $lockedPost);

            $like = PostLike::query()
                ->where('post_id', $lockedPost->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($like) {
                if (! $like->is_active) {
                    $like->update(['is_active' => true]);
                }
            } else {
                PostLike::create([
                    'community_id' => $lockedCommunity->id,
                    'post_id' => $lockedPost->id,
                    'user_id' => $user->id,
                    'is_active' => true,
                ]);
            }
        });

        return back()->with('status', 'post-liked');
    }

    /**
     * Unlike a post (idempotent).
     */
    public function unlike(Request $request, Community $community, Post $post): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeAccess($user, $community, $post);

        DB::transaction(function () use ($user, $community, $post) {
            $lockedCommunity = Community::query()->lockForUpdate()->findOrFail($community->id);
            $lockedPost = $lockedCommunity->posts()->lockForUpdate()->findOrFail($post->id);

            $this->authorizeAccess($user, $lockedCommunity, $lockedPost);

            $like = PostLike::query()
                ->where('post_id', $lockedPost->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($like && $like->is_active) {
                $like->update(['is_active' => false]);
            }
        });

        return back()->with('status', 'post-unliked');
    }

    /**
     * Save a post to personal bookmarks (idempotent, self-bookmark allowed).
     */
    public function save(Request $request, Community $community, Post $post): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeAccess($user, $community, $post);

        DB::transaction(function () use ($user, $community, $post) {
            $lockedCommunity = Community::query()->lockForUpdate()->findOrFail($community->id);
            $lockedPost = $lockedCommunity->posts()->lockForUpdate()->findOrFail($post->id);

            $this->authorizeAccess($user, $lockedCommunity, $lockedPost);

            PostBookmark::firstOrCreate(
                [
                    'post_id' => $lockedPost->id,
                    'user_id' => $user->id,
                ],
                [
                    'community_id' => $lockedCommunity->id,
                ]
            );
        });

        return back()->with('status', 'post-saved');
    }

    /**
     * Remove a post from personal bookmarks (idempotent).
     */
    public function unsave(Request $request, Community $community, Post $post): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeAccess($user, $community, $post);

        DB::transaction(function () use ($user, $community, $post) {
            $lockedCommunity = Community::query()->lockForUpdate()->findOrFail($community->id);
            $lockedPost = $lockedCommunity->posts()->lockForUpdate()->findOrFail($post->id);

            $this->authorizeAccess($user, $lockedCommunity, $lockedPost);

            PostBookmark::query()
                ->where('post_id', $lockedPost->id)
                ->where('user_id', $user->id)
                ->delete();
        });

        return back()->with('status', 'post-unsaved');
    }

    /**
     * Display the authenticated user's personal saved posts within the community.
     */
    public function saved(Request $request, Community $community): View
    {
        $user = $request->user();
        $this->authorizeAccess($user, $community);

        $posts = $community->posts()
            ->whereHas('bookmarks', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with([
                'author:id,name',
                'category',
                'likes' => fn ($q) => $q->where('is_active', true)->where('user_id', $user->id),
                'bookmarks' => fn ($q) => $q->where('user_id', $user->id),
            ])
            ->withCount([
                'comments',
                'likes as active_likes_count' => fn ($q) => $q->where('is_active', true),
            ])
            ->orderByDesc('id')
            ->paginate(15);

        return view('communities.saved', [
            'community' => $community,
            'posts' => $posts,
        ]);
    }
}
