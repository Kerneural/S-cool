<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Post;
use App\Models\PostBookmark;
use App\Models\PostLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityPostReactionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_like_increments_count_and_repeated_requests_are_idempotent(): void
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

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'Great Community Post',
        ]);

        $this->actingAs($member);

        // 1. Initial like
        $likeResponse = $this->post(route('communities.posts.like', [$community, $post]));
        $likeResponse->assertSessionHasNoErrors();

        $this->assertDatabaseHas('post_likes', [
            'community_id' => $community->id,
            'post_id' => $post->id,
            'user_id' => $member->id,
            'is_active' => true,
        ]);
        $this->assertSame(1, $post->activeLikes()->count());
        $this->assertSame(1, PostLike::count());

        // 2. Repeated like request (idempotency check)
        $repeatResponse = $this->post(route('communities.posts.like', [$community, $post]));
        $repeatResponse->assertSessionHasNoErrors();

        $this->assertSame(1, $post->activeLikes()->count());
        $this->assertSame(1, PostLike::count());
    }

    public function test_unlike_decrements_count_and_repeated_requests_are_idempotent(): void
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

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
        ]);

        $this->actingAs($member);

        // Start with an active like
        $this->post(route('communities.posts.like', [$community, $post]));
        $this->assertSame(1, $post->activeLikes()->count());

        // 1. Unlike post
        $unlikeResponse = $this->delete(route('communities.posts.unlike', [$community, $post]));
        $unlikeResponse->assertSessionHasNoErrors();

        $this->assertSame(0, $post->activeLikes()->count());
        $this->assertDatabaseHas('post_likes', [
            'post_id' => $post->id,
            'user_id' => $member->id,
            'is_active' => false,
        ]);

        // 2. Repeated unlike request (idempotency check)
        $repeatUnlike = $this->delete(route('communities.posts.unlike', [$community, $post]));
        $repeatUnlike->assertSessionHasNoErrors();

        $this->assertSame(0, $post->activeLikes()->count());
        $this->assertSame(1, PostLike::count());
    }

    public function test_relike_preserves_original_created_at_timestamp_identity(): void
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

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
        ]);

        $this->actingAs($member);

        // 1. Initial like
        $this->post(route('communities.posts.like', [$community, $post]));
        $initialLike = PostLike::firstOrFail();
        $originalCreatedAt = $initialLike->created_at;

        // 2. Unlike
        $this->delete(route('communities.posts.unlike', [$community, $post]));
        $this->assertFalse(PostLike::firstOrFail()->is_active);

        // 3. Re-like
        $this->post(route('communities.posts.like', [$community, $post]));
        $reLiked = PostLike::firstOrFail();

        $this->assertTrue($reLiked->is_active);
        $this->assertSame($initialLike->id, $reLiked->id);
        $this->assertSame($originalCreatedAt->toDateTimeString(), $reLiked->created_at->toDateTimeString());
        $this->assertSame(1, PostLike::count());
    }

    public function test_self_like_is_rejected_without_count_change(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
        ]);

        $this->actingAs($creator);

        // Creator attempts to like own post
        $response = $this->post(route('communities.posts.like', [$community, $post]));
        $response->assertSessionHasErrors('like');

        $this->assertSame(0, $post->activeLikes()->count());
        $this->assertDatabaseMissing('post_likes', [
            'post_id' => $post->id,
            'user_id' => $creator->id,
        ]);
    }

    public function test_self_bookmark_is_allowed_and_idempotent(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
        ]);

        $this->actingAs($creator);

        // 1. Creator saves own post -> Success
        $saveResponse = $this->post(route('communities.posts.save', [$community, $post]));
        $saveResponse->assertSessionHasNoErrors();

        $this->assertDatabaseHas('post_bookmarks', [
            'community_id' => $community->id,
            'post_id' => $post->id,
            'user_id' => $creator->id,
        ]);
        $this->assertSame(1, PostBookmark::count());

        // 2. Repeated save -> Idempotent
        $this->post(route('communities.posts.save', [$community, $post]));
        $this->assertSame(1, PostBookmark::count());

        // 3. Unsave -> Removed
        $unsaveResponse = $this->delete(route('communities.posts.unsave', [$community, $post]));
        $unsaveResponse->assertSessionHasNoErrors();
        $this->assertSame(0, PostBookmark::count());

        // 4. Repeated unsave -> Idempotent
        $this->delete(route('communities.posts.unsave', [$community, $post]));
        $this->assertSame(0, PostBookmark::count());
    }

    public function test_saved_posts_list_shows_only_requesting_users_saved_posts(): void
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

        $post1 = Post::factory()->create(['community_id' => $community->id, 'user_id' => $creator->id, 'title' => 'Post Saved by A']);
        $post2 = Post::factory()->create(['community_id' => $community->id, 'user_id' => $creator->id, 'title' => 'Post Saved by B']);

        // Member A saves post 1
        $this->actingAs($memberA)->post(route('communities.posts.save', [$community, $post1]));

        // Member B saves post 2
        $this->actingAs($memberB)->post(route('communities.posts.save', [$community, $post2]));

        // Member A saved list contains only Post 1, never Post 2
        $responseA = $this->actingAs($memberA)->get(route('communities.saved.index', $community));
        $responseA->assertOk()
            ->assertSee('Post Saved by A')
            ->assertDontSee('Post Saved by B');

        // Member B saved list contains only Post 2, never Post 1
        $responseB = $this->actingAs($memberB)->get(route('communities.saved.index', $community));
        $responseB->assertOk()
            ->assertSee('Post Saved by B')
            ->assertDontSee('Post Saved by A');
    }

    public function test_soft_deleted_post_and_suspended_community_deny_mutations_and_hide_from_saved_list(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $member = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);

        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $creator->id, 'title' => 'To be deleted']);

        // Member saves post
        $this->actingAs($member)->post(route('communities.posts.save', [$community, $post]));
        $this->actingAs($member)->get(route('communities.saved.index', $community))->assertSee('To be deleted');

        // Soft delete post
        $post->delete();

        // 1. Soft-deleted post disappears from saved posts listing
        $this->get(route('communities.saved.index', $community))
            ->assertOk()
            ->assertDontSee('To be deleted')
            ->assertSee('No saved posts yet');

        // 2. Mutations on soft deleted post are denied with 404
        $this->post(route('communities.posts.like', [$community, $post]))->assertNotFound();
        $this->delete(route('communities.posts.unlike', [$community, $post]))->assertNotFound();
        $this->post(route('communities.posts.save', [$community, $post]))->assertNotFound();
        $this->delete(route('communities.posts.unsave', [$community, $post]))->assertNotFound();

        // 3. Suspended or Archived community denies all mutations with 404
        foreach (['SUSPENDED', 'ARCHIVED'] as $status) {
            $lockedCommunity = Community::factory()->create(['creator_id' => $creator->id, 'status' => $status]);
            $lockedPost = Post::factory()->create(['community_id' => $lockedCommunity->id, 'user_id' => $creator->id]);
            CommunityMembership::factory()->create(['community_id' => $lockedCommunity->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);

            $this->get(route('communities.saved.index', $lockedCommunity))->assertNotFound();
            $this->post(route('communities.posts.like', [$lockedCommunity, $lockedPost]))->assertNotFound();
            $this->delete(route('communities.posts.unlike', [$lockedCommunity, $lockedPost]))->assertNotFound();
            $this->post(route('communities.posts.save', [$lockedCommunity, $lockedPost]))->assertNotFound();
            $this->delete(route('communities.posts.unsave', [$lockedCommunity, $lockedPost]))->assertNotFound();
        }
    }

    public function test_non_member_and_suspended_member_cannot_like_or_save_posts(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $creator->id]);

        $outsider = User::factory()->create();
        $suspendedMember = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $suspendedMember->id, 'status' => 'SUSPENDED']);

        // Outsider denied with 404
        $this->actingAs($outsider);
        $this->post(route('communities.posts.like', [$community, $post]))->assertNotFound();
        $this->post(route('communities.posts.save', [$community, $post]))->assertNotFound();
        $this->get(route('communities.saved.index', $community))->assertNotFound();

        // Suspended member denied with 404
        $this->actingAs($suspendedMember);
        $this->post(route('communities.posts.like', [$community, $post]))->assertNotFound();
        $this->post(route('communities.posts.save', [$community, $post]))->assertNotFound();
        $this->get(route('communities.saved.index', $community))->assertNotFound();
    }

    public function test_cross_tenant_post_cannot_be_liked_or_saved_under_current_community(): void
    {
        $creator = User::factory()->create();
        $commA = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $commB = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        $member = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $commA->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);
        CommunityMembership::factory()->create(['community_id' => $commB->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);

        $postB = Post::factory()->create(['community_id' => $commB->id, 'user_id' => $creator->id]);

        $this->actingAs($member);

        // Attempting to mutate postB using commA slug -> Rejected with 404
        $this->post(route('communities.posts.like', [$commA, $postB]))->assertNotFound();
        $this->post(route('communities.posts.save', [$commA, $postB]))->assertNotFound();
    }

    public function test_like_and_save_button_states_render_correctly_on_feed_and_detail(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        $member = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);

        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $creator->id, 'title' => 'Reaction Render Post']);

        $this->actingAs($member);

        // Before liking or saving: feed shows unliked / unsaved state
        $initialFeed = $this->get(route('communities.posts.index', $community));
        $initialFeed->assertOk()
            ->assertSee('0 likes')
            ->assertSee('Save');

        // Like and save
        $this->post(route('communities.posts.like', [$community, $post]));
        $this->post(route('communities.posts.save', [$community, $post]));

        // After liking & saving: feed shows liked / saved state
        $likedFeed = $this->get(route('communities.posts.index', $community));
        $likedFeed->assertOk()
            ->assertSee('1 likes')
            ->assertSee('Saved')
            ->assertSee('Unlike post');

        // Post detail show view also reflects 1 like and Saved
        $showResponse = $this->get(route('communities.posts.show', [$community, $post]));
        $showResponse->assertOk()
            ->assertSee('1 likes')
            ->assertSee('Saved')
            ->assertSee('Unlike post');
    }

    public function test_eager_loaded_likes_relation_only_contains_active_like_of_current_viewer_while_active_likes_count_includes_all_likes(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        $memberA = User::factory()->create();
        $memberB = User::factory()->create();
        $memberC = User::factory()->create();
        $memberD = User::factory()->create();

        foreach ([$memberA, $memberB, $memberC, $memberD] as $m) {
            CommunityMembership::factory()->create([
                'community_id' => $community->id,
                'user_id' => $m->id,
                'status' => 'ACTIVE',
            ]);
        }

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'Multi Like Test Post',
        ]);

        // Member A and Member B actively liked the post
        PostLike::create([
            'community_id' => $community->id,
            'post_id' => $post->id,
            'user_id' => $memberA->id,
            'is_active' => true,
        ]);
        PostLike::create([
            'community_id' => $community->id,
            'post_id' => $post->id,
            'user_id' => $memberB->id,
            'is_active' => true,
        ]);
        // Member D has an unliked (inactive) record
        PostLike::create([
            'community_id' => $community->id,
            'post_id' => $post->id,
            'user_id' => $memberD->id,
            'is_active' => false,
        ]);
        // Member A saved the post
        PostBookmark::create([
            'community_id' => $community->id,
            'post_id' => $post->id,
            'user_id' => $memberA->id,
        ]);

        // 1. When Member A (who liked and saved) visits
        $this->actingAs($memberA);

        // Feed view
        $feedRespA = $this->get(route('communities.posts.index', $community));
        $feedRespA->assertOk();
        $feedPostA = $feedRespA->viewData('posts')->first();
        $this->assertSame(2, $feedPostA->active_likes_count);
        $this->assertTrue($feedPostA->relationLoaded('likes'));
        $this->assertCount(1, $feedPostA->likes);
        $this->assertSame($memberA->id, $feedPostA->likes->first()->user_id);
        $this->assertTrue($feedPostA->isLikedBy($memberA));

        // Detail view
        $detailRespA = $this->get(route('communities.posts.show', [$community, $post]));
        $detailRespA->assertOk();
        $detailPostA = $detailRespA->viewData('post');
        $this->assertSame(2, $detailPostA->active_likes_count);
        $this->assertTrue($detailPostA->relationLoaded('likes'));
        $this->assertCount(1, $detailPostA->likes);
        $this->assertSame($memberA->id, $detailPostA->likes->first()->user_id);
        $this->assertTrue($detailPostA->isLikedBy($memberA));

        // Saved view
        $savedRespA = $this->get(route('communities.saved.index', $community));
        $savedRespA->assertOk();
        $savedPostA = $savedRespA->viewData('posts')->first();
        $this->assertSame(2, $savedPostA->active_likes_count);
        $this->assertTrue($savedPostA->relationLoaded('likes'));
        $this->assertCount(1, $savedPostA->likes);
        $this->assertSame($memberA->id, $savedPostA->likes->first()->user_id);
        $this->assertTrue($savedPostA->isLikedBy($memberA));

        // 2. When Member C (who has not liked the post) visits
        $this->actingAs($memberC);

        // Feed view
        $feedRespC = $this->get(route('communities.posts.index', $community));
        $feedRespC->assertOk();
        $feedPostC = $feedRespC->viewData('posts')->first();
        $this->assertSame(2, $feedPostC->active_likes_count);
        $this->assertTrue($feedPostC->relationLoaded('likes'));
        $this->assertCount(0, $feedPostC->likes);
        $this->assertFalse($feedPostC->isLikedBy($memberC));

        // Detail view
        $detailRespC = $this->get(route('communities.posts.show', [$community, $post]));
        $detailRespC->assertOk();
        $detailPostC = $detailRespC->viewData('post');
        $this->assertSame(2, $detailPostC->active_likes_count);
        $this->assertTrue($detailPostC->relationLoaded('likes'));
        $this->assertCount(0, $detailPostC->likes);
        $this->assertFalse($detailPostC->isLikedBy($memberC));
    }
}
