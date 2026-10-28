<?php

namespace App\Http\Controllers;

use App\Actions\Communities\CreateInvitation;
use App\Models\Community;
use App\Models\ReferralInvitationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReferralController extends Controller
{
    /**
     * Display the creator's referral dashboard and pending requests.
     */
    public function creatorIndex(Community $community): View
    {
        Gate::authorize('update', $community);

        $commissions = $community->referralCommissions()
            ->with(['referrer', 'payer'])
            ->orderByDesc('id')
            ->paginate(15);

        $pendingRequests = $community->referralRequests()
            ->with('referrer')
            ->where('status', ReferralInvitationRequest::STATUS_PENDING)
            ->orderByDesc('id')
            ->get();

        $totalSimulatedCommissions = (int) $community->referralCommissions()->sum('commission_amount');
        $totalAttributedCount = $community->referralCommissions()->count();

        return view('communities.referrals', [
            'community' => $community,
            'commissions' => $commissions,
            'pendingRequests' => $pendingRequests,
            'totalSimulatedCommissions' => $totalSimulatedCommissions,
            'totalAttributedCount' => $totalAttributedCount,
        ]);
    }

    /**
     * Member requests an invitation for a referred contact.
     */
    public function requestInvitation(Request $request, Community $community): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor->hasVerifiedEmail() && $community->isActive(), 403);

        $membership = $community->memberships()->where('user_id', $actor->id)->first();
        $isCreator = $community->isCreator($actor);
        $hasAccess = $isCreator || ($membership && $membership->hasEffectiveAccess());

        abort_unless($hasAccess, 403);

        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $normalizedEmail = strtolower(trim($validated['email']));

        if ($normalizedEmail === strtolower($actor->email)) {
            throw ValidationException::withMessages([
                'email' => 'You cannot refer yourself.',
            ]);
        }

        if ($normalizedEmail === strtolower($community->creator->email)) {
            throw ValidationException::withMessages([
                'email' => 'The community creator already has access.',
            ]);
        }

        $existingPending = ReferralInvitationRequest::query()
            ->where('community_id', $community->id)
            ->where('email', $normalizedEmail)
            ->where('status', ReferralInvitationRequest::STATUS_PENDING)
            ->exists();

        if ($existingPending) {
            throw ValidationException::withMessages([
                'email' => 'A referral request for this email is already awaiting creator approval.',
            ]);
        }

        ReferralInvitationRequest::create([
            'community_id' => $community->id,
            'referrer_id' => $actor->id,
            'email' => $normalizedEmail,
            'status' => ReferralInvitationRequest::STATUS_PENDING,
        ]);

        return redirect()->back()->with('status', __('Referral invitation requested. Awaiting creator review and approval.'));
    }

    /**
     * Creator approves a member's referral request and dispatches the private invitation.
     */
    public function approveRequest(Request $request, Community $community, ReferralInvitationRequest $referralRequest, CreateInvitation $action): RedirectResponse
    {
        DB::transaction(function () use ($request, $community, $referralRequest, $action): void {
            $currentCommunity = Community::query()->whereKey($community->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('update', $currentCommunity);
            $currentRequest = $currentCommunity->referralRequests()->whereKey($referralRequest->id)
                ->lockForUpdate()->firstOrFail();
            abort_unless($currentRequest->isPending(), 404);

            $invite = $action->handle($currentCommunity, $request->user(), $currentRequest->email, $currentRequest->referrer);
            $currentRequest->update([
                'status' => ReferralInvitationRequest::STATUS_APPROVED,
                'community_invitation_id' => $invite->id,
            ]);
        }, 3);

        return redirect()->back()->with('status', __('Referral approved and private invitation queued.'));
    }

    /**
     * Creator rejects a member's referral request.
     */
    public function rejectRequest(Request $request, Community $community, ReferralInvitationRequest $referralRequest): RedirectResponse
    {
        DB::transaction(function () use ($community, $referralRequest): void {
            $currentCommunity = Community::query()->whereKey($community->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('update', $currentCommunity);
            $currentRequest = $currentCommunity->referralRequests()->whereKey($referralRequest->id)
                ->lockForUpdate()->firstOrFail();
            abort_unless($currentRequest->isPending(), 404);
            $currentRequest->update(['status' => ReferralInvitationRequest::STATUS_REJECTED]);
        }, 3);

        return redirect()->back()->with('status', __('Referral request rejected.'));
    }

    /**
     * Member views their own safe referral earnings summary across communities.
     */
    public function memberIndex(Request $request): View
    {
        $actor = $request->user();

        $commissions = $actor->earnedReferralCommissions()
            ->with('community')
            ->orderByDesc('id')
            ->paginate(15);

        $pendingRequests = $actor->referralRequests()
            ->with('community')
            ->orderByDesc('id')
            ->get();

        $totalSimulatedEarnings = (int) $actor->earnedReferralCommissions()->sum('commission_amount');

        return view('account.referrals', [
            'commissions' => $commissions,
            'pendingRequests' => $pendingRequests,
            'totalSimulatedEarnings' => $totalSimulatedEarnings,
        ]);
    }
}
