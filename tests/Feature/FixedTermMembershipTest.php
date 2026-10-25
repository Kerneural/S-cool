<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Jobs\SendCoursePublishedNotification;
use App\Jobs\SendMemberActivatedNotification;
use App\Models\AppNotification;
use App\Models\Community;
use App\Models\CommunityInvitation;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseEntitlement;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\Payment;
use App\Models\PointContribution;
use App\Models\Post;
use App\Models\ProcessedWebhookEvent;
use App\Models\User;
use App\Services\Payment\SePayGateway;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FixedTermMembershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-01-01 12:00:00', 'UTC'));
        config(['services.sepay.test_mode' => true, 'services.sepay.webhook_token' => 'term-test-token', 'services.sepay.account_number' => 'TERM-TEST']);
        $this->app->bind(PaymentGateway::class, SePayGateway::class);
        Storage::fake('community_media');
    }

    private function member(?Carbon $expiry = null, string $kind = 'TERM'): array
    {
        $community = Community::factory()->create(['access_mode' => 'PAID', 'price_vnd' => 200000, 'billing_period' => 'TERM_30_DAYS']);
        $user = User::factory()->create();
        $membership = CommunityMembership::forceCreate(['community_id' => $community->id, 'user_id' => $user->id, 'status' => 'ACTIVE', 'access_kind' => $kind, 'expires_at' => $kind === 'TERM' ? ($expiry ?? now('UTC')->addDays(5)) : null]);

        return [$community, $user, $membership];
    }

    private function renewal(User $user, CommunityMembership $membership): Payment
    {
        $this->actingAs($user)->post(route('account.memberships.renew', $membership), ['amount' => 1, 'term_days' => 9999, 'user_id' => 999999])->assertRedirect();

        return Payment::where('community_membership_id', $membership->id)->where('status', 'PENDING')->firstOrFail();
    }

    private function webhook(Payment $payment, int $id = 1900000001)
    {
        return $this->postJson('/webhooks/sepay', ['id' => $id, 'transferType' => 'in', 'accountNumber' => 'TERM-TEST', 'referenceCode' => 'TERM-EVENT-'.$id, 'content' => $payment->external_reference, 'transferAmount' => $payment->amount, 'currency' => 'VND'], ['Authorization' => 'Apikey term-test-token']);
    }

    public function test_exact_expiry_denies_all_internal_modules_and_preserves_safe_own_renewal(): void
    {
        [$community, $user, $membership] = $this->member();
        $course = Course::factory()->published()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create(['course_section_id' => $section->id, 'status' => 'PUBLISHED']);
        $this->actingAs($community->creator)->post(route('communities.cover.store', $community), ['cover' => UploadedFile::fake()->image('term-cover.png')])->assertRedirect();
        $this->actingAs($user)->get(route('communities.show', $community))->assertOk();
        $this->get(route('communities.cover.show', $community))->assertOk();
        $this->travelTo($membership->expires_at->copy());
        foreach (['communities.show', 'communities.about', 'communities.posts.index', 'communities.classroom.index', 'communities.events.index', 'communities.directory.index', 'communities.leaderboard', 'communities.notifications.index', 'communities.saved.index'] as $route) {
            $this->get(route($route, $community))->assertNotFound();
        }
        $this->get(route('communities.search', ['community' => $community, 'q' => 'test']))->assertNotFound();
        $this->get(route('communities.lessons.show', [$community, $course, $lesson]))->assertNotFound();
        $this->post(route('communities.lessons.progress.update', [$community, $course, $lesson]), ['completed' => true])->assertNotFound();
        $this->post(route('communities.posts.store', $community), ['title' => 'Forbidden', 'body' => 'Expired writer'])->assertNotFound();
        $this->post(route('communities.onboarding.video', $community))->assertNotFound();
        $this->get(route('communities.cover.show', $community))->assertOk();
        $this->get(route('communities.index'))->assertOk()->assertSee($community->name)->assertSee('Invitation required');
        $this->get(route('communities.index', ['filter' => 'joined']))->assertOk()->assertDontSee($community->name);
        $this->get(route('account.memberships.show', $membership))->assertOk()->assertSee('Expired access')->assertSee('Start Test Renewal')->assertDontSee('Open Community');
        $this->get(route('account.payment-history.index'))->assertOk();
        $this->assertFalse($membership->hasEffectiveAccess());
        $this->assertFalse(CommunityMembership::whereKey($membership->id)->effective()->exists());
        $this->assertSame('ACTIVE', $membership->fresh()->status);
        $this->assertDatabaseMissing('posts', ['title' => 'Forbidden']);
    }

    public function test_expired_successful_admission_checkout_redirects_to_safe_receipt(): void
    {
        [$community, $user, $membership] = $this->member();
        $payment = Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'community_membership_id' => $membership->id,
            'purpose' => Payment::PURPOSE_COMMUNITY_ACCESS,
            'membership_access_kind' => 'TERM',
            'term_days' => 30,
            'status' => Payment::STATUS_SUCCEEDED,
            'paid_at' => now('UTC'),
        ]);
        $this->actingAs($user)->get(route('payments.checkout', $payment->external_reference))
            ->assertRedirect(route('communities.show', $community));
        $this->travelTo($membership->expires_at->copy());
        $this->get(route('payments.checkout', $payment->external_reference))
            ->assertRedirect(route('payments.status', $payment->external_reference));
        $this->get(route('payments.status', $payment->external_reference))->assertOk()
            ->assertViewHas('accessAvailable', false)->assertDontSee('Active Membership');
        $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->fresh()->status);
        $this->assertTrue($membership->fresh()->expires_at->equalTo($membership->expires_at));
    }

    public function test_exact_expiry_denies_notification_read_mutations(): void
    {
        [$community, $user, $membership] = $this->member();
        $course = Course::factory()->published()->create(['community_id' => $community->id]);
        $notice = AppNotification::createNotification(userId: $user->id, communityId: $community->id, type: 'course_published', dedupeKey: 'term-expiry-read-test', data: ['course_id' => $course->id]);
        $this->actingAs($user)->get(route('communities.notifications.index', $community))->assertOk();
        $this->travelTo($membership->expires_at->copy());
        $this->patch(route('communities.notifications.read', [$community, $notice]))->assertNotFound();
        $this->assertNull($notice->fresh()->read_at);
        $this->get(route('communities.notifications.follow', [$community, $notice]))->assertNotFound();
        $this->assertNull($notice->fresh()->read_at);
    }

    public function test_fixed_term_admission_uses_immutable_price_and_term_snapshots(): void
    {
        $community = Community::factory()->create(['access_mode' => 'PAID', 'price_vnd' => 200000, 'billing_period' => 'TERM_30_DAYS']);
        $user = User::factory()->create();
        $token = bin2hex(random_bytes(32));
        $invite = CommunityInvitation::forceCreate(['community_id' => $community->id, 'inviter_id' => $community->creator_id, 'normalized_email' => $user->email, 'token_hash' => hash('sha256', $token), 'status' => 'PENDING', 'expires_at' => now('UTC')->addDays(7)]);
        $this->actingAs($user)->post(route('invitations.checkout', $invite), ['token' => $token, 'term_days' => 999])->assertRedirect();
        $payment = Payment::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('TERM', $payment->membership_access_kind);
        $this->assertSame(30, $payment->term_days);
        $community->forceFill(['price_vnd' => 900000, 'billing_period' => 'ONE_TIME'])->save();
        $this->webhook($payment)->assertOk();
        $membership = $community->memberships()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame('TERM', $membership->access_kind);
        $this->assertSame('2026-01-31 12:00:00', $membership->expires_at->format('Y-m-d H:i:s'));
        $this->assertSame(200000, $payment->fresh()->amount);
        $this->webhook($payment)->assertOk();
        $this->assertSame('2026-01-31 12:00:00', $membership->fresh()->expires_at->format('Y-m-d H:i:s'));
    }

    public function test_renewal_extends_from_future_expiry_once_and_never_changes_membership_state(): void
    {
        [$community, $user, $membership] = $this->member();
        $originalExpiry = $membership->expires_at->copy();
        $payment = $this->renewal($user, $membership);
        $this->assertSame(30, $payment->term_days);
        $this->assertSame(200000, $payment->amount);
        $this->assertSame(Payment::PURPOSE_MEMBERSHIP_RENEWAL, $payment->purpose);
        $this->assertNull($payment->community_invitation_id);
        $this->assertNull($payment->course_id);
        $community->forceFill(['price_vnd' => 500000, 'billing_period' => 'ONE_TIME'])->save();
        $this->actingAs($user)->post(route('account.memberships.renew', $membership))->assertRedirect(route('payments.checkout', $payment->external_reference));
        $this->webhook($payment)->assertOk();
        $this->webhook($payment)->assertOk();
        $this->webhook($payment, 1900000002)->assertOk();
        $this->assertTrue($membership->fresh()->expires_at->equalTo($originalExpiry->addDays(30)));
        $this->assertSame('ACTIVE', $membership->fresh()->status);
        $this->assertSame(200000, $payment->fresh()->amount);
        $this->assertTrue($payment->fresh()->paid_at->equalTo(now('UTC')));
        $this->assertDatabaseCount('community_invitations', 0);
        $this->assertDatabaseCount('course_entitlements', 0);
        $this->get(route('account.payment-history.show', $payment))->assertOk()->assertSee('Membership Renewal')->assertSee('Active Membership');
    }

    public function test_expired_renewal_uses_payment_time_in_utc_without_worker_or_scheduler(): void
    {
        config(['app.timezone' => 'Pacific/Auckland', 'queue.default' => 'database']);
        $this->travelTo(Carbon::parse('2026-03-28 23:30:00', 'UTC'));
        [$community, $user, $membership] = $this->member(now('UTC')->subDays(3));
        $payment = $this->renewal($user, $membership);
        $this->get(route('payments.checkout', $payment->external_reference))->assertOk()->assertSee('Membership Renewal')->assertSee('Do not transfer real funds');
        $this->webhook($payment)->assertOk();
        $expiry = $membership->fresh()->expires_at;
        $this->assertSame('2026-04-27 23:30:00', $expiry->format('Y-m-d H:i:s'));
        $this->assertSame(30 * 86400, $expiry->getTimestamp() - now('UTC')->getTimestamp());
        $this->get(route('communities.show', $community))->assertOk();
        $this->travelTo($expiry);
        $this->get(route('communities.show', $community))->assertNotFound();
        $this->getJson(route('payments.status', $payment->external_reference))->assertOk()->assertJson(['is_succeeded' => true, 'redirect_url' => null]);
        $this->get(route('payments.status', $payment->external_reference))->assertOk()->assertSee('Access Inactive');
    }

    public function test_revoked_states_or_inactive_community_cannot_be_restored_by_late_renewal(): void
    {
        [$community, $user, $membership] = $this->member();
        $payment = $this->renewal($user, $membership);
        $originalExpiry = $membership->expires_at->copy();
        foreach (['SUSPENDED', 'REMOVED', 'LEFT', 'PENDING_PAYMENT'] as $index => $status) {
            $membership->forceFill(['status' => $status])->save();
            $this->actingAs($user)->post(route('account.memberships.renew', $membership))->assertNotFound();
            $this->get(route('payments.checkout', $payment->external_reference))->assertNotFound();
            $this->webhook($payment, 1900000010 + $index)->assertStatus(409);
            $this->assertSame($status, $membership->fresh()->status);
            $this->assertTrue($membership->fresh()->expires_at->equalTo($originalExpiry));
            $this->assertSame('PENDING', $payment->fresh()->status);
        }
        $membership->forceFill(['status' => 'ACTIVE'])->save();
        $community->forceFill(['status' => 'SUSPENDED'])->save();
        $this->webhook($payment, 1900000020)->assertStatus(409);
        $this->assertTrue($membership->fresh()->expires_at->equalTo($originalExpiry));
    }

    public function test_existing_lifetime_access_is_not_retroactively_converted_and_cannot_buy_renewal(): void
    {
        [$community, $user, $membership] = $this->member(null, 'LIFETIME');
        $this->actingAs($community->creator)->put(route('communities.update', $community), ['name' => $community->name, 'access_mode' => 'PAID', 'price_vnd' => 300000, 'billing_period' => 'TERM_30_DAYS'])->assertRedirect();
        $this->assertSame('LIFETIME', $membership->fresh()->access_kind);
        $this->assertNull($membership->fresh()->expires_at);
        $this->travel(400)->days();
        $this->actingAs($user)->get(route('communities.show', $community))->assertOk();
        $this->get(route('account.memberships.show', $membership))->assertOk()->assertSee('Lifetime access')->assertDontSee('Start Test Renewal');
        $this->post(route('account.memberships.renew', $membership))->assertNotFound();
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_renewal_and_history_are_isolated_by_payer_and_membership_identity(): void
    {
        [$community, $user, $membership] = $this->member();
        $payment = $this->renewal($user, $membership);
        $other = User::factory()->create();
        $this->actingAs($other)->get(route('account.memberships.show', $membership))->assertNotFound();
        $this->post(route('account.memberships.renew', $membership))->assertNotFound();
        $this->get(route('payments.checkout', $payment->external_reference))->assertNotFound();
        $this->get(route('account.payment-history.show', $payment))->assertNotFound();
        $this->get(route('account.memberships.index'))->assertOk()->assertDontSee($community->name);
        $this->assertSame('PENDING', $payment->fresh()->status);
    }

    public function test_course_purchase_and_retained_entitlement_cannot_bypass_expired_membership(): void
    {
        [$community, $user, $membership] = $this->member();
        $course = Course::factory()->oneTime(100000)->published()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create(['course_section_id' => $section->id, 'status' => 'PUBLISHED']);
        $payment = Payment::factory()->forCourse($course)->succeeded()->create(['user_id' => $user->id]);
        CourseEntitlement::create(['community_id' => $community->id, 'user_id' => $user->id, 'course_id' => $course->id, 'payment_id' => $payment->id, 'amount' => 100000]);
        $this->actingAs($user)->get(route('communities.lessons.show', [$community, $course, $lesson]))->assertOk();
        $this->travelTo($membership->expires_at->copy());
        $this->get(route('communities.lessons.show', [$community, $course, $lesson]))->assertNotFound();
        $this->post(route('communities.courses.checkout', [$community, $course]))->assertNotFound();
        $this->get(route('account.payment-history.show', $payment))->assertOk()->assertSee('Course Access Inactive');
        $this->assertDatabaseCount('course_entitlements', 1);
    }

    public function test_delayed_cancelled_renewal_cannot_extend_retry_or_revive_access(): void
    {
        [, $user, $membership] = $this->member();
        $expiry = $membership->expires_at->copy();
        $first = $this->renewal($user, $membership);
        $this->post(route('payments.cancel', $first->external_reference))->assertRedirect();
        $second = $this->renewal($user, $membership);
        $this->assertNotSame($first->id, $second->id);
        $this->webhook($first)->assertStatus(409);
        $this->assertTrue($membership->fresh()->expires_at->equalTo($expiry));
        $this->assertSame('PENDING', $second->fresh()->status);
    }

    public function test_expiry_update_failure_rolls_back_event_and_payment_then_replay_extends_once(): void
    {
        [, $user, $membership] = $this->member();
        $payment = $this->renewal($user, $membership);
        $expiry = $membership->expires_at->copy();
        CommunityMembership::saving(function (CommunityMembership $current): void {
            if ($current->isDirty('expires_at')) {
                throw new \RuntimeException('Synthetic renewal persistence failure.');
            }
        });
        $this->withoutExceptionHandling();
        try {
            $this->webhook($payment);
            $this->fail('Expected expiry persistence failure.');
        } catch (\RuntimeException $error) {
            $this->assertSame('Synthetic renewal persistence failure.', $error->getMessage());
        } finally {
            CommunityMembership::flushEventListeners();
            $this->withExceptionHandling();
        }
        $this->assertSame('PENDING', $payment->fresh()->status);
        $this->assertTrue($membership->fresh()->expires_at->equalTo($expiry));
        $this->assertSame(0, ProcessedWebhookEvent::where('provider_event_id', '1900000001')->count());
        $this->webhook($payment)->assertOk();
        $this->assertTrue($membership->fresh()->expires_at->equalTo($expiry->addDays(30)));
    }

    public function test_expired_members_are_excluded_from_delivery_directory_points_and_aggregates(): void
    {
        [$community, $user, $membership] = $this->member();
        $course = Course::factory()->published()->create(['community_id' => $community->id]);
        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $community->creator_id]);
        $this->actingAs($user)->post(route('communities.posts.like', [$community, $post]))->assertRedirect();
        $this->assertSame(1, PointContribution::query()->eligible($community)->count());
        $notice = AppNotification::createNotification(userId: $user->id, communityId: $community->id, type: 'course_published', dedupeKey: 'term-expiry-test', data: ['course_id' => $course->id]);
        $this->travelTo($membership->expires_at->copy());
        (new SendCoursePublishedNotification($course->id, $course->published_revision))->handle();
        (new SendMemberActivatedNotification($community->id, $membership->id, 'payment', 999))->handle();
        $this->assertFalse($notice->isAvailableFor($user));
        $this->assertSame(0, AppNotification::query()->visibleFor($user, $community)->count());
        $this->assertSame(1, AppNotification::count());
        $this->assertSame(0, PointContribution::query()->eligible($community)->count());
        $this->actingAs($community->creator)->get(route('communities.directory.index', $community))->assertOk()->assertDontSee($user->name);
        $this->get(route('communities.manage', $community))->assertOk()->assertViewHas('activeMembersCount', 0)->assertViewHas('accessibleLessonPairsCount', 0);
    }

    public function test_invalid_missing_term_expiry_fails_closed_and_creator_config_cannot_choose_arbitrary_duration(): void
    {
        [$community, $user, $membership] = $this->member();
        $membership->forceFill(['expires_at' => null])->save();
        $this->assertFalse($membership->fresh()->hasEffectiveAccess());
        $this->actingAs($user)->get(route('communities.show', $community))->assertNotFound();
        $this->post(route('account.memberships.renew', $membership))->assertNotFound();
        $this->actingAs($community->creator)->put(route('communities.update', $community), ['name' => $community->name, 'access_mode' => 'PAID', 'price_vnd' => 200000, 'billing_period' => 'TERM_365_DAYS'])->assertSessionHasErrors('billing_period');
        $this->assertSame('TERM_30_DAYS', $community->fresh()->billing_period);
    }

    public function test_free_invitation_never_converts_or_restores_an_existing_term(): void
    {
        [$community, $user, $membership] = $this->member();
        $expiry = $membership->expires_at->copy();
        $community->forceFill(['access_mode' => 'FREE', 'price_vnd' => null, 'billing_period' => 'ONE_TIME'])->save();
        foreach ([false, true] as $expired) {
            if ($expired) {
                $this->travelTo($expiry->copy());
            }
            $token = bin2hex(random_bytes(32));
            $invite = CommunityInvitation::forceCreate(['community_id' => $community->id, 'inviter_id' => $community->creator_id, 'normalized_email' => $user->email, 'token_hash' => hash('sha256', $token), 'status' => 'PENDING', 'expires_at' => now('UTC')->addDays(7)]);
            $response = $this->actingAs($user)->post(route('invitations.accept', $invite), ['token' => $token]);
            if ($expired) {
                $response->assertNotFound();
                $this->assertSame('PENDING', $invite->fresh()->status);
            } else {
                $response->assertRedirect();
            }
            $this->assertSame('TERM', $membership->fresh()->access_kind);
            $this->assertTrue($membership->fresh()->expires_at->equalTo($expiry));
        }
    }

    public function test_non_utc_php_timezone_does_not_shift_renewal_expiry_or_receipt_time(): void
    {
        $previousTimezone = date_default_timezone_get();
        date_default_timezone_set('Pacific/Auckland');
        try {
            $this->travelTo(Carbon::parse('2026-03-28 23:30:00', 'UTC'));
            [, $user, $membership] = $this->member(now('UTC')->subDays(1));
            $payment = $this->renewal($user, $membership);
            $this->webhook($payment)->assertOk();
            $this->assertSame('2026-04-27 23:30:00', $membership->fresh()->expires_at->format('Y-m-d H:i:s'));
            $this->assertSame('2026-03-28 23:30:00', $payment->fresh()->paid_at->format('Y-m-d H:i:s'));
        } finally {
            date_default_timezone_set($previousTimezone);
        }
    }
}
