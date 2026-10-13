<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\PostCategory;
use App\Services\VideoEmbedService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CommunityController extends Controller
{
    /**
     * Display a listing of the user's communities.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $rawFilter = $request->query('filter', 'all');
        $filter = is_string($rawFilter) && in_array($rawFilter, ['all', 'owned', 'joined'], true) ? $rawFilter : 'all';

        $query = Community::query();

        if ($filter === 'owned') {
            $query->where('creator_id', $user->id);
        } elseif ($filter === 'joined') {
            $query->where('status', 'ACTIVE')
                ->where('creator_id', '!=', $user->id)
                ->whereHas('memberships', function ($q) use ($user): void {
                    $q->where('user_id', $user->id)->where('status', 'ACTIVE');
                });
        } else {
            $query->accessibleTo($user);
        }

        $rawQ = $request->query('q');
        $search = is_string($rawQ) ? trim(mb_substr($rawQ, 0, 100)) : '';
        if ($search !== '') {
            $escaped = addcslashes($search, '%_\\');
            $query->where('name', 'like', "%{$escaped}%");
        }

        $communities = $query->distinct()->orderByDesc('id')->paginate(12)->withQueryString();

        return view('communities.index', [
            'communities' => $communities,
            'currentFilter' => $filter,
            'searchQuery' => $search,
        ]);
    }

    /**
     * Show the form for creating a new community.
     */
    public function create(): View
    {
        Gate::authorize('create', Community::class);

        return view('communities.create');
    }

    /**
     * Store a newly created community in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Community::class);

        if (is_string($request->input('slug'))) {
            $request->merge(['slug' => strtolower($request->input('slug'))]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['bail', 'required', 'string', 'max:255', 'alpha_dash:ascii', Rule::notIn(['create']), 'unique:communities,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
            'access_mode' => ['sometimes', 'required', Rule::in(['FREE', 'PAID'])],
            'price_vnd' => ['nullable', 'required_if:access_mode,PAID', 'integer', 'min:1', 'max:4294967295'],
        ]);

        try {
            /** @var Community $community */
            $community = $request->user()->createdCommunities()->make([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?? null,
            ]);
            $mode = $validated['access_mode'] ?? 'FREE';
            $community->forceFill(['access_mode' => $mode, 'price_vnd' => $mode === 'PAID' ? $validated['price_vnd'] : null])->save();
            PostCategory::seedDefaultsFor($community);
        } catch (UniqueConstraintViolationException) {
            // The slug is the only client-controlled unique key on this insert.
            throw ValidationException::withMessages(['slug' => 'The slug is not available.']);
        }

        return redirect()->route('communities.show', $community)->with('status', 'community-created');
    }

    /**
     * Display the specified community dashboard/about view.
     */
    public function show(Community $community): View
    {
        Gate::authorize('view', $community);

        $activeMemberCount = $community->memberships()->where('status', 'ACTIVE')->count();

        return view('communities.show', [
            'community' => $community,
            'activeMemberCount' => $activeMemberCount,
        ]);
    }

    /**
     * Dedicated About view action for the community.
     */
    public function about(Community $community): View
    {
        return $this->show($community);
    }

    /**
     * Show the form for editing the specified community.
     */
    public function edit(Community $community): View
    {
        Gate::authorize('update', $community);

        return view('communities.edit', [
            'community' => $community,
        ]);
    }

    /**
     * Update the specified community in storage.
     */
    public function update(Request $request, Community $community): RedirectResponse
    {
        Gate::authorize('update', $community);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'rules' => ['nullable', 'string', 'max:5000'],
            'intro_video_url' => ['nullable', 'string', 'max:500'],
            'access_mode' => ['sometimes', 'required', Rule::in(['FREE', 'PAID'])],
            'price_vnd' => ['nullable', 'required_if:access_mode,PAID', 'integer', 'min:1', 'max:4294967295'],
        ]);

        if (! empty($validated['intro_video_url']) && ! VideoEmbedService::isValid($validated['intro_video_url'])) {
            throw ValidationException::withMessages([
                'intro_video_url' => 'Please provide a valid YouTube or Vimeo URL.',
            ]);
        }

        $mode = $validated['access_mode'] ?? $community->access_mode;
        $price = $mode === 'PAID' ? ($validated['price_vnd'] ?? $community->price_vnd) : null;
        if ($mode === 'PAID' && ! $price) {
            throw ValidationException::withMessages(['price_vnd' => 'Set a positive whole-VND price for paid access.']);
        }
        $community->fill(array_intersect_key($validated, array_flip(['name', 'description', 'rules', 'intro_video_url'])));
        $community->forceFill(['access_mode' => $mode, 'price_vnd' => $price])->save();

        return redirect()->route('communities.show', $community)->with('status', 'community-updated');
    }
}
