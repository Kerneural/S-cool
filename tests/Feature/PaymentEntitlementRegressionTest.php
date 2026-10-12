<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Models\Community;
use App\Models\CommunityInvitation;
use App\Models\CommunityMembership;
use App\Models\Payment;
use App\Models\ProcessedWebhookEvent;
use App\Models\User;
use App\Services\Payment\FakePaymentGateway;
use App\Services\Payment\SePayGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentEntitlementRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function attempt(string $memberStatus = 'PENDING_PAYMENT', string $paymentStatus = 'PENDING'): array
    {
        $user = User::factory()->create();
        $community = Community::factory()->create(['access_mode' => 'PAID', 'price_vnd' => 100000]);
        $token = bin2hex(random_bytes(32));
        $invite = new CommunityInvitation;
        $invite->forceFill([
            'community_id' => $community->id, 'inviter_id' => $community->creator_id,
            'normalized_email' => strtolower($user->email), 'token_hash' => hash('sha256', $token),
            'status' => 'PENDING', 'expires_at' => now()->addDay(),
        ])->save();
        $member = new CommunityMembership;
        $member->forceFill(['community_id' => $community->id, 'user_id' => $user->id, 'status' => $memberStatus])->save();
        $payment = Payment::factory()->forInvitation($invite)->create(['user_id' => $user->id, 'status' => $paymentStatus]);

        return [$user, $community, $invite, $member, $payment, $token];
    }

    public function test_checkout_cannot_reset_suspended_or_removed_membership(): void
    {
        foreach (['SUSPENDED', 'REMOVED'] as $status) {
            [$user, , $invite, $member, , $token] = $this->attempt($status);
            $this->actingAs($user)->post('/invitations/'.$invite->id.'/checkout', ['token' => $token])->assertNotFound();
            $this->assertSame($status, $member->fresh()->status);
        }
    }

    public function test_webhook_cannot_overwrite_terminal_payment(): void
    {
        foreach (['FAILED', 'CANCELLED'] as $status) {
            [, , , $member, $payment] = $this->attempt('PENDING_PAYMENT', $status);
            $this->app->instance(PaymentGateway::class, new FakePaymentGateway);
            $this->postJson('/webhooks/sepay', ['id' => 'terminal-'.$status, 'content' => $payment->external_reference, 'amount' => $payment->amount])->assertStatus(409);
            $this->assertSame($status, $payment->fresh()->status);
            $this->assertSame('PENDING_PAYMENT', $member->fresh()->status);
        }
    }

    public function test_verified_failure_or_cancel_never_activates_membership(): void
    {
        foreach (['FAILED', 'CANCELLED'] as $status) {
            [, , , $member, $payment] = $this->attempt();
            $this->app->instance(PaymentGateway::class, new FakePaymentGateway);
            $this->postJson('/webhooks/sepay', ['id' => 'result-'.$status, 'content' => $payment->external_reference, 'amount' => $payment->amount, 'status' => $status])->assertOk();
            $this->assertSame($status, $payment->fresh()->status);
            $this->assertSame('PENDING_PAYMENT', $member->fresh()->status);
        }
    }

    public function test_delayed_ipn_cannot_reactivate_suspended_removed_or_left_membership(): void
    {
        foreach (['SUSPENDED', 'REMOVED', 'LEFT'] as $status) {
            [, , , $member, $payment] = $this->attempt($status);
            $this->app->instance(PaymentGateway::class, new FakePaymentGateway);
            $this->postJson('/webhooks/sepay', ['id' => 'delayed-'.$status, 'content' => $payment->external_reference, 'amount' => $payment->amount])->assertStatus(409);
            $this->assertSame($status, $member->fresh()->status);
            $this->assertSame('PENDING', $payment->fresh()->status);
        }
    }

    public function test_activation_requires_current_invitation_community_identity_and_pending_membership(): void
    {
        foreach (['revoked', 'expired', 'community_suspended', 'email_changed', 'unverified', 'missing_membership', 'missing_invitation'] as $case) {
            [$user, $community, $invite, $member, $payment] = $this->attempt();
            match ($case) {
                'revoked' => $invite->forceFill(['status' => 'REVOKED'])->save(),
                'expired' => $invite->forceFill(['expires_at' => now()->subMinute()])->save(),
                'community_suspended' => $community->forceFill(['status' => 'SUSPENDED'])->save(),
                'email_changed' => $user->forceFill(['email' => 'changed-'.$user->id.'@example.test'])->save(),
                'unverified' => $user->forceFill(['email_verified_at' => null])->save(),
                'missing_membership' => $member->delete(),
                'missing_invitation' => $payment->update(['community_invitation_id' => null]),
            };
            $this->app->instance(PaymentGateway::class, new FakePaymentGateway);
            $this->postJson('/webhooks/sepay', ['id' => 'boundary-'.$case, 'content' => $payment->external_reference, 'amount' => $payment->amount])->assertStatus(409);
            $this->assertSame('PENDING', $payment->fresh()->status);
            $this->assertDatabaseMissing('community_memberships', ['community_id' => $community->id, 'user_id' => $user->id, 'status' => 'ACTIVE']);
        }
    }

    public function test_owner_can_cancel_and_retry_with_a_new_attempt_without_access(): void
    {
        [$user, , $invite, $member, $payment, $token] = $this->attempt();
        $this->actingAs(User::factory()->create())->post('/payments/'.$payment->external_reference.'/cancel')->assertNotFound();
        $this->assertSame('PENDING', $payment->fresh()->status);
        $this->actingAs($user)->post('/payments/'.$payment->external_reference.'/cancel')->assertRedirect();
        $this->assertSame('CANCELLED', $payment->fresh()->status);
        $this->assertSame('PENDING_PAYMENT', $member->fresh()->status);
        $this->actingAs($user)->post('/invitations/'.$invite->id.'/checkout', ['token' => $token])->assertRedirect();
        $this->assertSame(2, Payment::where('user_id', $user->id)->count());
        $this->assertSame('CANCELLED', $payment->fresh()->status);
        $this->assertSame('PENDING_PAYMENT', $member->fresh()->status);
    }

    public function test_bank_adapter_rejects_outgoing_malformed_wrong_account_and_disabled_test_mode(): void
    {
        [, , , $member, $payment] = $this->attempt();
        config(['services.sepay.test_mode' => true, 'services.sepay.webhook_token' => 'test-only-key', 'services.sepay.account_number' => 'TEST-ACCOUNT-001']);
        $this->app->instance(PaymentGateway::class, new SePayGateway);
        $valid = ['id' => 987654, 'content' => $payment->external_reference, 'transferType' => 'in', 'transferAmount' => $payment->amount, 'referenceCode' => 'TEST-REF-001', 'accountNumber' => 'TEST-ACCOUNT-001'];
        foreach ([['transferType' => 'out'], ['transferAmount' => 100000.9], ['id' => []], ['content' => []], ['currency' => 'USD'], ['accountNumber' => 'OTHER-ACCOUNT']] as $override) {
            $this->postJson('/webhooks/sepay', array_replace($valid, $override), ['Authorization' => 'Apikey test-only-key'])->assertStatus(422);
            $this->assertSame('PENDING', $payment->fresh()->status);
            $this->assertSame('PENDING_PAYMENT', $member->fresh()->status);
        }
        config(['services.sepay.test_mode' => false]);
        $this->postJson('/webhooks/sepay', $valid, ['Authorization' => 'Apikey test-only-key'])->assertUnauthorized();
        $this->assertSame('PENDING_PAYMENT', $member->fresh()->status);
    }

    public function test_creator_can_set_price_but_other_users_and_invalid_prices_are_rejected(): void
    {
        $creator = User::factory()->create();
        $this->actingAs($creator)->post('/communities', ['name' => 'Paid training', 'slug' => 'paid-training', 'access_mode' => 'PAID', 'price_vnd' => 250000])->assertRedirect();
        $community = Community::where('slug', 'paid-training')->firstOrFail();
        $this->assertSame('PAID', $community->access_mode);
        $this->assertSame(250000, (int) $community->price_vnd);
        $this->assertSame('PRIVATE', $community->visibility);
        $this->actingAs(User::factory()->create())->put('/communities/'.$community->slug, ['name' => 'Changed', 'access_mode' => 'FREE'])->assertNotFound();
        $this->assertSame('PAID', $community->fresh()->access_mode);
        foreach ([null, 0, -1, 100.5] as $price) {
            $this->actingAs($creator)->put('/communities/'.$community->slug, ['name' => $community->name, 'access_mode' => 'PAID', 'price_vnd' => $price])->assertSessionHasErrors('price_vnd');
            $this->assertSame(250000, (int) $community->fresh()->price_vnd);
        }
        $this->actingAs($creator)->put('/communities/'.$community->slug, ['name' => $community->name, 'access_mode' => 'FREE'])->assertRedirect();
        $this->assertNull($community->fresh()->price_vnd);
    }

    public function test_checkout_snapshots_configured_price_and_later_edits_do_not_change_it(): void
    {
        [$user, $community, $invite, $member, $oldAttempt, $token] = $this->attempt();
        $oldAttempt->delete();
        $community->forceFill(['price_vnd' => 250000])->save();
        $this->actingAs($user)->post('/invitations/'.$invite->id.'/checkout', ['token' => $token])->assertRedirect();
        $payment = Payment::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(250000, $payment->amount);
        $community->forceFill(['price_vnd' => 300000])->save();
        $this->actingAs($user)->post('/invitations/'.$invite->id.'/checkout', ['token' => $token])->assertRedirect();
        $this->assertSame(250000, $payment->fresh()->amount);
        $this->assertSame(1, Payment::where('user_id', $user->id)->count());
        $this->assertSame('PENDING_PAYMENT', $member->fresh()->status);
    }

    public function test_provider_event_cannot_be_reassigned_to_a_second_payment_and_payload_is_minimized(): void
    {
        [, , , , $first] = $this->attempt();
        [, , , $secondMember, $second] = $this->attempt();
        $this->app->instance(PaymentGateway::class, new FakePaymentGateway);
        $this->postJson('/webhooks/sepay', ['id' => 'shared-event', 'content' => $first->external_reference, 'amount' => $first->amount, 'token' => 'synthetic-do-not-store'])->assertOk()->assertJson(['success' => true]);
        $this->postJson('/webhooks/sepay', ['id' => 'shared-event', 'content' => $second->external_reference, 'amount' => $second->amount])->assertStatus(422);
        $this->assertSame('PENDING', $second->fresh()->status);
        $this->assertSame('PENDING_PAYMENT', $secondMember->fresh()->status);
        $event = ProcessedWebhookEvent::where('provider_event_id', 'shared-event')->firstOrFail();
        $this->assertArrayNotHasKey('token', $event->payload);
    }

    public function test_activation_failure_rolls_back_payment_event_and_invitation(): void
    {
        [, , $invite, $member, $payment] = $this->attempt();
        $this->app->instance(PaymentGateway::class, new FakePaymentGateway);
        CommunityMembership::saving(function (CommunityMembership $candidate): void {
            if ($candidate->status === 'ACTIVE') {
                throw new \RuntimeException('Synthetic activation failure');
            }
        });
        try {
            $this->postJson('/webhooks/sepay', ['id' => 'rollback-event', 'content' => $payment->external_reference, 'amount' => $payment->amount])->assertStatus(500);
            $this->assertSame('PENDING', $payment->fresh()->status);
            $this->assertSame('PENDING_PAYMENT', $member->fresh()->status);
            $this->assertSame('PENDING', $invite->fresh()->status);
            $this->assertDatabaseMissing('processed_webhook_events', ['provider_event_id' => 'rollback-event']);
        } finally {
            CommunityMembership::flushEventListeners();
        }
    }
}
