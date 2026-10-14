<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Models\Community;
use App\Models\CommunityInvitation;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseEntitlement;
use App\Models\Payment;
use App\Models\ProcessedWebhookEvent;
use App\Models\ReferralCommission;
use App\Models\ReferralInvitationRequest;
use App\Models\User;
use App\Services\Payment\SePayGateway;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ReferralAuditRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-01-31 12:00:00', 'UTC'));
        config(['services.sepay.webhook_token' => 'referral-audit-token', 'services.sepay.test_mode' => true, 'services.sepay.account_number' => 'REFERRAL-AUDIT']);
        $this->app->bind(PaymentGateway::class, SePayGateway::class);
        Queue::fake();
    }

    private function member(Community $community): User
    {
        $user = User::factory()->create();
        CommunityMembership::forceCreate(['community_id' => $community->id, 'user_id' => $user->id, 'status' => 'ACTIVE']);

        return $user;
    }

    private function invite(Community $community, User $payer, ?User $referrer, Carbon $created, string $status = 'ACCEPTED'): CommunityInvitation
    {
        return CommunityInvitation::forceCreate([
            'community_id' => $community->id, 'inviter_id' => $community->creator_id,
            'referrer_id' => $referrer?->id, 'normalized_email' => $payer->email,
            'created_at' => $created, 'expires_at' => now('UTC')->addDays(7),
            'status' => $status, 'accepted_at' => $status === 'ACCEPTED' ? $created : null,
            'accepted_by_user_id' => $status === 'ACCEPTED' ? $payer->id : null,
        ]);
    }

    private function coursePayment(Community $community, User $payer): Payment
    {
        $course = Course::factory()->published()->create(['community_id' => $community->id, 'access_mode' => 'ONE_TIME', 'price' => 100003]);

        return Payment::factory()->create(['community_id' => $community->id, 'user_id' => $payer->id,
            'purpose' => Payment::PURPOSE_COURSE_ACCESS, 'course_id' => $course->id,
            'community_invitation_id' => null, 'status' => 'PENDING', 'amount' => 100003]);
    }

    private function webhook(Payment $payment, int $event = 1750000001)
    {
        return $this->postJson('/webhooks/sepay', ['id' => $event, 'transferType' => 'in',
            'accountNumber' => 'REFERRAL-AUDIT', 'referenceCode' => 'AUDIT-'.$event,
            'content' => $payment->external_reference, 'transferAmount' => $payment->amount, 'currency' => 'VND'],
            ['Authorization' => 'Apikey referral-audit-token']);
    }

    public function test_course_purchase_does_not_skip_unattributed_first_accepted_invitation(): void
    {
        $community = Community::factory()->create(['referral_commission_rate' => 15]);
        $payer = $this->member($community);
        $referrer = $this->member($community);
        $this->invite($community, $payer, null, now('UTC')->subDays(5));
        $this->invite($community, $payer, $referrer, now('UTC')->subDay());
        $payment = $this->coursePayment($community, $payer);
        $this->webhook($payment)->assertOk();
        $this->assertDatabaseCount('referral_commissions', 0);
        $this->assertDatabaseHas('course_entitlements', ['payment_id' => $payment->id]);
    }

    public function test_rejoin_admission_does_not_restart_first_touch_window(): void
    {
        $community = Community::factory()->create(['access_mode' => 'PAID', 'price_vnd' => 100003, 'referral_commission_rate' => 15]);
        $payer = User::factory()->create();
        $referrer = $this->member($community);
        $this->invite($community, $payer, $referrer, now('UTC')->subDays(31));
        $current = $this->invite($community, $payer, $referrer, now('UTC'), 'PENDING');
        CommunityMembership::forceCreate(['community_id' => $community->id, 'user_id' => $payer->id, 'status' => 'PENDING_PAYMENT']);
        $payment = Payment::factory()->create(['community_id' => $community->id, 'user_id' => $payer->id,
            'community_invitation_id' => $current->id, 'purpose' => Payment::PURPOSE_COMMUNITY_ACCESS,
            'status' => 'PENDING', 'amount' => 100003]);
        $this->webhook($payment)->assertOk();
        $this->assertDatabaseCount('referral_commissions', 0);
        $this->assertSame('SUCCEEDED', $payment->fresh()->status);
    }

    public function test_exact_30_day_boundary_is_excluded_but_one_second_before_is_eligible(): void
    {
        $community = Community::factory()->create(['referral_commission_rate' => 15]);
        $payer = $this->member($community);
        $referrer = $this->member($community);
        $this->invite($community, $payer, $referrer, now('UTC')->subDays(30));
        $payment = $this->coursePayment($community, $payer);
        $this->travelBack();
        $this->travelTo(Carbon::parse('2026-01-31 11:59:59', 'UTC'));
        $this->webhook($payment)->assertOk();
        $this->assertDatabaseHas('referral_commissions', ['payment_id' => $payment->id, 'commission_amount' => 15000]);
        $next = $this->coursePayment($community, $payer);
        $this->travelTo(Carbon::parse('2026-01-31 12:00:00', 'UTC'));
        $this->webhook($next, 1750000002)->assertOk();
        $this->assertDatabaseMissing('referral_commissions', ['payment_id' => $next->id]);
    }

    public function test_approval_failure_rolls_back_invitation_and_request_link(): void
    {
        $community = Community::factory()->create(['referral_commission_rate' => 15]);
        $referrer = $this->member($community);
        $candidate = User::factory()->create();
        $referralRequest = ReferralInvitationRequest::create(['community_id' => $community->id, 'referrer_id' => $referrer->id, 'email' => $candidate->email]);
        ReferralInvitationRequest::updating(function (): void {
            throw new \RuntimeException('Injected referral approval failure');
        });
        try {
            $this->withoutExceptionHandling()->actingAs($community->creator)
                ->post(route('communities.referrals.approve', [$community, $referralRequest]));
            $this->fail('Expected the injected write failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Injected referral approval failure', $exception->getMessage());
        } finally {
            ReferralInvitationRequest::flushEventListeners();
        }
        $this->assertDatabaseCount('community_invitations', 0);
        $this->assertSame('PENDING', $referralRequest->fresh()->status);
        $this->assertNull($referralRequest->fresh()->community_invitation_id);
    }

    public function test_stale_rejection_cannot_overwrite_an_approved_request(): void
    {
        $community = Community::factory()->create(['referral_commission_rate' => 15]);
        $referrer = $this->member($community);
        $candidate = User::factory()->create();
        $referralRequest = ReferralInvitationRequest::create(['community_id' => $community->id, 'referrer_id' => $referrer->id, 'email' => $candidate->email]);
        $changed = false;
        ReferralInvitationRequest::retrieved(function ($bound) use ($referralRequest, &$changed): void {
            if (! $changed && $bound->id === $referralRequest->id) {
                $changed = true;
                DB::table('referral_invitation_requests')->where('id', $bound->id)->update(['status' => 'APPROVED']);
            }
        });
        try {
            $this->actingAs($community->creator)->post(route('communities.referrals.reject', [$community, $referralRequest]))->assertNotFound();
        } finally {
            ReferralInvitationRequest::flushEventListeners();
        }
        $this->assertSame('APPROVED', $referralRequest->fresh()->status);
    }

    public function test_approval_conflict_displays_validation_error_without_extra_invitation(): void
    {
        $community = Community::factory()->create(['referral_commission_rate' => 15]);
        $referrer = $this->member($community);
        $candidate = User::factory()->create();
        $this->invite($community, $candidate, null, now('UTC'), 'PENDING');
        $referralRequest = ReferralInvitationRequest::create(['community_id' => $community->id, 'referrer_id' => $referrer->id, 'email' => $candidate->email]);
        $dashboard = route('communities.referrals.index', $community);
        $this->actingAs($community->creator)->from($dashboard)
            ->post(route('communities.referrals.approve', [$community, $referralRequest]))
            ->assertRedirect($dashboard)->assertSessionHasErrors('email');
        $this->get($dashboard)->assertOk()->assertSee('A pending invitation already exists. Revoke it before sending another.');
        $this->assertDatabaseCount('community_invitations', 1);
        $this->assertSame('PENDING', $referralRequest->fresh()->status);
        $this->assertNull($referralRequest->fresh()->community_invitation_id);
    }

    public function test_creation_persists_valid_creator_referral_rates(): void
    {
        $creator = User::factory()->create();
        foreach ([0, 15, 30] as $rate) {
            $slug = 'referral-create-rate-'.$rate;
            $this->actingAs($creator)->post(route('communities.store'), ['name' => 'Referral Creation',
                'slug' => $slug, 'access_mode' => 'FREE', 'referral_commission_rate' => $rate])->assertRedirect();
            $this->assertDatabaseHas('communities', ['slug' => $slug, 'creator_id' => $creator->id, 'referral_commission_rate' => $rate]);
        }
    }

    public function test_creation_and_update_reject_invalid_rates_without_mutation(): void
    {
        $community = Community::factory()->create(['referral_commission_rate' => 15]);
        foreach ([-1, 31, '1.5'] as $index => $rate) {
            $slug = 'referral-invalid-rate-'.$index;
            $this->actingAs($community->creator)->post(route('communities.store'), ['name' => 'Invalid Referral',
                'slug' => $slug, 'access_mode' => 'FREE', 'referral_commission_rate' => $rate])->assertSessionHasErrors('referral_commission_rate');
            $this->assertDatabaseMissing('communities', ['slug' => $slug]);
            $this->put(route('communities.update', $community), ['name' => $community->name,
                'referral_commission_rate' => $rate])->assertSessionHasErrors('referral_commission_rate');
            $this->assertSame(15, $community->fresh()->referral_commission_rate);
        }
    }

    public function test_request_review_actions_are_creator_and_tenant_scoped(): void
    {
        $community = Community::factory()->create(['referral_commission_rate' => 15]);
        $other = Community::factory()->create();
        $referrer = $this->member($community);
        $candidate = User::factory()->create();
        $referralRequest = ReferralInvitationRequest::create(['community_id' => $community->id, 'referrer_id' => $referrer->id, 'email' => $candidate->email]);
        foreach (['approve', 'reject'] as $action) {
            $this->actingAs($referrer)->post(route('communities.referrals.'.$action, [$community, $referralRequest]))->assertNotFound();
            $this->actingAs($other->creator)->post(route('communities.referrals.'.$action, [$community, $referralRequest]))->assertNotFound();
            $this->post(route('communities.referrals.'.$action, [$other, $referralRequest]))->assertNotFound();
        }
        $this->assertDatabaseCount('community_invitations', 0);
        $this->assertSame('PENDING', $referralRequest->fresh()->status);
    }

    public function test_commission_failure_rolls_back_course_payment_entitlement_and_event_then_replays(): void
    {
        $community = Community::factory()->create(['referral_commission_rate' => 15]);
        $payer = $this->member($community);
        $referrer = $this->member($community);
        $this->invite($community, $payer, $referrer, now('UTC')->subDay());
        $payment = $this->coursePayment($community, $payer);
        ReferralCommission::creating(function (): void {
            throw new \RuntimeException('Injected commission write failure');
        });
        try {
            $this->withoutExceptionHandling()->webhook($payment);
            $this->fail('Expected the injected write failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Injected commission write failure', $exception->getMessage());
        } finally {
            ReferralCommission::flushEventListeners();
        }
        $this->assertSame('PENDING', $payment->fresh()->status);
        $this->assertSame(0, CourseEntitlement::count());
        $this->assertSame(0, ProcessedWebhookEvent::count());
        $this->webhook($payment)->assertOk();
        $this->webhook($payment)->assertOk();
        $this->assertDatabaseCount('referral_commissions', 1);
        $this->assertDatabaseCount('course_entitlements', 1);
    }
}
