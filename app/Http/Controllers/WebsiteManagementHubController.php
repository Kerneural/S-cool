<?php

namespace App\Http\Controllers;

use App\Models\CommunityMembership;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteManagementHubController extends Controller
{
    /**
     * Display the website management hub for the verified deployment owner.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user && $user->isDeploymentOwner(), 403);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:ACTIVE,SUSPENDED,ARCHIVED'],
        ]);
        $communities = $user->createdCommunities()
            ->when($filters['q'] ?? '', fn ($q, $term) => $q->where('name', 'like', '%'.addcslashes($term, '%_'.chr(92)).'%'))
            ->when($filters['status'] ?? '', fn ($q, $status) => $q->where('status', $status))
            ->withCount(['memberships' => fn ($q) => CommunityMembership::applyEffectiveAccess($q)])
            ->orderBy('name')
            ->paginate(15)->withQueryString();

        return view('website-hub.index', [
            'communities' => $communities, 'filters' => $filters,
        ]);
    }
}
