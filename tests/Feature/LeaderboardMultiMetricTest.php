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

class LeaderboardMultiMetricTest extends TestCase
{
    use RefreshDatabase;

    public function test_leaderboard_renders_metric_switcher_tabs_and_explanatory_copy(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $member = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);

        $this->actingAs($member);

        $response = $this->get(route('communities.leaderboard', $community));
        $response->assertOk()
            ->assertSee('Leaderboard')
            ->assertSee('Engagement Points')
            ->assertSee('Verified Skills')
            ->assertSee('Helpful Answers')
            ->assertSee('Leaderboard & Scoring');
    }

    public function test_leaderboard_verified_skills_ranks_users_with_deterministic_tie_breaking(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        // User A (lower id, 1 skill)
        $userA = User::factory()->create(['name' => 'Alice Skills', 'id' => 100]);
        // User B (higher id, 1 skill - same score, should be ranked after User A due to score DESC, user_id ASC)
        $userB = User::factory()->create(['name' => 'Bob Skills', 'id' => 200]);
        // User C (2 skills - top rank)
        $userC = User::factory()->create(['name' => 'Charlie Skills', 'id' => 300]);

        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $userA->id, 'status' => 'ACTIVE']);
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $userB->id, 'status' => 'ACTIVE']);
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $userC->id, 'status' => 'ACTIVE']);

        $course = Course::factory()->create(['community_id' => $community->id, 'status' => 'PUBLISHED']);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson1 = Lesson::factory()->create(['course_section_id' => $section->id, 'status' => 'PUBLISHED', 'is_practical' => true, 'skill_label' => 'Skill 1']);
        $lesson2 = Lesson::factory()->create(['course_section_id' => $section->id, 'status' => 'PUBLISHED', 'is_practical' => true, 'skill_label' => 'Skill 2']);

        // User C: 2 verified skills
        LessonProofOfWork::create([
            'community_id' => $community->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson1->id,
            'user_id' => $userC->id,
            'status' => LessonProofOfWork::STATUS_VERIFIED,
            'is_current' => true,
            'skill_label_snapshot' => 'Skill 1',
            'submitted_at' => now(),
            'reviewed_at' => now(),
            'reviewer_id' => $creator->id,
        ]);
        LessonProofOfWork::create([
            'community_id' => $community->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson2->id,
            'user_id' => $userC->id,
            'status' => LessonProofOfWork::STATUS_VERIFIED,
            'is_current' => true,
            'skill_label_snapshot' => 'Skill 2',
            'submitted_at' => now(),
            'reviewed_at' => now(),
            'reviewer_id' => $creator->id,
        ]);

        // User A: 1 verified skill
        LessonProofOfWork::create([
            'community_id' => $community->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson1->id,
            'user_id' => $userA->id,
            'status' => LessonProofOfWork::STATUS_VERIFIED,
            'is_current' => true,
            'skill_label_snapshot' => 'Skill 1',
            'submitted_at' => now(),
            'reviewed_at' => now(),
            'reviewer_id' => $creator->id,
        ]);

        // User B: 1 verified skill
        LessonProofOfWork::create([
            'community_id' => $community->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson1->id,
            'user_id' => $userB->id,
            'status' => LessonProofOfWork::STATUS_VERIFIED,
            'is_current' => true,
            'skill_label_snapshot' => 'Skill 1',
            'submitted_at' => now(),
            'reviewed_at' => now(),
            'reviewer_id' => $creator->id,
        ]);

        $this->actingAs($userA);

        $response = $this->get(route('communities.leaderboard', [$community, 'metric' => 'verified_skills']));
        $response->assertOk()
            ->assertSeeInOrder([
                'Charlie Skills',
                'Alice Skills',
                'Bob Skills',
            ]);
    }

    public function test_leaderboard_helpful_answers_ranks_users_and_excludes_deleted_content(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        $userTop = User::factory()->create(['name' => 'Helper Champion']);
        $userSecond = User::factory()->create(['name' => 'Helper RunnerUp']);
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $userTop->id, 'status' => 'ACTIVE']);
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $userSecond->id, 'status' => 'ACTIVE']);

        $course = Course::factory()->create(['community_id' => $community->id, 'status' => 'PUBLISHED']);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create(['course_section_id' => $section->id, 'status' => 'PUBLISHED', 'is_practical' => true]);

        $post1 = Post::factory()->create(['community_id' => $community->id, 'user_id' => $creator->id, 'course_id' => $course->id, 'lesson_id' => $lesson->id]);
        $post2 = Post::factory()->create(['community_id' => $community->id, 'user_id' => $creator->id, 'course_id' => $course->id, 'lesson_id' => $lesson->id]);

        $commentTop1 = Comment::factory()->create(['post_id' => $post1->id, 'user_id' => $userTop->id, 'body' => 'Solution 1']);
        $commentTop2 = Comment::factory()->create(['post_id' => $post2->id, 'user_id' => $userTop->id, 'body' => 'Solution 2']);
        $commentSecond = Comment::factory()->create(['post_id' => $post1->id, 'user_id' => $userSecond->id, 'body' => 'Solution RunnerUp']);

        $helpfulService = app(HelpfulAnswerService::class);
        $helpfulService->accept($community, $post1, $commentTop1, $creator);
        $helpfulService->accept($community, $post2, $commentTop2, $creator);

        $this->actingAs($userTop);

        $response = $this->get(route('communities.leaderboard', [$community, 'metric' => 'helpful_answers']));
        $response->assertOk()
            ->assertSee('Helper Champion');
    }
}
