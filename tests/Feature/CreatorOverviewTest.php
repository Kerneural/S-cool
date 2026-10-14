<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Payment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatorOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_creator_sees_exact_counts_for_members_posts_courses_and_payments(): void
    {
        $creator = User::factory()->create(['name' => 'Host Creator']);
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        // Members: 2 ACTIVE, 1 SUSPENDED, 1 REMOVED
        $member1 = User::factory()->create();
        $member2 = User::factory()->create();
        $suspendedUser = User::factory()->create();
        $removedUser = User::factory()->create();

        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member1->id,
            'status' => 'ACTIVE',
        ]);
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member2->id,
            'status' => 'ACTIVE',
        ]);
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $suspendedUser->id,
            'status' => 'SUSPENDED',
        ]);
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $removedUser->id,
            'status' => 'REMOVED',
        ]);

        // Posts: 3 active, 1 soft-deleted
        Post::factory()->count(3)->create(['community_id' => $community->id]);
        $deletedPost = Post::factory()->create(['community_id' => $community->id]);
        $deletedPost->delete();

        // Courses: 2 published, 1 draft
        Course::factory()->count(2)->create([
            'community_id' => $community->id,
            'status' => 'PUBLISHED',
        ]);
        Course::factory()->create([
            'community_id' => $community->id,
            'status' => 'DRAFT',
        ]);

        // Payments: 2 SUCCEEDED (100k + 200k), 1 PENDING (500k), 1 CANCELLED (300k), 1 FAILED (150k)
        Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member1->id,
            'status' => Payment::STATUS_SUCCEEDED,
            'amount' => 100000,
            'currency' => 'VND',
            'paid_at' => now(),
        ]);
        Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member2->id,
            'status' => Payment::STATUS_SUCCEEDED,
            'amount' => 200000,
            'currency' => 'VND',
            'paid_at' => now(),
        ]);
        Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $suspendedUser->id,
            'status' => Payment::STATUS_PENDING,
            'amount' => 500000,
            'currency' => 'VND',
        ]);
        Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $removedUser->id,
            'status' => Payment::STATUS_CANCELLED,
            'amount' => 300000,
            'currency' => 'VND',
        ]);
        Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $removedUser->id,
            'status' => Payment::STATUS_FAILED,
            'amount' => 150000,
            'currency' => 'VND',
        ]);

        $response = $this->actingAs($creator)->get(route('communities.manage', $community));

        $response->assertOk();
        $response->assertViewHas('activeMembersCount', 2);
        $response->assertViewHas('postsCount', 3);
        $response->assertViewHas('publishedCoursesCount', 2);
        $response->assertViewHas('simulatedReceipts', 300000);
        $response->assertSee('300.000 VND');
        $response->assertSee('Host Creator');
        $response->assertSee('Simulated successful receipts (Test Mode)');
    }

    public function test_completion_ratio_calculates_accurately_and_handles_zero_denominator(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        // Case A: 0 published courses -> zero denominator returns 0 and "No data"
        $response = $this->actingAs($creator)->get(route('communities.manage', $community));
        $response->assertOk();
        $response->assertViewHas('hasCompletionData', false);
        $response->assertViewHas('completionRatio', 0);
        $response->assertSee('No data');

        // Case B: Add 2 active members and 1 removed member
        $member1 = User::factory()->create();
        $member2 = User::factory()->create();
        $removed = User::factory()->create();

        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member1->id,
            'status' => 'ACTIVE',
        ]);
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member2->id,
            'status' => 'ACTIVE',
        ]);
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $removed->id,
            'status' => 'REMOVED',
        ]);

        // Add 1 published course with 2 published lessons and 1 draft lesson
        $course = Course::factory()->create([
            'community_id' => $community->id,
            'status' => 'PUBLISHED',
        ]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $pubLesson1 = Lesson::factory()->create(['course_section_id' => $section->id, 'status' => 'PUBLISHED']);
        $pubLesson2 = Lesson::factory()->create(['course_section_id' => $section->id, 'status' => 'PUBLISHED']);
        $draftLesson = Lesson::factory()->create(['course_section_id' => $section->id, 'status' => 'DRAFT']);

        // Completions:
        // Member 1 completes pubLesson1 (valid)
        LessonProgress::create(['user_id' => $member1->id, 'lesson_id' => $pubLesson1->id, 'completed' => true]);
        // Member 2 completes pubLesson1 (valid) and draftLesson (ineligible, should not count)
        LessonProgress::create(['user_id' => $member2->id, 'lesson_id' => $pubLesson1->id, 'completed' => true]);
        LessonProgress::create(['user_id' => $member2->id, 'lesson_id' => $draftLesson->id, 'completed' => true]);
        // Removed member completes pubLesson1 (ineligible member, should not count)
        LessonProgress::create(['user_id' => $removed->id, 'lesson_id' => $pubLesson1->id, 'completed' => true]);

        // Denominator = 2 active members * 2 published lessons = 4
        // Numerator = 2 valid completions
        // Ratio = 50.0%
        $response = $this->actingAs($creator)->get(route('communities.manage', $community));
        $response->assertOk();
        $response->assertViewHas('hasCompletionData', true);
        $response->assertViewHas('completedLessonsCount', 2);
        $response->assertViewHas('publishedLessonsCount', 2);
        $response->assertViewHas('completionRatio', 50.0);
        $response->assertSee('50%');
    }

    public function test_unauthorized_users_and_platform_admin_cannot_access_creator_overview(): void
    {
        $creatorA = User::factory()->create();
        $communityA = Community::factory()->create(['creator_id' => $creatorA->id, 'status' => 'ACTIVE']);

        $creatorB = User::factory()->create();
        $communityB = Community::factory()->create(['creator_id' => $creatorB->id, 'status' => 'ACTIVE']);

        $activeMember = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $communityA->id,
            'user_id' => $activeMember->id,
            'status' => 'ACTIVE',
        ]);

        $outsider = User::factory()->create();

        $platformAdmin = User::factory()->create([
            'is_platform_admin' => true,
        ]);

        // Guest is redirected
        $this->get(route('communities.manage', $communityA))->assertRedirect('/login');

        // Member cannot access (404 anti-enumeration)
        $this->actingAs($activeMember)->get(route('communities.manage', $communityA))->assertNotFound();

        // Outsider cannot access
        $this->actingAs($outsider)->get(route('communities.manage', $communityA))->assertNotFound();

        // Creator B cannot access Creator A's workspace
        $this->actingAs($creatorB)->get(route('communities.manage', $communityA))->assertNotFound();

        // Platform Admin role alone cannot access
        $this->actingAs($platformAdmin)->get(route('communities.manage', $communityA))->assertNotFound();
    }

    public function test_date_boundaries_filter_and_max_range_clamping(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        $payer = User::factory()->create();

        // Payment 1: Today (2 days ago)
        Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $payer->id,
            'status' => Payment::STATUS_SUCCEEDED,
            'amount' => 100000,
            'currency' => 'VND',
            'paid_at' => now()->subDays(2),
            'created_at' => now()->subDays(2),
        ]);

        // Payment 2: 20 days ago
        Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $payer->id,
            'status' => Payment::STATUS_SUCCEEDED,
            'amount' => 200000,
            'currency' => 'VND',
            'paid_at' => now()->subDays(20),
            'created_at' => now()->subDays(20),
        ]);

        // Payment 3: 60 days ago
        Payment::factory()->create([
            'community_id' => $community->id,
            'user_id' => $payer->id,
            'status' => Payment::STATUS_SUCCEEDED,
            'amount' => 400000,
            'currency' => 'VND',
            'paid_at' => now()->subDays(60),
            'created_at' => now()->subDays(60),
        ]);

        // Range = 7 -> Only Payment 1 (100,000)
        $res7 = $this->actingAs($creator)->get(route('communities.manage', ['community' => $community, 'range' => 7]));
        $res7->assertOk();
        $res7->assertViewHas('simulatedReceipts', 100000);

        // Range = 30 -> Payment 1 + Payment 2 (300,000)
        $res30 = $this->actingAs($creator)->get(route('communities.manage', ['community' => $community, 'range' => 30]));
        $res30->assertOk();
        $res30->assertViewHas('simulatedReceipts', 300000);

        // Range = 999 -> Clamped to 90 -> Payment 1 + 2 + 3 (700,000)
        $resClamped = $this->actingAs($creator)->get(route('communities.manage', ['community' => $community, 'range' => 999]));
        $resClamped->assertOk();
        $resClamped->assertViewHas('range', 90);
        $resClamped->assertViewHas('simulatedReceipts', 700000);
    }
}
