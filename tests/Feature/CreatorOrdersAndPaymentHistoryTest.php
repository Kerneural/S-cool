<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityInvitation;
use App\Models\CommunityMembership;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatorOrdersAndPaymentHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_creator_sees_only_own_community_attempts_with_status_filtering(): void
    {
        $creatorA = User::factory()->create();
        $communityA = Community::factory()->create(['creator_id' => $creatorA->id, 'status' => 'ACTIVE']);

        $creatorB = User::factory()->create();
        $communityB = Community::factory()->create(['creator_id' => $creatorB->id, 'status' => 'ACTIVE']);

        $user1 = User::factory()->create(['name' => 'Buyer One']);
        $user2 = User::factory()->create(['name' => 'Buyer Two']);

        // Attempts in Community A
        Payment::factory()->create([
            'community_id' => $communityA->id,
            'user_id' => $user1->id,
            'status' => Payment::STATUS_SUCCEEDED,
            'amount' => 100000,
            'external_reference' => 'REF-COMM-A-SUCC',
        ]);
        Payment::factory()->create([
            'community_id' => $communityA->id,
            'user_id' => $user2->id,
            'status' => Payment::STATUS_PENDING,
            'amount' => 100000,
            'external_reference' => 'REF-COMM-A-PEND',
        ]);

        // Attempt in Community B
        Payment::factory()->create([
            'community_id' => $communityB->id,
            'user_id' => $user1->id,
            'status' => Payment::STATUS_SUCCEEDED,
            'amount' => 500000,
            'external_reference' => 'REF-COMM-B-SECRET',
        ]);

        // Creator A views Community A orders
        $response = $this->actingAs($creatorA)->get(route('communities.orders.index', $communityA));
        $response->assertOk();
        $response->assertSee('REF-COMM-A-SUCC');
        $response->assertSee('REF-COMM-A-PEND');
        $response->assertDontSee('REF-COMM-B-SECRET');

        // Status filter: SUCCEEDED
        $resFilter = $this->actingAs($creatorA)->get(route('communities.orders.index', [
            'community' => $communityA,
            'status' => 'SUCCEEDED',
        ]));
        $resFilter->assertOk();
        $resFilter->assertSee('REF-COMM-A-SUCC');
        $resFilter->assertDontSee('REF-COMM-A-PEND');

        // Creator B cannot access Community A orders
        $this->actingAs($creatorB)->get(route('communities.orders.index', $communityA))->assertNotFound();
    }

    public function test_payer_sees_only_own_attempts_and_guessed_id_returns_404(): void
    {
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $paymentA = Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $userA->id,
            'amount' => 150000,
            'status' => Payment::STATUS_SUCCEEDED,
            'external_reference' => 'REF-USER-A',
        ]);

        $paymentB = Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $userB->id,
            'amount' => 250000,
            'status' => Payment::STATUS_SUCCEEDED,
            'external_reference' => 'REF-USER-B',
        ]);

        // User A visits index
        $response = $this->actingAs($userA)->get(route('account.payment-history.index'));
        $response->assertOk();
        $response->assertSee('REF-USER-A');
        $response->assertDontSee('REF-USER-B');

        // User A views own receipt
        $this->actingAs($userA)
            ->get(route('account.payment-history.show', $paymentA))
            ->assertOk()
            ->assertSee('REF-USER-A')
            ->assertSee('150.000 VND');

        // User A tries to view User B's receipt by ID -> 404
        $this->actingAs($userA)
            ->get(route('account.payment-history.show', $paymentB))
            ->assertNotFound();
    }

    public function test_price_change_preserves_past_snapshot_and_retries_remain_separate(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
            'price_vnd' => 200000,
        ]);

        $user = User::factory()->create();
        $invitation = CommunityInvitation::factory()->create(['community_id' => $community->id]);

        // Attempt 1: Cancelled
        $attempt1 = Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'community_invitation_id' => $invitation->id,
            'amount' => 200000,
            'status' => Payment::STATUS_CANCELLED,
            'external_reference' => 'REF-ATTEMPT-1',
        ]);

        // Later community price changes
        $community->update(['price_vnd' => 500000]);

        // Attempt 2: Succeeded (e.g. at 200,000 price)
        $attempt2 = Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'community_invitation_id' => $invitation->id,
            'amount' => 200000,
            'status' => Payment::STATUS_SUCCEEDED,
            'external_reference' => 'REF-ATTEMPT-2',
            'paid_at' => now(),
        ]);

        // Check creator orders
        $response = $this->actingAs($creator)->get(route('communities.orders.index', $community));
        $response->assertOk();
        $response->assertViewHas('summary', function ($summary) {
            return $summary['total'] === 2
                && $summary['succeeded'] === 1
                && $summary['cancelled'] === 1
                && $summary['succeeded_sum'] === 200000;
        });

        // Price change did not alter attempt 1 snapshot
        $this->assertSame(200000, $attempt1->fresh()->amount);
        $this->assertSame(200000, $attempt2->fresh()->amount);
    }

    public function test_safe_history_after_removal_exposes_no_secrets_and_grants_no_access(): void
    {
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $user = User::factory()->create();

        // User was removed
        $membership = CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => 'REMOVED',
        ]);

        $payment = Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => Payment::STATUS_SUCCEEDED,
            'amount' => 300000,
            'external_reference' => 'REF-REMOVED-USER',
            'payload' => ['secret_api_token' => 'super-secret-xyz', 'raw_webhook_dump' => 12345],
        ]);

        $response = $this->actingAs($user)->get(route('account.payment-history.show', $payment));
        $response->assertOk();
        $response->assertSee('REF-REMOVED-USER');
        $response->assertSee('300.000 VND');

        // Safe: never dumps payload secrets
        $response->assertDontSee('super-secret-xyz');
        $response->assertDontSee('raw_webhook_dump');

        // Inactive membership notice, does not link to community feed
        $response->assertSee('Your membership in this community is currently inactive');
        $response->assertDontSee('Go to Community &rarr;');

        // Does not reactivate membership
        $this->assertSame('REMOVED', $membership->fresh()->status);
    }

    public function test_all_order_views_are_read_only_and_cannot_modify_database_state(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $user = User::factory()->create();

        $payment = Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => Payment::STATUS_PENDING,
            'amount' => 100000,
        ]);

        // Sending unexpected query params does not modify status or amount
        $this->actingAs($creator)->get(route('communities.orders.index', [
            'community' => $community,
            'status' => Payment::STATUS_SUCCEEDED,
            'amount' => 999999,
        ]))->assertOk();

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertSame(100000, $payment->fresh()->amount);

        // POST to order views is method not allowed / not found
        $this->actingAs($creator)->post(route('communities.orders.index', $community))->assertMethodNotAllowed();
        $this->actingAs($user)->post(route('account.payment-history.index'))->assertMethodNotAllowed();
    }

    public function test_array_input_for_status_does_not_cause_500_or_type_error(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        // Probe passing status as an array: status[]=SUCCEEDED
        $response = $this->actingAs($creator)->get('/communities/'.$community->slug.'/orders?status[]=SUCCEEDED');
        $response->assertOk();
        $response->assertViewHas('statusFilter', 'ALL');
    }

    public function test_creator_orders_date_boundary_filter_and_tenant_isolation(): void
    {
        $creatorA = User::factory()->create();
        $communityA = Community::factory()->create(['creator_id' => $creatorA->id, 'status' => 'ACTIVE']);

        $creatorB = User::factory()->create();
        $communityB = Community::factory()->create(['creator_id' => $creatorB->id, 'status' => 'ACTIVE']);

        $user = User::factory()->create();

        // Payment 1: Today in Community A
        Payment::factory()->create([
            'community_id' => $communityA->id,
            'user_id' => $user->id,
            'external_reference' => 'COMM-A-RECENT',
            'created_at' => now(),
        ]);

        // Payment 2: 40 days ago in Community A
        Payment::factory()->create([
            'community_id' => $communityA->id,
            'user_id' => $user->id,
            'external_reference' => 'COMM-A-OLD',
            'created_at' => now()->subDays(40),
        ]);

        // Payment 3: Today in Community B
        Payment::factory()->create([
            'community_id' => $communityB->id,
            'user_id' => $user->id,
            'external_reference' => 'COMM-B-RECENT',
            'created_at' => now(),
        ]);

        // Range = 7 -> Only COMM-A-RECENT (COMM-A-OLD is excluded by date boundary, COMM-B-RECENT is excluded by tenant scope)
        $res7 = $this->actingAs($creatorA)->get(route('communities.orders.index', [
            'community' => $communityA,
            'range' => 7,
        ]));
        $res7->assertOk();
        $res7->assertSee('COMM-A-RECENT');
        $res7->assertDontSee('COMM-A-OLD');
        $res7->assertDontSee('COMM-B-RECENT');

        // Range = 90 -> Both COMM-A-RECENT and COMM-A-OLD appear, but never COMM-B-RECENT
        $res90 = $this->actingAs($creatorA)->get(route('communities.orders.index', [
            'community' => $communityA,
            'range' => 90,
        ]));
        $res90->assertOk();
        $res90->assertSee('COMM-A-RECENT');
        $res90->assertSee('COMM-A-OLD');
        $res90->assertDontSee('COMM-B-RECENT');
    }

    public function test_resume_checkout_button_respects_current_eligibility_and_hides_when_ineligible(): void
    {
        $community = Community::factory()->create(['status' => 'ACTIVE', 'access_mode' => 'PAID']);
        $user = User::factory()->create();

        // Case A: Valid pending invitation and pending payment -> button is visible
        $validInvite = CommunityInvitation::factory()->create([
            'community_id' => $community->id,
            'status' => 'PENDING',
            'expires_at' => now()->addDays(7),
        ]);
        $validPayment = Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'community_invitation_id' => $validInvite->id,
            'status' => Payment::STATUS_PENDING,
            'external_reference' => 'REF-ELIGIBLE',
        ]);

        $resValid = $this->actingAs($user)->get(route('account.payment-history.show', $validPayment));
        $resValid->assertOk();
        $resValid->assertSee('Open Checkout');
        $this->actingAs($user)->get(route('account.payment-history.index'))->assertSee('Resume Checkout');

        // Case B: Expired invitation -> button hidden, shows unavailable message
        $expiredInvite = CommunityInvitation::factory()->create([
            'community_id' => $community->id,
            'status' => 'PENDING',
            'expires_at' => now()->subDay(),
        ]);
        $expiredPayment = Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'community_invitation_id' => $expiredInvite->id,
            'status' => Payment::STATUS_PENDING,
            'external_reference' => 'REF-EXPIRED',
        ]);

        $resExpired = $this->actingAs($user)->get(route('account.payment-history.show', $expiredPayment));
        $resExpired->assertOk();
        $resExpired->assertDontSee('Open Checkout');
        $resExpired->assertSee('Checkout No Longer Available');

        // Case C: Revoked invitation -> button hidden, shows unavailable message
        $revokedInvite = CommunityInvitation::factory()->create([
            'community_id' => $community->id,
            'status' => 'REVOKED',
            'expires_at' => now()->addDays(7),
        ]);
        $revokedPayment = Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'community_invitation_id' => $revokedInvite->id,
            'status' => Payment::STATUS_PENDING,
            'external_reference' => 'REF-REVOKED',
        ]);

        $resRevoked = $this->actingAs($user)->get(route('account.payment-history.show', $revokedPayment));
        $resRevoked->assertOk();
        $resRevoked->assertDontSee('Open Checkout');
        $resRevoked->assertSee('Checkout No Longer Available');

        // Case D: User membership is SUSPENDED or REMOVED -> button hidden
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => 'SUSPENDED',
        ]);

        $resSuspended = $this->actingAs($user)->get(route('account.payment-history.show', $validPayment));
        $resSuspended->assertOk();
        $resSuspended->assertDontSee('Open Checkout');
        $resSuspended->assertSee('Checkout No Longer Available');
    }
}
