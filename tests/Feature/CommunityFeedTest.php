<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_member_and_creator_can_view_feed(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);
        $memberUser = User::factory()->create();
        CommunityMembership::factory()->active()->create([
            'community_id' => $community->id,
            'user_id' => $memberUser->id,
        ]);

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'Welcome to the Community',
            'body' => 'First announcement post',
        ]);

        // Creator can view feed
        $this->actingAs($creator)
            ->get("/communities/{$community->slug}/posts")
            ->assertOk()
            ->assertSee('Welcome to the Community')
            ->assertSee('First announcement post');

        // Member can view feed
        $this->actingAs($memberUser)
            ->get("/communities/{$community->slug}/posts")
            ->assertOk()
            ->assertSee('Welcome to the Community');
    }

    public function test_outsider_and_nonactive_member_cannot_view_feed(): void
    {
        $community = Community::factory()->create();
        Post::factory()->create([
            'community_id' => $community->id,
            'title' => 'Secret Post',
        ]);

        $outsider = User::factory()->create();
        $this->actingAs($outsider)
            ->get("/communities/{$community->slug}/posts")
            ->assertNotFound()
            ->assertDontSee('Secret Post');

        // Non-active membership states
        foreach (['PENDING_PAYMENT', 'SUSPENDED', 'REMOVED', 'LEFT'] as $status) {
            $inactiveUser = User::factory()->create();
            CommunityMembership::factory()->create([
                'community_id' => $community->id,
                'user_id' => $inactiveUser->id,
                'status' => $status,
            ]);

            $this->actingAs($inactiveUser)
                ->get("/communities/{$community->slug}/posts")
                ->assertNotFound()
                ->assertDontSee('Secret Post');
        }
    }

    public function test_active_member_can_create_post(): void
    {
        $community = Community::factory()->create();
        $member = CommunityMembership::factory()->active()->create(['community_id' => $community->id]);

        $response = $this->actingAs($member->user)
            ->post("/communities/{$community->slug}/posts", [
                'title' => 'My First Question',
                'body' => 'How do I start the course?',
            ]);

        $response->assertRedirect("/communities/{$community->slug}/posts");
        $this->assertDatabaseHas('posts', [
            'community_id' => $community->id,
            'user_id' => $member->user_id,
            'title' => 'My First Question',
            'body' => 'How do I start the course?',
        ]);
    }

    public function test_post_creation_requires_valid_title_and_body(): void
    {
        $community = Community::factory()->create();
        $member = CommunityMembership::factory()->active()->create(['community_id' => $community->id]);

        $this->actingAs($member->user)
            ->post("/communities/{$community->slug}/posts", [
                'title' => '',
                'body' => '',
            ])
            ->assertSessionHasErrors(['title', 'body']);

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_author_can_update_own_post(): void
    {
        $community = Community::factory()->create();
        $member = CommunityMembership::factory()->active()->create(['community_id' => $community->id]);
        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->user_id,
            'title' => 'Old Title',
            'body' => 'Old Body',
        ]);

        $response = $this->actingAs($member->user)
            ->put("/communities/{$community->slug}/posts/{$post->id}", [
                'title' => 'Updated Title',
                'body' => 'Updated Body',
            ]);

        $response->assertRedirect("/communities/{$community->slug}/posts");
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'body' => 'Updated Body',
        ]);
    }

    public function test_creator_and_other_members_cannot_update_another_authors_post(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);
        $author = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;
        $otherMember = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $author->id,
            'title' => 'Original Title',
        ]);

        // Other member cannot update
        $this->actingAs($otherMember)
            ->put("/communities/{$community->slug}/posts/{$post->id}", [
                'title' => 'Hacked by member',
                'body' => 'Hacked body',
            ])
            ->assertNotFound();

        // Creator cannot edit member's post (authorship preservation)
        $this->actingAs($creator)
            ->put("/communities/{$community->slug}/posts/{$post->id}", [
                'title' => 'Edited by creator',
                'body' => 'Creator changed content',
            ])
            ->assertNotFound();

        $this->assertSame('Original Title', $post->refresh()->title);
    }

    public function test_author_can_delete_own_post_via_soft_delete(): void
    {
        $community = Community::factory()->create();
        $author = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;
        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $author->id,
        ]);

        $this->actingAs($author)
            ->delete("/communities/{$community->slug}/posts/{$post->id}")
            ->assertRedirect("/communities/{$community->slug}/posts");

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_creator_can_delete_any_post_in_community_for_moderation(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);
        $author = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;
        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $author->id,
            'title' => 'Spam Post',
        ]);

        $this->actingAs($creator)
            ->delete("/communities/{$community->slug}/posts/{$post->id}")
            ->assertRedirect("/communities/{$community->slug}/posts");

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_non_author_member_cannot_delete_other_members_post(): void
    {
        $community = Community::factory()->create();
        $author = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;
        $otherMember = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;
        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $author->id,
        ]);

        $this->actingAs($otherMember)
            ->delete("/communities/{$community->slug}/posts/{$post->id}")
            ->assertNotFound();

        $this->assertNotSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_post_cannot_be_accessed_via_another_community_slug(): void
    {
        $communityA = Community::factory()->create();
        $communityB = Community::factory()->create();
        $user = CommunityMembership::factory()->active()->create(['community_id' => $communityA->id])->user;
        CommunityMembership::factory()->active()->create(['community_id' => $communityB->id, 'user_id' => $user->id]);

        $postA = Post::factory()->create(['community_id' => $communityA->id, 'user_id' => $user->id]);

        // Access postA through communityB slug fails with 404
        $this->actingAs($user)
            ->get("/communities/{$communityB->slug}/posts/{$postA->id}")
            ->assertNotFound();
    }

    public function test_active_member_can_comment_on_post(): void
    {
        $community = Community::factory()->create();
        $member = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;
        $post = Post::factory()->create(['community_id' => $community->id]);

        $response = $this->actingAs($member)
            ->post("/communities/{$community->slug}/posts/{$post->id}/comments", [
                'body' => 'Great question, here is what I think.',
            ]);

        $response->assertRedirect("/communities/{$community->slug}/posts/{$post->id}");
        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $member->id,
            'body' => 'Great question, here is what I think.',
        ]);
    }

    public function test_author_and_creator_can_delete_comment(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);
        $memberA = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;
        $memberB = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;
        $post = Post::factory()->create(['community_id' => $community->id]);

        $commentA = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $memberA->id]);
        $commentB = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $memberB->id]);

        // MemberA deletes own comment
        $this->actingAs($memberA)
            ->delete("/communities/{$community->slug}/posts/{$post->id}/comments/{$commentA->id}")
            ->assertRedirect("/communities/{$community->slug}/posts/{$post->id}");
        $this->assertSoftDeleted('comments', ['id' => $commentA->id]);

        // Creator deletes MemberB's comment (moderation)
        $this->actingAs($creator)
            ->delete("/communities/{$community->slug}/posts/{$post->id}/comments/{$commentB->id}")
            ->assertRedirect("/communities/{$community->slug}/posts/{$post->id}");
        $this->assertSoftDeleted('comments', ['id' => $commentB->id]);

        // MemberA cannot delete MemberB's comment
        $commentC = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $memberB->id]);
        $this->actingAs($memberA)
            ->delete("/communities/{$community->slug}/posts/{$post->id}/comments/{$commentC->id}")
            ->assertNotFound();
        $this->assertNotSoftDeleted('comments', ['id' => $commentC->id]);
    }

    public function test_xss_content_is_escaped_in_rendered_feed(): void
    {
        $community = Community::factory()->create();
        $member = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;
        $maliciousTitle = '<script>alert("xss")</script>';
        $maliciousBody = '<img src=x onerror=alert(1)>';

        Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'title' => $maliciousTitle,
            'body' => $maliciousBody,
        ]);

        $response = $this->actingAs($member)
            ->get("/communities/{$community->slug}/posts");

        $response->assertOk();
        $response->assertDontSee($maliciousTitle, false);
        $response->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false);
    }

    public function test_feed_pagination_works_and_avoids_unbounded_queries(): void
    {
        $community = Community::factory()->create();
        $member = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;

        Post::factory()->count(20)->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
        ]);

        $response = $this->actingAs($member)
            ->get("/communities/{$community->slug}/posts");

        $response->assertOk();
        // 15 posts per page
        $this->assertCount(15, $response->viewData('posts'));
    }
}
