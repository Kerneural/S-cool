<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\LessonProofOfWork;
use App\Models\Post;
use App\Models\User;
use App\Services\HelpfulAnswerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityContributionProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_renders_verified_skills_scoped_to_community(): void
    {
        $creator = User::factory()->create();
        $communityA = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $communityB = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        $member = User::factory()->create([
            'name' => 'Alice Member',
            'email' => 'alice.private@example.com',
            'github_url' => 'https://github.com/alicemember',
            'portfolio_url' => 'https://alice.dev',
        ]);
        CommunityMembership::factory()->create(['community_id' => $communityA->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);
        CommunityMembership::factory()->create(['community_id' => $communityB->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);

        // In Community A: Course, CourseSection, 2 Lessons with skills
        $courseA = Course::factory()->create(['community_id' => $communityA->id, 'status' => 'PUBLISHED']);
        $sectionA = CourseSection::factory()->create(['course_id' => $courseA->id]);
        $lessonA1 = Lesson::factory()->create(['course_section_id' => $sectionA->id, 'status' => 'PUBLISHED', 'is_practical' => true, 'skill_label' => 'Docker Mastery']);
        $lessonA2 = Lesson::factory()->create(['course_section_id' => $sectionA->id, 'status' => 'PUBLISHED', 'is_practical' => true, 'skill_label' => 'Laravel Security']);

        // In Community B: Course, Lesson with different skill
        $courseB = Course::factory()->create(['community_id' => $communityB->id, 'status' => 'PUBLISHED']);
        $sectionB = CourseSection::factory()->create(['course_id' => $courseB->id]);
        $lessonB = Lesson::factory()->create(['course_section_id' => $sectionB->id, 'status' => 'PUBLISHED', 'is_practical' => true, 'skill_label' => 'Kubernetes Ops']);

        // Verified proof in Community A
        LessonProofOfWork::create([
            'community_id' => $communityA->id,
            'course_id' => $courseA->id,
            'lesson_id' => $lessonA1->id,
            'user_id' => $member->id,
            'revision' => 1,
            'is_current' => true,
            'status' => LessonProofOfWork::STATUS_VERIFIED,
            'skill_label_snapshot' => 'Docker Mastery',
            'summary' => 'Private submission details for Docker',
            'submitted_at' => now(),
            'reviewed_at' => now(),
            'reviewer_id' => $creator->id,
        ]);

        // Verified proof in Community B
        LessonProofOfWork::create([
            'community_id' => $communityB->id,
            'course_id' => $courseB->id,
            'lesson_id' => $lessonB->id,
            'user_id' => $member->id,
            'revision' => 1,
            'is_current' => true,
            'status' => LessonProofOfWork::STATUS_VERIFIED,
            'skill_label_snapshot' => 'Kubernetes Ops',
            'summary' => 'Private submission details for K8s',
            'submitted_at' => now(),
            'reviewed_at' => now(),
            'reviewer_id' => $creator->id,
        ]);

        // Profile view in Community A viewed by creator (different user):
        $this->actingAs($creator);
        $responseA = $this->get(route('communities.directory.show', [$communityA, $member]));
        $responseA->assertOk()
            ->assertSee('Alice Member')
            ->assertSee('Docker Mastery')
            ->assertDontSee('Kubernetes Ops') // Scoped away from Community B
            ->assertDontSee('alice.private@example.com') // Non-disclosure of email to other members
            ->assertDontSee('Private submission details for Docker') // Non-leakage of submission content
            ->assertSee('https://github.com/alicemember')
            ->assertSee('https://alice.dev');

        // Profile view in Community B:
        $responseB = $this->get(route('communities.directory.show', [$communityB, $member]));
        $responseB->assertOk()
            ->assertSee('Kubernetes Ops')
            ->assertDontSee('Docker Mastery');
    }

    public function test_profile_renders_helpful_answers_count_scoped_to_community(): void
    {
        $creator = User::factory()->create();
        $communityA = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $communityB = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        $member = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $communityA->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);
        CommunityMembership::factory()->create(['community_id' => $communityB->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);

        $courseA = Course::factory()->create(['community_id' => $communityA->id, 'status' => 'PUBLISHED']);
        $sectionA = CourseSection::factory()->create(['course_id' => $courseA->id]);
        $lessonA = Lesson::factory()->create(['course_section_id' => $sectionA->id, 'status' => 'PUBLISHED', 'is_practical' => true]);
        $postA = Post::factory()->create(['community_id' => $communityA->id, 'user_id' => $creator->id, 'course_id' => $courseA->id, 'lesson_id' => $lessonA->id]);

        $commentA = Comment::factory()->create(['post_id' => $postA->id, 'user_id' => $member->id, 'body' => 'Helpful comment in A']);

        $helpfulService = app(HelpfulAnswerService::class);
        $helpfulService->accept($communityA, $postA, $commentA, $creator);

        $this->actingAs($creator);

        $responseA = $this->get(route('communities.directory.show', [$communityA, $member]));
        $responseA->assertOk()
            ->assertSee('1 Helpful Answer');

        // In Community B, user has 0 helpful answers
        $responseB = $this->get(route('communities.directory.show', [$communityB, $member]));
        $responseB->assertOk()
            ->assertSee('0 Helpful Answer');
    }
}
