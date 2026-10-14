<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Models\Community;
use App\Models\CommunityInvitation;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\Payment;
use App\Models\ReferralCommission;
use App\Models\ReferralInvitationRequest;
use App\Models\User;
use App\Services\Payment\SePayGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ReferralCommissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.sepay.webhook_token' => 'test-webhook-secret-token',
            'services.sepay.test_mode' => true,
            'services.sepay.account_number' => 'TEST-ACCOUNT-001',
        ]);
        $this->app->bind(PaymentGateway::class, SePayGateway::class);
    }

    protected function createActiveMember(Community $community, ?User $user = null): User
    {
        $user = $user ?? User::factory()->create();
        CommunityMembership::forceCreate([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => 'ACTIVE',
            'access_kind' => CommunityMembership::ACCESS_LIFETIME,
        ]);

        return $user;
    }

    protected function createAttributedInvitation(Community $community, User $invitee, ?User $referrer = null, int $daysAgo = 0): array
    {
        $token = bin2hex(random_bytes(32));
        $invitation = new CommunityInvitation;
        $invitation->forceFill([
            'community_id' => $community->id,
            'inviter_id' => $community->creator_id,
            'referrer_id' => $referrer?->id,
            'normalized_email' => strtolower($invitee->email),
            'token_hash' => hash('sha256', $token),
            'status' => 'PENDING',
            'created_at' => now('UTC')->subDays($daysAgo),
            'expires_at' => now('UTC')->addDays(7),
        ])->save();

        return [$invitation, $token];
    }

    protected function sendIpn(Payment $payment, int $transferAmount = 100000, string $referenceCode = 'MB-FT-998877'): TestResponse
    {
        $ipnPayload = [
            'id' => random_int(10000, 99999),
            'transferType' => 'in',
            'accountNumber' => 'TEST-ACCOUNT-001',
            'referenceCode' => $referenceCode,
            'content' => 'Thanh toan '.$payment->external_reference,
            'transferAmount' => $transferAmount,
            'currency' => 'VND',
        ];

        return $this->postJson('/webhooks/sepay', $ipnPayload, [
            'Authorization' => 'Apikey test-webhook-secret-token',
        ]);
    }

    public function test_ac01_approved_attributed_invitation_with_admission_payment_creates_exact_floor_commission(): void
    {
        $community = Community::factory()->create([
            'access_mode' => 'PAID',
            'price_vnd' => 200000,
            'referral_commission_rate' => 15,
        ]);
        $referrer = $this->createActiveMember($community);
        $invitee = User::factory()->create();

        [$invitation, $token] = $this->createAttributedInvitation($community, $invitee, $referrer);

        // Initiate admission checkout
        $payment = Payment::factory()->forInvitation($invitation)->create([
            'community_id' => $community->id,
            'user_id' => $invitee->id,
            'community_invitation_id' => $invitation->id,
            'purpose' => Payment::PURPOSE_COMMUNITY_ACCESS,
            'membership_access_kind' => CommunityMembership::ACCESS_LIFETIME,
            'term_days' => null,
            'amount' => 200000,
            'currency' => 'VND',
            'status' => Payment::STATUS_PENDING,
        ]);

        CommunityMembership::forceCreate([
            'community_id' => $community->id,
            'user_id' => $invitee->id,
            'status' => 'PENDING_PAYMENT',
        ]);

        $response = $this->sendIpn($payment, 200000);
        $response->assertOk();

        // 15% of 200,000 = 30,000 VND
        $this->assertDatabaseHas('referral_commissions', [
            'community_id' => $community->id,
            'payment_id' => $payment->id,
            'referrer_id' => $referrer->id,
            'payer_id' => $invitee->id,
            'community_invitation_id' => $invitation->id,
            'purpose' => Payment::PURPOSE_COMMUNITY_ACCESS,
            'commission_rate' => 15,
            'payment_amount' => 200000,
            'commission_amount' => 30000,
            'currency' => 'VND',
            'status' => 'SIMULATED_UNPAID',
        ]);

        $this->assertSame(1, ReferralCommission::count());
    }

    public function test_ac01_course_purchase_by_referred_member_within_30_days_creates_course_access_commission(): void
    {
        $community = Community::factory()->create([
            'access_mode' => 'FREE',
            'referral_commission_rate' => 10,
        ]);
        $referrer = $this->createActiveMember($community);
        $buyer = User::factory()->create();

        // Attributed admission invitation accepted 10 days ago
        [$invitation, $token] = $this->createAttributedInvitation($community, $buyer, $referrer, 10);
        $invitation->forceFill([
            'status' => 'ACCEPTED',
            'accepted_at' => now('UTC')->subDays(10),
            'accepted_by_user_id' => $buyer->id,
        ])->save();

        $this->createActiveMember($community, $buyer);

        // Paid course setup
        $course = Course::factory()->create([
            'community_id' => $community->id,
            'access_mode' => Course::ACCESS_MODE_ONE_TIME,
            'price' => 350000,
            'status' => 'PUBLISHED',
        ]);

        $payment = Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $buyer->id,
            'course_id' => $course->id,
            'community_invitation_id' => null,
            'purpose' => Payment::PURPOSE_COURSE_ACCESS,
            'amount' => 350000,
            'currency' => 'VND',
            'status' => Payment::STATUS_PENDING,
        ]);

        $response = $this->sendIpn($payment, 350000);
        $response->assertOk();

        // 10% of 350,000 = 35,000 VND
        $this->assertDatabaseHas('referral_commissions', [
            'community_id' => $community->id,
            'payment_id' => $payment->id,
            'referrer_id' => $referrer->id,
            'payer_id' => $buyer->id,
            'community_invitation_id' => $invitation->id,
            'purpose' => Payment::PURPOSE_COURSE_ACCESS,
            'commission_rate' => 10,
            'payment_amount' => 350000,
            'commission_amount' => 35000,
            'currency' => 'VND',
            'status' => 'SIMULATED_UNPAID',
        ]);

        $this->assertSame(1, ReferralCommission::count());
    }

    public function test_ac02_uninvited_visitor_with_referral_code_cannot_discover_or_join_private_community(): void
    {
        $community = Community::factory()->create(['access_mode' => 'PAID', 'price_vnd' => 100000]);
        $outsider = User::factory()->create();

        // Guest or non-member visiting private community with referral query gets 404
        $this->actingAs($outsider)
            ->get('/communities/'.$community->slug.'?ref=RANDOMREF123')
            ->assertNotFound();

        // Outsider cannot view classroom, feed or members
        $this->actingAs($outsider)->get('/communities/'.$community->slug.'/classroom')->assertNotFound();
        $this->actingAs($outsider)->get('/communities/'.$community->slug.'/posts')->assertNotFound();
    }

    public function test_ac02_self_referral_generates_no_commission(): void
    {
        $community = Community::factory()->create([
            'access_mode' => 'PAID',
            'price_vnd' => 200000,
            'referral_commission_rate' => 20,
        ]);
        $user = User::factory()->create();

        // Invitation attributed to the same user (self-referral)
        [$invitation, $token] = $this->createAttributedInvitation($community, $user, $user);

        $payment = Payment::factory()->forInvitation($invitation)->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'community_invitation_id' => $invitation->id,
            'purpose' => Payment::PURPOSE_COMMUNITY_ACCESS,
            'membership_access_kind' => CommunityMembership::ACCESS_LIFETIME,
            'term_days' => null,
            'amount' => 200000,
            'currency' => 'VND',
            'status' => Payment::STATUS_PENDING,
        ]);

        CommunityMembership::forceCreate([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => 'PENDING_PAYMENT',
        ]);

        $response = $this->sendIpn($payment, 200000);
        $response->assertOk();

        // Admission passes, but NO commission is generated
        $this->assertSame(0, ReferralCommission::count());
    }

    public function test_ac02_payment_outside_30_day_attribution_window_generates_no_commission(): void
    {
        $community = Community::factory()->create([
            'access_mode' => 'FREE',
            'referral_commission_rate' => 15,
        ]);
        $referrer = $this->createActiveMember($community);
        $buyer = User::factory()->create();

        // Attributed invitation created 31 days ago (outside 30-day window)
        [$invitation, $token] = $this->createAttributedInvitation($community, $buyer, $referrer, 31);
        $invitation->forceFill([
            'status' => 'ACCEPTED',
            'accepted_at' => now('UTC')->subDays(31),
            'accepted_by_user_id' => $buyer->id,
        ])->save();

        $this->createActiveMember($community, $buyer);

        $course = Course::factory()->create([
            'community_id' => $community->id,
            'access_mode' => Course::ACCESS_MODE_ONE_TIME,
            'price' => 100000,
            'status' => 'PUBLISHED',
        ]);

        $payment = Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $buyer->id,
            'course_id' => $course->id,
            'purpose' => Payment::PURPOSE_COURSE_ACCESS,
            'amount' => 100000,
            'currency' => 'VND',
            'status' => Payment::STATUS_PENDING,
        ]);

        $response = $this->sendIpn($payment, 100000);
        $response->assertOk();

        // Outside window -> no commission
        $this->assertSame(0, ReferralCommission::count());
    }

    public function test_ac03_webhook_replay_and_failed_payment_never_create_duplicate_or_spurious_commission(): void
    {
        $community = Community::factory()->create([
            'access_mode' => 'PAID',
            'price_vnd' => 100000,
            'referral_commission_rate' => 10,
        ]);
        $referrer = $this->createActiveMember($community);
        $invitee = User::factory()->create();

        [$invitation, $token] = $this->createAttributedInvitation($community, $invitee, $referrer);

        $payment = Payment::factory()->forInvitation($invitation)->create([
            'community_id' => $community->id,
            'user_id' => $invitee->id,
            'community_invitation_id' => $invitation->id,
            'purpose' => Payment::PURPOSE_COMMUNITY_ACCESS,
            'membership_access_kind' => CommunityMembership::ACCESS_LIFETIME,
            'amount' => 100000,
            'currency' => 'VND',
            'status' => Payment::STATUS_PENDING,
        ]);

        CommunityMembership::forceCreate([
            'community_id' => $community->id,
            'user_id' => $invitee->id,
            'status' => 'PENDING_PAYMENT',
        ]);

        // First successful delivery
        $this->sendIpn($payment, 100000, 'EVENT-001')->assertOk();
        $this->assertSame(1, ReferralCommission::count());

        // Replay of same event
        $this->sendIpn($payment, 100000, 'EVENT-001')->assertOk();
        $this->assertSame(1, ReferralCommission::count());

        // Another payment that fails
        $payment2 = Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $invitee->id,
            'amount' => 100000,
            'currency' => 'VND',
            'status' => Payment::STATUS_PENDING,
        ]);

        // Wrong amount triggers failure
        $this->sendIpn($payment2, 50000, 'EVENT-002')->assertStatus(422);
        $this->assertSame(1, ReferralCommission::count());
    }

    public function test_ac03_membership_renewal_is_excluded_from_referral_commissions(): void
    {
        $community = Community::factory()->create([
            'access_mode' => 'PAID',
            'price_vnd' => 150000,
            'billing_period' => 'TERM_30_DAYS',
            'referral_commission_rate' => 20,
        ]);
        $referrer = $this->createActiveMember($community);
        $member = User::factory()->create();

        // Member has active term membership
        $membership = CommunityMembership::forceCreate([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
            'access_kind' => CommunityMembership::ACCESS_TERM,
            'expires_at' => now('UTC')->addDays(5),
        ]);

        // Membership renewal payment
        $payment = Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'community_membership_id' => $membership->id,
            'purpose' => Payment::PURPOSE_MEMBERSHIP_RENEWAL,
            'membership_access_kind' => CommunityMembership::ACCESS_TERM,
            'term_days' => 30,
            'amount' => 150000,
            'currency' => 'VND',
            'status' => Payment::STATUS_PENDING,
        ]);

        $response = $this->sendIpn($payment, 150000);
        $response->assertOk();

        // Renewal extended expiry, but generated NO referral commission
        $this->assertSame(0, ReferralCommission::count());
    }

    public function test_ac03_changing_community_rate_preserves_historical_snapshots_and_zero_rate_disables(): void
    {
        $community = Community::factory()->create([
            'access_mode' => 'PAID',
            'price_vnd' => 100000,
            'referral_commission_rate' => 15,
        ]);
        $referrer = $this->createActiveMember($community);
        $invitee1 = User::factory()->create();

        [$invitation1, $token1] = $this->createAttributedInvitation($community, $invitee1, $referrer);
        $payment1 = Payment::factory()->forInvitation($invitation1)->create([
            'community_id' => $community->id,
            'user_id' => $invitee1->id,
            'community_invitation_id' => $invitation1->id,
            'purpose' => Payment::PURPOSE_COMMUNITY_ACCESS,
            'membership_access_kind' => CommunityMembership::ACCESS_LIFETIME,
            'amount' => 100000,
            'currency' => 'VND',
            'status' => Payment::STATUS_PENDING,
        ]);
        CommunityMembership::forceCreate([
            'community_id' => $community->id,
            'user_id' => $invitee1->id,
            'status' => 'PENDING_PAYMENT',
        ]);
        $this->sendIpn($payment1, 100000, 'EVENT-SNAP-1')->assertOk();

        $commission1 = ReferralCommission::first();
        $this->assertSame(15, $commission1->commission_rate);
        $this->assertSame(15000, $commission1->commission_amount);

        // Creator updates rate to 0% (disabled)
        $this->actingAs($community->creator)->put('/communities/'.$community->slug, [
            'name' => $community->name,
            'access_mode' => 'PAID',
            'price_vnd' => 100000,
            'referral_commission_rate' => 0,
        ])->assertRedirect();

        $community->refresh();
        $this->assertSame(0, $community->referral_commission_rate);

        // Historical commission row is unaffected
        $commission1->refresh();
        $this->assertSame(15, $commission1->commission_rate);
        $this->assertSame(15000, $commission1->commission_amount);

        // Second payment under 0% rate generates NO commission
        $invitee2 = User::factory()->create();
        [$invitation2, $token2] = $this->createAttributedInvitation($community, $invitee2, $referrer);
        $payment2 = Payment::factory()->forInvitation($invitation2)->create([
            'community_id' => $community->id,
            'user_id' => $invitee2->id,
            'community_invitation_id' => $invitation2->id,
            'purpose' => Payment::PURPOSE_COMMUNITY_ACCESS,
            'membership_access_kind' => CommunityMembership::ACCESS_LIFETIME,
            'amount' => 100000,
            'currency' => 'VND',
            'status' => Payment::STATUS_PENDING,
        ]);
        CommunityMembership::forceCreate([
            'community_id' => $community->id,
            'user_id' => $invitee2->id,
            'status' => 'PENDING_PAYMENT',
        ]);
        $this->sendIpn($payment2, 100000, 'EVENT-SNAP-2')->assertOk();

        $this->assertSame(1, ReferralCommission::count());
    }

    public function test_ac04_referrer_sees_only_own_safe_totals_and_creator_sees_tenant_ledger(): void
    {
        $communityA = Community::factory()->create(['name' => 'Community Alpha', 'referral_commission_rate' => 10]);
        $communityB = Community::factory()->create(['name' => 'Community Beta', 'referral_commission_rate' => 10]);

        $referrerA = $this->createActiveMember($communityA);
        $referrerB = $this->createActiveMember($communityB);
        $payerA = User::factory()->create(['name' => 'Secret Payer Alpha', 'email' => 'alpha_secret@example.com']);
        $payerB = User::factory()->create(['name' => 'Secret Payer Beta', 'email' => 'beta_secret@example.com']);

        $paymentA = Payment::factory()->create(['community_id' => $communityA->id, 'user_id' => $payerA->id, 'amount' => 100000]);
        $paymentB = Payment::factory()->create(['community_id' => $communityB->id, 'user_id' => $payerB->id, 'amount' => 200000]);

        ReferralCommission::create([
            'community_id' => $communityA->id,
            'payment_id' => $paymentA->id,
            'referrer_id' => $referrerA->id,
            'payer_id' => $payerA->id,
            'purpose' => 'COMMUNITY_ACCESS',
            'commission_rate' => 10,
            'payment_amount' => 100000,
            'commission_amount' => 10000,
            'currency' => 'VND',
            'status' => 'SIMULATED_UNPAID',
        ]);

        ReferralCommission::create([
            'community_id' => $communityB->id,
            'payment_id' => $paymentB->id,
            'referrer_id' => $referrerB->id,
            'payer_id' => $payerB->id,
            'purpose' => 'COMMUNITY_ACCESS',
            'commission_rate' => 10,
            'payment_amount' => 200000,
            'commission_amount' => 20000,
            'currency' => 'VND',
            'status' => 'SIMULATED_UNPAID',
        ]);

        // Referrer A visits personal /account/referrals
        $responseA = $this->actingAs($referrerA)->get('/account/referrals');
        $responseA->assertOk();
        $responseA->assertSee('10,000 VND');
        $responseA->assertDontSee('20,000 VND');
        $responseA->assertDontSee('alpha_secret@example.com'); // Payer's email is not leaked

        // Non-creator cannot view creator referrals tab
        $this->actingAs($referrerA)->get('/communities/'.$communityA->slug.'/referrals')->assertNotFound();

        // Creator of Community A visits /communities/{slug}/referrals
        $responseCreator = $this->actingAs($communityA->creator)->get('/communities/'.$communityA->slug.'/referrals');
        $responseCreator->assertOk();
        $responseCreator->assertSee('10,000 VND');
        $responseCreator->assertDontSee('20,000 VND'); // Community B commission isolated
    }

    public function test_ac05_referrer_without_effective_access_at_payment_time_earns_no_new_commission_but_retains_history(): void
    {
        $community = Community::factory()->create([
            'access_mode' => 'PAID',
            'price_vnd' => 100000,
            'billing_period' => 'TERM_30_DAYS',
            'referral_commission_rate' => 20,
        ]);
        $referrer = User::factory()->create();

        // Referrer has an EXPIRED term membership
        CommunityMembership::forceCreate([
            'community_id' => $community->id,
            'user_id' => $referrer->id,
            'status' => 'ACTIVE',
            'access_kind' => CommunityMembership::ACCESS_TERM,
            'expires_at' => now('UTC')->subDay(),
        ]);

        // Historical commission from earlier valid period
        $oldPayment = Payment::factory()->create(['community_id' => $community->id, 'user_id' => User::factory()->create()->id, 'amount' => 100000]);
        ReferralCommission::create([
            'community_id' => $community->id,
            'payment_id' => $oldPayment->id,
            'referrer_id' => $referrer->id,
            'payer_id' => $oldPayment->user_id,
            'purpose' => 'COMMUNITY_ACCESS',
            'commission_rate' => 20,
            'payment_amount' => 100000,
            'commission_amount' => 20000,
            'currency' => 'VND',
            'status' => 'SIMULATED_UNPAID',
        ]);

        // New invitee pays today while referrer is expired
        $invitee = User::factory()->create();
        [$invitation, $token] = $this->createAttributedInvitation($community, $invitee, $referrer);
        $newPayment = Payment::factory()->forInvitation($invitation)->create([
            'community_id' => $community->id,
            'user_id' => $invitee->id,
            'community_invitation_id' => $invitation->id,
            'purpose' => Payment::PURPOSE_COMMUNITY_ACCESS,
            'membership_access_kind' => CommunityMembership::ACCESS_LIFETIME,
            'amount' => 100000,
            'currency' => 'VND',
            'status' => Payment::STATUS_PENDING,
        ]);
        CommunityMembership::forceCreate([
            'community_id' => $community->id,
            'user_id' => $invitee->id,
            'status' => 'PENDING_PAYMENT',
        ]);

        $this->sendIpn($newPayment, 100000, 'EVENT-EXP-1')->assertOk();

        // No NEW commission created because referrer lacked effective access
        $this->assertSame(1, ReferralCommission::count());

        // But referrer can STILL view their historical commission on /account/referrals
        $this->actingAs($referrer)->get('/account/referrals')
            ->assertOk()
            ->assertSee('20,000 VND')
            ->assertSee('SIMULATED_UNPAID');
    }

    public function test_member_can_request_referral_invitation_and_creator_can_approve_or_reject(): void
    {
        $community = Community::factory()->create(['access_mode' => 'PAID', 'referral_commission_rate' => 15]);
        $member = $this->createActiveMember($community);

        // Member requests an invitation for friend
        $this->actingAs($member)->post('/communities/'.$community->slug.'/referrals/request', [
            'email' => 'friend_invitee@example.com',
        ])->assertRedirect();

        $request = ReferralInvitationRequest::where('community_id', $community->id)
            ->where('email', 'friend_invitee@example.com')
            ->first();

        $this->assertNotNull($request);
        $this->assertSame('PENDING', $request->status);
        $this->assertSame($member->id, $request->referrer_id);

        // Member cannot self-refer
        $this->actingAs($member)->post('/communities/'.$community->slug.'/referrals/request', [
            'email' => $member->email,
        ])->assertSessionHasErrors('email');

        // Creator approves request
        $this->actingAs($community->creator)->post('/communities/'.$community->slug.'/referrals/'.$request->id.'/approve')
            ->assertRedirect();

        $request->refresh();
        $this->assertSame('APPROVED', $request->status);
        $this->assertNotNull($request->community_invitation_id);

        $invitation = CommunityInvitation::find($request->community_invitation_id);
        $this->assertSame($member->id, $invitation->referrer_id);
        $this->assertSame('friend_invitee@example.com', $invitation->normalized_email);
    }
}
