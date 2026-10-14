<?php

namespace App\Http\Controllers;

use App\Actions\Payments\InitiateMembershipRenewal;
use App\Models\CommunityMembership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembershipAccessController extends Controller
{
    public function index(Request $request): View
    {
        // Own access/history remains safe to read after expiry; no internal content is delivered.
        $memberships = $request->user()->memberships()->with('community')->orderByDesc('id')->paginate(15);

        return view('account.memberships.index', compact('memberships'));
    }

    public function show(Request $request, CommunityMembership $membership): View
    {
        abort_unless((int) $membership->user_id === (int) $request->user()->id, 404);
        $membership->load('community');

        return view('account.memberships.show', compact('membership'));
    }

    public function renew(Request $request, CommunityMembership $membership, InitiateMembershipRenewal $action): RedirectResponse
    {
        $payment = $action->handle($membership, $request->user());

        return redirect()->route('payments.checkout', $payment->external_reference);
    }
}
