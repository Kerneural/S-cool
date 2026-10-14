<?php

namespace App\Actions\Payments;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InitiateMembershipRenewal
{
    public function handle(CommunityMembership $membership, User $user): Payment
    {
        abort_unless((int) $membership->user_id === (int) $user->id, 404);

        return DB::transaction(function () use ($membership, $user): Payment {
            $actor = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $community = Community::query()->whereKey($membership->community_id)->lockForUpdate()->firstOrFail();
            $current = $community->memberships()->whereKey($membership->id)->lockForUpdate()->firstOrFail();
            $current->setRelation('community', $community);
            abort_unless($current->canRenew($actor), 404);

            $pending = Payment::query()->where('community_membership_id', $current->id)
                ->where('user_id', $actor->id)->where('community_id', $community->id)
                ->where('purpose', Payment::PURPOSE_MEMBERSHIP_RENEWAL)->where('status', Payment::STATUS_PENDING)
                ->lockForUpdate()->first();
            if ($pending) {
                return $pending;
            }

            return Payment::create([
                'external_reference' => 'RNW-'.strtoupper(Str::random(10)),
                'community_id' => $community->id,
                'user_id' => $actor->id,
                'community_membership_id' => $current->id,
                'purpose' => Payment::PURPOSE_MEMBERSHIP_RENEWAL,
                'membership_access_kind' => CommunityMembership::ACCESS_TERM,
                'term_days' => CommunityMembership::TERM_DAYS,
                'amount' => (int) $community->price_vnd,
                'currency' => 'VND',
                'status' => Payment::STATUS_PENDING,
                'gateway_provider' => Payment::PROVIDER_SEPAY_SANDBOX,
            ]);
        }, 3);
    }
}
