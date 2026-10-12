<?php

namespace Tests\Feature;

use App\Http\Controllers\LessonProgressController;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LessonProgressRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $membership = CommunityMembership::factory()->active()->create(['community_id' => $community->id]);
        $course = Course::factory()->create(['community_id' => $community->id, 'status' => 'PUBLISHED']);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create(['course_section_id' => $section->id, 'status' => 'PUBLISHED']);

        return [$community, $membership->user, $course, $section, $lesson, $membership];
    }

    public function test_retries_preserve_timestamps_and_database_rejects_duplicate_identity(): void
    {
        [$community, $member, $course, , $lesson] = $this->fixture();
        $url = route('communities.lessons.progress.update', [$community, $course, $lesson]);
        $this->actingAs($member)->postJson($url, ['completed' => true])->assertOk();
        $original = LessonProgress::sole();
        $this->travel(10)->seconds();
        $this->postJson($url, ['completed' => true])->assertOk();
        $this->assertSame($original->completed_at->toISOString(), $original->fresh()->completed_at->toISOString());
        $this->assertSame($original->updated_at->toISOString(), $original->fresh()->updated_at->toISOString());
        $this->postJson($url, ['completed' => false])->assertOk();
        $incomplete = $original->fresh();
        $this->assertNull($incomplete->completed_at);
        $this->travel(10)->seconds();
        $this->postJson($url, ['completed' => false])->assertOk();
        $this->assertSame($incomplete->updated_at->toISOString(), $incomplete->fresh()->updated_at->toISOString());
        $this->assertDatabaseCount('lesson_progresses', 1);

        $this->expectException(UniqueConstraintViolationException::class);
        DB::table('lesson_progresses')->insert(['user_id' => $member->id, 'lesson_id' => $lesson->id, 'completed' => true]);
    }

    public function test_invalid_boolean_has_feedback_and_cannot_mutate_progress(): void
    {
        [$community, $member, $course, , $lesson] = $this->fixture();
        $url = route('communities.lessons.progress.update', [$community, $course, $lesson]);
        $this->actingAs($member);
        foreach ([[], ['completed' => 'true'], ['completed' => 2], ['completed' => []], ['completed' => null]] as $payload) {
            $this->postJson($url, $payload)->assertUnprocessable()->assertJsonValidationErrors('completed');
        }
        $show = route('communities.lessons.show', [$community, $course, $lesson]);
        $this->from($show)->post($url, ['completed' => 'invalid'])->assertRedirect($show)->assertSessionHasErrors('completed');
        $this->get($show)->assertOk()->assertSee('role="alert"', false);
        $this->assertDatabaseCount('lesson_progresses', 0);
    }

    public function test_csrf_is_required_when_the_usual_testing_bypass_is_disabled(): void
    {
        [$community, $member, $course, , $lesson] = $this->fixture();
        $this->app->bind(ValidateCsrfToken::class, fn ($app) => new class($app, $app['encrypter']) extends ValidateCsrfToken
        {
            protected function runningUnitTests()
            {
                return false;
            }
        });
        $url = route('communities.lessons.progress.update', [$community, $course, $lesson]);
        $this->actingAs($member)->withSession(['_token' => 'synthetic-progress-test-token']);
        $this->postJson($url, ['completed' => true])->assertStatus(419);
        $this->assertDatabaseCount('lesson_progresses', 0);
        $this->postJson($url, ['completed' => true, '_token' => 'synthetic-progress-test-token'])->assertOk();
        $this->assertDatabaseCount('lesson_progresses', 1);
    }

    public function test_course_and_lesson_unpublish_block_reads_and_writes_but_keep_progress(): void
    {
        [$community, $member, $course, , $lesson] = $this->fixture();
        $show = route('communities.lessons.show', [$community, $course, $lesson]);
        $update = route('communities.lessons.progress.update', [$community, $course, $lesson]);
        $this->actingAs($member)->postJson($update, ['completed' => true])->assertOk();
        $original = LessonProgress::sole();
        foreach ([$lesson, $course] as $content) {
            $content->update(['status' => 'DRAFT']);
            $this->get($show)->assertNotFound();
            $this->postJson($update, ['completed' => false])->assertNotFound();
            $this->assertTrue($original->fresh()->completed);
            $this->assertSame($original->completed_at->toISOString(), $original->fresh()->completed_at->toISOString());
            $this->assertSame(0, $course->fresh()->progressSummaryFor($member)['completed']);
            $content->update(['status' => 'PUBLISHED']);
            $this->get($show)->assertOk()->assertSee('Mark Incomplete');
            $this->assertSame(100, $course->fresh()->progressSummaryFor($member)['percentage']);
        }
    }

    public function test_membership_revocation_blocks_reads_and_writes_without_erasing_progress(): void
    {
        [$community, $member, $course, , $lesson, $membership] = $this->fixture();
        $url = route('communities.lessons.progress.update', [$community, $course, $lesson]);
        $this->actingAs($member)->postJson($url, ['completed' => true])->assertOk();
        foreach (['PENDING_PAYMENT', 'SUSPENDED', 'REMOVED', 'LEFT'] as $status) {
            $membership->forceFill(['status' => $status])->save();
            $this->get(route('communities.lessons.show', [$community, $course, $lesson]))->assertNotFound();
            $this->postJson($url, ['completed' => false])->assertNotFound();
            $this->assertTrue(LessonProgress::sole()->completed);
        }
        $membership->forceFill(['status' => 'ACTIVE'])->save();
        $community->forceFill(['status' => 'ARCHIVED'])->save();
        $this->get(route('communities.lessons.show', [$community, $course, $lesson]))->assertNotFound();
        $this->postJson($url, ['completed' => false])->assertNotFound();
        $this->assertTrue(LessonProgress::sole()->completed);
    }

    public function test_stale_route_bound_lesson_is_reauthorized_at_mutation(): void
    {
        [$community, $member, $course, , $lesson] = $this->fixture();
        $lesson->load('section.course.community');
        $this->actingAs($member);
        DB::table('lessons')->where('id', $lesson->id)->update(['status' => 'DRAFT']);
        $request = Request::create('/progress', 'POST', ['completed' => true]);
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn () => $member);
        try {
            app(LessonProgressController::class)->update($request, $community, $course, $lesson);
            $this->fail('A stale published snapshot must not grant progress writes.');
        } catch (AuthorizationException $exception) {
            $this->assertSame(404, $exception->status());
        }
        $this->assertDatabaseCount('lesson_progresses', 0);
    }

    public function test_summary_is_personal_eligible_and_rendered_consistently_on_all_three_pages(): void
    {
        [$community, $member, $course, $section, $lesson] = $this->fixture();
        $other = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;
        Lesson::factory()->create(['course_section_id' => $section->id, 'status' => 'PUBLISHED']);
        $draft = Lesson::factory()->create(['course_section_id' => $section->id, 'status' => 'DRAFT']);
        foreach ([$lesson, $draft] as $completed) {
            LessonProgress::create(['user_id' => $member->id, 'lesson_id' => $completed->id, 'completed' => true]);
        }
        $this->assertSame(['total' => 2, 'completed' => 1, 'percentage' => 50], $course->progressSummaryFor($member));
        $this->assertSame(['total' => 2, 'completed' => 0, 'percentage' => 0], $course->progressSummaryFor($other));
        $this->actingAs($member)->get(route('communities.courses.show', [$community, $course]))->assertOk()->assertSee('1 of 2 completed (50%)');
        $this->get(route('communities.classroom.index', $community))->assertOk()->assertSee('Your Progress: 50%');
        $this->get(route('communities.lessons.show', [$community, $course, $lesson]))->assertOk()->assertSee('Mark Incomplete')->assertSee('Completed');
        $this->actingAs($other)->get(route('communities.courses.show', [$community, $course]))->assertOk()->assertSee('0 of 2 completed (0%)');
        $empty = Course::factory()->create(['community_id' => $community->id, 'status' => 'PUBLISHED']);
        $this->get(route('communities.courses.show', [$community, $empty]))->assertOk()->assertSee('0 of 0 completed (0%)');
    }

    public function test_progress_cards_keep_pagination_without_loading_lesson_bodies_or_per_course_count_queries(): void
    {
        [$community, $member, , , $lesson] = $this->fixture();
        $lesson->update(['content' => 'Never eager load this lesson body on a course listing']);
        Course::factory()->count(12)->create(['community_id' => $community->id, 'status' => 'PUBLISHED']);
        DB::enableQueryLog();
        try {
            $this->actingAs($member)->get(route('communities.classroom.index', $community))
                ->assertOk()->assertViewHas('courses', fn ($courses) => $courses->count() === 12 && $courses->total() === 13)
                ->assertDontSee($lesson->content);
            $progressQueries = collect(DB::getQueryLog())->filter(fn ($query) => str_contains($query['query'], 'lesson_progresses'));
            $this->assertCount(1, $progressQueries, 'Card counts must share one paginated query, not query once per card.');
        } finally {
            DB::disableQueryLog();
            DB::flushQueryLog();
        }
    }

    public function test_wrong_course_foreign_tenant_and_outsider_cannot_read_or_write(): void
    {
        [$community, $member, $course, , $lesson] = $this->fixture();
        $otherCourse = Course::factory()->create(['community_id' => $community->id, 'status' => 'PUBLISHED']);
        $this->actingAs($member)->get(route('communities.lessons.show', [$community, $otherCourse, $lesson]))->assertNotFound();
        $this->postJson(route('communities.lessons.progress.update', [$community, $otherCourse, $lesson]), ['completed' => true])->assertNotFound();
        [$foreign, , $foreignCourse, , $foreignLesson] = $this->fixture();
        $this->get(route('communities.lessons.show', [$foreign, $foreignCourse, $foreignLesson]))->assertNotFound();
        $this->postJson(route('communities.lessons.progress.update', [$community, $course, $foreignLesson]), ['completed' => true])->assertNotFound();
        $outsider = User::factory()->create();
        $this->actingAs($outsider)->postJson(route('communities.lessons.progress.update', [$community, $course, $lesson]), ['completed' => true])->assertNotFound();
        $this->actingAs($community->creator)->get(route('communities.lessons.show', [$community, $course, $lesson]))->assertOk()->assertDontSee('Mark Complete');
        $this->assertDatabaseCount('lesson_progresses', 0);
    }
}
