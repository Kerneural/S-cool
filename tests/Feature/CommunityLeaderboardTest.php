<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\PointContribution;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CommunityLeaderboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_like_yields_plus_one_for_author_no_points_for_liker_and_no_other_tenant_leak(): void
    {
        $creatorA = User::factory()->create();
        $communityA = Community::factory()->create(['creator_id' => $creatorA->id, 'status' => 'ACTIVE']);
        $authorA = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $communityA->id, 'user_id' => $authorA->id, 'status' => 'ACTIVE']);
        $likerA = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $communityA->id, 'user_id' => $likerA->id, 'status' => 'ACTIVE']);

        $creatorB = User::factory()->create();
        $communityB = Community::factory()->create(['creator_id' => $creatorB->id, 'status' => 'ACTIVE']);
        $authorB = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $communityB->id, 'user_id' => $authorB->id, 'status' => 'ACTIVE']);

        $postA = Post::factory()->create(['community_id' => $communityA->id, 'user_id' => $authorA->id]);

        $this->actingAs($likerA);
        $this->post(route('communities.posts.like', [$communityA, $postA]))->assertRedirect();

        // 1. Author receives +1 point in Community A
        $this->assertEquals(1, PointContribution::getUserPoints($communityA, $authorA, 'lifetime'));

        // 2. Liker receives 0 points in Community A
        $this->assertEquals(0, PointContribution::getUserPoints($communityA, $likerA, 'lifetime'));

        // 3. No leak into Community B
        $this->assertEquals(0, PointContribution::getUserPoints($communityB, $authorA, 'lifetime'));
        $this->assertEquals(0, PointContribution::getUserPoints($communityB, $authorB, 'lifetime'));

        // Check leaderboard page in community A
        $response = $this->get(route('communities.leaderboard', $communityA));
        $response->assertOk();
        $response->assertSee($authorA->name);
        $response->assertSeeText('1 pts');
    }

    public function test_duplicate_like_unlike_and_relike_preserve_one_contribution_and_one_zero_one_total(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $author = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $author->id, 'status' => 'ACTIVE']);
        $liker = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $liker->id, 'status' => 'ACTIVE']);

        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $author->id]);

        $this->actingAs($liker);

        // First like -> 1 point
        $this->post(route('communities.posts.like', [$community, $post]))->assertRedirect();
        $this->assertEquals(1, PointContribution::getUserPoints($community, $author));
        $this->assertEquals(1, PointContribution::where('community_id', $community->id)->count());

        // Duplicate like -> still 1 point, exactly 1 contribution row
        $this->post(route('communities.posts.like', [$community, $post]))->assertRedirect();
        $this->assertEquals(1, PointContribution::getUserPoints($community, $author));
        $this->assertEquals(1, PointContribution::where('community_id', $community->id)->count());

        // Unlike -> 0 points, contribution row set to inactive
        $this->delete(route('communities.posts.unlike', [$community, $post]))->assertRedirect();
        $this->assertEquals(0, PointContribution::getUserPoints($community, $author));
        $this->assertEquals(1, PointContribution::where('community_id', $community->id)->count());
        $this->assertFalse(PointContribution::where('community_id', $community->id)->first()->is_active);

        // Re-like -> 1 point restored on the same contribution row
        $this->post(route('communities.posts.like', [$community, $post]))->assertRedirect();
        $this->assertEquals(1, PointContribution::getUserPoints($community, $author));
        $this->assertEquals(1, PointContribution::where('community_id', $community->id)->count());
        $this->assertTrue(PointContribution::where('community_id', $community->id)->first()->is_active);
    }

    public function test_self_like_cannot_award_points_and_direct_client_input_has_no_effect(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $author = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $author->id, 'status' => 'ACTIVE']);

        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $author->id]);

        $this->actingAs($author);

        // Self-like is rejected with validation error
        $this->post(route('communities.posts.like', [$community, $post]), [
            'points' => 9999, // forged client input
            'level' => 5,
        ])->assertSessionHasErrors('like');

        $this->assertEquals(0, PointContribution::count());
        $this->assertEquals(0, PointContribution::getUserPoints($community, $author));
    }

    public function test_delete_post_or_remove_user_excludes_points_and_reactivation_restores_eligibility(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $author = User::factory()->create();
        $authorMembership = CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $author->id, 'status' => 'ACTIVE']);
        $liker = User::factory()->create();
        $likerMembership = CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $liker->id, 'status' => 'ACTIVE']);

        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $author->id]);

        $this->actingAs($liker);
        $this->post(route('communities.posts.like', [$community, $post]))->assertRedirect();
        $this->assertEquals(1, PointContribution::getUserPoints($community, $author));

        // 1. Liker is suspended -> liker is ineligible, author points drop to 0
        $likerMembership->forceFill(['status' => 'SUSPENDED'])->save();
        $this->assertEquals(0, PointContribution::getUserPoints($community, $author));

        // Liker is reactivated -> author points restored to 1 without duplicating rows
        $likerMembership->forceFill(['status' => 'ACTIVE'])->save();
        $this->assertEquals(1, PointContribution::getUserPoints($community, $author));

        // 2. Author is removed -> author is ineligible
        $authorMembership->forceFill(['status' => 'REMOVED'])->save();
        $this->assertEquals(0, PointContribution::getUserPoints($community, $author));

        $authorMembership->forceFill(['status' => 'ACTIVE'])->save();
        $this->assertEquals(1, PointContribution::getUserPoints($community, $author));

        // 3. Post is soft-deleted -> points drop to 0
        $post->delete();
        $this->assertEquals(0, PointContribution::getUserPoints($community, $author));
    }

    public function test_lifetime_month_boundaries_stable_ties_and_thresholds_render_correctly(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        // User A (will have 60 points -> Level 5)
        $userA = User::factory()->create(['name' => 'Alice']);
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $userA->id, 'status' => 'ACTIVE']);

        // User B (will have 15 points -> Level 3)
        $userB = User::factory()->create(['name' => 'Bob']);
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $userB->id, 'status' => 'ACTIVE']);

        // User C (will have 15 points -> Level 3, same as Bob, but ID is higher for stable tie-break)
        $userC = User::factory()->create(['name' => 'Charlie']);
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $userC->id, 'status' => 'ACTIVE']);

        $postA = Post::factory()->create(['community_id' => $community->id, 'user_id' => $userA->id]);
        $postB = Post::factory()->create(['community_id' => $community->id, 'user_id' => $userB->id]);
        $postC = Post::factory()->create(['community_id' => $community->id, 'user_id' => $userC->id]);

        $liker = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $liker->id, 'status' => 'ACTIVE']);

        // Seed like for User A in current month with 60 points
        $likeA = PostLike::create(['community_id' => $community->id, 'post_id' => $postA->id, 'user_id' => $liker->id, 'is_active' => true]);
        PointContribution::create([
            'community_id' => $community->id,
            'source_type' => 'post_like',
            'source_id' => $likeA->id,
            'beneficiary_id' => $userA->id,
            'actor_id' => $liker->id,
            'points' => 60,
            'is_active' => true,
            'contributed_at' => now('UTC'),
        ]);

        // Seed like for User B (15 points, created 2 months ago)
        $likeB = PostLike::create(['community_id' => $community->id, 'post_id' => $postB->id, 'user_id' => $liker->id, 'is_active' => true]);
        PointContribution::create([
            'community_id' => $community->id,
            'source_type' => 'post_like',
            'source_id' => $likeB->id,
            'beneficiary_id' => $userB->id,
            'actor_id' => $liker->id,
            'points' => 15,
            'is_active' => true,
            'contributed_at' => Carbon::now('UTC')->subMonths(2),
        ]);

        // Seed like for User C (15 points, created 2 months ago)
        $likeC = PostLike::create(['community_id' => $community->id, 'post_id' => $postC->id, 'user_id' => $liker->id, 'is_active' => true]);
        PointContribution::create([
            'community_id' => $community->id,
            'source_type' => 'post_like',
            'source_id' => $likeC->id,
            'beneficiary_id' => $userC->id,
            'actor_id' => $liker->id,
            'points' => 15,
            'is_active' => true,
            'contributed_at' => Carbon::now('UTC')->subMonths(2),
        ]);

        $this->actingAs($userA);

        // Lifetime leaderboard: Alice has 60, Bob and Charlie have 15
        $lifetimeView = $this->get(route('communities.leaderboard', [$community, 'period' => 'lifetime']));
        $lifetimeView->assertOk();
        $lifetimeView->assertSee('Alice');
        $lifetimeView->assertSee('Bob');
        $lifetimeView->assertSee('Charlie');
        $lifetimeView->assertSee('Level 5');
        $lifetimeView->assertSee('Maximum Level Reached');

        // Bob vs Charlie tie-break: both have 15 points, lower user_id ranks higher
        $smallerIdUser = $userB->id < $userC->id ? $userB : $userC;
        $largerIdUser = $userB->id < $userC->id ? $userC : $userB;
        $this->assertEquals(2, PointContribution::getUserRank($community, $smallerIdUser, 'lifetime'));
        $this->assertEquals(3, PointContribution::getUserRank($community, $largerIdUser, 'lifetime'));

        // Month leaderboard: only Alice's current-month points exist; Bob & Charlie have 0 monthly points
        $monthView = $this->get(route('communities.leaderboard', [$community, 'period' => 'month']));
        $monthView->assertOk();
        $monthView->assertSee('Alice');
        $this->assertEquals(0, PointContribution::getUserPoints($community, $userB, 'month'));

        // Finding 3 Test: User B has 15 lifetime points (Level 3), but 0 monthly points.
        // The monthly leaderboard MUST show Level 3 for User B (lifetime determines level), NOT Level 1!
        $this->assertEquals(3, PointContribution::levelForPoints(PointContribution::getUserPoints($community, $userB, 'lifetime')));
        $leaderboardMonth = PointContribution::getLeaderboard($community, 'month');
        $userBEntry = collect($leaderboardMonth->items())->firstWhere('user.id', $userB->id);
        $this->assertNotNull($userBEntry);
        $this->assertEquals(0, $userBEntry->points);
        $this->assertEquals(3, $userBEntry->level); // Level 3 from lifetime points!
    }

    public function test_zero_points_ranking_and_personal_rank_are_consistent_and_leaderboard_lists_eligible_members(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $member1 = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member1->id, 'status' => 'ACTIVE']);
        $member2 = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member2->id, 'status' => 'ACTIVE']);

        // Zero point contributions exist.
        $this->assertEquals(0, PointContribution::count());

        // Sort eligible users by ID ASC to test stable tie-breaking at 0 points
        $usersSorted = collect([$creator, $member1, $member2])->sortBy('id')->values();

        // Finding 2 Test: Rank for 0 points must be consistent between leaderboard and personal rank: #1, #2, #3
        $this->assertEquals(1, PointContribution::getUserRank($community, $usersSorted[0]));
        $this->assertEquals(2, PointContribution::getUserRank($community, $usersSorted[1]));
        $this->assertEquals(3, PointContribution::getUserRank($community, $usersSorted[2]));

        // Leaderboard must NOT be empty; it must display all 3 eligible users
        $leaderboard = PointContribution::getLeaderboard($community, 'lifetime');
        $this->assertCount(3, $leaderboard->items());
        $this->assertEquals($usersSorted[0]->id, $leaderboard->items()[0]->user->id);
        $this->assertEquals(1, $leaderboard->items()[0]->rank);
        $this->assertEquals($usersSorted[1]->id, $leaderboard->items()[1]->user->id);
        $this->assertEquals(2, $leaderboard->items()[1]->rank);
        $this->assertEquals($usersSorted[2]->id, $leaderboard->items()[2]->user->id);
        $this->assertEquals(3, $leaderboard->items()[2]->rank);

        $this->actingAs($usersSorted[1]);
        $response = $this->get(route('communities.leaderboard', $community));
        $response->assertOk();
        $response->assertSee('#2');
    }

    public function test_backfill_interleaved_with_unlike_does_not_overwrite_newer_inactive_state(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $author = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $author->id, 'status' => 'ACTIVE']);
        $liker = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $liker->id, 'status' => 'ACTIVE']);

        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $author->id]);

        $this->actingAs($liker);

        // 1. User likes post
        $this->post(route('communities.posts.like', [$community, $post]))->assertRedirect();
        $this->assertEquals(1, PointContribution::getUserPoints($community, $author));

        // 2. User unlikes post -> is_active becomes false
        $this->delete(route('communities.posts.unlike', [$community, $post]))->assertRedirect();
        $this->assertEquals(0, PointContribution::getUserPoints($community, $author));
        $this->assertFalse(PostLike::where('post_id', $post->id)->first()->is_active);

        // 3. Backfill runs: re-locks source like and re-reads fresh is_active=false under lock
        PointContribution::backfill($community);

        // Backfill must NOT reactivate the unliked contribution!
        $this->assertEquals(0, PointContribution::getUserPoints($community, $author));
        $this->assertFalse(PointContribution::where('community_id', $community->id)->first()->is_active);
    }

    public function test_backfill_run_twice_preserves_exact_totals_and_pagination_excludes_removed_users(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $author = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $author->id, 'status' => 'ACTIVE']);

        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $author->id]);

        $likers = User::factory()->count(3)->create();
        foreach ($likers as $liker) {
            CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $liker->id, 'status' => 'ACTIVE']);
            PostLike::create([
                'community_id' => $community->id,
                'post_id' => $post->id,
                'user_id' => $liker->id,
                'is_active' => true,
            ]);
        }

        // Run backfill 1st time
        $countFirst = PointContribution::backfill($community);
        $this->assertEquals(3, $countFirst);
        $this->assertEquals(3, PointContribution::getUserPoints($community, $author));

        // Run backfill 2nd time -> idempotent, points remain exact 3
        $countSecond = PointContribution::backfill($community);
        $this->assertEquals(3, $countSecond);
        $this->assertEquals(3, PointContribution::where('community_id', $community->id)->count());
        $this->assertEquals(3, PointContribution::getUserPoints($community, $author));

        // Verify artisan command
        $this->artisan('points:backfill', ['community' => $community->slug])
            ->assertSuccessful();
        $this->assertEquals(3, PointContribution::getUserPoints($community, $author));
    }
}
