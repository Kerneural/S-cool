<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\User;
use App\Services\VideoEmbedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ClassroomRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function hierarchy(): array
    {
        $community = Community::factory()->create();
        $course = Course::factory()->published()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->published()->create(['course_section_id' => $section->id]);

        return [$community, $course, $section, $lesson];
    }

    public function test_inactive_membership_matrix_and_outsiders_cannot_read_or_manage_classroom(): void
    {
        [$community, $course, $section, $lesson] = $this->hierarchy();
        $user = User::factory()->create();
        $membership = CommunityMembership::factory()->active()->create(['community_id' => $community->id, 'user_id' => $user->id]);
        foreach (['PENDING_PAYMENT', 'SUSPENDED', 'REMOVED', 'LEFT'] as $state) {
            $membership->forceFill(['status' => $state])->save();
            foreach (['communities.classroom.index' => [$community], 'communities.courses.show' => [$community, $course], 'communities.lessons.show' => [$community, $course, $lesson]] as $name => $parameters) {
                $this->actingAs($user)->get(route($name, $parameters))->assertNotFound();
            }
        }
        $membership->forceFill(['status' => 'ACTIVE'])->save();
        foreach ([$user, User::factory()->create()] as $actor) {
            $this->actingAs($actor)->post(route('communities.courses.sections.store', [$community, $course]), ['title' => 'No write'])->assertNotFound();
            $this->put(route('communities.courses.sections.update', [$community, $course, $section]), ['title' => 'No write'])->assertNotFound();
            $this->post(route('communities.lessons.store', [$community, $course, $section]), ['title' => 'No write', 'content' => 'No write', 'status' => 'PUBLISHED'])->assertNotFound();
            $this->put(route('communities.lessons.update', [$community, $course, $section, $lesson]), ['title' => 'No write', 'content' => 'No write', 'status' => 'PUBLISHED'])->assertNotFound();
            $this->post(route('communities.lessons.reorder', [$community, $course, $section]), ['order' => [$lesson->id]])->assertNotFound();
        }
        $this->assertNotSame('No write', $lesson->fresh()->title);
        $this->assertDatabaseCount('lessons', 1);
    }

    public function test_creator_cannot_substitute_course_section_or_lesson_ancestry(): void
    {
        [$community, $course, $section, $lesson] = $this->hierarchy();
        $otherCourse = Course::factory()->create(['community_id' => $community->id]);
        $otherSection = CourseSection::factory()->create(['course_id' => $otherCourse->id]);
        $otherLesson = Lesson::factory()->create(['course_section_id' => $otherSection->id]);
        [$foreignCommunity, $foreignCourse, $foreignSection, $foreignLesson] = $this->hierarchy();
        $this->actingAs($community->creator);
        foreach ([$otherLesson, $foreignLesson] as $wrongLesson) {
            $this->get(route('communities.lessons.show', [$community, $course, $wrongLesson]))->assertNotFound();
            $this->put(route('communities.lessons.update', [$community, $course, $section, $wrongLesson]), ['title' => 'No write'])->assertNotFound();
        }
        foreach ([$otherSection, $foreignSection] as $wrongSection) {
            $this->put(route('communities.courses.sections.update', [$community, $course, $wrongSection]), ['title' => 'No write'])->assertNotFound();
            $this->post(route('communities.lessons.store', [$community, $course, $wrongSection]), ['title' => 'No write'])->assertNotFound();
        }
        $this->get(route('communities.courses.show', [$community, $foreignCourse]))->assertNotFound();
        $this->assertNotSame('No write', $lesson->fresh()->title);
    }

    public function test_video_url_limit_matches_storage_and_rejects_credential_edge_cases_on_create_and_update(): void
    {
        [$community, $course, $section, $lesson] = $this->hierarchy();
        $store = route('communities.lessons.store', [$community, $course, $section]);
        $update = route('communities.lessons.update', [$community, $course, $section, $lesson]);
        $this->actingAs($community->creator);
        $url = 'https://youtube.com/watch?v=dQw4w9WgXcQ&note='.str_repeat('a', 420);
        $this->put($update, ['title' => 'Long URL', 'content' => null, 'video_url' => $url, 'status' => 'PUBLISHED'])->assertSessionHasNoErrors();
        $this->assertSame($url, $lesson->fresh()->video_url);
        $this->assertSame('https://www.youtube.com/embed/dQw4w9WgXcQ', $lesson->fresh()->embed_url);
        foreach (['0', 'https://0@youtube.com/watch?v=dQw4w9WgXcQ', 'https://@youtube.com/watch?v=dQw4w9WgXcQ', 'https://youtube.com:8443/watch?v=dQw4w9WgXcQ', 'https://youtube.com/watch?v[]=dQw4w9WgXcQ', '<iframe></iframe>', 'https://youtube.com.attacker.test/watch?v=dQw4w9WgXcQ', str_repeat('a', 501), ['url']] as $invalid) {
            $payload = ['title' => 'Must not persist', 'content' => 'Text', 'video_url' => $invalid, 'status' => 'PUBLISHED'];
            $this->post($store, $payload)->assertSessionHasErrors('video_url');
            $this->put($update, $payload)->assertSessionHasErrors('video_url');
        }
        $this->assertSame('Long URL', $lesson->fresh()->title);
        $this->assertDatabaseCount('lessons', 1);
        $this->assertNotNull(VideoEmbedService::parse('https://vimeo.com/76979871'));
    }

    public function test_empty_or_oversized_lesson_content_fails_without_mutation_and_cannot_move_parents(): void
    {
        [$community, $course, $section, $lesson] = $this->hierarchy();
        $url = route('communities.lessons.update', [$community, $course, $section, $lesson]);
        $this->actingAs($community->creator);
        foreach ([null, str_repeat('x', 50001), ['bad']] as $content) {
            $this->put($url, ['title' => 'No write', 'content' => $content, 'video_url' => null, 'status' => 'PUBLISHED'])->assertSessionHasErrors('content');
        }
        $this->put($url, ['title' => 'Valid text', 'content' => 'Text only', 'video_url' => null, 'status' => 'PUBLISHED', 'course_section_id' => 99999, 'community_id' => 99999])->assertSessionHasNoErrors();
        $this->assertSame($section->id, $lesson->fresh()->course_section_id);
        $this->assertSame('Valid text', $lesson->fresh()->title);
        $this->put($url, ['title' => 'Zero text', 'content' => '0', 'video_url' => null, 'status' => 'PUBLISHED'])->assertSessionHasNoErrors();
        $this->get(route('communities.lessons.show', [$community, $course, $lesson]))->assertOk()->assertSee('>0</p>', false);
    }

    public function test_hard_deletion_is_denied_for_creator_and_all_hierarchy_rows_are_retained(): void
    {
        [$community, $course, $section, $lesson] = $this->hierarchy();
        $this->actingAs($community->creator);
        $this->delete(route('communities.lessons.destroy', [$community, $course, $section, $lesson]))->assertNotFound();
        $this->delete(route('communities.courses.sections.destroy', [$community, $course, $section]))->assertNotFound();
        $this->delete(route('communities.courses.destroy', [$community, $course]))->assertNotFound();
        $this->assertDatabaseCount('courses', 1);
        $this->assertDatabaseCount('course_sections', 1);
        $this->assertDatabaseCount('lessons', 1);
        $rules = DB::table('information_schema.REFERENTIAL_CONSTRAINTS')->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())->whereIn('TABLE_NAME', ['courses', 'course_sections', 'lessons'])->pluck('DELETE_RULE');
        $this->assertCount(3, $rules);
        foreach ($rules as $rule) {
            $this->assertSame('RESTRICT', $rule);
        }
    }

    public function test_suspension_between_preliminary_gate_and_write_is_rechecked(): void
    {
        [$community, $course] = $this->hierarchy();
        $changed = false;
        Gate::after(function ($user, $ability, $result, $arguments) use ($community, &$changed): void {
            if (! $changed && $ability === 'update' && ($arguments[0] ?? null) instanceof Course) {
                $changed = true;
                $community->forceFill(['status' => 'SUSPENDED'])->save();
            }
        });
        $this->actingAs($community->creator)->put(route('communities.courses.update', [$community, $course]), ['title' => 'No write', 'status' => 'PUBLISHED'])->assertNotFound();
        $this->assertTrue($changed);
        $this->assertNotSame('No write', $course->fresh()->title);
    }

    public function test_reorder_rechecks_current_siblings_and_rejects_stale_or_invalid_payloads_atomically(): void
    {
        [$community, $course, $section, $lesson] = $this->hierarchy();
        $second = Lesson::factory()->create(['course_section_id' => $section->id, 'order' => 2]);
        $lesson->update(['order' => 1]);
        $foreign = Lesson::factory()->create();
        $url = route('communities.lessons.reorder', [$community, $course, $section]);
        $this->actingAs($community->creator);
        foreach ([[$lesson->id], [$second->id, $second->id], [$foreign->id, $lesson->id], ['bad', $lesson->id], []] as $order) {
            $this->post($url, ['order' => $order])->assertSessionHasErrors();
            $this->assertSame(1, $lesson->fresh()->order);
            $this->assertSame(2, $second->fresh()->order);
        }
        $changed = false;
        Gate::after(function ($user, $ability, $result, $arguments) use ($section, &$changed): void {
            if (! $changed && $ability === 'update' && ($arguments[0] ?? null) instanceof Course) {
                $changed = true;
                Lesson::factory()->create(['course_section_id' => $section->id, 'order' => 3]);
            }
        });
        $this->post($url, ['order' => [$second->id, $lesson->id]])->assertSessionHasErrors('order');
        $this->assertTrue($changed);
        $this->assertSame(1, $lesson->fresh()->order);
        $this->assertSame(2, $second->fresh()->order);
    }

    public function test_classroom_paginates_without_loading_threads_and_keeps_draft_counts_private(): void
    {
        [$community, $course, $section, $lesson] = $this->hierarchy();
        Lesson::factory()->draft()->create(['course_section_id' => $section->id, 'title' => 'Secret draft title']);
        $course->update(['order' => 1]);
        Course::factory()->count(12)->published()->create(['community_id' => $community->id, 'order' => 2]);
        Course::factory()->draft()->create(['community_id' => $community->id]);
        $membership = CommunityMembership::factory()->active()->create(['community_id' => $community->id]);
        $response = $this->actingAs($membership->user)->get(route('communities.classroom.index', $community))->assertOk()->assertDontSee('Secret draft title')->assertDontSee('Move up');
        $page = $response->viewData('courses');
        $this->assertSame(13, $page->total());
        $this->assertCount(12, $page->items());
        $this->assertSame(1, $page->first()->visible_lessons_count);
        foreach ($page as $item) {
            $this->assertFalse($item->relationLoaded('sections'));
        }
        $this->get(route('communities.classroom.index', $community).'?page=2')->assertOk();
    }

    public function test_malformed_flashed_fields_do_not_crash_the_validation_retry_page(): void
    {
        [$community, $course, $section, $lesson] = $this->hierarchy();
        $this->actingAs($community->creator);
        $url = route('communities.classroom.index', $community);
        $this->from($url)->post(route('communities.courses.store', $community), ['_form' => 'create-course', 'title' => ['bad'], 'description' => ['bad'], 'status' => 'DRAFT'])->assertSessionHasErrors(['title', 'description']);
        $this->get($url)->assertOk();
        $url = route('communities.courses.show', [$community, $course]);
        $this->from($url)->post(route('communities.lessons.store', [$community, $course, $section]), ['_form' => 'create-lesson-'.$section->id, 'title' => ['bad'], 'content' => ['bad'], 'video_url' => ['bad'], 'status' => 'DRAFT'])->assertSessionHasErrors(['title', 'content', 'video_url']);
        $this->get($url)->assertOk();
        $this->from($url)->put(route('communities.courses.sections.update', [$community, $course, $section]), ['_form' => 'edit-section-'.$section->id, 'title' => ['bad']])->assertSessionHasErrors('title');
        $this->get($url)->assertOk();
    }

    public function test_creator_ui_exposes_section_edit_reorder_and_provider_fallback_and_retains_invalid_form(): void
    {
        [$community, $course, $section, $lesson] = $this->hierarchy();
        $second = Lesson::factory()->create(['course_section_id' => $section->id]);
        $courseUrl = route('communities.courses.show', [$community, $course]);
        $this->actingAs($community->creator)->get($courseUrl)->assertOk()->assertSee('Edit Section')->assertSee('Move up')->assertSee('Move down')->assertDontSee('Delete Section');
        $this->get(route('communities.lessons.show', [$community, $course, $lesson]))->assertOk()->assertSee('Open on provider')->assertDontSee('Delete Lesson');
        $key = 'create-lesson-'.$section->id;
        $this->from($courseUrl)->post(route('communities.lessons.store', [$community, $course, $section]), ['_form' => $key, 'title' => 'Keep this title', 'content' => 'Keep this content', 'status' => 'PUBLISHED', 'video_url' => 'https://invalid.test/video'])->assertSessionHasErrors('video_url');
        $retry = $this->get($courseUrl)->assertOk()->assertSee('Keep this title')->assertSee('Keep this content');
        $this->assertMatchesRegularExpression('/<details\s+open\s*>/', $retry->getContent());
        $this->assertDatabaseCount('lessons', 2);
        $this->assertSame('PUBLISHED', $lesson->fresh()->status);
    }

    public function test_provider_fallback_uses_canonical_watch_page_instead_of_embed_url(): void
    {
        [$community, $course, $section, $lesson] = $this->hierarchy();
        $membership = CommunityMembership::factory()->active()->create(['community_id' => $community->id]);
        $this->actingAs($membership->user);
        $url = route('communities.lessons.show', [$community, $course, $lesson]);
        foreach ([
            'https://www.youtube.com/embed/dQw4w9WgXcQ' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://youtube.com/watch?v=dQw4w9WgXcQ&untrusted=value' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://player.vimeo.com/video/76979871' => 'https://vimeo.com/76979871',
            'https://vimeo.com/76979871' => 'https://vimeo.com/76979871',
        ] as $input => $watchUrl) {
            $lesson->update(['video_url' => $input]);
            $response = $this->get($url)->assertOk();
            $response->assertSee('href="'.$watchUrl.'"', false);
            $response->assertSee('src="'.VideoEmbedService::getEmbedUrl($input).'"', false);
            $response->assertSee('rel="noopener noreferrer"', false);
        }
        $lesson->update(['video_url' => 'https://youtube.com.attacker.test/watch?v=dQw4w9WgXcQ']);
        $this->get($url)->assertOk()->assertDontSee('Open on provider');
    }
}
