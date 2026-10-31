<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseManagementWorkspaceTest extends TestCase
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

    /**
     * AC-01: Given the owner, when they create a course, two chapters and three lessons,
     * then each appears in the tree without a full page hunt and in the saved order.
     */
    public function test_ac01_owner_sees_all_chapters_and_lessons_in_saved_order_in_workspace_tree(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $course = Course::factory()->create([
            'community_id' => $community->id,
            'title' => 'Cấu trúc Dữ liệu & Giải thuật',
            'status' => 'DRAFT',
        ]);

        $section1 = CourseSection::factory()->create([
            'course_id' => $course->id,
            'title' => 'Chương 1: Khởi động',
            'order' => 1,
        ]);
        $section2 = CourseSection::factory()->create([
            'course_id' => $course->id,
            'title' => 'Chương 2: Cấu trúc nâng cao',
            'order' => 2,
        ]);

        $lesson1 = Lesson::factory()->create([
            'course_section_id' => $section1->id,
            'title' => 'Bài 1.1: Độ phức tạp thuật toán',
            'order' => 1,
            'status' => 'PUBLISHED',
        ]);
        $lesson2 = Lesson::factory()->create([
            'course_section_id' => $section1->id,
            'title' => 'Bài 1.2: Mảng và Con trỏ',
            'order' => 2,
            'status' => 'DRAFT',
        ]);
        $lesson3 = Lesson::factory()->create([
            'course_section_id' => $section2->id,
            'title' => 'Bài 2.1: Cây nhị phân tìm kiếm',
            'order' => 1,
            'status' => 'DRAFT',
        ]);

        $response = $this->actingAs($creator)
            ->get(route('communities.manage.courses.show', [$community, $course]))
            ->assertOk();

        $content = $response->getContent();

        // Check chapters appear in order
        $posSec1 = strpos($content, 'Chương 1: Khởi động');
        $posSec2 = strpos($content, 'Chương 2: Cấu trúc nâng cao');
        $this->assertNotFalse($posSec1);
        $this->assertNotFalse($posSec2);
        $this->assertLessThan($posSec2, $posSec1);

        // Check lessons appear in order
        $posL1 = strpos($content, 'Bài 1.1: Độ phức tạp thuật toán');
        $posL2 = strpos($content, 'Bài 1.2: Mảng và Con trỏ');
        $posL3 = strpos($content, 'Bài 2.1: Cây nhị phân tìm kiếm');
        $this->assertNotFalse($posL1);
        $this->assertNotFalse($posL2);
        $this->assertNotFalse($posL3);
        $this->assertLessThan($posL2, $posL1);
        $this->assertLessThan($posL3, $posL2);

        // Check badges
        $response->assertSee('Đã xuất bản');
        $response->assertSee('Bản nháp');
    }

    /**
     * AC-02: Given a validation error while editing lesson 2, the page returns
     * with lesson 2 selected and the entered text preserved.
     */
    public function test_ac02_validation_error_while_editing_lesson_returns_page_with_lesson_selected_and_old_input(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $course = Course::factory()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson1 = Lesson::factory()->create(['course_section_id' => $section->id, 'order' => 1]);
        $lesson2 = Lesson::factory()->create(['course_section_id' => $section->id, 'order' => 2]);

        $workspaceUrl = route('communities.manage.courses.show', [$community, $course, 'lesson' => $lesson2->id]);

        // Submit invalid update for lesson 2 from workspace view
        $response = $this->actingAs($creator)
            ->from($workspaceUrl)
            ->put(route('communities.lessons.update', [$community, $course, $section, $lesson2]), [
                '_form' => 'edit-lesson-'.$lesson2->id,
                'title' => '', // required, so validation fails
                'content' => 'Nội dung dở dang cần được giữ lại',
                'status' => 'DRAFT',
            ]);

        $response->assertRedirect($workspaceUrl);
        $response->assertSessionHasErrors(['title']);

        // Follow redirect back to workspace
        $followUp = $this->actingAs($creator)
            ->get($workspaceUrl)
            ->assertOk();

        // Check lesson 2 remains selected and old content preserved
        $followUp->assertSee('Nội dung dở dang cần được giữ lại');
        $followUp->assertSee('edit-lesson-'.$lesson2->id);
    }

    public function test_ac02_validation_error_from_clean_url_without_query_param_still_restores_selected_lesson_via_old_form(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $course = Course::factory()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson1 = Lesson::factory()->create(['course_section_id' => $section->id, 'order' => 1]);
        $lesson2 = Lesson::factory()->create(['course_section_id' => $section->id, 'order' => 2]);

        $cleanUrl = route('communities.manage.courses.show', [$community, $course]);

        // Submit invalid update for lesson 2 from clean workspace URL (no query param in referer)
        $response = $this->actingAs($creator)
            ->from($cleanUrl)
            ->put(route('communities.lessons.update', [$community, $course, $section, $lesson2]), [
                '_form' => 'edit-lesson-'.$lesson2->id,
                'title' => '',
                'content' => 'Nội dung dở dang từ clean URL',
                'status' => 'DRAFT',
            ]);

        $response->assertRedirect($cleanUrl);
        $response->assertSessionHasErrors(['title']);

        // Follow redirect back to clean URL
        $followUp = $this->actingAs($creator)
            ->get($cleanUrl)
            ->assertOk();

        // Check lesson 2 is automatically selected because of old('_form'), NOT defaulted to lesson 1
        $followUp->assertSee('Nội dung dở dang từ clean URL');
        $followUp->assertSee('edit-lesson-'.$lesson2->id);
    }

    /**
     * AC-03: Reordering a lesson within a chapter and a chapter within the course persists after refresh.
     */
    public function test_ac03_reordering_sections_and_lessons_persists(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $course = Course::factory()->create(['community_id' => $community->id]);
        $section1 = CourseSection::factory()->create(['course_id' => $course->id, 'order' => 1]);
        $section2 = CourseSection::factory()->create(['course_id' => $course->id, 'order' => 2]);

        $lesson1 = Lesson::factory()->create(['course_section_id' => $section1->id, 'order' => 1]);
        $lesson2 = Lesson::factory()->create(['course_section_id' => $section1->id, 'order' => 2]);

        // Reorder sections: swap section 2 and section 1
        $this->actingAs($creator)
            ->post(route('communities.courses.sections.reorder', [$community, $course]), [
                'order' => [$section2->id, $section1->id],
            ])
            ->assertRedirect();

        $this->assertSame(1, $section2->fresh()->order);
        $this->assertSame(2, $section1->fresh()->order);

        // Reorder lessons: swap lesson 2 and lesson 1
        $this->actingAs($creator)
            ->post(route('communities.lessons.reorder', [$community, $course, $section1]), [
                'order' => [$lesson2->id, $lesson1->id],
            ])
            ->assertRedirect();

        $this->assertSame(1, $lesson2->fresh()->order);
        $this->assertSame(2, $lesson1->fresh()->order);
    }

    /**
     * AC-04: Given a learner or a creator of another community, the management view
     * and every mutation (including forged section/lesson IDs from another course) return 403/404.
     */
    public function test_ac04_learner_or_foreign_creator_cannot_view_workspace_or_mutate(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $course = Course::factory()->published()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->published()->create(['course_section_id' => $section->id]);

        $learner = $this->createActiveMember($community);

        [$foreignCommunity, $foreignCreator] = $this->createCommunityWithCreator();
        $foreignCourse = Course::factory()->create(['community_id' => $foreignCommunity->id]);
        $foreignSection = CourseSection::factory()->create(['course_id' => $foreignCourse->id]);

        // Learner attempt to view workspace
        $this->actingAs($learner)
            ->get(route('communities.manage.courses.show', [$community, $course]))
            ->assertNotFound();

        // Foreign creator attempt to view workspace
        $this->actingAs($foreignCreator)
            ->get(route('communities.manage.courses.show', [$community, $course]))
            ->assertNotFound();

        // Forged section mutation (foreign section on this course)
        $this->actingAs($creator)
            ->put(route('communities.courses.sections.update', [$community, $course, $foreignSection]), [
                'title' => 'Forged title',
            ])
            ->assertNotFound();

        // Forged lesson mutation
        $this->actingAs($creator)
            ->put(route('communities.lessons.update', [$community, $course, $section, Lesson::factory()->create(['course_section_id' => $foreignSection->id])]), [
                'title' => 'Forged lesson',
                'status' => 'PUBLISHED',
            ])
            ->assertNotFound();
    }

    /**
     * AC-05: Responsive at 375px without horizontal overflow; keyboard reaches tree items,
     * editor fields and save button.
     */
    public function test_ac05_workspace_contains_responsive_and_accessible_markup(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $course = Course::factory()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create(['course_section_id' => $section->id]);

        $response = $this->actingAs($creator)
            ->get(route('communities.manage.courses.show', [$community, $course, 'lesson' => $lesson->id]))
            ->assertOk();

        // Responsive grid classes
        $response->assertSee('grid-cols-1');
        $response->assertSee('md:grid-cols-12');

        // Accessible semantic roles
        $response->assertSee('<aside', false);
        $response->assertSee('<main', false);

        // Labels associated with form fields
        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);

        $inputs = $xpath->query('//main//input[@type!="hidden"] | //main//textarea | //main//select');
        $this->assertGreaterThan(0, $inputs->length);

        /** @var \DOMElement $input */
        foreach ($inputs as $input) {
            $id = $input->getAttribute('id');
            if ($id !== '') {
                $labels = $xpath->query('//label[@for="'.$id.'"]');
                $this->assertGreaterThanOrEqual(1, $labels->length, "Missing label for input id: {$id}");
            }
        }
    }

    /**
     * AC-06: All labels are Vietnamese.
     */
    public function test_ac06_all_labels_are_vietnamese(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $course = Course::factory()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create(['course_section_id' => $section->id]);

        $this->assertFileExists(base_path('lang/modules/classroom/vi.json'));

        $response = $this->actingAs($creator)
            ->get(route('communities.manage.courses.show', [$community, $course, 'lesson' => $lesson->id]))
            ->assertOk();

        $response->assertSee('Lớp Học');
        $response->assertSee('Quản lý khóa học');
        $response->assertSee('Chương trình học');
        $response->assertSee('Thêm chương');
        $response->assertSee('Chỉnh sửa bài học');
        $response->assertSee('Tiêu đề bài học');
        $response->assertSee('Nội dung bài học');
        $response->assertSee('Lưu bài học');
        $response->assertSee('Cài đặt khóa học');

        $settingsResponse = $this->actingAs($creator)
            ->get(route('communities.manage.courses.show', [$community, $course, 'tab' => 'settings']))
            ->assertOk();

        $settingsResponse->assertSee('Tiêu đề khóa học');
        $settingsResponse->assertSee('Mô tả');
        $settingsResponse->assertSee('Quyền truy cập');
        $settingsResponse->assertSee('Bao gồm trong cộng đồng');
        $settingsResponse->assertSee('Mua một lần');
        $settingsResponse->assertSee('Giá (VNĐ)');
        $settingsResponse->assertSee('Bắt buộc đối với Mua một lần');
        $settingsResponse->assertSee('Trạng thái');
        $settingsResponse->assertSee('Lưu khóa học');
        $settingsResponse->assertSee('Ảnh bìa khóa học');
    }
}
