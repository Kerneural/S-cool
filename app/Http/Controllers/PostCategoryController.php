<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\PostCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PostCategoryController extends Controller
{
    /**
     * Store a newly created post category for the community (creator only).
     */
    public function store(Request $request, Community $community): RedirectResponse
    {
        if (! $community->isActive()) {
            abort(404);
        }

        if (! $community->isCreator($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60'],
        ]);

        $name = trim($validated['name']);
        $slug = Str::slug($name);

        DB::transaction(function () use ($community, $name, $slug, $request) {
            $lockedCommunity = Community::query()->lockForUpdate()->findOrFail($community->id);

            if (! $lockedCommunity->isActive()) {
                abort(404);
            }

            if (! $lockedCommunity->isCreator($request->user())) {
                abort(403);
            }

            if ($slug === '' || $lockedCommunity->categories()->where('slug', $slug)->exists()) {
                throw ValidationException::withMessages([
                    'name' => 'A category with this name or slug already exists in this community.',
                ]);
            }

            $lockedCommunity->categories()->create([
                'name' => $name,
                'slug' => $slug,
            ]);
        });

        return redirect()->route('communities.posts.index', $community)->with('status', 'category-created');
    }

    /**
     * Remove the specified category from storage (creator only).
     * Reassigns posts in this category to uncategorized (null category_id).
     */
    public function destroy(Request $request, Community $community, PostCategory $category): RedirectResponse
    {
        if (! $community->isActive()) {
            abort(404);
        }

        if (! $community->isCreator($request->user()) || (int) $category->community_id !== (int) $community->id) {
            abort(403);
        }

        DB::transaction(function () use ($community, $category, $request) {
            $lockedCommunity = Community::query()->lockForUpdate()->findOrFail($community->id);

            if (! $lockedCommunity->isActive()) {
                abort(404);
            }

            if (! $lockedCommunity->isCreator($request->user())) {
                abort(403);
            }

            $lockedCategory = $lockedCommunity->categories()->lockForUpdate()->findOrFail($category->id);
            // Deleting a category sets its posts to uncategorized; posts and comments are preserved
            $lockedCategory->posts()->update(['category_id' => null]);
            $lockedCategory->delete();
        });

        return redirect()->route('communities.posts.index', $community)->with('status', 'category-deleted');
    }
}
