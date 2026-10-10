<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Models\Community;
use App\Models\CommunityInvitation;
use App\Models\CommunityMembership;
use App\Models\Payment;
use App\Models\ProcessedWebhookEvent;
use App\Models\User;
use App\Services\Payment\SePayGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SePayPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.sepay.webhook_token' => 'test-webhook-secret-token']);
        $this->app->bind(PaymentGateway::class, SePayGateway::class);
    }

    /**
     * Helper to create an invitation for testing.
     *
     * @return array{0: CommunityInvitation, 1: string}
     */
    protected function createInvitation(Community $community, User $user): array
    {
        $token = bin2hex(random_bytes(32));
        $invitation = new CommunityInvitation;
        $invitation->forceFill([
            'community_id' => $community->id,
            'inviter_id' => $community->creator_id,
            'normalized_email' => strtolower(trim($user->email)),
            'token_hash' => hash('sha256', $token),
            'status' => 'PENDING',
            'expires_at' => now()->addDays(7),
        ])->save();

        return [$invitation, $token];
    }

    /**
     * Helper to create a guarded membership safely.
     */
    protected function createMembership(Community $community, User $user, string $status = 'PENDING_PAYMENT'): CommunityMembership
    {
        $membership = new CommunityMembership;
        $membership->forceFill([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => $status,
        ])->save();

        return $membership;
    }

    public function test_paid_invitation_initiates_pending_membership_and_checkout_redirect(): void
    {
        $community = Community::factory()->create(['access_mode' => 'PAID']);
        $user = User::factory()->create();
        [$invitation, $token] = $this->createInvitation($community, $user);

        $response = $this->actingAs($user)->post('/invitations/'.$invitation->id.'/checkout', [
            'token' => $token,
        ]);

        $payment = Payment::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($payment);
        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
        $this->assertSame(100000, $payment->amount);
        $this->assertSame('VND', $payment->currency);

        $response->assertRedirect(route('payments.checkout', $payment->external_reference));

        // Membership created in PENDING_PAYMENT
        $this->assertDatabaseHas('community_memberships', [
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => 'PENDING_PAYMENT',
        ]);

        // User cannot access community yet
        $this->actingAs($user)->get('/communities/'.$community->slug)->assertNotFound();
    }

    public function test_verified_success_ipn_transitions_payment_to_succeeded_and_activates_membership(): void
    {
        $community = Community::factory()->create(['access_mode' => 'PAID']);
        $user = User::factory()->create();
        [$invitation, $token] = $this->createInvitation($community, $user);

        /** @var Payment $payment */
        $payment = Payment::factory()->forInvitation($invitation)->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'amount' => 100000,
            'currency' => 'VND',
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->createMembership($community, $user, 'PENDING_PAYMENT');

        $ipnPayload = [
            'id' => 'sepay-evt-1001',
            'referenceCode' => 'MB-FT-998877',
            'content' => 'Thanh toan '.$payment->external_reference,
            'transferAmount' => 100000,
            'currency' => 'VND',
        ];

        $response = $this->postJson('/webhooks/sepay', $ipnPayload, [
            'Authorization' => 'Apikey test-webhook-secret-token',
        ]);

        $response->assertOk();
        $response->assertJson(['message' => 'Payment verified and membership activated.']);

        // Payment is SUCCEEDED (AC-01)
        $payment->refresh();
        $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->status);
        $this->assertSame('MB-FT-998877', $payment->gateway_transaction_id);
        $this->assertNotNull($payment->paid_at);

        // Membership is ACTIVE (AC-01)
        $this->assertDatabaseHas('community_memberships', [
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => 'ACTIVE',
        ]);

        // Invitation is marked ACCEPTED
        $this->assertSame('ACCEPTED', $invitation->refresh()->status);

        // Member now has full community access
        $this->actingAs($user)->get('/communities/'.$community->slug)->assertOk();
    }

    public function test_tampered_amount_currency_or_reference_is_rejected_without_activation(): void
    {
        $community = Community::factory()->create(['access_mode' => 'PAID']);
        $user = User::factory()->create();

        $payment = Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'amount' => 100000,
            'currency' => 'VND',
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->createMembership($community, $user, 'PENDING_PAYMENT');

        // 1. Amount mismatch (tampered amount 50.000 instead of 100.000)
        $tamperedAmountPayload = [
            'id' => 'sepay-evt-tamper-1',
            'referenceCode' => 'MB-FT-1111',
            'content' => $payment->external_reference,
            'transferAmount' => 50000,
            'currency' => 'VND',
        ];

        $response = $this->postJson('/webhooks/sepay', $tamperedAmountPayload, [
            'Authorization' => 'Apikey test-webhook-secret-token',
        ]);

        $response->assertStatus(422);
        $this->assertSame(Payment::STATUS_FAILED, $payment->refresh()->status);
        $this->assertSame('PENDING_PAYMENT', CommunityMembership::where('user_id', $user->id)->value('status'));

        // 2. Non-existent reference
        $fakeRefPayload = [
            'id' => 'sepay-evt-tamper-2',
            'referenceCode' => 'MB-FT-2222',
            'content' => 'INV-DOESNOTEXIST',
            'transferAmount' => 100000,
            'currency' => 'VND',
        ];

        $this->postJson('/webhooks/sepay', $fakeRefPayload, [
            'Authorization' => 'Apikey test-webhook-secret-token',
        ])->assertNotFound();

        $this->assertDatabaseMissing('processed_webhook_events', [
            'provider_event_id' => 'sepay-evt-tamper-2',
        ]);
    }

    public function test_mismatched_webhook_after_successful_payment_does_not_downgrade_status(): void
    {
        $community = Community::factory()->create(['access_mode' => 'PAID']);
        $user = User::factory()->create();

        $payment = Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'amount' => 100000,
            'currency' => 'VND',
            'status' => Payment::STATUS_SUCCEEDED,
            'paid_at' => now(),
        ]);

        $this->createMembership($community, $user, 'ACTIVE');

        $payload = [
            'id' => 'sepay-evt-post-success-mismatch',
            'referenceCode' => 'MB-FT-post-success-mismatch',
            'content' => $payment->external_reference,
            'transferAmount' => 50000,
            'currency' => 'USD',
        ];

        $response = $this->postJson('/webhooks/sepay', $payload, [
            'Authorization' => 'Apikey test-webhook-secret-token',
        ]);

        $response->assertOk();
        $response->assertJson(['message' => 'Payment was already completed.']);
        $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->refresh()->status);
        $this->assertSame('ACTIVE', CommunityMembership::where('user_id', $user->id)->value('status'));
    }

    public function test_invalid_webhook_secret_or_auth_header_is_rejected_with_401(): void
    {
        $payment = Payment::factory()->create([
            'amount' => 100000,
            'status' => Payment::STATUS_PENDING,
        ]);

        $payload = [
            'id' => 'sepay-evt-invalid-auth',
            'content' => $payment->external_reference,
            'transferAmount' => 100000,
        ];

        // Wrong secret token
        $this->postJson('/webhooks/sepay', $payload, [
            'Authorization' => 'Apikey wrong-secret-token',
        ])->assertStatus(401);

        // Missing authorization header
        $this->postJson('/webhooks/sepay', $payload)->assertStatus(401);

        $this->assertSame(Payment::STATUS_PENDING, $payment->refresh()->status);
    }

    public function test_duplicate_or_replayed_ipn_is_idempotent_and_does_not_reactivate_or_duplicate(): void
    {
        $community = Community::factory()->create(['access_mode' => 'PAID']);
        $user = User::factory()->create();

        $payment = Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'amount' => 100000,
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->createMembership($community, $user, 'PENDING_PAYMENT');

        $payload = [
            'id' => 'sepay-event-replay-101',
            'referenceCode' => 'MB-FT-1234',
            'content' => $payment->external_reference,
            'transferAmount' => 100000,
            'currency' => 'VND',
        ];

        // First delivery: success
        $res1 = $this->postJson('/webhooks/sepay', $payload, [
            'Authorization' => 'Apikey test-webhook-secret-token',
        ]);
        $res1->assertOk();
        $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->refresh()->status);
        $this->assertSame(1, ProcessedWebhookEvent::where('provider_event_id', 'sepay-event-replay-101')->count());

        // Second delivery (duplicate / replay): safe 200 with idempotent message
        $res2 = $this->postJson('/webhooks/sepay', $payload, [
            'Authorization' => 'Apikey test-webhook-secret-token',
        ]);
        $res2->assertOk();
        $res2->assertJson(['message' => 'Webhook event has already been processed.']);

        // Processed event count remains exactly 1
        $this->assertSame(1, ProcessedWebhookEvent::where('provider_event_id', 'sepay-event-replay-101')->count());
        $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->refresh()->status);
    }

    public function test_browser_redirect_or_status_check_does_not_grant_membership_access(): void
    {
        $community = Community::factory()->create(['access_mode' => 'PAID']);
        $user = User::factory()->create();

        $payment = Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->createMembership($community, $user, 'PENDING_PAYMENT');

        // Calling status endpoint with fake parameters
        $this->actingAs($user)->getJson('/payments/'.$payment->external_reference.'/status?status=SUCCEEDED&paid=1')
            ->assertOk()
            ->assertJson([
                'status' => 'PENDING',
                'is_succeeded' => false,
            ]);

        // Membership MUST remain PENDING_PAYMENT (AC-04)
        $this->assertSame('PENDING_PAYMENT', CommunityMembership::where('user_id', $user->id)->value('status'));
        $this->assertSame(Payment::STATUS_PENDING, $payment->refresh()->status);

        // Community still 404
        $this->actingAs($user)->get('/communities/'.$community->slug)->assertNotFound();
    }

    public function test_unauthorized_user_cannot_view_or_checkout_another_users_payment(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $payment = Payment::factory()->create([
            'user_id' => $userA->id,
            'status' => Payment::STATUS_PENDING,
        ]);

        // User B cannot access User A's checkout
        $this->actingAs($userB)->get('/payments/'.$payment->external_reference.'/checkout')->assertNotFound();

        // User B cannot access User A's status
        $this->actingAs($userB)->get('/payments/'.$payment->external_reference.'/status')->assertNotFound();
    }

    public function test_payment_checkout_screen_renders_sepay_transfer_details_and_qr(): void
    {
        $user = User::factory()->create();
        $payment = Payment::factory()->create([
            'user_id' => $user->id,
            'amount' => 100000,
            'currency' => 'VND',
            'status' => Payment::STATUS_PENDING,
        ]);

        $response = $this->actingAs($user)->get('/payments/'.$payment->external_reference.'/checkout');

        $response->assertOk();
        $response->assertSee($payment->external_reference);
        $response->assertSee('100.000 VND');
        $response->assertSee('SePay Sandbox');
    }
}
