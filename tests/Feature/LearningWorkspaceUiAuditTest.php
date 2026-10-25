<?php

namespace Tests\Feature;

use App\Livewire\DiscussionViewer;
use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class LearningWorkspaceUiAuditTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $community = Community::factory()->create();
        $membership = CommunityMembership::factory()->active()->create(['community_id' => $community->id]);
        $course = Course::factory()->published()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->published()->create(['course_section_id' => $section->id]);

        return [$community, $membership->user, $course, $lesson];
    }

    public function test_alpine_only_study_and_account_pages_and_livewire_feed_load_one_shared_runtime(): void
    {
        [$community, $member, $course, $lesson] = $this->fixture();
        $this->actingAs($member);

        foreach ([
            route('communities.lessons.show', [$community, $course, $lesson]),
            route('communities.courses.show', [$community, $course]),
            route('profile.edit'),
            route('communities.posts.index', $community),
        ] as $url) {
            $response = $this->get($url)->assertOk();
            $response->assertSee('/livewire/livewire', false);
            $this->assertSame(1, substr_count($response->getContent(), 'data-update-uri='), $url);
        }
    }

    public function test_discussion_mutations_publish_authorized_feed_metadata_without_a_reload(): void
    {
        Queue::fake();
        [$community, $member] = $this->fixture();
        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $member->id]);
        $this->actingAs($member);
        $viewer = Livewire::test(DiscussionViewer::class, ['community' => $community, 'postId' => $post->id])
            ->set('newCommentBody', '<script>Safe new reply</script>')
            ->call('addComment')
            ->assertDispatched('discussion-comments-updated', function ($name, $params) use ($community, $post): bool {
                return $params['communityId'] === $community->id && $params['postId'] === $post->id
                    && $params['count'] === 1 && $params['latestComment']['body'] === '<script>Safe new reply</script>';
            });
        $comment = $post->comments()->firstOrFail();
        $viewer->call('editComment', $comment->id)->set('editingCommentBody', 'Revised latest reply')
            ->call('updateComment')
            ->assertDispatched('discussion-comments-updated', fn ($name, $params) => $params['count'] === 1 && $params['latestComment']['body'] === 'Revised latest reply');
        $viewer->call('deleteComment', $comment->id)
            ->assertDispatched('discussion-comments-updated', fn ($name, $params) => $params['count'] === 0 && $params['latestComment'] === null);

        $community->memberships()->where('user_id', $member->id)->update(['status' => 'LEFT']);
        $viewer->set('newCommentBody', 'Access was revoked')->call('addComment')
            ->assertNotDispatched('discussion-comments-updated');
        $this->assertSame(0, $post->comments()->count());
    }

    public function test_direct_composer_rejects_malformed_context_without_a_render_error(): void
    {
        [$community, $member] = $this->fixture();
        $this->actingAs($member)->getJson(route('communities.posts.create', [$community, 'course_id' => ['bad']]))
            ->assertUnprocessable()->assertJsonValidationErrors('course_id');
        $this->getJson(route('communities.posts.create', [$community, 'lesson_id' => 'bad']))
            ->assertUnprocessable()->assertJsonValidationErrors('lesson_id');
    }

    public function test_direct_composer_preserves_context_and_values_after_failed_post(): void
    {
        [$community, $member, $course, $lesson] = $this->fixture();
        $url = route('communities.posts.create', [$community, 'course_id' => $course->id, 'lesson_id' => $lesson->id]);
        $this->actingAs($member)->from($url)->post(route('communities.posts.store', $community), [
            'title' => 'Retained question', 'body' => '', 'course_id' => $course->id, 'lesson_id' => $lesson->id,
        ])->assertRedirect($url)->assertSessionHasErrors('body');
        $this->get($url)->assertOk()->assertSee('Retained question')
            ->assertSee('name="course_id" value="'.$course->id.'"', false)
            ->assertSee('name="lesson_id" value="'.$lesson->id.'"', false)
            ->assertSee('The body field is required.');
    }

    public function test_feed_batches_one_latest_visible_comment_per_post_and_uses_private_avatar_routes(): void
    {
        [$community, $member] = $this->fixture();
        $member->update(['avatar_path' => 'avatars/synthetic-private-avatar.png']);
        $posts = Post::factory()->count(4)->create(['community_id' => $community->id, 'user_id' => $member->id]);
        foreach ($posts as $post) {
            Comment::factory()->create(['post_id' => $post->id, 'user_id' => $member->id, 'body' => 'Older reply', 'created_at' => now()->subMinutes(2)]);
            Comment::factory()->create(['post_id' => $post->id, 'user_id' => $member->id, 'body' => '<script>Latest safe reply</script>', 'created_at' => now()->subMinute()]);
            Comment::factory()->create(['post_id' => $post->id, 'user_id' => $member->id, 'body' => 'Deleted latest reply', 'created_at' => now()])->delete();
        }
        $foreign = Post::factory()->create();
        Comment::factory()->create(['post_id' => $foreign->id, 'body' => 'Foreign private reply']);
        $commentQueries = [];
        DB::listen(function ($query) use (&$commentQueries): void {
            if (str_contains($query->sql, 'from `comments`')) {
                $commentQueries[] = $query->sql;
            }
        });

        $response = $this->actingAs($member)->get(route('communities.posts.index', $community))->assertOk()
            ->assertSee(route('communities.directory.avatar', [$community, $member]), false)
            ->assertDontSee('avatars/synthetic-private-avatar.png')
            ->assertSee('<script>Latest safe reply</script>')->assertDontSee('<script>Latest safe reply</script>', false)
            ->assertDontSee('Older reply')->assertDontSee('Deleted latest reply')->assertDontSee('Foreign private reply');
        $response->assertViewHas('posts', function ($rendered): bool {
            return $rendered->count() === 4 && $rendered->every(fn ($post) => ! $post->relationLoaded('comments') && $post->latestVisibleComment?->relationLoaded('author'));
        });
        $this->assertLessThanOrEqual(3, count($commentQueries));
        // The rail keeps one learning entry and no duplicate About card of classroom links.
        $this->assertSame(1, substr_count($response->getContent(), 'Learning Focus'));
        $this->assertSame(0, substr_count($response->getContent(), 'Browse Classroom Courses'));
    }

    public function test_composer_has_a_real_form_fallback_and_retains_course_filter_in_toolbar(): void
    {
        [$community, $member, $course] = $this->fixture();
        $response = $this->actingAs($member)->get(route('communities.posts.index', [$community, 'course_id' => $course->id]))->assertOk();
        $response->assertSee(route('communities.posts.create', [$community, 'course_id' => $course->id]))
            ->assertSee(route('communities.posts.index', [$community, 'sort' => 'most_commented', 'course_id' => $course->id]))
            ->assertSee('aria-labelledby="composer-modal-title"', false)
            ->assertSee('name="course_id" :value="courseId" value="'.$course->id.'"', false);
    }

    public function test_lesson_server_fallback_exposes_progress_and_outline_and_preserves_practical_checkbox(): void
    {
        [$community, $member, $course, $lesson] = $this->fixture();
        $lesson->update(['is_practical' => true, 'skill_label' => 'Safe configuration']);
        $url = route('communities.lessons.show', [$community, $course, $lesson]);
        $this->actingAs($member)->get($url)->assertOk()->assertSee('Incomplete')
            // Hidden only by Alpine on narrow screens; without JS the outline stays rendered.
            ->assertSee('<div id="lesson-outline-panel" :class="{ \'max-lg:hidden\': !panelOpen }" class="lesson-rail__panel">', false)
            ->assertSee('value="0" :value="percentage"', false);
        $this->post(route('communities.lessons.progress.update', [$community, $course, $lesson]), ['completed' => 1])->assertRedirect($url);
        $this->get($url)->assertOk()->assertSee('1 / 1 · 100%')->assertSee('Mark Incomplete');
        $creatorResponse = $this->actingAs($community->creator)->get($url)->assertOk();
        $creatorResponse->assertSee('value="1" checked', false)->assertSee('value="Safe configuration"', false);
    }
}
