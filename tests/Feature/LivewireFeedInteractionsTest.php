<?php

namespace Tests\Feature;

use App\Jobs\SendPostReplyNotification;
use App\Livewire\DiscussionViewer;
use App\Livewire\PostLikeButton;
use App\Livewire\PostSaveButton;
use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireFeedInteractionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_like_button_reactively_likes_and_unlikes_post(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'Test Post for Like Island',
        ]);

        $this->actingAs($member);

        // 1. Initial state: not liked, 0 likes
        Livewire::test(PostLikeButton::class, ['community' => $community, 'post' => $post])
            ->assertSet('isLiked', false)
            ->assertSet('likesCount', 0)
            ->assertSee('0 likes')
            ->call('toggleLike')
            ->assertSet('isLiked', true)
            ->assertSet('likesCount', 1)
            ->assertSee('1 likes')
            ->assertSee('Unlike post');

        $this->assertDatabaseHas('post_likes', [
            'community_id' => $community->id,
            'post_id' => $post->id,
            'user_id' => $member->id,
            'is_active' => true,
        ]);

        // 2. Unlike toggle
        Livewire::test(PostLikeButton::class, ['community' => $community, 'post' => $post])
            ->assertSet('isLiked', true)
            ->call('toggleLike')
            ->assertSet('isLiked', false)
            ->assertSet('likesCount', 0)
            ->assertSee('0 likes')
            ->assertSee('Like post');

        $this->assertDatabaseHas('post_likes', [
            'community_id' => $community->id,
            'post_id' => $post->id,
            'user_id' => $member->id,
            'is_active' => false,
        ]);
    }

    public function test_author_cannot_like_own_post_in_livewire_component(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'Own Post',
        ]);

        $this->actingAs($creator);

        Livewire::test(PostLikeButton::class, ['community' => $community, 'post' => $post])
            ->assertSet('isOwnPost', true)
            ->assertSee('You cannot like your own post')
            ->call('toggleLike')
            ->assertSet('errorMessage', 'You cannot like your own post.')
            ->assertSet('isLiked', false)
            ->assertSet('likesCount', 0);

        $this->assertDatabaseMissing('post_likes', [
            'post_id' => $post->id,
            'user_id' => $creator->id,
        ]);
    }

    public function test_post_save_button_reactively_bookmarks_and_unbookmarks(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'Test Post for Save Island',
        ]);

        $this->actingAs($member);

        // Initial save
        Livewire::test(PostSaveButton::class, ['community' => $community, 'post' => $post])
            ->assertSet('isSaved', false)
            ->assertSee('Save')
            ->call('toggleSave')
            ->assertSet('isSaved', true)
            ->assertSee('Saved')
            ->assertDispatched('post-bookmark-updated', postId: $post->id, saved: true);

        $this->assertDatabaseHas('post_bookmarks', [
            'community_id' => $community->id,
            'post_id' => $post->id,
            'user_id' => $member->id,
        ]);

        // Unsave
        Livewire::test(PostSaveButton::class, ['community' => $community, 'post' => $post])
            ->assertSet('isSaved', true)
            ->call('toggleSave')
            ->assertSet('isSaved', false)
            ->assertSee('Save')
            ->assertDispatched('post-bookmark-updated', postId: $post->id, saved: false);

        $this->assertDatabaseMissing('post_bookmarks', [
            'community_id' => $community->id,
            'post_id' => $post->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_discussion_viewer_opens_post_and_renders_ordered_comments(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'Deep Discussion Post',
            'body' => 'Detailed architectural discussion body text.',
        ]);

        Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $member->id,
            'body' => 'First comment from member',
            'created_at' => now()->subMinutes(10),
        ]);

        Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $creator->id,
            'body' => 'Second comment from creator',
            'created_at' => now()->subMinutes(5),
        ]);

        $this->actingAs($member);

        Livewire::test(DiscussionViewer::class, ['community' => $community])
            ->assertSet('isOpen', false)
            ->call('openPost', $post->id)
            ->assertSet('isOpen', true)
            ->assertSet('postTitle', 'Deep Discussion Post')
            ->assertSet('commentsCount', 2)
            ->assertSee('Deep Discussion Post')
            ->assertSee('Detailed architectural discussion body text.')
            ->assertSee('First comment from member')
            ->assertSee('Second comment from creator')
            ->assertDispatched(
                'discussion-opened',
                postId: $post->id,
                url: route('communities.posts.show', [$community, $post]),
            )
            ->call('close')
            ->assertSet('isOpen', false)
            ->assertSet('postId', null)
            ->assertDispatched('discussion-closed');
    }

    public function test_discussion_viewer_bounds_initial_comments_and_loads_earlier_pages_on_demand(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
        ]);

        foreach (range(1, 25) as $index) {
            Comment::factory()->create([
                'post_id' => $post->id,
                'user_id' => $creator->id,
                'body' => sprintf('Ordered comment #%02d', $index),
                'created_at' => now()->addSeconds($index),
            ]);
        }

        $this->actingAs($creator);

        $component = Livewire::test(DiscussionViewer::class, ['community' => $community, 'postId' => $post->id])
            ->assertSet('commentsCount', 25)
            ->assertSet('hasEarlierComments', true)
            ->assertDontSee('Ordered comment #01')
            ->assertSee('Ordered comment #06')
            ->assertSee('Ordered comment #25');

        $this->assertCount(20, $component->get('comments'));

        $component
            ->call('loadEarlierComments')
            ->assertSet('hasEarlierComments', false)
            ->assertSee('Ordered comment #01');

        $this->assertCount(25, $component->get('comments'));
    }

    public function test_reaction_and_comment_mutations_recheck_effective_membership(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $member = User::factory()->create();
        $membership = CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);
        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
        ]);

        $this->actingAs($member);

        $like = Livewire::test(PostLikeButton::class, ['community' => $community, 'post' => $post]);
        $viewer = Livewire::test(DiscussionViewer::class, ['community' => $community, 'postId' => $post->id]);

        CommunityMembership::query()->whereKey($membership->id)->update(['status' => 'REMOVED']);

        $like
            ->call('toggleLike')
            ->assertSet('isLiked', false)
            ->assertSet('likesCount', 0)
            ->assertSet('errorMessage', 'Unable to update like. Please try again.');

        $viewer
            ->set('newCommentBody', 'This must not be persisted after revocation.')
            ->call('addComment')
            ->assertSet('errorMessage', 'Unable to post comment. Please check your access and try again.');

        $this->assertDatabaseMissing('post_likes', [
            'post_id' => $post->id,
            'user_id' => $member->id,
        ]);
        $this->assertDatabaseMissing('comments', [
            'post_id' => $post->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_reaction_components_render_native_form_fallbacks(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);
        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
        ]);

        $this->actingAs($member);

        Livewire::test(PostLikeButton::class, ['community' => $community, 'post' => $post])
            ->assertSee(route('communities.posts.like', [$community, $post]), false)
            ->assertSee('method="POST"', false);

        Livewire::test(PostSaveButton::class, ['community' => $community, 'post' => $post])
            ->assertSee(route('communities.posts.save', [$community, $post]), false)
            ->assertSee('method="POST"', false);
    }

    public function test_discussion_viewer_allows_adding_comments_with_notification_and_onboarding(): void
    {
        Queue::fake();

        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'Post Awaiting Replies',
        ]);

        $this->actingAs($member);

        Livewire::test(DiscussionViewer::class, ['community' => $community, 'postId' => $post->id])
            ->assertSet('commentsCount', 0)
            ->set('newCommentBody', 'This is a livewire reply!')
            ->call('addComment')
            ->assertSet('commentsCount', 1)
            ->assertSet('newCommentBody', '')
            ->assertSee('This is a livewire reply!');

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $member->id,
            'body' => 'This is a livewire reply!',
        ]);

        Queue::assertPushed(SendPostReplyNotification::class);
        $this->assertDatabaseHas('onboarding_step_completions', [
            'community_id' => $community->id,
            'user_id' => $member->id,
            'step_key' => 'comment',
        ]);
    }

    public function test_discussion_viewer_validates_comment_length(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
        ]);

        $this->actingAs($creator);

        Livewire::test(DiscussionViewer::class, ['community' => $community, 'postId' => $post->id])
            ->set('newCommentBody', '')
            ->call('addComment')
            ->assertHasErrors(['newCommentBody' => 'required']);

        Livewire::test(DiscussionViewer::class, ['community' => $community, 'postId' => $post->id])
            ->set('newCommentBody', str_repeat('a', 2001))
            ->call('addComment')
            ->assertHasErrors(['newCommentBody' => 'max']);
    }

    public function test_discussion_viewer_in_place_edit_and_delete_comment(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
        ]);

        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $member->id,
            'body' => 'Original text to edit',
        ]);

        $this->actingAs($member);

        // Edit
        Livewire::test(DiscussionViewer::class, ['community' => $community, 'postId' => $post->id])
            ->call('editComment', $comment->id)
            ->assertSet('editingCommentId', $comment->id)
            ->assertSet('editingCommentBody', 'Original text to edit')
            ->set('editingCommentBody', 'Updated comment text via Livewire')
            ->call('updateComment')
            ->assertSet('editingCommentId', null)
            ->assertSee('Updated comment text via Livewire');

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'body' => 'Updated comment text via Livewire',
        ]);

        // Delete
        Livewire::test(DiscussionViewer::class, ['community' => $community, 'postId' => $post->id])
            ->call('deleteComment', $comment->id)
            ->assertSet('commentsCount', 0);

        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_creator_can_delete_member_comment_but_cannot_edit_it(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
        ]);

        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $member->id,
            'body' => 'Member comment to moderate',
        ]);

        $this->actingAs($creator);

        // Creator cannot edit member's comment
        Livewire::test(DiscussionViewer::class, ['community' => $community, 'postId' => $post->id])
            ->call('editComment', $comment->id)
            ->assertSet('editingCommentId', null); // denied edit

        // Creator can delete (moderate) member's comment
        Livewire::test(DiscussionViewer::class, ['community' => $community, 'postId' => $post->id])
            ->call('deleteComment', $comment->id)
            ->assertSet('commentsCount', 0);

        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_outsider_and_cross_tenant_denial(): void
    {
        $creator = User::factory()->create();
        $communityA = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $communityB = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        $postA = Post::factory()->create([
            'community_id' => $communityA->id,
            'user_id' => $creator->id,
        ]);

        $outsider = User::factory()->create();
        $this->actingAs($outsider);

        // Outsider cannot open post in private community
        Livewire::test(DiscussionViewer::class, ['community' => $communityA])
            ->call('openPost', $postA->id)
            ->assertStatus(404);
    }
}
