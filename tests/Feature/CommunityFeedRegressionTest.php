<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class CommunityFeedRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_authors_cannot_read_or_mutate_owned_content(): void
    {
        foreach (['PENDING_PAYMENT', 'SUSPENDED', 'REMOVED', 'LEFT'] as $state) {
            $community = Community::factory()->create();
            $membership = CommunityMembership::factory()->create(['community_id' => $community->id, 'status' => $state]);
            $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $membership->user_id]);
            $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $membership->user_id]);
            $url = "/communities/{$community->slug}/posts/{$post->id}";
            $this->actingAs($membership->user);
            $this->get($url)->assertNotFound();
            $this->get("$url/edit")->assertNotFound();
            $this->put($url, ['title' => 'Changed', 'body' => 'Changed'])->assertNotFound();
            $this->delete($url)->assertNotFound();
            $this->post("$url/comments", ['body' => 'New'])->assertNotFound();
            $this->get("$url/comments/{$comment->id}/edit")->assertNotFound();
            $this->put("$url/comments/{$comment->id}", ['body' => 'Changed'])->assertNotFound();
            $this->delete("$url/comments/{$comment->id}")->assertNotFound();
            $this->assertNotSoftDeleted($post);
            $this->assertNotSoftDeleted($comment);
        }
    }

    public function test_comment_edit_preserves_ownership_and_escapes_text(): void
    {
        $community = Community::factory()->create();
        $membership = CommunityMembership::factory()->active()->create(['community_id' => $community->id]);
        $post = Post::factory()->create(['community_id' => $community->id]);
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $membership->user_id]);
        $url = "/communities/{$community->slug}/posts/{$post->id}/comments/{$comment->id}";
        $payload = '<script>synthetic()</script>';
        $this->actingAs($membership->user)->get("$url/edit")->assertOk()->assertSee('Save comment');
        $this->put($url, ['body' => $payload, 'user_id' => $community->creator_id, 'post_id' => 999, 'community_id' => 999])->assertRedirect();
        $comment->refresh();
        $this->assertSame($payload, $comment->body);
        $this->assertSame($membership->user_id, $comment->user_id);
        $this->assertSame($post->id, $comment->post_id);
        $this->get("/communities/{$community->slug}/posts/{$post->id}")->assertSee($payload)->assertDontSee($payload, false);
        $this->actingAs($community->creator)->put($url, ['body' => 'Not allowed'])->assertNotFound();
        $other = CommunityMembership::factory()->active()->create(['community_id' => $community->id]);
        $this->actingAs($other->user)->get("$url/edit")->assertNotFound();
        $this->put($url, ['body' => 'Not allowed'])->assertNotFound();
    }

    public function test_comment_edit_validation_does_not_write(): void
    {
        $community = Community::factory()->create();
        $membership = CommunityMembership::factory()->active()->create(['community_id' => $community->id]);
        $post = Post::factory()->create(['community_id' => $community->id]);
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $membership->user_id, 'body' => 'Original']);
        foreach (['   ', str_repeat('x', 2001)] as $body) {
            $this->actingAs($membership->user)->put("/communities/{$community->slug}/posts/{$post->id}/comments/{$comment->id}", ['body' => $body])->assertSessionHasErrors('body');
            $this->assertSame('Original', $comment->refresh()->body);
        }
    }

    public function test_foreign_and_deleted_parent_comments_are_not_editable(): void
    {
        $community = Community::factory()->create();
        $membership = CommunityMembership::factory()->active()->create(['community_id' => $community->id]);
        $post = Post::factory()->create(['community_id' => $community->id]);
        $otherPost = Post::factory()->create(['community_id' => $community->id]);
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $membership->user_id]);
        $base = "/communities/{$community->slug}/posts";
        $this->actingAs($membership->user)->put("$base/{$otherPost->id}/comments/{$comment->id}", ['body' => 'Foreign'])->assertNotFound();
        $post->delete();
        $this->get("$base/{$post->id}")->assertNotFound();
        $this->post("$base/{$post->id}/comments", ['body' => 'New'])->assertNotFound();
        $this->put("$base/{$post->id}/comments/{$comment->id}", ['body' => 'Changed'])->assertNotFound();
        $this->delete("$base/{$post->id}/comments/{$comment->id}")->assertNotFound();
    }

    public function test_comments_paginate_with_stable_ties_and_loaded_authors(): void
    {
        $community = Community::factory()->create();
        $membership = CommunityMembership::factory()->active()->create(['community_id' => $community->id]);
        $post = Post::factory()->create(['community_id' => $community->id]);
        $comments = Comment::factory()->count(41)->create(['post_id' => $post->id, 'created_at' => '2026-10-08 00:00:00']);
        Comment::factory()->create();
        $url = "/communities/{$community->slug}/posts/{$post->id}";
        $page = $this->actingAs($membership->user)->get($url)->assertOk()->viewData('comments');
        $this->assertSame(41, $page->total());
        $this->assertSame($comments->take(20)->modelKeys(), $page->getCollection()->modelKeys());
        foreach ($page as $comment) {
            $this->assertTrue($comment->relationLoaded('author'));
            $this->assertTrue($comment->relationLoaded('post'));
        }
        $last = $this->get("$url?page=3")->assertOk()->viewData('comments');
        $this->assertSame([$comments->last()->id], $last->getCollection()->modelKeys());
    }

    public function test_feed_does_not_load_threads_and_uses_stable_ties(): void
    {
        $community = Community::factory()->create();
        $membership = CommunityMembership::factory()->active()->create(['community_id' => $community->id]);
        $posts = Post::factory()->count(16)->create(['community_id' => $community->id, 'created_at' => '2026-10-08 00:00:00']);
        Comment::factory()->count(30)->create(['post_id' => $posts->last()->id]);
        $page = $this->actingAs($membership->user)->get("/communities/{$community->slug}/posts")->assertOk()->viewData('posts');
        $this->assertSame(array_reverse($posts->slice(1)->modelKeys()), $page->getCollection()->modelKeys());
        $this->assertSame(30, $page->first()->comments_count);
        foreach ($page as $post) {
            $this->assertTrue($post->relationLoaded('author'));
            $this->assertFalse($post->relationLoaded('comments'));
        }
    }

    public function test_parent_deleted_after_preliminary_authorization_is_rechecked(): void
    {
        $community = Community::factory()->create();
        $membership = CommunityMembership::factory()->active()->create(['community_id' => $community->id]);
        $post = Post::factory()->create(['community_id' => $community->id]);
        $changed = false;
        Gate::after(function ($user, $ability, $result, $arguments) use ($post, &$changed): void {
            if (! $changed && $ability === 'create' && in_array(Comment::class, $arguments, true)) {
                $changed = true;
                $post->delete();
            }
        });
        $this->actingAs($membership->user)->post("/communities/{$community->slug}/posts/{$post->id}/comments", ['body' => 'Must not persist'])->assertNotFound();
        $this->assertTrue($changed);
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_revocation_after_preliminary_authorization_is_rechecked(): void
    {
        $community = Community::factory()->create();
        $membership = CommunityMembership::factory()->active()->create(['community_id' => $community->id]);
        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $membership->user_id]);
        $changed = false;
        Gate::after(function ($user, $ability) use ($membership, &$changed): void {
            if (! $changed && $ability === 'update') {
                $changed = true;
                $membership->transitionTo('REMOVED');
            }
        });
        $this->actingAs($membership->user)->put("/communities/{$community->slug}/posts/{$post->id}", ['title' => 'Must not persist', 'body' => 'No write'])->assertNotFound();
        $this->assertTrue($changed);
        $this->assertNotSame('Must not persist', $post->refresh()->title);
    }
}
