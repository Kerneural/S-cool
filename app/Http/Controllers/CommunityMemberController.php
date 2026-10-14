<?php

namespace App\Http\Controllers;

use App\Actions\Communities\TransitionCommunityMembership;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
            ->with(['user', 'latestAction.actor'])
            ->orderByDesc('id')
            ->paginate(15);

        return view('communities.members.index', [
            'community' => $community,
            'memberships' => $memberships,
            'counts' => [
                'total' => $community->memberships()->count(),
                'active' => $community->memberships()->effective()->count(),
                'suspended' => $community->memberships()->where('status', 'SUSPENDED')->count(),
                'removed' => $community->memberships()->where('status', 'REMOVED')->count(),
            ],
        ]);
    }

    /**
     * Suspend an active community member.
     */
    public function suspend(
        Request $request,
        Community $community,
        CommunityMembership $membership,
        TransitionCommunityMembership $transition,
    ): RedirectResponse {
        $transition->handle($this->authenticatedUser($request), $community, $membership, 'SUSPENDED', $this->validatedReason($request));

        return redirect()
            ->route('communities.members.index', $community)
            ->with('status', 'Member has been suspended.');
    }

    /**
     * Reactivate a suspended community member back to active.
     */
    public function reactivate(
        Request $request,
        Community $community,
        CommunityMembership $membership,
        TransitionCommunityMembership $transition,
    ): RedirectResponse {
        $transition->handle($this->authenticatedUser($request), $community, $membership, 'ACTIVE', $this->validatedReason($request));

        return redirect()
            ->route('communities.members.index', $community)
            ->with('status', 'Member has been reactivated.');
    }

    /**
     * Remove a member from the community.
     */
    public function remove(
        Request $request,
        Community $community,
        CommunityMembership $membership,
        TransitionCommunityMembership $transition,
    ): RedirectResponse {
        $transition->handle($this->authenticatedUser($request), $community, $membership, 'REMOVED', $this->validatedReason($request));

        return redirect()
            ->route('communities.members.index', $community)
            ->with('status', 'Member has been removed from the community.');
    }

    private function validatedReason(Request $request): string
    {
        $validated = $request->validate([
            'reason' => [
                'required',
                'string',
                'max:500',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || mb_strlen(trim($value)) < 3) {
                        $fail('The reason must contain at least 3 non-whitespace characters.');
                    }
                },
            ],
        ]);

        if (! is_string($validated['reason'])) {
            throw ValidationException::withMessages(['reason' => 'A valid reason is required.']);
        }

        return trim($validated['reason']);
    }

    private function authenticatedUser(Request $request): User
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }
}
