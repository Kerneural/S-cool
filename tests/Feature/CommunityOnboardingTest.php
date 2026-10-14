<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\OnboardingStepCompletion;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_active_member_sees_incomplete_steps_and_matching_actions_complete_them(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
            'intro_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'onboarding_enabled' => true,
        ]);
        PostCategory::initializeDefaultsFor($community);

        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($member);

        // 1. Initial feed view: member sees welcome checklist with 0/3 completed
        $response = $this->get(route('communities.posts.index', $community));
        $response->assertOk();
        $response->assertSee('Welcome Checklist');
        $response->assertSee('0 of 3 steps completed');

        // 2. Acknowledge video
        $this->post(route('communities.onboarding.video', $community))
            ->assertRedirect();
        $this->assertDatabaseHas('onboarding_step_completions', [
            'community_id' => $community->id,
            'user_id' => $member->id,
            'step_key' => 'video',
        ]);

        // Feed view now reflects 1 of 3
        $this->get(route('communities.posts.index', $community))
            ->assertSee('1 of 3 steps completed');

        // 3. Post a comment on existing post
        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
        ]);

        $this->post(route('communities.posts.comments.store', [$community, $post]), [
            'body' => 'Hello everyone in the community!',
        ])->assertRedirect();

        $this->assertDatabaseHas('onboarding_step_completions', [
            'community_id' => $community->id,
            'user_id' => $member->id,
            'step_key' => 'comment',
        ]);
        $this->get(route('communities.posts.index', $community))
            ->assertSee('2 of 3 steps completed');

        // 4. Create an introduction post in the Introductions category
        $introCat = $community->categories()->where('slug', 'introductions')->firstOrFail();
        $this->post(route('communities.posts.store', $community), [
            'title' => 'My Introduction',
            'body' => 'Excited to join this community!',
            'category_id' => $introCat->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('onboarding_step_completions', [
            'community_id' => $community->id,
            'user_id' => $member->id,
            'step_key' => 'post',
        ]);
        $this->assertEquals(3, OnboardingStepCompletion::where('community_id', $community->id)->where('user_id', $member->id)->count());
    }

    public function test_repeated_actions_and_relod_preserve_one_completion_and_tenant_isolation(): void
    {
        $creatorA = User::factory()->create();
        $communityA = Community::factory()->create(['creator_id' => $creatorA->id, 'status' => 'ACTIVE', 'onboarding_enabled' => true]);
        PostCategory::initializeDefaultsFor($communityA);

        $creatorB = User::factory()->create();
        $communityB = Community::factory()->create(['creator_id' => $creatorB->id, 'status' => 'ACTIVE', 'onboarding_enabled' => true]);
        PostCategory::initializeDefaultsFor($communityB);

        $member = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $communityA->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);
        CommunityMembership::factory()->create(['community_id' => $communityB->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);

        $postB = Post::factory()->create(['community_id' => $communityB->id, 'user_id' => $creatorB->id]);

        $this->actingAs($member);

        // Member comments in Community B
        $this->post(route('communities.posts.comments.store', [$communityB, $postB]), [
            'body' => 'Comment in B',
        ])->assertRedirect();

        // Community B has comment completion; Community A does NOT
        $this->assertDatabaseHas('onboarding_step_completions', [
            'community_id' => $communityB->id,
            'user_id' => $member->id,
            'step_key' => 'comment',
        ]);
        $this->assertDatabaseMissing('onboarding_step_completions', [
            'community_id' => $communityA->id,
            'user_id' => $member->id,
            'step_key' => 'comment',
        ]);

        // Repeated comments in B keep exactly 1 completion row in B
        $this->post(route('communities.posts.comments.store', [$communityB, $postB]), [
            'body' => 'Second comment in B',
        ])->assertRedirect();

        $this->assertEquals(1, OnboardingStepCompletion::where('community_id', $communityB->id)->where('user_id', $member->id)->where('step_key', 'comment')->count());
    }

    public function test_invalid_or_rolled_back_comment_creates_no_completion_and_deleted_source_does_not_erase_completion(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE', 'onboarding_enabled' => true]);
        PostCategory::initializeDefaultsFor($community);

        $member = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);

        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $creator->id]);

        $this->actingAs($member);

        // Invalid comment (empty body) fails validation
        $this->post(route('communities.posts.comments.store', [$community, $post]), [
            'body' => '',
        ])->assertSessionHasErrors('body');

        $this->assertEquals(0, OnboardingStepCompletion::where('user_id', $member->id)->count());

        // Valid comment creates completion
        $this->post(route('communities.posts.comments.store', [$community, $post]), [
            'body' => 'Valid comment here',
        ])->assertRedirect();

        $this->assertEquals(1, OnboardingStepCompletion::where('community_id', $community->id)->where('user_id', $member->id)->where('step_key', 'comment')->count());

        // Comment is later deleted
        $comment = Comment::where('post_id', $post->id)->firstOrFail();
        $comment->delete();

        // Historical completion remains intact
        $this->assertEquals(1, OnboardingStepCompletion::where('community_id', $community->id)->where('user_id', $member->id)->where('step_key', 'comment')->count());
    }

    public function test_disabled_onboarding_missing_video_and_deleted_category_have_defined_behavior(): void
    {
        $creator = User::factory()->create();
        // Community with no intro video
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
            'intro_video_url' => null,
            'onboarding_enabled' => true,
        ]);
        PostCategory::initializeDefaultsFor($community);

        $member = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);

        $this->actingAs($member);

        // Missing video: denominator is 2, video step is omitted
        $feed = $this->get(route('communities.posts.index', $community));
        $feed->assertOk();
        $feed->assertSee('0 of 2 steps completed');
        $feed->assertDontSee('Watch Introduction Video');

        // Creator deletes the Introductions category
        $this->actingAs($creator);
        $introCat = $community->categories()->where('slug', 'introductions')->firstOrFail();
        $this->delete(route('communities.categories.destroy', [$community, $introCat]))->assertRedirect();

        // Back to member view: Introductions category is missing
        // Denominator drops to 1 (only comment step is active, post step marked unavailable)
        $this->actingAs($member);
        $feedAfterDelete = $this->get(route('communities.posts.index', $community));
        $feedAfterDelete->assertOk();
        $feedAfterDelete->assertSee('0 of 1 steps completed');
        $feedAfterDelete->assertSee('Category Unavailable');

        // Creator disables onboarding
        $this->actingAs($creator);
        $community->update(['onboarding_enabled' => false]);

        $this->actingAs($member);
        $feedDisabled = $this->get(route('communities.posts.index', $community));
        $feedDisabled->assertDontSee('Welcome Checklist');
    }

    public function test_outsider_and_removed_member_cannot_access_and_creator_preview_creates_no_membership(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
            'intro_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'onboarding_enabled' => true,
        ]);

        $outsider = User::factory()->create();
        $this->actingAs($outsider);

        // Outsider cannot acknowledge video
        $this->post(route('communities.onboarding.video', $community))->assertNotFound();

        // Removed member cannot acknowledge video
        $removedUser = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $removedUser->id,
            'status' => 'REMOVED',
        ]);
        $this->actingAs($removedUser);
        $this->post(route('communities.onboarding.video', $community))->assertNotFound();

        // Creator views feed (preview mode)
        $this->actingAs($creator);
        $creatorFeed = $this->get(route('communities.posts.index', $community));
        $creatorFeed->assertOk();
        $creatorFeed->assertSee('Creator Preview');

        // Verify creator preview did NOT silently create a membership record
        $this->assertDatabaseMissing('community_memberships', [
            'community_id' => $community->id,
            'user_id' => $creator->id,
        ]);
    }

    public function test_deleting_custom_introduction_category_makes_task_unavailable_without_falling_back_to_slug(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
            'onboarding_enabled' => true,
        ]);
        PostCategory::initializeDefaultsFor($community);

        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        // Creator creates custom category and designates it as introduction category
        $this->actingAs($creator);
        $this->post(route('communities.categories.store', $community), [
            'name' => 'Welcome Newcomers',
        ])->assertRedirect();

        $customCat = $community->categories()->where('slug', 'welcome-newcomers')->firstOrFail();

        $this->put(route('communities.update', $community), [
            'name' => $community->name,
            'introduction_category_id' => $customCat->id,
            'onboarding_enabled' => true,
        ])->assertRedirect();

        $this->assertEquals($customCat->id, $community->fresh()->introduction_category_id);
        $this->assertEquals($customCat->id, $community->fresh()->getIntroductionCategory()->id);

        // Member sees custom category name in checklist
        $this->actingAs($member);
        $this->get(route('communities.posts.index', $community))
            ->assertOk()
            ->assertSee('Welcome Newcomers');

        // Creator deletes the custom category
        $this->actingAs($creator);
        $this->delete(route('communities.categories.destroy', [$community, $customCat]))->assertRedirect();

        // Ensure introduction_category_id is now null
        $this->assertNull($community->fresh()->introduction_category_id);

        // Crucial test: routine reseed must NOT reassign or fallback to introductions!
        PostCategory::seedDefaultsFor($community->fresh());
        $this->assertNull($community->fresh()->introduction_category_id);
        $this->assertNull($community->fresh()->getIntroductionCategory());

        // Member views feed: task is unavailable and excluded from denominator
        $this->actingAs($member);
        $response = $this->get(route('communities.posts.index', $community));
        $response->assertOk();
        $response->assertSee('Introduce Yourself (Category Unavailable)');
        $response->assertDontSee('Welcome Newcomers');
        // Only 1 step in denominator (comment step), post step excluded
        $response->assertSee('0 of 1 steps completed');
    }
}
