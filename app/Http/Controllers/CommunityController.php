<?php

namespace App\Http\Controllers;

use App\Models\Community;
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
        $communities = $request->user()->createdCommunities()->orderByDesc('id')->paginate(12);

        return view('communities.index', [
            'communities' => $communities,
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
        ]);

        try {
            /** @var Community $community */
            $community = $request->user()->createdCommunities()->create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?? null,
            ]);
        } catch (UniqueConstraintViolationException) {
            // The slug is the only client-controlled unique key on this insert.
            throw ValidationException::withMessages(['slug' => 'The slug is not available.']);
        }

        return redirect()->route('communities.show', $community)->with('status', 'community-created');
    }

    /**
     * Display the specified community dashboard.
     */
    public function show(Community $community): View
    {
        Gate::authorize('view', $community);

        return view('communities.show', [
            'community' => $community,
        ]);
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
        ]);

        $community->update($validated);

        return redirect()->route('communities.show', $community)->with('status', 'community-updated');
    }
}
