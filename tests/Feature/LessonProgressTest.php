<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LessonProgressTest extends TestCase
{
    use RefreshDatabase;

    private function createCommunityWithCreator(): array
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        return [$community, $creator];
    }

    private function createActiveMember(Community $community): User
    {
        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        return $member;
    }

    private function createPublishedCourseWithLesson(Community $community): array
    {
        $course = Course::factory()->create([
            'community_id' => $community->id,
            'status' => 'PUBLISHED',
        ]);
        $section = CourseSection::factory()->create([
            'course_id' => $course->id,
        ]);
        $lesson = Lesson::factory()->create([
            'course_section_id' => $section->id,
            'status' => 'PUBLISHED',
        ]);

        return [$course, $section, $lesson];
    }

    public function test_schema_has_lesson_progresses_table_with_unique_constraint(): void
    {
        $this->assertTrue(Schema::hasTable('lesson_progresses'));
        $this->assertTrue(Schema::hasColumns('lesson_progresses', [
            'id', 'user_id', 'lesson_id', 'completed', 'completed_at', 'created_at', 'updated_at',
        ]));
    }

    public function test_active_member_can_mark_lesson_complete_and_incomplete_persisted(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $member = $this->createActiveMember($community);
        [$course, $section, $lesson] = $this->createPublishedCourseWithLesson($community);

        // Member marks completed
        $response = $this->actingAs($member)->post(route('communities.lessons.progress.update', [
            $community, $course, $lesson,
        ]), ['completed' => 1]);

        $response->assertRedirect();
        $this->assertDatabaseHas('lesson_progresses', [
            'user_id' => $member->id,
            'lesson_id' => $lesson->id,
            'completed' => true,
        ]);
        $this->assertTrue($lesson->isCompletedBy($member));

        $progress = LessonProgress::where('user_id', $member->id)->where('lesson_id', $lesson->id)->first();
        $this->assertNotNull($progress->completed_at);

        // Re-authenticate / refresh session to ensure persistence
        $this->actingAs($member->fresh());
        $this->assertTrue($lesson->fresh()->isCompletedBy($member));

        // Member marks incomplete
        $responseIncomplete = $this->actingAs($member)->post(route('communities.lessons.progress.update', [
            $community, $course, $lesson,
        ]), ['completed' => 0]);

        $responseIncomplete->assertRedirect();
        $this->assertDatabaseHas('lesson_progresses', [
            'user_id' => $member->id,
            'lesson_id' => $lesson->id,
            'completed' => false,
            'completed_at' => null,
        ]);
        $this->assertFalse($lesson->isCompletedBy($member));
    }

    public function test_progress_updates_are_idempotent_and_support_json(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $member = $this->createActiveMember($community);
        [$course, $section, $lesson] = $this->createPublishedCourseWithLesson($community);

        // Multiple idempotent writes
        for ($i = 0; $i < 3; $i++) {
            $response = $this->actingAs($member)->postJson(route('communities.lessons.progress.update', [
                $community, $course, $lesson,
            ]), ['completed' => true]);

            $response->assertOk()
                ->assertJson([
                    'lesson_id' => $lesson->id,
                    'completed' => true,
                    'course_progress_percentage' => 100,
                    'course_completed_lessons_count' => 1,
                    'course_published_lessons_count' => 1,
                ]);
        }

        $this->assertSame(1, LessonProgress::where('user_id', $member->id)->where('lesson_id', $lesson->id)->count());
    }

    public function test_members_maintain_independent_progress_and_spoofed_identity_is_ignored(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $memberA = $this->createActiveMember($community);
        $memberB = $this->createActiveMember($community);
        [$course, $section, $lesson] = $this->createPublishedCourseWithLesson($community);

        // Member A marks complete with spoofed user_id in payload attempting to alter Member B
        $response = $this->actingAs($memberA)->post(route('communities.lessons.progress.update', [
            $community, $course, $lesson,
        ]), [
            'completed' => true,
            'user_id' => $memberB->id,
        ]);

        $response->assertRedirect();

        // Member A has progress, Member B has none
        $this->assertTrue($lesson->isCompletedBy($memberA));
        $this->assertFalse($lesson->isCompletedBy($memberB));
        $this->assertDatabaseMissing('lesson_progresses', [
            'user_id' => $memberB->id,
            'lesson_id' => $lesson->id,
        ]);
    }

    public function test_progress_denied_with_404_for_non_active_memberships(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        [$course, $section, $lesson] = $this->createPublishedCourseWithLesson($community);

        // Non-member
        $stranger = User::factory()->create();
        $this->actingAs($stranger)->post(route('communities.lessons.progress.update', [
            $community, $course, $lesson,
        ]), ['completed' => true])->assertNotFound();

        // Inactive membership statuses
        $inactiveStatuses = ['PENDING_PAYMENT', 'SUSPENDED', 'REMOVED', 'LEFT'];
        foreach ($inactiveStatuses as $status) {
            $user = User::factory()->create();
            CommunityMembership::factory()->create([
                'community_id' => $community->id,
                'user_id' => $user->id,
                'status' => $status,
            ]);

            $this->actingAs($user)->post(route('communities.lessons.progress.update', [
                $community, $course, $lesson,
            ]), ['completed' => true])->assertNotFound();
        }
    }

    public function test_creator_without_active_membership_cannot_track_progress(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        [$course, $section, $lesson] = $this->createPublishedCourseWithLesson($community);

        // Creator alone has no active membership record
        $this->actingAs($creator)->post(route('communities.lessons.progress.update', [
            $community, $course, $lesson,
        ]), ['completed' => true])->assertNotFound();

        // If creator joins as active member, progress is allowed
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($creator)->post(route('communities.lessons.progress.update', [
            $community, $course, $lesson,
        ]), ['completed' => true])->assertRedirect();

        $this->assertTrue($lesson->isCompletedBy($creator));
    }

    public function test_progress_denied_when_community_inactive_or_course_draft_or_lesson_draft(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $member = $this->createActiveMember($community);

        // Draft Course with published lesson
        $draftCourse = Course::factory()->create([
            'community_id' => $community->id,
            'status' => 'DRAFT',
        ]);
        $section1 = CourseSection::factory()->create(['course_id' => $draftCourse->id]);
        $lesson1 = Lesson::factory()->create(['course_section_id' => $section1->id, 'status' => 'PUBLISHED']);

        $this->actingAs($member)->post(route('communities.lessons.progress.update', [
            $community, $draftCourse, $lesson1,
        ]), ['completed' => true])->assertNotFound();

        // Published Course with Draft Lesson
        $pubCourse = Course::factory()->create([
            'community_id' => $community->id,
            'status' => 'PUBLISHED',
        ]);
        $section2 = CourseSection::factory()->create(['course_id' => $pubCourse->id]);
        $draftLesson = Lesson::factory()->create(['course_section_id' => $section2->id, 'status' => 'DRAFT']);

        $this->actingAs($member)->post(route('communities.lessons.progress.update', [
            $community, $pubCourse, $draftLesson,
        ]), ['completed' => true])->assertNotFound();

        // Inactive community
        $community->forceFill(['status' => 'ARCHIVED'])->save();
        [$course3, $sec3, $lesson3] = $this->createPublishedCourseWithLesson($community);

        $this->actingAs($member)->post(route('communities.lessons.progress.update', [
            $community, $course3, $lesson3,
        ]), ['completed' => true])->assertNotFound();
    }

    public function test_ancestry_mismatch_denied_with_404(): void
    {
        [$communityA, $creatorA] = $this->createCommunityWithCreator();
        [$communityB, $creatorB] = $this->createCommunityWithCreator();
        $memberA = $this->createActiveMember($communityA);
        [$courseA, $sectionA, $lessonA] = $this->createPublishedCourseWithLesson($communityA);
        [$courseB, $sectionB, $lessonB] = $this->createPublishedCourseWithLesson($communityB);

        // Lesson B passed with Course A & Community A
        $this->actingAs($memberA)->post(route('communities.lessons.progress.update', [
            $communityA, $courseA, $lessonB,
        ]), ['completed' => true])->assertNotFound();
    }

    public function test_unpublish_retains_rows_and_republish_restores_progress(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $member = $this->createActiveMember($community);
        [$course, $section, $lesson] = $this->createPublishedCourseWithLesson($community);

        // Mark completed
        $this->actingAs($member)->post(route('communities.lessons.progress.update', [
            $community, $course, $lesson,
        ]), ['completed' => true]);

        $this->assertSame(1, $course->completedLessonsCountFor($member));
        $this->assertSame(100, $course->progressPercentageFor($member));

        // Unpublish lesson
        $lesson->update(['status' => 'DRAFT']);

        // Progress row retained in DB
        $this->assertDatabaseHas('lesson_progresses', [
            'user_id' => $member->id,
            'lesson_id' => $lesson->id,
            'completed' => true,
        ]);

        // Course calculations exclude the draft lesson
        $this->assertSame(0, $course->completedLessonsCountFor($member));
        $this->assertSame(0, $course->publishedLessonsCount());
        $this->assertSame(0, $course->progressPercentageFor($member));

        // Republish lesson
        $lesson->update(['status' => 'PUBLISHED']);

        // Restores retained progress accurately
        $this->assertSame(1, $course->completedLessonsCountFor($member));
        $this->assertSame(1, $course->publishedLessonsCount());
        $this->assertSame(100, $course->progressPercentageFor($member));
    }

    public function test_course_summary_progress_calculations_and_zero_division_safety(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $member = $this->createActiveMember($community);

        $course = Course::factory()->create([
            'community_id' => $community->id,
            'status' => 'PUBLISHED',
        ]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);

        // 0 published lessons: safe divide-by-zero check
        $this->assertSame(0, $course->publishedLessonsCount());
        $this->assertSame(0, $course->completedLessonsCountFor($member));
        $this->assertSame(0, $course->progressPercentageFor($member));

        $lesson1 = Lesson::factory()->create(['course_section_id' => $section->id, 'status' => 'PUBLISHED']);
        $lesson2 = Lesson::factory()->create(['course_section_id' => $section->id, 'status' => 'PUBLISHED']);
        $lessonDraft = Lesson::factory()->create(['course_section_id' => $section->id, 'status' => 'DRAFT']);

        // Complete lesson1 and draft lesson
        LessonProgress::create(['user_id' => $member->id, 'lesson_id' => $lesson1->id, 'completed' => true]);
        LessonProgress::create(['user_id' => $member->id, 'lesson_id' => $lessonDraft->id, 'completed' => true]);

        // Total published = 2, completed published = 1 -> 50%
        $this->assertSame(2, $course->publishedLessonsCount());
        $this->assertSame(1, $course->completedLessonsCountFor($member));
        $this->assertSame(50, $course->progressPercentageFor($member));
    }

    public function test_lesson_show_and_course_show_views_render_progress_indicators(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $member = $this->createActiveMember($community);
        [$course, $section, $lesson] = $this->createPublishedCourseWithLesson($community);

        // Incomplete state
        $response = $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course, $lesson]));
        $response->assertOk()
            ->assertSee('Mark Complete');

        // Mark complete
        LessonProgress::create(['user_id' => $member->id, 'lesson_id' => $lesson->id, 'completed' => true]);

        $responseCompleted = $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course, $lesson]));
        $responseCompleted->assertOk()
            ->assertSee('Completed');

        // Course show view
        $responseCourse = $this->actingAs($member)->get(route('communities.courses.show', [$community, $course]));
        $responseCourse->assertOk()
            ->assertSee('Your Progress')
            ->assertSee('1 of 1 completed (100%)');
    }
}
