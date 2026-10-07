<?php

namespace App\Http\Controllers;

use App\Actions\Communities\AcceptInvitation;
use App\Actions\Communities\CreateInvitation;
use App\Actions\Communities\RevokeInvitation;
use App\Models\Community;
use App\Models\CommunityInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CommunityInvitationController extends Controller
{
    public function index(Community $community): View
    {
        Gate::authorize('update', $community);

        return view('communities.invitations', [
            'community' => $community,
            'invitations' => $community->invitations()->orderByDesc('id')->paginate(12),
        ]);
    }

    public function store(Request $request, Community $community, CreateInvitation $action): RedirectResponse
    {
        Gate::authorize('update', $community);
        $validated = $request->validate(['email' => ['required', 'string', 'email', 'max:255']]);
        $action->handle($community, $request->user(), $validated['email']);

        return redirect()->route('communities.invitations.index', $community)->with('status', 'Invitation queued. Check Mailpit locally.');
    }

    public function revoke(Request $request, Community $community, CommunityInvitation $invitation, RevokeInvitation $action): RedirectResponse
    {
        $action->handle($community, $invitation, $request->user());

        return redirect()->route('communities.invitations.index', $community)->with('status', 'Invitation revoked.');
    }

    public function show(string $invitation): View
    {
        // Always generic, including guessed/missing IDs. No tenant lookup here.
        return view('invitations.accept', ['invitationId' => $invitation]);
    }

    public function accept(Request $request, string $invitation, AcceptInvitation $action): RedirectResponse
    {
        $token = $request->input('token');
        abort_unless(is_string($token), 404);
        $community = $action->handle((int) $invitation, $request->user(), $token);

        return redirect()->route('communities.show', $community)->with('status', 'Invitation accepted.');
    }
}
