<?php

namespace Tests\Feature;

use App\Livewire\DiscussionViewer;
use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\HelpfulAnswerAction;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HelpfulAnswerLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function createPostWithLearningContext(Community $community, User $author): Post
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

        return Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $author->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
        ]);
    }

    public function test_creator_can_accept_and_revoke_helpful_answer_on_contextual_post(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $post = $this->createPostWithLearningContext($community, $creator);

        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $member->id,
            'body' => 'Here is the correct solution to the exercise.',
        ]);

        $this->actingAs($creator);

        // 1. Accept comment
        $response = $this->post(route('communities.posts.comments.helpful-answer.store', [$community, $post, $comment]));
        $response->assertRedirect();

        $post->refresh();
        $comment->refresh();
        $this->assertSame($comment->id, $post->accepted_comment_id);
        $this->assertTrue($comment->is_helpful);

        $this->assertDatabaseHas('helpful_answer_actions', [
            'community_id' => $community->id,
            'post_id' => $post->id,
            'comment_id' => $comment->id,
            'author_id' => $member->id,
            'actor_id' => $creator->id,
            'action' => HelpfulAnswerAction::ACTION_ACCEPTED,
        ]);

        // 2. Idempotent acceptance
        $idempotentResponse = $this->post(route('communities.posts.comments.helpful-answer.store', [$community, $post, $comment]));
        $idempotentResponse->assertRedirect();
        $this->assertSame(1, HelpfulAnswerAction::where('action', HelpfulAnswerAction::ACTION_ACCEPTED)->count());

        // 3. Revoke comment
        $revokeResponse = $this->delete(route('communities.posts.comments.helpful-answer.destroy', [$community, $post, $comment]));
        $revokeResponse->assertRedirect();

        $post->refresh();
        $comment->refresh();
        $this->assertNull($post->accepted_comment_id);
        $this->assertFalse($comment->is_helpful);

        $this->assertDatabaseHas('helpful_answer_actions', [
            'community_id' => $community->id,
            'post_id' => $post->id,
            'comment_id' => $comment->id,
            'action' => HelpfulAnswerAction::ACTION_REVOKED,
        ]);
    }

    public function test_accepting_new_comment_replaces_previous_accepted_comment(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $memberA = User::factory()->create();
        $memberB = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $memberA->id, 'status' => 'ACTIVE']);
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $memberB->id, 'status' => 'ACTIVE']);

        $post = $this->createPostWithLearningContext($community, $creator);

        $commentA = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $memberA->id,
            'body' => 'Solution A',
        ]);
        $commentB = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $memberB->id,
            'body' => 'Solution B',
        ]);

        $this->actingAs($creator);

        // Accept Comment A
        $this->post(route('communities.posts.comments.helpful-answer.store', [$community, $post, $commentA]));
        $post->refresh();
        $commentA->refresh();
        $this->assertSame($commentA->id, $post->accepted_comment_id);
        $this->assertTrue($commentA->is_helpful);

        // Accept Comment B -> Replaces Comment A
        $this->post(route('communities.posts.comments.helpful-answer.store', [$community, $post, $commentB]));
        $post->refresh();
        $commentA->refresh();
        $commentB->refresh();

        $this->assertSame($commentB->id, $post->accepted_comment_id);
        $this->assertFalse($commentA->is_helpful);
        $this->assertTrue($commentB->is_helpful);

        $this->assertDatabaseHas('helpful_answer_actions', [
            'comment_id' => $commentA->id,
            'action' => HelpfulAnswerAction::ACTION_REPLACED,
        ]);
        $this->assertDatabaseHas('helpful_answer_actions', [
            'comment_id' => $commentB->id,
            'action' => HelpfulAnswerAction::ACTION_ACCEPTED,
        ]);
    }

    public function test_non_creator_cannot_accept_helpful_answer(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $member = User::factory()->create();
        $postAuthor = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $postAuthor->id, 'status' => 'ACTIVE']);

        $post = $this->createPostWithLearningContext($community, $postAuthor);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $member->id,
        ]);

        // Regular member attempt
        $this->actingAs($member);
        $this->post(route('communities.posts.comments.helpful-answer.store', [$community, $post, $comment]))
            ->assertForbidden();

        // Post author (who is not community creator) attempt
        $this->actingAs($postAuthor);
        $this->post(route('communities.posts.comments.helpful-answer.store', [$community, $post, $comment]))
            ->assertForbidden();
    }

    public function test_creator_cannot_accept_own_comment_as_helpful_answer(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $post = $this->createPostWithLearningContext($community, $creator);
        $creatorComment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $creator->id,
            'body' => 'My own commentary',
        ]);

        $this->actingAs($creator);
        $this->post(route('communities.posts.comments.helpful-answer.store', [$community, $post, $creatorComment]))
            ->assertForbidden();
    }

    public function test_cannot_accept_helpful_answer_on_non_contextual_post(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $member = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);

        // Post without classroom/course/lesson context
        $generalPost = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'course_id' => null,
            'lesson_id' => null,
        ]);

        $comment = Comment::factory()->create([
            'post_id' => $generalPost->id,
            'user_id' => $member->id,
        ]);

        $this->actingAs($creator);
        $this->post(route('communities.posts.comments.helpful-answer.store', [$community, $generalPost, $comment]))
            ->assertStatus(422);
    }

    public function test_livewire_discussion_viewer_renders_badges_and_creator_actions(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $member = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);

        $post = $this->createPostWithLearningContext($community, $creator);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $member->id,
            'body' => 'Community answer here',
        ]);

        // Viewing as creator: sees "Accept Answer" button
        Livewire::actingAs($creator)
            ->test(DiscussionViewer::class, ['community' => $community, 'postId' => $post->id])
            ->assertSee('Accept Answer')
            ->call('acceptHelpfulAnswer', $comment->id)
            ->assertSee('Accepted Answer')
            ->assertSee('Revoke');

        // Viewing as regular member: sees "Accepted Answer" badge but NOT "Revoke" or "Accept Answer"
        Livewire::actingAs($member)
            ->test(DiscussionViewer::class, ['community' => $community, 'postId' => $post->id])
            ->assertSee('Accepted Answer')
            ->assertDontSee('Accept Answer')
            ->assertDontSee('Revoke');
    }
}
