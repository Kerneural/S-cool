<?php

namespace Tests\Feature;

use App\Actions\CommunityContent\FeedMutation;
use App\Livewire\DiscussionViewer;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContextualDiscussionTest extends TestCase
{
    use RefreshDatabase;

    private function setupCommunityAndMember(): array
    {
        $community = Community::factory()->create();
        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $course = Course::factory()->published()->create([
            'community_id' => $community->id,
            'title' => 'Vue 3 Architecture',
            'access_mode' => Course::ACCESS_MODE_INCLUDED,
        ]);
        $section = CourseSection::factory()->create(['course_id' => $course->id, 'title' => 'Composition API']);
        $lesson = Lesson::factory()->published()->create([
            'course_section_id' => $section->id,
            'title' => 'Reactivity Fundamentals',
        ]);

        return [$community, $member, $course, $lesson];
    }

    public function test_active_member_can_create_post_with_valid_course_and_lesson_learning_context(): void
    {
        [$community, $member, $course, $lesson] = $this->setupCommunityAndMember();
        $this->actingAs($member);

        $response = $this->post(route('communities.posts.store', $community), [
            'title' => 'Question about ref vs reactive in lesson',
            'body' => 'Can someone explain when ref should be preferred over reactive in this pattern?',
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
        ]);

        $response->assertRedirect(route('communities.posts.index', $community));
        $this->assertDatabaseHas('posts', [
            'community_id' => $community->id,
            'user_id' => $member->id,
            'title' => 'Question about ref vs reactive in lesson',
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
        ]);
    }

    public function test_cannot_create_post_with_lesson_id_without_course_id(): void
    {
        [$community, $member, $course, $lesson] = $this->setupCommunityAndMember();
        $this->actingAs($member);

        $response = $this->post(route('communities.posts.store', $community), [
            'title' => 'Invalid Context Post',
            'body' => 'Post with lesson only without course',
            'course_id' => null,
            'lesson_id' => $lesson->id,
        ]);

        $response->assertSessionHasErrors(['lesson_id']);
        $this->assertDatabaseMissing('posts', ['title' => 'Invalid Context Post']);
    }

    public function test_cannot_create_post_with_course_from_different_community(): void
    {
        [$community, $member] = $this->setupCommunityAndMember();
        $otherCommunity = Community::factory()->create();
        $foreignCourse = Course::factory()->published()->create(['community_id' => $otherCommunity->id]);

        $this->actingAs($member);

        $response = $this->post(route('communities.posts.store', $community), [
            'title' => 'Cross tenant attack attempt',
            'body' => 'Trying to link a course from other community',
            'course_id' => $foreignCourse->id,
        ]);

        $response->assertSessionHasErrors(['course_id']);
        $this->assertDatabaseMissing('posts', ['title' => 'Cross tenant attack attempt']);
    }

    public function test_cannot_create_post_with_lesson_belonging_to_different_course(): void
    {
        [$community, $member, $course] = $this->setupCommunityAndMember();
        $anotherCourse = Course::factory()->published()->create(['community_id' => $community->id]);
        $anotherSection = CourseSection::factory()->create(['course_id' => $anotherCourse->id]);
        $mismatchedLesson = Lesson::factory()->published()->create(['course_section_id' => $anotherSection->id]);

        $this->actingAs($member);

        $response = $this->post(route('communities.posts.store', $community), [
            'title' => 'Mismatched lesson attempt',
            'body' => 'Testing cross-course lesson association rejection',
            'course_id' => $course->id,
            'lesson_id' => $mismatchedLesson->id,
        ]);

        $response->assertSessionHasErrors(['lesson_id']);
        $this->assertDatabaseMissing('posts', ['title' => 'Mismatched lesson attempt']);
    }

    public function test_cannot_create_post_with_unentitled_paid_course_context(): void
    {
        $community = Community::factory()->create();
        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $paidCourse = Course::factory()->published()->create([
            'community_id' => $community->id,
            'access_mode' => Course::ACCESS_MODE_ONE_TIME,
            'price' => 750000,
        ]);

        $this->actingAs($member);

        $response = $this->post(route('communities.posts.store', $community), [
            'title' => 'Unentitled course post',
            'body' => 'Trying to post on a paid course without purchase',
            'course_id' => $paidCourse->id,
        ]);

        $response->assertSessionHasErrors(['course_id']);
        $this->assertDatabaseMissing('posts', ['title' => 'Unentitled course post']);
    }

    public function test_post_feed_and_show_render_context_breadcrumb_when_accessible(): void
    {
        [$community, $member, $course, $lesson] = $this->setupCommunityAndMember();

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'title' => 'Scoped Question Title',
            'body' => 'Post body content',
        ]);

        $this->actingAs($member);

        // Feed view
        $feedResponse = $this->get(route('communities.posts.index', $community));
        $feedResponse->assertOk();
        $feedResponse->assertSee($course->title);
        $feedResponse->assertSee($lesson->title);
        $feedResponse->assertDontSee('Learning context unavailable');

        // Show view
        $showResponse = $this->get(route('communities.posts.show', [$community, $post]));
        $showResponse->assertOk();
        $showResponse->assertSee($course->title);
        $showResponse->assertSee($lesson->title);
        $showResponse->assertDontSee('Learning context unavailable');
    }

    public function test_post_feed_and_show_render_fallback_badge_when_context_is_inaccessible_without_leaking_title(): void
    {
        [$community, $member] = $this->setupCommunityAndMember();

        // Create paid course that this member is NOT entitled to
        $secretCourse = Course::factory()->published()->create([
            'community_id' => $community->id,
            'access_mode' => Course::ACCESS_MODE_ONE_TIME,
            'price' => 1000000,
            'title' => 'Top Secret Masterclass Title',
        ]);
        $sec = CourseSection::factory()->create(['course_id' => $secretCourse->id]);
        $secretLesson = Lesson::factory()->published()->create([
            'course_section_id' => $sec->id,
            'title' => 'Top Secret Proprietary Lesson',
        ]);

        // Post created by creator with the secret course context
        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $community->creator_id,
            'course_id' => $secretCourse->id,
            'lesson_id' => $secretLesson->id,
            'title' => 'Discussion about secret course',
            'body' => 'Public discussion body',
        ]);

        // Member views feed and post show
        $this->actingAs($member);

        $feedResponse = $this->get(route('communities.posts.index', $community));
        $feedResponse->assertOk();
        $feedResponse->assertSee('Learning context unavailable');
        $feedResponse->assertDontSee('Top Secret Masterclass Title');
        $feedResponse->assertDontSee('Top Secret Proprietary Lesson');

        $showResponse = $this->get(route('communities.posts.show', [$community, $post]));
        $showResponse->assertOk();
        $showResponse->assertSee('Learning context unavailable');
        $showResponse->assertDontSee('Top Secret Masterclass Title');
        $showResponse->assertDontSee('Top Secret Proprietary Lesson');
    }

    public function test_posts_feed_can_be_filtered_by_course_id(): void
    {
        [$community, $member, $course1] = $this->setupCommunityAndMember();
        $course2 = Course::factory()->published()->create(['community_id' => $community->id]);

        $post1 = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'course_id' => $course1->id,
            'title' => 'Post about Course 1',
        ]);
        $post2 = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'course_id' => $course2->id,
            'title' => 'Post about Course 2',
        ]);

        $this->actingAs($member);

        $filteredResponse = $this->get(route('communities.posts.index', [$community, 'course_id' => $course1->id]));
        $filteredResponse->assertOk();
        $filteredResponse->assertSee('Post about Course 1');
        $filteredResponse->assertDontSee('Post about Course 2');
        $filteredResponse->assertSee('Filtering discussions by Course #'.$course1->id);
    }

    public function test_livewire_discussion_viewer_displays_context_or_safe_fallback(): void
    {
        [$community, $member, $course, $lesson] = $this->setupCommunityAndMember();

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'title' => 'Livewire Context Test Post',
        ]);

        $this->actingAs($member);

        Livewire::test(DiscussionViewer::class, ['community' => $community, 'postId' => $post->id])
            ->assertSet('hasLearningContext', true)
            ->assertSet('isLearningContextAccessible', true)
            ->assertSet('courseTitle', $course->title)
            ->assertSet('lessonTitle', $lesson->title)
            ->assertSee($course->title)
            ->assertSee($lesson->title);
    }

    public function test_open_viewer_reauthorizes_context_after_unpublishing(): void
    {
        [$community, $member, $course, $lesson] = $this->setupCommunityAndMember();
        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'course_id' => $course->id, 'lesson_id' => $lesson->id]);
        $viewer = Livewire::actingAs($member)->test(DiscussionViewer::class, ['community' => $community, 'postId' => $post->id]);
        $course->update(['status' => 'DRAFT']);
        $viewer->call('$refresh')->assertSet('isLearningContextAccessible', false)
            ->assertSet('courseTitle', null)->assertSet('lessonTitle', null)
            ->assertSee('Learning context unavailable')->assertDontSee($course->title)->assertDontSee($lesson->title);
    }

    public function test_context_is_rechecked_inside_the_feed_transaction(): void
    {
        [$community, $member, $course, $lesson] = $this->setupCommunityAndMember();
        $this->mock(FeedMutation::class, function ($mock) use ($course): void {
            $mock->shouldReceive('run')->once()->andReturnUsing(function ($user, $tenant, $postId, $commentId, $mutate) use ($course) {
                $course->update(['status' => 'DRAFT']);

                return (new FeedMutation)->run($user, $tenant, $postId, $commentId, $mutate);
            });
        });
        $this->actingAs($member)->post(route('communities.posts.store', $community), [
            'title' => 'Stale course context', 'body' => 'Do not attach after unpublication.',
            'course_id' => $course->id, 'lesson_id' => $lesson->id,
        ])->assertSessionHasErrors('course_id');
        $this->assertDatabaseMissing('posts', ['title' => 'Stale course context']);
    }

    public function test_edit_rechecks_retained_context_and_allows_explicit_detachment(): void
    {
        [$community, $member, $course, $lesson] = $this->setupCommunityAndMember();
        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'course_id' => $course->id, 'lesson_id' => $lesson->id]);
        $course->update(['status' => 'DRAFT']);
        $this->actingAs($member)->put(route('communities.posts.update', [$community, $post]), [
            'title' => 'Changed question', 'body' => 'Edited body',
        ])->assertSessionHasErrors('course_id');
        $this->get(route('communities.posts.edit', [$community, $post]))
            ->assertOk()->assertSee('Remove learning context');
        $this->put(route('communities.posts.update', [$community, $post]), [
            'title' => 'Changed question', 'body' => 'Edited body', 'remove_learning_context' => true,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNull($post->fresh()->course_id);
        $this->assertNull($post->fresh()->lesson_id);
    }

    public function test_malformed_composer_query_is_rejected_without_rendering_alpine_input(): void
    {
        [$community, $member] = $this->setupCommunityAndMember();
        foreach ([['course_id' => ['1']], ['course_id' => "';window.bad=1;//"], ['lesson_id' => ['1']]] as $query) {
            $this->actingAs($member)->getJson(route('communities.posts.index', [$community, ...$query]))->assertUnprocessable();
        }
    }
}
