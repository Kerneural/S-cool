<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityMembership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityMemberController extends Controller
{
    /**
     * Display a paginated listing of community members for the owning creator.
     */
    public function index(Request $request, Community $community): View
    {
        abort_unless($community->isActive() && $community->isCreator($request->user()), 404);

        $memberships = $community->memberships()
            ->with('user')
            ->orderByDesc('id')
            ->paginate(15);

        return view('communities.members.index', [
            'community' => $community,
            'memberships' => $memberships,
            'counts' => [
                'total' => $community->memberships()->count(),
                'active' => $community->memberships()->where('status', 'ACTIVE')->count(),
                'suspended' => $community->memberships()->where('status', 'SUSPENDED')->count(),
                'removed' => $community->memberships()->where('status', 'REMOVED')->count(),
            ],
        ]);
    }

    /**
     * Suspend an active community member.
     */
    public function suspend(Request $request, Community $community, CommunityMembership $membership): RedirectResponse
    {
        abort_unless($community->isActive() && $community->isCreator($request->user()), 404);
        abort_unless($membership->community_id === $community->id, 404);
        abort_if($membership->user_id === $community->creator_id, 403);

        $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $membership->transitionTo('SUSPENDED');

        return redirect()
            ->route('communities.members.index', $community)
            ->with('status', 'Member has been suspended.');
    }

    /**
     * Reactivate a suspended community member back to active.
     */
    public function reactivate(Request $request, Community $community, CommunityMembership $membership): RedirectResponse
    {
        abort_unless($community->isActive() && $community->isCreator($request->user()), 404);
        abort_unless($membership->community_id === $community->id, 404);

        $membership->transitionTo('ACTIVE');

        return redirect()
            ->route('communities.members.index', $community)
            ->with('status', 'Member has been reactivated.');
    }

    /**
     * Remove a member from the community.
     */
    public function remove(Request $request, Community $community, CommunityMembership $membership): RedirectResponse
    {
        abort_unless($community->isActive() && $community->isCreator($request->user()), 404);
        abort_unless($membership->community_id === $community->id, 404);
        abort_if($membership->user_id === $community->creator_id, 403);

        $membership->transitionTo('REMOVED');

        return redirect()
            ->route('communities.members.index', $community)
            ->with('status', 'Member has been removed from the community.');
    }
}
