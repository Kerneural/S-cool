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

class LessonAuthoringToolbarTest extends TestCase
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

    public function test_authoring_toolbar_and_tabs_render_in_lesson_forms(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $course = Course::factory()->create([
            'community_id' => $community->id,
            'title' => 'Khóa học lập trình',
            'status' => 'DRAFT',
        ]);
        $section = CourseSection::factory()->create([
            'course_id' => $course->id,
            'title' => 'Chương 1',
            'order' => 1,
        ]);
        $lesson = Lesson::factory()->create([
            'course_section_id' => $section->id,
            'title' => 'Bài 1: Giới thiệu',
            'order' => 1,
            'content' => '## Khởi đầu',
        ]);

        $response = $this->actingAs($creator)
            ->get(route('communities.manage.courses.show', [$community, $course, 'lesson' => $lesson->id]))
            ->assertOk();

        // 1. Alpine authoring component registration
        $response->assertSee('lessonAuthoring(', false);

        // 2. Formatting toolbar buttons (type="button")
        $response->assertSee('type="button"', false);
        $response->assertSee('title="Tiêu đề (H2)"', false);
        $response->assertSee('title="In đậm"', false);
        $response->assertSee('title="In nghiêng"', false);
        $response->assertSee('title="Danh sách chấm"', false);
        $response->assertSee('title="Danh sách số"', false);
        $response->assertSee('title="Trích dẫn"', false);
        $response->assertSee('title="Liên kết"', false);

        // 3. Tabs: Soạn thảo and Xem trước
        $response->assertSee('Soạn thảo');
        $response->assertSee('Xem trước');

        // 4. Parity styling container
        $response->assertSee('class="lesson-prose"', false);

        // 5. Resource link guidance text
        $response->assertSee('Để đính kèm tài liệu tham khảo hoặc liên kết bài học khác, hãy chèn cú pháp: [Tên tài nguyên](URL).');

        // 6. Form control association
        $response->assertSee('name="content"', false);
        $response->assertSee('Nội dung bài học');
    }

    public function test_course_creator_can_preview_markdown_content(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $course = Course::factory()->create([
            'community_id' => $community->id,
        ]);

        $markdown = "## Giới thiệu khóa học\n\nĐây là bài học **quan trọng** với [Tài liệu](https://example.com).";

        $response = $this->actingAs($creator)
            ->postJson(route('communities.courses.lessons.preview', [$community, $course]), [
                'content' => $markdown,
            ])
            ->assertOk()
            ->assertJsonStructure(['html']);

        $html = $response->json('html');
        $this->assertStringContainsString('<h2>Giới thiệu khóa học</h2>', $html);
        $this->assertStringContainsString('<strong>quan trọng</strong>', $html);
        $this->assertStringContainsString('<a href="https://example.com"', $html);
        $this->assertStringContainsString('Tài liệu</a>', $html);
    }

    public function test_preview_sanitizes_xss_and_javascript_schemes(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $course = Course::factory()->create([
            'community_id' => $community->id,
        ]);

        $unsafeContent = "<script>alert('xss')</script>\n\n[Click here](javascript:alert(1)) <img src=x onerror=alert(1)>";

        $response = $this->actingAs($creator)
            ->postJson(route('communities.courses.lessons.preview', [$community, $course]), [
                'content' => $unsafeContent,
            ])
            ->assertOk();

        $html = $response->json('html');
        $this->assertStringNotContainsString("<script>alert('xss')</script>", $html);
        $this->assertStringNotContainsString('javascript:alert', $html);
        $this->assertStringNotContainsString('onerror=', $html);
    }

    public function test_preview_denies_unauthenticated_and_unauthorized_users(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $course = Course::factory()->create([
            'community_id' => $community->id,
        ]);

        // Guest request
        $this->postJson(route('communities.courses.lessons.preview', [$community, $course]), [
            'content' => 'Sample markdown',
        ])->assertUnauthorized();

        // Non-creator member
        $otherUser = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $otherUser->id,
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($otherUser)
            ->postJson(route('communities.courses.lessons.preview', [$community, $course]), [
                'content' => 'Sample markdown',
            ])
            ->assertNotFound();
    }

    public function test_preview_rejects_mismatched_community_and_course_ancestry(): void
    {
        [$community1, $creator1] = $this->createCommunityWithCreator();
        [$community2] = $this->createCommunityWithCreator();

        $course1 = Course::factory()->create([
            'community_id' => $community1->id,
        ]);

        // Attempting to preview course 1 using community 2
        $this->actingAs($creator1)
            ->postJson(route('communities.courses.lessons.preview', [$community2, $course1]), [
                'content' => 'Cross-community preview attempt',
            ])
            ->assertNotFound();
    }

    public function test_preview_validates_oversized_content(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $course = Course::factory()->create([
            'community_id' => $community->id,
        ]);

        $oversizedContent = str_repeat('a', 50001);

        $this->actingAs($creator)
            ->postJson(route('communities.courses.lessons.preview', [$community, $course]), [
                'content' => $oversizedContent,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['content']);
    }

    public function test_preview_handles_empty_or_null_content(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $course = Course::factory()->create([
            'community_id' => $community->id,
        ]);

        $response = $this->actingAs($creator)
            ->postJson(route('communities.courses.lessons.preview', [$community, $course]), [
                'content' => '',
            ])
            ->assertOk()
            ->assertExactJson(['html' => '']);

        $this->assertSame('', $response->json('html'));
    }

    public function test_lesson_fields_resolves_preview_url_for_create_and_edit_forms_in_workspace(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $course = Course::factory()->create([
            'community_id' => $community->id,
            'status' => 'DRAFT',
        ]);
        $section = CourseSection::factory()->create([
            'course_id' => $course->id,
            'title' => 'Chương 1',
        ]);
        $lesson = Lesson::factory()->create([
            'course_section_id' => $section->id,
            'title' => 'Bài 1',
        ]);

        $expectedPreviewRoute = route('communities.courses.lessons.preview', [$community, $course]);

        // 1. Edit mode (?lesson=1)
        $editResponse = $this->actingAs($creator)
            ->get(route('communities.manage.courses.show', [$community, $course, 'lesson' => $lesson->id]))
            ->assertOk();

        $editResponse->assertSee('courses\/'.$course->id.'\/lessons\/preview', false);

        // 2. Create lesson mode (?create_lesson=1)
        $createResponse = $this->actingAs($creator)
            ->get(route('communities.manage.courses.show', [$community, $course, 'create_lesson' => $section->id]))
            ->assertOk();

        $createResponse->assertSee('courses\/'.$course->id.'\/lessons\/preview', false);
    }
}
