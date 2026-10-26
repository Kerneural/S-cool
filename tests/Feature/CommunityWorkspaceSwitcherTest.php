<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityWorkspaceSwitcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_header_renders_on_feed_classroom_events_directory_and_about(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'name' => 'Design Masters',
            'slug' => 'design-masters',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($creator);

        // Feed
        $feedResponse = $this->get(route('communities.posts.index', $community));
        $feedResponse->assertOk()
            ->assertSee('Design Masters')
            ->assertSee('ACTIVE')
            ->assertSee('Community Feed')
            ->assertSee('Classroom')
            ->assertSee('Events')
            ->assertSee('Members')
            ->assertSee('About');

        // Classroom
        $classroomResponse = $this->get(route('communities.classroom.index', $community));
        $classroomResponse->assertOk()
            ->assertSee('Design Masters')
            ->assertSee('ACTIVE')
            ->assertSee('Classroom');

        // Events
        $eventsResponse = $this->get(route('communities.events.index', $community));
        $eventsResponse->assertOk()
            ->assertSee('Design Masters')
            ->assertSee('ACTIVE')
            ->assertSee('Events');

        // Directory
        $directoryResponse = $this->get(route('communities.directory.index', $community));
        $directoryResponse->assertOk()
            ->assertSee('Design Masters')
            ->assertSee('ACTIVE')
            ->assertSee('Members');

        // About / Show
        $aboutResponse = $this->get(route('communities.show', $community));
        $aboutResponse->assertOk()
            ->assertSee('Design Masters')
            ->assertSee('ACTIVE')
            ->assertSee('About');
    }

    public function test_switcher_dropdown_lists_user_accessible_active_communities_linking_to_feed(): void
    {
        $user = User::factory()->create();

        $communityA = Community::factory()->create([
            'creator_id' => $user->id,
            'name' => 'Community Alpha',
            'slug' => 'community-alpha',
            'status' => 'ACTIVE',
        ]);

        $otherCreator = User::factory()->create();
        $communityB = Community::factory()->create([
            'creator_id' => $otherCreator->id,
            'name' => 'Community Beta',
            'slug' => 'community-beta',
            'status' => 'ACTIVE',
        ]);
        CommunityMembership::factory()->create([
            'community_id' => $communityB->id,
            'user_id' => $user->id,
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($user);

        // Visiting Community A shows Community B in switcher with link to B's feed
        $response = $this->get(route('communities.posts.index', $communityA));
        $response->assertOk()
            ->assertSee('Switch Community')
            ->assertSee('Community Beta')
            ->assertSee(route('communities.posts.index', $communityB));

        // Visiting Community B shows Community A in switcher with link to A's feed
        $responseB = $this->get(route('communities.posts.index', $communityB));
        $responseB->assertOk()
            ->assertSee('Switch Community')
            ->assertSee('Community Alpha')
            ->assertSee(route('communities.posts.index', $communityA));
    }

    public function test_dashboard_supports_filter_and_search_with_deduplication(): void
    {
        $user = User::factory()->create();

        // Owned community
        $owned = Community::factory()->create([
            'creator_id' => $user->id,
            'name' => 'PHP Artisans Guild',
            'slug' => 'php-artisans',
            'status' => 'ACTIVE',
        ]);

        // Joined community
        $otherUser = User::factory()->create();
        $joined = Community::factory()->create([
            'creator_id' => $otherUser->id,
            'name' => 'Vue Developers Club',
            'slug' => 'vue-devs',
            'status' => 'ACTIVE',
        ]);
        CommunityMembership::factory()->create([
            'community_id' => $joined->id,
            'user_id' => $user->id,
            'status' => 'ACTIVE',
        ]);

        // Unrelated community
        $unrelated = Community::factory()->create([
            'creator_id' => $otherUser->id,
            'name' => 'Secret Python Den',
            'slug' => 'python-den',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($user);

        // Filter: All discovers active community metadata, including unrelated communities.
        $allResponse = $this->get(route('communities.index', ['filter' => 'all']));
        $allResponse->assertOk()
            ->assertSee('PHP Artisans Guild')
            ->assertSee('Vue Developers Club')
            ->assertSee('Secret Python Den')->assertSee('View community');

        // Filter: Owned (shows only owned)
        $ownedResponse = $this->get(route('communities.index', ['filter' => 'owned']));
        $ownedResponse->assertOk()
            ->assertSee('PHP Artisans Guild')
            ->assertDontSee('Vue Developers Club')
            ->assertDontSee('Secret Python Den');

        // Filter: Joined (shows only joined)
        $joinedResponse = $this->get(route('communities.index', ['filter' => 'joined']));
        $joinedResponse->assertOk()
            ->assertSee('Vue Developers Club')
            ->assertDontSee('PHP Artisans Guild')
            ->assertDontSee('Secret Python Den');

        // Search: ?q=Vue
        $searchResponse = $this->get(route('communities.index', ['q' => 'Vue']));
        $searchResponse->assertOk()
            ->assertSee('Vue Developers Club')
            ->assertDontSee('PHP Artisans Guild');

        // Search with no match shows empty search message
        $noMatchResponse = $this->get(route('communities.index', ['q' => 'NonExistentKeyword']));
        $noMatchResponse->assertOk()
            ->assertSee('No communities found matching "NonExistentKeyword"');
    }

    public function test_inactive_owned_community_shows_in_owned_filter_but_excluded_from_member_switcher(): void
    {
        $creator = User::factory()->create();
        $member = User::factory()->create();

        // Active community
        $activeComm = Community::factory()->create([
            'creator_id' => $creator->id,
            'name' => 'Active Space',
            'slug' => 'active-space',
            'status' => 'ACTIVE',
        ]);
        CommunityMembership::factory()->create([
            'community_id' => $activeComm->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        // Inactive community owned by creator
        $suspendedComm = Community::factory()->create([
            'creator_id' => $creator->id,
            'name' => 'Suspended Space',
            'slug' => 'suspended-space',
            'status' => 'SUSPENDED',
        ]);
        CommunityMembership::factory()->create([
            'community_id' => $suspendedComm->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        // Owned filter retains the creator's inactive community and status badge.
        $this->actingAs($creator);
        $creatorDash = $this->get(route('communities.index', ['filter' => 'owned']));
        $creatorDash->assertOk()
            ->assertSee('Suspended Space')
            ->assertSee('SUSPENDED');

        // Switcher for member in Active Space does NOT list the suspended space
        $this->actingAs($member);
        $memberFeed = $this->get(route('communities.posts.index', $activeComm));
        $memberFeed->assertOk()
            ->assertDontSee('Suspended Space');
    }

    public function test_direct_url_access_to_inaccessible_community_returns_404(): void
    {
        $creator = User::factory()->create();
        $outsider = User::factory()->create();

        $privateCommunity = Community::factory()->create([
            'creator_id' => $creator->id,
            'name' => 'Private Vault',
            'slug' => 'private-vault',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($outsider);

        $this->get(route('communities.show', $privateCommunity))->assertNotFound();
        $this->get(route('communities.posts.index', $privateCommunity))->assertNotFound();
        $this->get(route('communities.classroom.index', $privateCommunity))->assertNotFound();
        $this->get(route('communities.events.index', $privateCommunity))->assertNotFound();
        $this->get(route('communities.directory.index', $privateCommunity))->assertNotFound();
    }

    public function test_community_index_safely_handles_array_and_oversize_filter_and_search_inputs(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $user->id,
            'name' => 'Tech Talks',
            'slug' => 'tech-talks',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($user);

        // Array filter and search inputs: ?filter[]=hack&q[]=test
        $arrayResponse = $this->get(route('communities.index', [
            'filter' => ['malicious', 'array'],
            'q' => ['nested' => 'array'],
        ]));
        $arrayResponse->assertOk()
            ->assertSee('Tech Talks');

        // Invalid string filter and oversize search query: 200 chars
        $oversizeResponse = $this->get(route('communities.index', [
            'filter' => 'non_existent_filter',
            'q' => str_repeat('k', 200),
        ]));
        $oversizeResponse->assertOk();
    }
}
