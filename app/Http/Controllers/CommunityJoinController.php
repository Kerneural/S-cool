<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CommunityJoinController extends Controller
{
    /**
     * Handle join community request from modal form.
     */
    public function join(Request $request, Community $community): RedirectResponse
    {
        abort_unless($community->isActive(), 404);

        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'referral_source' => ['nullable', 'string', 'max:200'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = Auth::user();

        if (! $user) {
            $user = User::where('email', $email)->first();

            if (! $user) {
                $nameParts = explode('@', $email);
                $name = ! empty($nameParts[0]) ? ucfirst($nameParts[0]) : 'Member';

                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make(Str::random(32)),
                    'email_verified_at' => now(),
                ]);
            }

            Auth::login($user);
        }

        // If user is already the community creator, they already have full access.
        if ($community->isCreator($user)) {
            return back()->with('status', __('You are the creator and administrator of this community.'));
        }

        $referralAnswer = ! empty($validated['referral_source'])
            ? trim($validated['referral_source'])
            : null;

        if ($referralAnswer !== null) {
            Log::info("Community join survey [community_id={$community->id}, user_id={$user->id}]: {$referralAnswer}");
        }

        /** @var CommunityMembership|null $membership */
        $membership = $community->memberships()->where('user_id', $user->id)->first();

        if ($membership instanceof CommunityMembership) {
            if ($membership->status !== 'ACTIVE') {
                $membership->forceFill([
                    'status' => 'ACTIVE',
                    'access_kind' => CommunityMembership::ACCESS_LIFETIME,
                    'expires_at' => null,
                ])->save();
            }
        } else {
            $membership = new CommunityMembership;
            $membership->forceFill([
                'community_id' => $community->id,
                'user_id' => $user->id,
                'status' => 'ACTIVE',
                'access_kind' => CommunityMembership::ACCESS_LIFETIME,
                'expires_at' => null,
                'referral_code' => Str::upper(Str::random(12)),
            ]);
            $membership->save();
        }

        return back()->with('status', __('Successfully joined the group! Welcome to the community.'));
    }
}
