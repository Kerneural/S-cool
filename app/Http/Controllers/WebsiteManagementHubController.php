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

        $communities = $user->createdCommunities()
            ->withCount(['memberships' => fn ($q) => CommunityMembership::applyEffectiveAccess($q)])
            ->with(['courses' => function ($query): void {
                $query->orderBy('order')
                    ->orderBy('id');
            }])
            ->orderBy('name')
            ->get();

        return view('website-hub.index', [
            'communities' => $communities,
        ]);
    }
}
