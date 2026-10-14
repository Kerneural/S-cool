<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Models\Community;
use App\Models\CommunityInvitation;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseEntitlement;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payment\SePayGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseEntitlementAndPurchaseTest extends TestCase
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

    protected function createActiveMember(Community $community): User
    {
        $user = User::factory()->create();
        CommunityMembership::forceCreate([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => 'ACTIVE',
        ]);

        return $user;
    }

    protected function createCourseWithLesson(Community $community, string $accessMode = Course::ACCESS_MODE_INCLUDED, ?int $price = null, string $status = 'PUBLISHED'): array
    {
        $course = Course::factory()->create([
            'community_id' => $community->id,
            'access_mode' => $accessMode,
            'price' => $price,
            'status' => $status,
        ]);

        $section = CourseSection::factory()->create([
            'course_id' => $course->id,
            'title' => 'Main Module',
            'order' => 1,
        ]);

        $lesson = Lesson::factory()->create([
            'course_section_id' => $section->id,
            'title' => 'Lesson for Course '.$course->id,
            'content' => 'Top secret course content lesson snippet '.$course->id,
            'status' => 'PUBLISHED',
            'order' => 1,
        ]);

        return [$course, $section, $lesson];
    }

    /**
     * AC-01: Included course works unchanged; paid course card is visible but lessons/embed are locked before verified success.
     */
    public function test_ac01_included_course_accessible_while_paid_course_lessons_locked_before_purchase(): void
    {
        $community = Community::factory()->create(['access_mode' => 'FREE']);
        $creator = $community->creator;
        $member = $this->createActiveMember($community);

        // 1. Included Course
        [$includedCourse, , $includedLesson] = $this->createCourseWithLesson($community, Course::ACCESS_MODE_INCLUDED);

        // 2. Paid Course
        [$paidCourse, , $paidLesson] = $this->createCourseWithLesson($community, Course::ACCESS_MODE_ONE_TIME, 500000);

        // Included course is accessible to active member
        $this->actingAs($member)->get(route('communities.courses.show', [$community, $includedCourse]))
            ->assertOk()
            ->assertSee($includedLesson->title);

        $this->actingAs($member)->get(route('communities.lessons.show', [$community, $includedCourse, $includedLesson]))
            ->assertOk()
            ->assertSee('Top secret course content');

        // Paid course outline is visible, but lessons are locked
        $response = $this->actingAs($member)->get(route('communities.courses.show', [$community, $paidCourse]));
        $response->assertOk()
            ->assertSee('Unlock full access to this course')
            ->assertSee('Buy Course')
            ->assertSee('500.000 VND')
            ->assertDontSee($paidLesson->title);

        // Direct access to paid lesson returns 404 for un-entitled member
        $this->actingAs($member)->get(route('communities.lessons.show', [$community, $paidCourse, $paidLesson]))
            ->assertNotFound();

        // Updating progress on paid lesson is denied (404)
        $this->actingAs($member)->post(route('communities.lessons.progress.update', [$community, $paidCourse, $paidLesson]), [
            'completed' => true,
        ])->assertNotFound();

        // Search does not leak lesson snippets of locked paid course
        $searchResponse = $this->actingAs($member)->get(route('communities.search', [
            'community' => $community,
            'type' => 'lessons',
            'q' => 'secret',
        ]));
        $searchResponse->assertOk()
            ->assertSee($includedLesson->title)
            ->assertDontSee($paidLesson->title);

        // Creator can view paid course and lessons without purchasing
        $this->actingAs($creator)->get(route('communities.lessons.show', [$community, $paidCourse, $paidLesson]))
            ->assertOk()
            ->assertSee('Top secret course content');
    }

    /**
     * AC-02: Successful exact-reference/amount/currency test webhook unlocks exactly one course; duplicate delivery creates no duplicate entitlement.
     */
    public function test_ac02_verified_webhook_unlocks_single_course_and_duplicate_delivery_is_idempotent(): void
    {
        $community = Community::factory()->create(['access_mode' => 'FREE']);
        $member = $this->createActiveMember($community);

        [$course1, , $lesson1] = $this->createCourseWithLesson($community, Course::ACCESS_MODE_ONE_TIME, 300000);
        [$course2, , $lesson2] = $this->createCourseWithLesson($community, Course::ACCESS_MODE_ONE_TIME, 600000);

        // Initiate checkout for course 1
        $checkoutResponse = $this->actingAs($member)->post(route('communities.courses.checkout', [$community, $course1]));

        $payment = Payment::where('community_id', $community->id)
            ->where('user_id', $member->id)
            ->where('course_id', $course1->id)
            ->where('purpose', Payment::PURPOSE_COURSE_ACCESS)
            ->first();

        $this->assertNotNull($payment);
        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
        $this->assertSame(300000, $payment->amount);
        $this->assertStringStartsWith('CRS-', $payment->external_reference);
        $checkoutResponse->assertRedirect(route('payments.checkout', $payment->external_reference));

        // Course 1 is still locked
        $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course1, $lesson1]))
            ->assertNotFound();

        // Webhook payload from SePay
        $ipnPayload = [
            'id' => 9001,
            'transferType' => 'in',
            'accountNumber' => 'TEST-ACCOUNT-001',
            'referenceCode' => 'MB-CRS-001',
            'content' => 'Course payment '.$payment->external_reference,
            'transferAmount' => 300000,
            'currency' => 'VND',
        ];

        $webhookResponse = $this->postJson('/webhooks/sepay', $ipnPayload, [
            'Authorization' => 'Apikey test-webhook-secret-token',
        ]);

        $webhookResponse->assertOk()
            ->assertJson(['message' => 'Payment verified and course access granted.']);

        $payment->refresh();
        $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->status);
        $this->assertSame('MB-CRS-001', $payment->gateway_transaction_id);

        // Entitlement exists
        $this->assertDatabaseHas('course_entitlements', [
            'community_id' => $community->id,
            'course_id' => $course1->id,
            'user_id' => $member->id,
            'payment_id' => $payment->id,
        ]);
        $this->assertSame(1, CourseEntitlement::count());

        // Course 1 lessons now fully accessible
        $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course1, $lesson1]))
            ->assertOk();

        // Progress can now be updated
        $this->actingAs($member)->post(route('communities.lessons.progress.update', [$community, $course1, $lesson1]), [
            'completed' => true,
        ])->assertRedirect();

        // Course 2 remains locked!
        $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course2, $lesson2]))
            ->assertNotFound();

        // Membership remains unchanged (never modified by course purchase)
        $this->assertDatabaseHas('community_memberships', [
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        // Duplicate webhook delivery is idempotent
        $duplicateResponse = $this->postJson('/webhooks/sepay', $ipnPayload, [
            'Authorization' => 'Apikey test-webhook-secret-token',
        ]);

        $duplicateResponse->assertOk();
        $this->assertSame(1, CourseEntitlement::count());
    }

    /**
     * AC-03: Forged client price/purpose/course/user and wrong-tenant target never unlock; existing admission flow still passes.
     */
    public function test_ac03_forged_requests_and_wrong_tenant_rejected_and_admission_flow_preserved(): void
    {
        $communityA = Community::factory()->create(['access_mode' => 'PAID', 'price_vnd' => 200000]);
        $communityB = Community::factory()->create(['access_mode' => 'FREE']);

        $memberA = $this->createActiveMember($communityA);
        $memberB = $this->createActiveMember($communityB);

        [$courseB, , $lessonB] = $this->createCourseWithLesson($communityB, Course::ACCESS_MODE_ONE_TIME, 400000);

        // 1. MemberA attempts to checkout courseB belonging to CommunityB via CommunityA route -> 404
        $this->actingAs($memberA)->post(route('communities.courses.checkout', [$communityA, $courseB]))
            ->assertNotFound();

        // 2. Client cannot forge price: even if request sends amount/price fields, server uses course->price
        $this->actingAs($memberB)->post(route('communities.courses.checkout', [$communityB, $courseB]), [
            'price' => 1000,
            'amount' => 1000,
        ]);

        $payment = Payment::where('course_id', $courseB->id)->where('user_id', $memberB->id)->first();
        $this->assertSame(400000, $payment->amount);

        // 3. Webhook with mismatched amount (underpaid) is rejected
        $underpaidPayload = [
            'id' => 9002,
            'transferType' => 'in',
            'accountNumber' => 'TEST-ACCOUNT-001',
            'referenceCode' => 'MB-CRS-002',
            'content' => 'Pay '.$payment->external_reference,
            'transferAmount' => 1000,
            'currency' => 'VND',
        ];
        $this->postJson('/webhooks/sepay', $underpaidPayload, [
            'Authorization' => 'Apikey test-webhook-secret-token',
        ])->assertStatus(422);

        $this->assertDatabaseMissing('course_entitlements', [
            'course_id' => $courseB->id,
            'user_id' => $memberB->id,
        ]);

        // 4. Existing admission payment flow still passes and does NOT grant course entitlement
        $invitee = User::factory()->create();
        $token = bin2hex(random_bytes(32));
        $invitation = CommunityInvitation::forceCreate([
            'community_id' => $communityA->id,
            'inviter_id' => $communityA->creator_id,
            'normalized_email' => strtolower(trim($invitee->email)),
            'token_hash' => hash('sha256', $token),
            'status' => 'PENDING',
            'expires_at' => now()->addDays(7),
        ]);

        $admissionPayment = Payment::create([
            'external_reference' => 'INV-TESTADM001',
            'community_id' => $communityA->id,
            'user_id' => $invitee->id,
            'community_invitation_id' => $invitation->id,
            'purpose' => Payment::PURPOSE_COMMUNITY_ACCESS,
            'amount' => 200000,
            'currency' => 'VND',
            'status' => Payment::STATUS_PENDING,
            'gateway_provider' => Payment::PROVIDER_SEPAY_SANDBOX,
        ]);
        CommunityMembership::forceCreate([
            'community_id' => $communityA->id,
            'user_id' => $invitee->id,
            'status' => 'PENDING_PAYMENT',
        ]);

        $admissionWebhook = [
            'id' => 9003,
            'transferType' => 'in',
            'accountNumber' => 'TEST-ACCOUNT-001',
            'referenceCode' => 'MB-ADM-001',
            'content' => 'Admission '.$admissionPayment->external_reference,
            'transferAmount' => 200000,
            'currency' => 'VND',
        ];

        $admResponse = $this->postJson('/webhooks/sepay', $admissionWebhook, [
            'Authorization' => 'Apikey test-webhook-secret-token',
        ]);
        $admResponse->assertOk()
            ->assertJson(['message' => 'Payment verified and membership activated.']);

        $admissionPayment->refresh();
        $this->assertSame(Payment::STATUS_SUCCEEDED, $admissionPayment->status);
        $this->assertDatabaseHas('community_memberships', [
            'community_id' => $communityA->id,
            'user_id' => $invitee->id,
            'status' => 'ACTIVE',
        ]);
        // Admission webhook issued NO course entitlements
        $this->assertDatabaseMissing('course_entitlements', [
            'user_id' => $invitee->id,
        ]);
    }

    /**
     * AC-04: Cancel/retry and delayed success follow immutable attempt rules; no client redirect grants entitlement.
     */
    public function test_ac04_cancel_retry_separate_attempts_and_redirect_never_grants_access(): void
    {
        $community = Community::factory()->create();
        $member = $this->createActiveMember($community);
        [$course, , $lesson] = $this->createCourseWithLesson($community, Course::ACCESS_MODE_ONE_TIME, 500000);

        // Initiate first attempt
        $this->actingAs($member)->post(route('communities.courses.checkout', [$community, $course]));
        $attempt1 = Payment::where('course_id', $course->id)->where('user_id', $member->id)->first();

        // User cancels first attempt
        $this->actingAs($member)->post(route('payments.cancel', $attempt1->external_reference));
        $attempt1->refresh();
        $this->assertSame(Payment::STATUS_CANCELLED, $attempt1->status);

        // Retrying checkout creates a NEW attempt with different reference
        $this->actingAs($member)->post(route('communities.courses.checkout', [$community, $course]));
        $attempt2 = Payment::where('course_id', $course->id)
            ->where('user_id', $member->id)
            ->where('status', Payment::STATUS_PENDING)
            ->first();

        $this->assertNotNull($attempt2);
        $this->assertNotSame($attempt1->id, $attempt2->id);
        $this->assertNotSame($attempt1->external_reference, $attempt2->external_reference);

        // Visiting status URL on pending attempt redirects but NEVER grants entitlement
        $statusResponse = $this->actingAs($member)->get(route('payments.status', $attempt2->external_reference));
        $statusResponse->assertOk()
            ->assertSee('Awaiting Bank Transfer');

        $this->assertDatabaseMissing('course_entitlements', [
            'course_id' => $course->id,
            'user_id' => $member->id,
        ]);
        $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course, $lesson]))
            ->assertNotFound();
    }

    /**
     * AC-05: Removed member or draft course blocks access despite entitlement; reactivation/republish restores retained eligible access.
     */
    public function test_ac05_membership_revocation_or_draft_course_blocks_access_retaining_entitlement(): void
    {
        $community = Community::factory()->create();
        $member = $this->createActiveMember($community);
        [$course, , $lesson] = $this->createCourseWithLesson($community, Course::ACCESS_MODE_ONE_TIME, 500000);

        // Issue valid entitlement
        $payment = Payment::factory()->forCourse($course)->succeeded()->create([
            'user_id' => $member->id,
        ]);
        CourseEntitlement::create([
            'community_id' => $community->id,
            'course_id' => $course->id,
            'user_id' => $member->id,
            'payment_id' => $payment->id,
        ]);

        // Entitled active member can view lesson
        $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course, $lesson]))
            ->assertOk();

        // 1. Member suspended/removed from community
        CommunityMembership::where('community_id', $community->id)
            ->where('user_id', $member->id)
            ->update(['status' => 'REMOVED']);

        // Lesson is immediately blocked (404)
        $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course, $lesson]))
            ->assertNotFound();

        // 2. Member reactivated
        CommunityMembership::where('community_id', $community->id)
            ->where('user_id', $member->id)
            ->update(['status' => 'ACTIVE']);

        // Access restored!
        $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course, $lesson]))
            ->assertOk();

        // 3. Course transitioned to DRAFT
        $course->update(['status' => 'DRAFT']);

        // Lesson is blocked (404) for member even with entitlement
        $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course, $lesson]))
            ->assertNotFound();

        // 4. Course republished
        $course->update(['status' => 'PUBLISHED']);

        // Access restored again without rebuying
        $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course, $lesson]))
            ->assertOk();
    }

    /**
     * AC-06: Order/history shows purchase purpose and stored price; neither endpoint exposes another payer's course/payment data.
     */
    public function test_ac06_order_and_history_display_correct_purpose_and_isolated_payer_data(): void
    {
        $community = Community::factory()->create();
        $creator = $community->creator;
        $memberA = $this->createActiveMember($community);
        $memberB = $this->createActiveMember($community);

        [$course] = $this->createCourseWithLesson($community, Course::ACCESS_MODE_ONE_TIME, 750000);

        $paymentA = Payment::factory()->forCourse($course)->succeeded()->create([
            'user_id' => $memberA->id,
            'amount' => 750000,
        ]);
        CourseEntitlement::create([
            'community_id' => $community->id,
            'course_id' => $course->id,
            'user_id' => $memberA->id,
            'payment_id' => $paymentA->id,
        ]);

        // Course price is later updated by creator to 900.000 VND
        $course->update(['price' => 900000]);

        // Member A views personal payment history
        $historyResponse = $this->actingAs($memberA)->get(route('account.payment-history.index'));
        $historyResponse->assertOk()
            ->assertSee('Course Access: '.$course->title)
            ->assertSee('750.000 VND')
            ->assertDontSee('900.000 VND');

        // Member A views receipt details
        $receiptResponse = $this->actingAs($memberA)->get(route('account.payment-history.show', $paymentA));
        $receiptResponse->assertOk()
            ->assertSee('Course Access: '.$course->title)
            ->assertSee('750.000 VND')
            ->assertSee('Course Unlocked');

        // Member B cannot view Member A's receipt (404 isolation)
        $this->actingAs($memberB)->get(route('account.payment-history.show', $paymentA))
            ->assertNotFound();

        // Creator views community orders
        $ordersResponse = $this->actingAs($creator)->get(route('communities.orders.index', $community));
        $ordersResponse->assertOk()
            ->assertSee($paymentA->external_reference)
            ->assertSee('Course Access: '.$course->title)
            ->assertSee('750.000 VND')
            ->assertSee($memberA->name);

        // Creator of another community cannot view Community A's orders (404)
        $otherCommunity = Community::factory()->create();
        $this->actingAs($otherCommunity->creator)->get(route('communities.orders.index', $community))
            ->assertNotFound();
    }
}
