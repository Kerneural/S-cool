<?php

namespace Tests\Feature;

use App\Livewire\DiscussionViewer;
use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseEntitlement;
use App\Models\CourseSection;
use App\Models\HelpfulAnswerAction;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\User;
use App\Services\HelpfulAnswerService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class HelpfulAnswerAuditRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $member = User::factory()->create();
        $membership = CommunityMembership::factory()->create([
            'community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE',
        ]);
        $course = Course::factory()->included()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create(['course_section_id' => $section->id, 'status' => 'PUBLISHED']);
        $post = Post::factory()->create([
            'community_id' => $community->id, 'user_id' => $creator->id,
            'course_id' => $course->id, 'lesson_id' => $lesson->id,
        ]);
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $member->id]);

        return compact('creator', 'community', 'member', 'membership', 'course', 'section', 'lesson', 'post', 'comment');
    }

    public static function staleStates(): array
    {
        return array_map(fn ($state) => [$state], [
            'inactive community', 'changed creator', 'draft course', 'draft lesson',
            'wrong course tenant', 'wrong lesson ancestry', 'expired membership',
            'suspended membership', 'missing membership', 'locked course',
            'removed context', 'deleted post', 'deleted comment', 'wrong comment parent', 'changed author to creator',
        ]);
    }

    #[DataProvider('staleStates')]
    public function test_acceptance_rechecks_fresh_access_and_hierarchy_instead_of_stale_models(string $state): void
    {
        extract($this->fixture());
        // Preload snapshots so the service cannot accidentally trust cached relationships.
        $post->load('community', 'course', 'lesson.section');
        $comment->load('author');
        match ($state) {
            'inactive community' => Community::whereKey($community->id)->update(['status' => 'SUSPENDED']),
            'changed creator' => Community::whereKey($community->id)->update(['creator_id' => $member->id]),
            'draft course' => Course::whereKey($course->id)->update(['status' => 'DRAFT']),
            'draft lesson' => Lesson::whereKey($lesson->id)->update(['status' => 'DRAFT']),
            'wrong course tenant' => Course::whereKey($course->id)->update(['community_id' => Community::factory()->create()->id]),
            'wrong lesson ancestry' => CourseSection::whereKey($section->id)->update(['course_id' => Course::factory()->create()->id]),
            'expired membership' => CommunityMembership::whereKey($membership->id)->update([
                'access_kind' => CommunityMembership::ACCESS_TERM, 'expires_at' => now('UTC')->subMinute(),
            ]),
            'suspended membership' => CommunityMembership::whereKey($membership->id)->update(['status' => 'SUSPENDED']),
            'missing membership' => CommunityMembership::whereKey($membership->id)->delete(),
            'locked course' => Course::whereKey($course->id)->update(['access_mode' => Course::ACCESS_MODE_ONE_TIME, 'price' => 100000]),
            'removed context' => Post::whereKey($post->id)->update(['course_id' => null, 'lesson_id' => null]),
            'deleted post' => Post::whereKey($post->id)->delete(),
            'deleted comment' => Comment::whereKey($comment->id)->delete(),
            'wrong comment parent' => Comment::whereKey($comment->id)->update(['post_id' => Post::factory()->create(['community_id' => $community->id])->id]),
            'changed author to creator' => Comment::whereKey($comment->id)->update(['user_id' => $creator->id]),
        };

        try {
            app(HelpfulAnswerService::class)->accept($community, $post, $comment, $creator);
            $this->fail('Stale eligibility must not authorize a helpful-answer mutation.');
        } catch (HttpException|ModelNotFoundException $exception) {
            $this->assertContains($exception instanceof HttpException ? $exception->getStatusCode() : 404, [403, 404, 422]);
        }
        $this->assertDatabaseCount('helpful_answer_actions', 0);
        $this->assertNull(Post::withTrashed()->findOrFail($post->id)->accepted_comment_id);
        $this->assertFalse(Comment::withTrashed()->findOrFail($comment->id)->is_helpful);
    }

    public function test_stale_revoke_conflicts_without_claiming_the_replacement_was_revoked(): void
    {
        extract($this->fixture());
        $other = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $member->id]);
        $service = app(HelpfulAnswerService::class);
        $service->accept($community, $post, $comment, $creator);
        $service->accept($community, $post, $other, $creator);

        $this->actingAs($creator)->deleteJson(route('communities.posts.comments.helpful-answer.destroy', [$community, $post, $comment]))
            ->assertConflict();
        $this->assertSame($other->id, $post->fresh()->accepted_comment_id);
        $this->assertTrue($other->fresh()->is_helpful);
        $this->assertDatabaseCount('helpful_answer_actions', 3);

        $service->revoke($community, $post, $other, $creator);
        $service->revoke($community, $post, $other, $creator);
        $this->assertNull($post->fresh()->accepted_comment_id);
        $this->assertDatabaseCount('helpful_answer_actions', 4);
    }

    public function test_removed_entitlement_cannot_be_reused_from_cached_context(): void
    {
        extract($this->fixture());
        $course->update(['access_mode' => Course::ACCESS_MODE_ONE_TIME, 'price' => 100000]);
        $entitlement = CourseEntitlement::create([
            'community_id' => $community->id, 'course_id' => $course->id, 'user_id' => $member->id, 'amount' => 100000,
        ]);
        $this->assertTrue($post->isLearningContextAccessibleFor($member));
        $entitlement->delete();
        try {
            app(HelpfulAnswerService::class)->accept($community, $post, $comment, $creator);
            $this->fail('A removed entitlement cannot authorize acceptance.');
        } catch (HttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }
        $this->assertDatabaseCount('helpful_answer_actions', 0);
        $this->assertNull($post->fresh()->accepted_comment_id);
    }

    public function test_livewire_hides_ineligible_award_badge_and_accept_action_but_keeps_creator_cleanup(): void
    {
        extract($this->fixture());
        app(HelpfulAnswerService::class)->accept($community, $post, $comment, $creator);
        $membership->forceFill(['status' => 'SUSPENDED'])->save();

        Livewire::actingAs($creator)->test(DiscussionViewer::class, ['community' => $community, 'postId' => $post->id])
            ->assertDontSee('Accepted Answer')
            ->assertDontSee('Accept Answer')
            ->assertSee('Revoke')
            ->call('revokeHelpfulAnswer', $comment->id)
            ->assertSee('Helpful answer revoked.')
            ->assertDontSee('Revoke');
        $this->assertNull($post->fresh()->accepted_comment_id);
    }

    public function test_soft_deleted_answer_replacement_clears_old_flag_and_preserves_replacement_audit(): void
    {
        extract($this->fixture());
        $service = app(HelpfulAnswerService::class);
        $service->accept($community, $post, $comment, $creator);
        $comment->delete();
        $other = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $member->id]);

        $service->accept($community, $post, $other, $creator);
        $this->assertFalse(Comment::withTrashed()->findOrFail($comment->id)->is_helpful);
        $this->assertDatabaseHas('helpful_answer_actions', ['comment_id' => $comment->id, 'action' => HelpfulAnswerAction::ACTION_REPLACED]);
        $this->assertSame($other->id, $post->fresh()->accepted_comment_id);
    }

    public function test_failed_replacement_audit_rolls_back_pointer_flags_and_history(): void
    {
        extract($this->fixture());
        $service = app(HelpfulAnswerService::class);
        $service->accept($community, $post, $comment, $creator);
        $other = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $member->id]);
        $event = 'eloquent.creating: '.HelpfulAnswerAction::class;
        Event::listen($event, fn () => throw new RuntimeException('synthetic audit persistence failure'));
        try {
            $service->accept($community, $post, $other, $creator);
            $this->fail('The injected persistence failure must propagate.');
        } catch (RuntimeException $exception) {
            $this->assertSame('synthetic audit persistence failure', $exception->getMessage());
        } finally {
            Event::forget($event);
        }

        $this->assertSame($comment->id, $post->fresh()->accepted_comment_id);
        $this->assertTrue($comment->fresh()->is_helpful);
        $this->assertFalse($other->fresh()->is_helpful);
        $this->assertDatabaseCount('helpful_answer_actions', 1);
    }

    public function test_livewire_helpful_failure_does_not_expose_raw_exception_details(): void
    {
        extract($this->fixture());
        $service = $this->mock(HelpfulAnswerService::class);
        $service->shouldReceive('accept')->once()->andThrow(new RuntimeException('private-sql-path-and-credentials'));
        $service->shouldReceive('revoke')->once()->andThrow(new RuntimeException('private-sql-path-and-credentials'));

        Livewire::actingAs($creator)->test(DiscussionViewer::class, ['community' => $community, 'postId' => $post->id])
            ->call('acceptHelpfulAnswer', $comment->id)
            ->assertSet('successMessage', null)
            ->assertSee('Unable to accept this answer.')
            ->assertDontSee('private-sql-path-and-credentials')
            ->call('revokeHelpfulAnswer', $comment->id)
            ->assertSet('successMessage', null)
            ->assertSee('Unable to revoke this answer.')
            ->assertDontSee('private-sql-path-and-credentials');
    }
}
