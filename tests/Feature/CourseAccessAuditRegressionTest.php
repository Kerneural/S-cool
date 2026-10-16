<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseEntitlement;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\Payment;
use App\Models\ProcessedWebhookEvent;
use App\Models\User;
use App\Services\Payment\SePayGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseAccessAuditRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.sepay.test_mode' => true, 'services.sepay.webhook_token' => 'audit-test-token', 'services.sepay.account_number' => 'AUDIT-TEST']);
        $this->app->bind(PaymentGateway::class, SePayGateway::class);
    }

    private function fixture(string $mode = 'ONE_TIME'): array
    {
        $community = Community::factory()->create(['access_mode' => 'FREE']);
        $member = User::factory()->create();
        $membership = CommunityMembership::forceCreate(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);
        $course = Course::factory()->published()->create(['community_id' => $community->id, 'title' => 'Purchasable Course', 'description' => 'PRIVATE-COURSE-DESCRIPTION', 'access_mode' => $mode, 'price' => $mode === 'ONE_TIME' ? 200000 : null]);
        $section = CourseSection::factory()->create(['course_id' => $course->id, 'title' => 'PRIVATE-SECTION-TITLE']);
        $lesson = Lesson::factory()->create(['course_section_id' => $section->id, 'title' => 'PRIVATE-LESSON-TITLE', 'content' => 'PRIVATE-LESSON-BODY', 'status' => 'PUBLISHED']);

        return [$community, $member, $membership, $course, $section, $lesson];
    }

    private function start(Community $community, User $member, Course $course): Payment
    {
        $this->actingAs($member)->post(route('communities.courses.checkout', [$community, $course]))->assertRedirect();

        return Payment::where('course_id', $course->id)->where('user_id', $member->id)->where('status', 'PENDING')->firstOrFail();
    }

    private function payload(Payment $payment, int $event = 1800000001): array
    {
        return ['id' => $event, 'transferType' => 'in', 'accountNumber' => 'AUDIT-TEST', 'referenceCode' => 'TEST-'.$event, 'content' => $payment->external_reference, 'transferAmount' => $payment->amount, 'currency' => 'VND'];
    }

    private function webhook(array $payload)
    {
        return $this->postJson('/webhooks/sepay', $payload, ['Authorization' => 'Apikey audit-test-token']);
    }

    public function test_locked_course_preview_never_delivers_outline_or_description(): void
    {
        [$community, $member, , $course, $section, $lesson] = $this->fixture();
        $this->actingAs($member)->get(route('communities.courses.show', [$community, $course]))->assertOk()
            ->assertSee($course->title)->assertSee('200.000 VND')
            ->assertDontSee($section->title)->assertDontSee($lesson->title)->assertDontSee($lesson->content)->assertDontSee($course->description);
        $this->get(route('communities.classroom.index', $community))->assertOk()
            ->assertDontSee($course->description)
            ->assertDontSee('Next Learning Step')
            ->assertDontSee('Overall Progress');
        $this->get(route('communities.search', ['community' => $community, 'type' => 'courses', 'q' => 'PRIVATE-COURSE-DESCRIPTION']))->assertOk()->assertDontSee($course->title);
        $this->get(route('communities.search', ['community' => $community, 'type' => 'courses', 'q' => $course->title]))->assertOk()->assertSee($course->title)->assertDontSee($course->description);
        $payment = $this->start($community, $member, $course);
        $this->webhook($this->payload($payment))->assertOk();
        $this->get(route('communities.courses.show', [$community, $course]))->assertOk()->assertSee($section->title)->assertSee($lesson->title);
    }

    public function test_completion_counts_only_currently_accessible_member_lesson_pairs(): void
    {
        [$community, $member, , $course, , $lesson] = $this->fixture('INCLUDED');
        $included = Course::factory()->published()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $included->id]);
        $otherLesson = Lesson::factory()->create(['course_section_id' => $section->id, 'status' => 'PUBLISHED']);
        foreach ([[$course, $lesson], [$included, $otherLesson]] as [$target, $item]) {
            $this->actingAs($member)->post(route('communities.lessons.progress.update', [$community, $target, $item]), ['completed' => true])->assertRedirect();
        }
        $this->actingAs($community->creator)->put(route('communities.courses.update', [$community, $course]), ['title' => $course->title, 'status' => 'PUBLISHED', 'access_mode' => 'ONE_TIME', 'price' => 200000])->assertRedirect();
        $this->get(route('communities.manage', $community))->assertOk()->assertViewHas('completedLessonsCount', 1)->assertViewHas('completionRatio', 100.0)->assertViewHas('accessibleLessonPairsCount', 1);
        $this->assertDatabaseHas('lesson_progresses', ['user_id' => $member->id, 'lesson_id' => $lesson->id, 'completed' => true]);
    }

    public function test_receipt_retains_purchase_but_never_claims_revoked_or_unpublished_access(): void
    {
        [$community, $member, $membership, $course] = $this->fixture();
        $payment = $this->start($community, $member, $course);
        $this->webhook($this->payload($payment))->assertOk();
        foreach (['REMOVED', 'SUSPENDED', 'LEFT'] as $state) {
            $membership->forceFill(['status' => $state])->save();
            $this->actingAs($member)->get(route('account.payment-history.show', $payment))->assertOk()->assertSee('Course Access Inactive')->assertDontSee('You have active access')->assertDontSee('Course Unlocked');
        }
        $membership->forceFill(['status' => 'ACTIVE'])->save();
        $course->update(['status' => 'DRAFT']);
        $this->get(route('account.payment-history.show', $payment))->assertOk()->assertSee('Course Access Inactive')->assertDontSee('Course Unlocked');
        $course->update(['status' => 'PUBLISHED']);
        $this->get(route('account.payment-history.show', $payment))->assertOk()->assertSee('Course Unlocked');
        $this->assertDatabaseCount('course_entitlements', 1);
    }

    public function test_course_checkout_names_its_target_and_rechecks_pending_eligibility(): void
    {
        [$community, $member, $membership, $course] = $this->fixture();
        $payment = $this->start($community, $member, $course);
        $this->get(route('payments.checkout', $payment->external_reference))->assertOk()->assertSee('Course Access')->assertSee($course->title)->assertDontSee('Community Access Payment');
        $this->get(route('account.payment-history.show', $payment))->assertOk()->assertDontSee('activate your membership');
        $membership->forceFill(['status' => 'REMOVED'])->save();
        $this->get(route('payments.checkout', $payment->external_reference))->assertNotFound();
        $this->webhook($this->payload($payment))->assertStatus(409);
        $this->assertSame('PENDING', $payment->fresh()->status);
        $this->assertDatabaseCount('course_entitlements', 0);
    }

    public function test_cancelled_delayed_course_event_cannot_unlock_a_retry(): void
    {
        [$community, $member, , $course] = $this->fixture();
        $first = $this->start($community, $member, $course);
        $this->post(route('payments.cancel', $first->external_reference))->assertRedirect();
        $second = $this->start($community, $member, $course);
        $this->webhook($this->payload($first))->assertStatus(409);
        $this->assertSame('CANCELLED', $first->fresh()->status);
        $this->assertSame('PENDING', $second->fresh()->status);
        $this->assertDatabaseCount('course_entitlements', 0);
    }

    public function test_entitlement_failure_rolls_back_payment_and_event_then_identical_retry_succeeds(): void
    {
        [$community, $member, , $course] = $this->fixture();
        $payment = $this->start($community, $member, $course);
        $payload = $this->payload($payment);
        CourseEntitlement::creating(fn () => throw new \RuntimeException('Synthetic entitlement persistence failure.'));
        $this->withoutExceptionHandling();
        try {
            $this->webhook($payload);
            $this->fail('Expected persistence failure.');
        } catch (\RuntimeException $error) {
            $this->assertSame('Synthetic entitlement persistence failure.', $error->getMessage());
        } finally {
            CourseEntitlement::flushEventListeners();
            $this->withExceptionHandling();
        }
        $this->assertSame('PENDING', $payment->fresh()->status);
        $this->assertSame(0, ProcessedWebhookEvent::where('provider_event_id', (string) $payload['id'])->count());
        $this->assertDatabaseCount('course_entitlements', 0);
        $course->update(['price' => 900000]);
        $this->webhook($payload)->assertOk();
        $this->webhook($payload)->assertOk();
        $this->assertDatabaseHas('course_entitlements', ['payment_id' => $payment->id, 'amount' => 200000]);
        $this->assertDatabaseCount('course_entitlements', 1);
    }
}
