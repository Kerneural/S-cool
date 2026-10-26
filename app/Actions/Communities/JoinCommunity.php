<?php

namespace App\Actions\Communities;

use App\Jobs\SendMemberActivatedNotification;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class JoinCommunity
{
    public function handle(Community $community, User $user): CommunityMembership|Payment
    {
        return DB::transaction(function () use ($community, $user): CommunityMembership|Payment {
            $actor = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $community = Community::query()->whereKey($community->id)->lockForUpdate()->firstOrFail();
            abort_unless($actor->hasVerifiedEmail() && $community->isActive() && ! $community->isCreator($actor), 404);
            $membership = $community->memberships()->where('user_id', $actor->id)->lockForUpdate()->first();

            if ($membership?->hasEffectiveAccess()) {
                return $membership;
            }

            // Self-join cannot undo moderation, an expired term, or pending paid access.
            $eligible = $membership === null || $membership->status === 'LEFT'
                || ($community->access_mode === 'PAID' && $membership->status === 'PENDING_PAYMENT');
            if (! $eligible) {
                throw ValidationException::withMessages(['join' => 'This membership cannot be joined again here. Use My Access to renew an expired term, or contact the creator about restricted access.']);
            }

            $paid = $community->access_mode === 'PAID';
            if ($paid && (int) $community->price_vnd <= 0) {
                throw ValidationException::withMessages(['join' => 'The creator must configure a positive admission price before checkout.']);
            }
            if (! in_array($community->access_mode, ['FREE', 'PAID'], true)) {
                abort(404);
            }

            $membership ??= new CommunityMembership;
            $membership->forceFill(['community_id' => $community->id, 'user_id' => $actor->id]);
            if (! $paid) {
                $membership->forceFill(['status' => 'ACTIVE', 'access_kind' => CommunityMembership::ACCESS_LIFETIME, 'expires_at' => null])->save();
                SendMemberActivatedNotification::dispatch($community->id, $membership->id, 'join', $membership->id);

                return $membership;
            }

            $membership->forceFill(['status' => 'PENDING_PAYMENT'])->save();
            $pending = Payment::query()->where('community_id', $community->id)->where('user_id', $actor->id)
                ->where('community_membership_id', $membership->id)->where('purpose', Payment::PURPOSE_COMMUNITY_JOIN)
                ->whereNull('community_invitation_id')->where('status', Payment::STATUS_PENDING)->lockForUpdate()->first();
            if ($pending) {
                return $pending;
            }

            return Payment::create([
                'external_reference' => 'INV-'.strtoupper(Str::random(10)),
                'community_id' => $community->id, 'user_id' => $actor->id,
                'community_invitation_id' => null, 'community_membership_id' => $membership->id,
                'purpose' => Payment::PURPOSE_COMMUNITY_JOIN,
                'membership_access_kind' => $community->billing_period === 'TERM_30_DAYS' ? CommunityMembership::ACCESS_TERM : CommunityMembership::ACCESS_LIFETIME,
                'term_days' => $community->billing_period === 'TERM_30_DAYS' ? CommunityMembership::TERM_DAYS : null,
                'amount' => (int) $community->price_vnd, 'currency' => 'VND',
                'status' => Payment::STATUS_PENDING, 'gateway_provider' => Payment::PROVIDER_SEPAY_SANDBOX,
            ]);
        }, 3);
    }
}
