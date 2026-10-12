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
        $communities = Community::query()->accessibleTo($request->user())->orderByDesc('id')->paginate(12);

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
            'access_mode' => ['sometimes', 'required', Rule::in(['FREE', 'PAID'])],
            'price_vnd' => ['nullable', 'required_if:access_mode,PAID', 'integer', 'min:1', 'max:4294967295'],
        ]);

        $mode = $validated['access_mode'] ?? $community->access_mode;
        $price = $mode === 'PAID' ? ($validated['price_vnd'] ?? $community->price_vnd) : null;
        if ($mode === 'PAID' && ! $price) {
            throw ValidationException::withMessages(['price_vnd' => 'Set a positive whole-VND price for paid access.']);
        }
        $community->fill(array_intersect_key($validated, array_flip(['name', 'description'])));
        $community->forceFill(['access_mode' => $mode, 'price_vnd' => $price])->save();

        return redirect()->route('communities.show', $community)->with('status', 'community-updated');
    }
}
