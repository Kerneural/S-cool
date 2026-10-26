<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningReaderTest extends TestCase
{
    use RefreshDatabase;

    /** Two ordered sections; the current lesson lives in the second one. */
    private function fixture(): array
    {
        $community = Community::factory()->create();
        $member = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;
        $course = Course::factory()->published()->create(['community_id' => $community->id]);
        $first = CourseSection::factory()->create(['course_id' => $course->id, 'order' => 1, 'title' => 'Must Have Resources']);
        $second = CourseSection::factory()->create(['course_id' => $course->id, 'order' => 2, 'title' => 'Module Two']);
        $a = Lesson::factory()->published()->create(['course_section_id' => $first->id, 'order' => 1, 'title' => 'Resource A']);
        $b = Lesson::factory()->published()->create(['course_section_id' => $second->id, 'order' => 1, 'title' => 'Lesson B']);
        $c = Lesson::factory()->published()->create(['course_section_id' => $second->id, 'order' => 2, 'title' => 'Lesson C']);
        $draft = Lesson::factory()->create(['course_section_id' => $second->id, 'order' => 3, 'status' => 'DRAFT', 'title' => 'Hidden draft title']);

        return [$community, $member, $course, $a, $b, $c, $draft];
    }

    public function test_lesson_markdown_renders_semantically_and_unsafe_markup_never_executes(): void
    {
        [$community, $member, $course, , $b] = $this->fixture();
        $b->update(['content' => "## What you need\n\n- **Terminal**\n\n<script>alert('x')</script>\n\n[bad](javascript:alert(1)) <img src=x onerror=alert(1)>"]);

        $response = $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course, $b]))->assertOk();

        $response->assertSee('<article class="lesson-prose"><h2>What you need</h2>', false)
            ->assertSee('<li><strong>Terminal</strong></li>', false)
            ->assertDontSee("<script>alert('x')</script>", false)
            ->assertDontSee('javascript:alert', false)
            ->assertDontSee('onerror=', false)
            ->assertDontSee('## What you need');
        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
        $this->assertSame("## What you need\n\n- **Terminal**\n\n<script>alert('x')</script>\n\n[bad](javascript:alert(1)) <img src=x onerror=alert(1)>", $b->fresh()->content, 'Stored source is unchanged');
    }

    public function test_outline_opens_only_the_current_section_and_hides_drafts_from_members(): void
    {
        [$community, $member, $course, $a, $b, , $draft] = $this->fixture();

        $html = $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course, $b]))->assertOk()->getContent();

        // Server-rendered `open` (not the Alpine `:open` binding) decides the no-JS and first-paint state.
        $openStates = [];
        foreach (array_slice(explode('<details class="outline-section"', $html), 1) as $chunk) {
            [$attributes, $summary] = explode('<summary', $chunk, 2);
            preg_match('/<span class="min-w-0 flex-1 break-words">([^<]+)<\/span>/', $summary, $title);
            $openStates[trim($title[1])] = (bool) preg_match('/\sopen\s/', $attributes);
        }
        $this->assertSame(['Must Have Resources' => false, 'Module Two' => true], $openStates);
        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertStringNotContainsString('Hidden draft title', $html, 'Search data and rows never include member-hidden drafts');
        $this->assertStringContainsString(route('communities.lessons.show', [$community, $course, $a]), $html);
    }

    public function test_one_completion_form_and_one_navigation_region_and_last_lesson_returns_to_course(): void
    {
        [$community, $member, $course, , $b, $c] = $this->fixture();

        $html = $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course, $b]))->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, route('communities.lessons.progress.update', [$community, $course, $b]).'" @submit.prevent'));
        $this->assertSame(1, substr_count($html, 'aria-label="Lesson navigation"'));
        $this->assertSame(2, substr_count($html, 'href="'.route('communities.lessons.show', [$community, $course, $c]).'"'), 'Next lesson: one outline row plus one Next link');

        $this->get(route('communities.lessons.show', [$community, $course, $c]))->assertOk()
            ->assertSee('Return to course')
            ->assertSee('href="'.route('communities.courses.show', [$community, $course]).'"', false);
    }

    public function test_course_entry_action_is_honest_about_start_continue_and_review(): void
    {
        [$community, $member, $course, $a, $b, $c] = $this->fixture();
        $url = route('communities.courses.show', [$community, $course]);
        $lessonUrl = fn ($lesson) => route('communities.lessons.show', [$community, $course, $lesson]);

        $this->actingAs($member)->get($url)->assertOk()
            ->assertSeeInOrder([$course->title, 'Start course', 'Course content', 'Must Have Resources'])
            ->assertSee('href="'.$lessonUrl($a).'"', false)
            ->assertSee('0 of 3 completed (0%)');

        LessonProgress::create(['user_id' => $member->id, 'lesson_id' => $a->id, 'completed' => true, 'completed_at' => now()]);
        $this->get($url)->assertOk()->assertSee('Continue course')->assertSee('Next:')->assertSee('Lesson B');

        foreach ([$b, $c] as $lesson) {
            LessonProgress::create(['user_id' => $member->id, 'lesson_id' => $lesson->id, 'completed' => true, 'completed_at' => now()]);
        }
        $this->get($url)->assertOk()->assertSee('Review course')->assertSee('3 of 3 completed (100%)');
    }

    public function test_course_without_lessons_has_no_broken_entry_action(): void
    {
        $community = Community::factory()->create();
        $member = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;
        $course = Course::factory()->published()->create(['community_id' => $community->id]);

        $this->actingAs($member)->get(route('communities.courses.show', [$community, $course]))->assertOk()
            ->assertSee('No lessons are available in this course yet.')
            ->assertDontSee('Start course');
    }
}
