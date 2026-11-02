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
use Illuminate\Support\Facades\App;
use Illuminate\Support\Js;
use Tests\TestCase;

class LearnerCourseFlowTest extends TestCase
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

    private function setupCourseWithLessons(Community $community, int $count = 3): array
    {
        $course = Course::factory()->create([
            'community_id' => $community->id,
            'status' => 'PUBLISHED',
            'access_mode' => 'INCLUDED',
        ]);

        $section = CourseSection::factory()->create([
            'course_id' => $course->id,
            'order' => 1,
            'title' => 'Chương 1: Khởi động',
        ]);

        $lessons = collect();
        for ($i = 1; $i <= $count; $i++) {
            $lessons->push(Lesson::factory()->create([
                'course_section_id' => $section->id,
                'order' => $i,
                'title' => "Bài $i: Nhập môn",
                'status' => 'PUBLISHED',
                'content' => "Nội dung bài học số $i",
            ]));
        }

        return [$course, $section, $lessons];
    }

    /**
     * AC-01: Given a learner with lessons 1–2 complete, "Học tiếp" opens lesson 3.
     */
    public function test_ac01_learner_with_lessons_1_and_2_completed_sees_continue_button_pointing_to_lesson_3(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $member = $this->createActiveMember($community);
        [$course, $section, $lessons] = $this->setupCourseWithLessons($community, 3);

        App::setLocale('vi');

        // Case 1: 0 completed lessons -> Shows "Bắt đầu học" pointing to Lesson 1
        $response = $this->actingAs($member)->get(route('communities.courses.show', [$community, $course]));
        $response->assertOk();
        $response->assertSee('Bắt đầu học');
        $response->assertSee(route('communities.lessons.show', [$community, $course, $lessons[0]]));

        // Mark lesson 1 and 2 as completed
        LessonProgress::create([
            'user_id' => $member->id,
            'lesson_id' => $lessons[0]->id,
            'completed' => true,
            'completed_at' => now(),
        ]);
        LessonProgress::create([
            'user_id' => $member->id,
            'lesson_id' => $lessons[1]->id,
            'completed' => true,
            'completed_at' => now(),
        ]);

        // Case 2: Lessons 1 and 2 complete -> Shows "Học tiếp" pointing to Lesson 3
        $responseContinue = $this->actingAs($member)->get(route('communities.courses.show', [$community, $course]));
        $responseContinue->assertOk();
        $responseContinue->assertSee('Học tiếp');
        $responseContinue->assertSee('Bài tiếp theo:');
        $responseContinue->assertSee($lessons[2]->title);
        $responseContinue->assertSee(route('communities.lessons.show', [$community, $course, $lessons[2]]));

        // Case 3: Complete all 3 lessons -> Shows "Ôn tập khóa học" pointing to Lesson 1
        LessonProgress::create([
            'user_id' => $member->id,
            'lesson_id' => $lessons[2]->id,
            'completed' => true,
            'completed_at' => now(),
        ]);

        $responseReview = $this->actingAs($member)->get(route('communities.courses.show', [$community, $course]));
        $responseReview->assertOk();
        $responseReview->assertSee('Ôn tập khóa học');
        $responseReview->assertSee(route('communities.lessons.show', [$community, $course, $lessons[0]]));
    }

    /**
     * AC-02: Given a failed completion request, previous state remains, localized Vietnamese error appears, retry works.
     */
    public function test_ac02_failed_completion_request_preserves_state_and_allows_retry(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $member = $this->createActiveMember($community);
        [$course, $section, $lessons] = $this->setupCourseWithLessons($community, 1);
        $lesson = $lessons[0];

        // Ensure initially incomplete
        $this->assertFalse($lesson->isCompletedBy($member));

        // Unauthenticated or invalid payload preserves uncompleted state
        $invalidResponse = $this->actingAs($member)->postJson(route('communities.lessons.progress.update', [
            $community, $course, $lesson,
        ]), ['completed' => 'invalid_boolean']);

        $invalidResponse->assertStatus(422);
        $this->assertDatabaseMissing('lesson_progresses', [
            'user_id' => $member->id,
            'lesson_id' => $lesson->id,
            'completed' => true,
        ]);
        $this->assertFalse($lesson->fresh()->isCompletedBy($member));

        // Verify lesson progress component renders localized error message and retry capability
        App::setLocale('vi');
        $this->assertSame('Không thể cập nhật tiến độ. Vui lòng thử lại.', __('Could not update progress. Please try again.'));
        $readerResponse = $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course, $lesson]));
        $readerResponse->assertOk();
        $readerResponse->assertSee(Js::from(__('Could not update progress. Please try again.')), false);
        $readerResponse->assertSee('Đánh dấu hoàn thành');

        // Successful retry persists completion
        $successResponse = $this->actingAs($member)->postJson(route('communities.lessons.progress.update', [
            $community, $course, $lesson,
        ]), ['completed' => 1]);

        $successResponse->assertOk();
        $successResponse->assertJson([
            'lesson_id' => $lesson->id,
            'completed' => true,
        ]);
        $this->assertTrue($lesson->fresh()->isCompletedBy($member));
    }

    /**
     * AC-03: Double-clicking completion creates one progress record and no duplicate contribution.
     */
    public function test_ac03_double_clicking_completion_creates_single_record_idempotently(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $member = $this->createActiveMember($community);
        [$course, $section, $lessons] = $this->setupCourseWithLessons($community, 1);
        $lesson = $lessons[0];

        // Send two consecutive completion mutations
        $response1 = $this->actingAs($member)->postJson(route('communities.lessons.progress.update', [
            $community, $course, $lesson,
        ]), ['completed' => 1]);
        $response1->assertOk();

        $response2 = $this->actingAs($member)->postJson(route('communities.lessons.progress.update', [
            $community, $course, $lesson,
        ]), ['completed' => 1]);
        $response2->assertOk();

        // Exactly one database record exists
        $this->assertSame(1, LessonProgress::where('user_id', $member->id)->where('lesson_id', $lesson->id)->count());
        $this->assertTrue($lesson->fresh()->isCompletedBy($member));
    }

    /**
     * AC-04: Drafts and empty unpublished sections stay hidden; suspended/removed users and mismatched URLs cannot read or modify progress.
     */
    public function test_ac04_drafts_and_empty_sections_hidden_from_learners_and_invalid_access_returns_404(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $member = $this->createActiveMember($community);
        [$course, $section, $lessons] = $this->setupCourseWithLessons($community, 1);

        // Section 2 has only DRAFT lessons
        $sectionDraft = CourseSection::factory()->create([
            'course_id' => $course->id,
            'order' => 2,
            'title' => 'Chương Bản Nháp',
        ]);
        $draftLesson = Lesson::factory()->create([
            'course_section_id' => $sectionDraft->id,
            'order' => 1,
            'title' => 'Bài học chưa xuất bản',
            'status' => 'DRAFT',
        ]);

        // Section 3 is empty
        $emptySection = CourseSection::factory()->create([
            'course_id' => $course->id,
            'order' => 3,
            'title' => 'Chương Trống',
        ]);

        // Member cannot see draft lessons, draft sections, or empty sections
        $courseView = $this->actingAs($member)->get(route('communities.courses.show', [$community, $course]));
        $courseView->assertOk();
        $courseView->assertDontSee('Chương Bản Nháp');
        $courseView->assertDontSee('Bài học chưa xuất bản');
        $courseView->assertDontSee('Chương Trống');

        // Member cannot view draft lesson directly
        $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course, $draftLesson]))
            ->assertStatus(404);

        // Member cannot mutate progress on draft lesson
        $this->actingAs($member)->postJson(route('communities.lessons.progress.update', [
            $community, $course, $draftLesson,
        ]), ['completed' => 1])->assertStatus(404);

        // Suspended or removed user cannot view course or mutate progress
        $suspendedUser = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $suspendedUser->id,
            'status' => 'SUSPENDED',
        ]);

        $this->actingAs($suspendedUser)->get(route('communities.lessons.show', [$community, $course, $lessons[0]]))
            ->assertStatus(404);
        $this->actingAs($suspendedUser)->postJson(route('communities.lessons.progress.update', [
            $community, $course, $lessons[0],
        ]), ['completed' => 1])->assertStatus(404);

        // Mismatched course/lesson URL returns 404
        $otherCourse = Course::factory()->create([
            'community_id' => $community->id,
            'status' => 'PUBLISHED',
        ]);
        $this->actingAs($member)->get(route('communities.lessons.show', [$community, $otherCourse, $lessons[0]]))
            ->assertStatus(404);
        $this->actingAs($member)->postJson(route('communities.lessons.progress.update', [
            $community, $otherCourse, $lessons[0],
        ]), ['completed' => 1])->assertStatus(404);
    }

    /**
     * AC-05: An unentitled learner on a paid course sees price/access explanation and cannot fetch lesson bodies or private media.
     */
    public function test_ac05_unentitled_learner_on_paid_course_sees_paywall_and_is_blocked_from_content(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $member = $this->createActiveMember($community);

        $paidCourse = Course::factory()->create([
            'community_id' => $community->id,
            'status' => 'PUBLISHED',
            'access_mode' => 'ONE_TIME',
            'price' => 200000,
        ]);
        $section = CourseSection::factory()->create([
            'course_id' => $paidCourse->id,
            'order' => 1,
            'title' => 'Chương 1',
        ]);
        $lesson = Lesson::factory()->create([
            'course_section_id' => $section->id,
            'order' => 1,
            'title' => 'Bí quyết đầu tư',
            'status' => 'PUBLISHED',
            'content' => 'Nội dung bí mật cao cấp',
        ]);

        App::setLocale('vi');

        // Unentitled member visits course show -> sees price explanation and buy course button
        $response = $this->actingAs($member)->get(route('communities.courses.show', [$community, $paidCourse]));
        $response->assertOk();
        $response->assertSee('Mở khóa toàn bộ khóa học này');
        $response->assertSee('Khóa học này được bán trọn gói một lần. Hoàn tất thanh toán qua Chế độ thử nghiệm SePay để mở khóa toàn bộ bài học.');
        $response->assertSee('Mua khóa học');
        $response->assertSee('200.000');
        // Curriculum is hidden from unentitled learner
        $response->assertDontSee('Bí quyết đầu tư');

        // Unentitled member cannot access lesson reader directly
        $this->actingAs($member)->get(route('communities.lessons.show', [$community, $paidCourse, $lesson]))
            ->assertStatus(404);

        // Unentitled member cannot mutate progress
        $this->actingAs($member)->postJson(route('communities.lessons.progress.update', [
            $community, $paidCourse, $lesson,
        ]), ['completed' => 1])->assertStatus(404);
    }

    /**
     * AC-06: 100% of reader and course view labels are properly translated into Vietnamese.
     */
    public function test_ac06_all_learner_flow_and_reader_labels_are_translated_in_vietnamese(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $member = $this->createActiveMember($community);
        [$course, $section, $lessons] = $this->setupCourseWithLessons($community, 2);
        $lesson = $lessons[0];

        App::setLocale('vi');

        // Test course view Vietnamese labels
        $courseView = $this->actingAs($member)->get(route('communities.courses.show', [$community, $course]));
        $courseView->assertOk();
        $courseView->assertSee('Lớp Học');
        $courseView->assertSee('Tiến độ của bạn');
        $courseView->assertSee('Nội dung khóa học');
        $courseView->assertSee('Bắt đầu học');

        // Test lesson reader view Vietnamese labels
        $lessonView = $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course, $lesson]));
        $lessonView->assertOk();
        $lessonView->assertSee('Lớp Học');
        $lessonView->assertSee('Nội dung khóa học');
        $lessonView->assertSee('Tổng quan khóa học');
        $lessonView->assertSee('Tìm bài học...');
        $lessonView->assertSee('Điều hướng bài học');
        $lessonView->assertSee('Bài tiếp theo');
        $lessonView->assertSee('Đặt câu hỏi về bài học này');
        $lessonView->assertSee('Đánh dấu hoàn thành');
        $lessonView->assertSee('Chưa hoàn thành');
    }
}
