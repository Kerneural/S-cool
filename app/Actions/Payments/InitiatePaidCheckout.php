<?php

namespace App\Actions\Payments;

use App\Models\Community;
use App\Models\CommunityInvitation;
use App\Models\CommunityMembership;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InitiatePaidCheckout
{
    /**
     * Initiate paid checkout for an invited user.
     *
     * Creates or retrieves a PENDING_PAYMENT membership and PENDING Payment attempt.
     */
    public function handle(int $invitationId, User $user, string $token): Payment
    {
        abort_unless(preg_match('/\A[a-f0-9]{64}\z/D', $token) === 1, 404);

        $tenantId = CommunityInvitation::query()->whereKey($invitationId)->value('community_id');
        abort_if($tenantId === null, 404);

        return DB::transaction(function () use ($invitationId, $user, $token, $tenantId): Payment {
            $actor = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $community = Community::query()->whereKey($tenantId)->lockForUpdate()->firstOrFail();
            /** @var CommunityInvitation $invite */
            $invite = $community->invitations()->whereKey($invitationId)->lockForUpdate()->firstOrFail();

            abort_unless(
                $actor->hasVerifiedEmail()
                && $community->isActive()
                && $invite->isPending()
                && $invite->matchesToken($token)
                && $invite->normalized_email === strtolower(trim($actor->email))
                && ! $community->isCreator($actor),
                404
            );

            if ($community->access_mode !== 'PAID') {
                throw ValidationException::withMessages([
                    'invitation' => 'This community is free. Use standard invitation acceptance.',
                ]);
            }

            // Lock or create membership in PENDING_PAYMENT state
            /** @var CommunityMembership|null $membership */
            $membership = $community->memberships()->where('user_id', $actor->id)->lockForUpdate()->first();
            abort_if($membership && ! in_array($membership->status, ['PENDING_PAYMENT', 'ACTIVE', 'LEFT'], true), 404);
            if ($membership && $membership->status === 'ACTIVE') {
                throw ValidationException::withMessages([
                    'invitation' => 'You are already an active member of this community.',
                ]);
            }

            if (! $membership) {
                $membership = new CommunityMembership;
                $membership->forceFill([
                    'community_id' => $community->id,
                    'user_id' => $actor->id,
                    'status' => 'PENDING_PAYMENT',
                ])->save();
            } else {
                /** @var CommunityMembership $membership */
                $membership->status = 'PENDING_PAYMENT';
                $membership->save();
            }

            // Reuse existing pending payment or create new one
            $payment = Payment::where('community_id', $community->id)
                ->where('user_id', $actor->id)
                ->where('community_invitation_id', $invite->id)
                ->where('status', Payment::STATUS_PENDING)
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                $reference = 'INV-'.strtoupper(Str::random(10));
                $amount = (int) $community->price_vnd;
                if ($amount <= 0) {
                    throw ValidationException::withMessages(['invitation' => 'The creator must configure a paid access price before checkout.']);
                }

                $payment = Payment::create([
                    'external_reference' => $reference,
                    'community_id' => $community->id,
                    'user_id' => $actor->id,
                    'community_invitation_id' => $invite->id,
                    'community_membership_id' => $membership->id,
                    'purpose' => Payment::PURPOSE_COMMUNITY_ACCESS,
                    'membership_access_kind' => $community->billing_period === 'TERM_30_DAYS' ? CommunityMembership::ACCESS_TERM : CommunityMembership::ACCESS_LIFETIME,
                    'term_days' => $community->billing_period === 'TERM_30_DAYS' ? CommunityMembership::TERM_DAYS : null,
                    'amount' => $amount,
                    'currency' => 'VND',
                    'status' => Payment::STATUS_PENDING,
                    'gateway_provider' => Payment::PROVIDER_SEPAY_SANDBOX,
                ]);
            }

            return $payment;
        }, 3);
    }
}
