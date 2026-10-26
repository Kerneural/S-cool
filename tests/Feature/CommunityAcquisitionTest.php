<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\Payment;
use App\Models\Post;
use App\Models\User;
use App\Services\Payment\SePayGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CommunityAcquisitionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        config(['services.sepay.webhook_token' => 'acquisition-test-token', 'services.sepay.test_mode' => true, 'services.sepay.account_number' => 'ACQUISITION-TEST']);
        $this->app->bind(PaymentGateway::class, SePayGateway::class);
    }

    public function test_non_member_can_open_marketing_preview_without_private_content_or_membership(): void
    {
        $community = Community::factory()->create(['access_mode' => 'PAID', 'price_vnd' => 123000]);
        $course = Course::factory()->create(['community_id' => $community->id, 'title' => 'Published curriculum preview']);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create(['course_section_id' => $section->id, 'content' => 'PRIVATE LESSON BODY']);
        $draft = Course::factory()->draft()->create(['community_id' => $community->id, 'title' => 'PRIVATE DRAFT TITLE']);
        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $community->creator_id, 'title' => 'PRIVATE POST TITLE']);
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('communities.index'))->assertOk()
            ->assertSee(route('communities.about', $community), false)->assertSee('View community');
        $this->get(route('communities.about', $community))->assertOk()->assertViewIs('communities.preview')
            ->assertSee($community->name)->assertSee($course->title)->assertSee('123.000')
            ->assertDontSee($lesson->content)->assertDontSee($lesson->video_url)
            ->assertDontSee($draft->title)->assertDontSee($post->title)
            ->assertDontSee(route('communities.courses.show', [$community, $course]), false);
        foreach (['communities.show', 'communities.posts.index', 'communities.classroom.index', 'communities.directory.index', 'communities.events.index'] as $route) {
            $this->get(route($route, $community))->assertNotFound();
        }
        $this->get(route('communities.lessons.show', [$community, $course, $lesson]))->assertNotFound();
        $this->assertDatabaseCount('community_memberships', 0);
    }

    public function test_preview_is_read_only_guest_login_required_and_inactive_communities_remain_hidden(): void
    {
        $community = Community::factory()->create();
        $this->get(route('communities.about', $community))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('communities.about', $community))->assertOk();
        $community->forceFill(['status' => 'SUSPENDED'])->save();
        $this->get(route('communities.about', $community))->assertNotFound();
        $this->post(route('communities.join', $community))->assertNotFound();
        $this->assertDatabaseCount('community_memberships', 0);
    }

    public function test_free_join_is_verified_actor_bound_idempotent_and_does_not_unlock_paid_lessons(): void
    {
        $community = Community::factory()->create();
        $user = User::factory()->create();
        $other = User::factory()->create();
        $course = Course::factory()->oneTime()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create(['course_section_id' => $section->id]);
        $this->actingAs($user)->post(route('communities.join', $community), ['email' => $other->email, 'user_id' => $other->id])
            ->assertRedirect(route('communities.show', $community));
        $membership = $community->memberships()->where('user_id', $user->id)->firstOrFail();
        $this->assertTrue($membership->hasEffectiveAccess());
        $this->assertSame(CommunityMembership::ACCESS_LIFETIME, $membership->access_kind);
        $this->post(route('communities.join', $community))->assertRedirect(route('communities.show', $community));
        $this->assertDatabaseCount('community_memberships', 1);
        $this->assertDatabaseMissing('community_memberships', ['user_id' => $other->id]);
        $this->get(route('communities.show', $community))->assertOk();
        $this->get(route('communities.lessons.show', [$community, $course, $lesson]))->assertNotFound();
        $this->assertDatabaseCount('course_entitlements', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_unverified_join_does_not_create_access(): void
    {
        $user = User::factory()->unverified()->create();
        $community = Community::factory()->create();
        $this->actingAs($user)->post(route('communities.join', $community))->assertRedirect(route('verification.notice'));
        $this->assertDatabaseCount('community_memberships', 0);
    }

    public function test_locked_course_without_price_is_not_advertised_as_included(): void
    {
        $membership = CommunityMembership::factory()->active()->create();
        Course::factory()->oneTime()->create(['community_id' => $membership->community_id, 'price' => null]);
        $this->actingAs($membership->user)->get(route('communities.classroom.index', $membership->community))
            ->assertOk()->assertSee('Separate access required')->assertSee('Locked Course');
        $this->assertDatabaseCount('course_entitlements', 0);
    }

    public function test_join_does_not_bypass_suspension_removal_pending_free_or_expired_term(): void
    {
        $user = User::factory()->create();
        foreach (['SUSPENDED', 'REMOVED', 'PENDING_PAYMENT', 'ACTIVE'] as $state) {
            $community = Community::factory()->create();
            $membership = CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $user->id, 'status' => $state,
                'access_kind' => CommunityMembership::ACCESS_TERM, 'expires_at' => now()->subDay()]);
            $before = $membership->fresh()->getAttributes();
            $this->actingAs($user)->post(route('communities.join', $community))->assertSessionHasErrors('join');
            $this->assertSame($before, $membership->fresh()->getAttributes());
        }
    }

    public function test_paid_join_reuses_pending_checkout_and_verified_ipn_is_required_for_access(): void
    {
        $community = Community::factory()->create(['access_mode' => 'PAID', 'price_vnd' => 120000, 'billing_period' => 'TERM_30_DAYS']);
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('communities.join', $community))->assertRedirect();
        $payment = Payment::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('COMMUNITY_JOIN', $payment->purpose);
        $this->assertNull($payment->community_invitation_id);
        $this->assertSame(120000, $payment->amount);
        $this->assertFalse($community->isActiveMember($user));
        $this->get(route('payments.checkout', $payment->external_reference))->assertOk()->assertSee('SePay Test Mode');
        $this->get(route('communities.show', $community))->assertNotFound();
        $this->post(route('communities.join', $community))->assertRedirect(route('payments.checkout', $payment->external_reference));
        $this->assertDatabaseCount('payments', 1);
        $this->postJson('/webhooks/sepay', $this->ipn($payment, 8401), ['Authorization' => 'Apikey wrong'])->assertStatus(401);
        $this->assertFalse($community->fresh()->isActiveMember($user));
        $this->postJson('/webhooks/sepay', $this->ipn($payment, 8402), ['Authorization' => 'Apikey acquisition-test-token'])->assertOk();
        $membership = $community->memberships()->where('user_id', $user->id)->firstOrFail();
        $this->assertTrue($membership->hasEffectiveAccess());
        $this->assertSame(CommunityMembership::ACCESS_TERM, $membership->access_kind);
        $this->assertEqualsWithDelta(30, now()->diffInDays($membership->expires_at), 0.01);
        $expiry = $membership->expires_at->toISOString();
        $this->postJson('/webhooks/sepay', $this->ipn($payment, 8403), ['Authorization' => 'Apikey acquisition-test-token'])->assertOk();
        $this->assertSame($expiry, $membership->fresh()->expires_at->toISOString());
    }

    public function test_cancelled_or_moderated_self_join_cannot_be_activated_by_late_ipn(): void
    {
        $user = User::factory()->create();
        foreach (['CANCELLED', 'SUSPENDED', 'REMOVED'] as $i => $state) {
            $community = Community::factory()->create(['access_mode' => 'PAID', 'price_vnd' => 90000]);
            $this->actingAs($user)->post(route('communities.join', $community));
            $payment = Payment::where('community_id', $community->id)->firstOrFail();
            if ($state === 'CANCELLED') {
                $this->post(route('payments.cancel', $payment->external_reference))->assertRedirect();
            } else {
                $community->memberships()->where('user_id', $user->id)->update(['status' => $state]);
            }
            $this->postJson('/webhooks/sepay', $this->ipn($payment, 8500 + $i), ['Authorization' => 'Apikey acquisition-test-token'])->assertStatus(409);
            $this->assertFalse($community->fresh()->isActiveMember($user));
            $this->assertNotSame(Payment::STATUS_SUCCEEDED, $payment->fresh()->status);
        }
    }

    public function test_direct_join_payment_cannot_activate_a_different_membership(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create(['access_mode' => 'PAID', 'price_vnd' => 120000]);
        $this->actingAs($user)->post(route('communities.join', $community), ['amount' => 1, 'purpose' => 'COURSE_ACCESS']);
        $payment = Payment::where('community_id', $community->id)->firstOrFail();
        $this->assertSame(120000, $payment->amount);
        $foreign = CommunityMembership::factory()->create();
        $payment->forceFill(['community_membership_id' => $foreign->id])->save();
        $this->get(route('payments.checkout', $payment->external_reference))->assertNotFound();
        $this->postJson('/webhooks/sepay', $this->ipn($payment, 8601), ['Authorization' => 'Apikey acquisition-test-token'])->assertStatus(409);
        $this->assertFalse($community->fresh()->isActiveMember($user));
        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertSame('PENDING_PAYMENT', $foreign->fresh()->status);
    }

    private function ipn(Payment $payment, int $id): array
    {
        return ['id' => $id, 'transferType' => 'in', 'accountNumber' => 'ACQUISITION-TEST', 'referenceCode' => 'ACQ-'.$id,
            'content' => 'Payment '.$payment->external_reference, 'transferAmount' => $payment->amount, 'currency' => 'VND'];
    }
}
