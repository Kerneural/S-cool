<?php

namespace App\Http\Controllers;

use App\Actions\Communities\JoinCommunity;
use App\Models\Community;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommunityJoinController extends Controller
{
    public function join(Request $request, Community $community, JoinCommunity $action): RedirectResponse
    {
        abort_unless($community->isActive(), 404);
        $user = $request->user();

        if ($community->isCreator($user) || $community->isActiveMember($user)) {
            return redirect()->route('communities.show', $community);
        }

        $result = $action->handle($community, $user);
        if ($result instanceof Payment) {
            return redirect()->route('payments.checkout', $result->external_reference);
        }

        return redirect()->route('communities.show', $community)->with('status', __('You have joined the community.'));
    }
}
