<?php

namespace App\Http\Controllers;

use App\Models\Community;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CommunityJoinController extends Controller
{
    public function join(Request $request, Community $community): RedirectResponse
    {
        abort_unless($community->isActive(), 404);
        $user = $request->user();

        if ($community->isCreator($user) || $community->isActiveMember($user)) {
            return back()->with('status', __('You already have access to this community.'));
        }

        throw ValidationException::withMessages([
            'email' => __('An invitation is required. Ask the community creator for an invitation and complete any required checkout.'),
        ]);
    }
}
