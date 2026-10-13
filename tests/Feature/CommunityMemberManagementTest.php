<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\CommunityMembershipAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityMemberManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to create a membership safely using forceFill.
     */
    protected function createMembership(Community $community, User $user, string $status = 'ACTIVE'): CommunityMembership
    {
        $membership = new CommunityMembership;
        $membership->forceFill([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => $status,
        ])->save();

        return $membership;
    }

    public function test_creator_can_view_paginated_member_list_with_counts(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);

        $member1 = User::factory()->create(['name' => 'Alice Member']);
        $member2 = User::factory()->create(['name' => 'Bob Member']);

        $this->createMembership($community, $member1, 'ACTIVE');
        $this->createMembership($community, $member2, 'SUSPENDED');

        $response = $this->actingAs($creator)->get('/communities/'.$community->slug.'/members');

        $response->assertOk();
        $response->assertSee('Alice Member');
        $response->assertSee('Bob Member');
        $response->assertSee('Danh sách thành viên');
        $response->assertSee('Thay đổi gần nhất');
        $response->assertSee('name="reason"', false);
    }

    public function test_non_creator_cannot_view_or_manage_member_list(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);

        $activeMember = User::factory()->create();
        $this->createMembership($community, $activeMember, 'ACTIVE');

        $outsider = User::factory()->create();

        // Active member receives 404 (anti-enumeration)
        $this->actingAs($activeMember)
            ->get('/communities/'.$community->slug.'/members')
            ->assertNotFound();

        // Outsider receives 404
        $this->actingAs($outsider)
            ->get('/communities/'.$community->slug.'/members')
            ->assertNotFound();

        $this->actingAs($activeMember)
            ->post('/communities/'.$community->slug.'/members/'.$activeMember->memberships()->firstOrFail()->id.'/suspend', [
                'reason' => 'Unauthorized state change',
            ])
            ->assertNotFound();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);

        $this->get('/communities/'.$community->slug.'/members')
            ->assertRedirect('/login');
    }

    public function test_creator_can_suspend_active_member_and_blocked_from_access(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);

        $member = User::factory()->create();
        $membership = $this->createMembership($community, $member, 'ACTIVE');

        // Member initially has access
        $this->actingAs($member)->get('/communities/'.$community->slug)->assertOk();

        // Creator suspends member
        $response = $this->actingAs($creator)->post(
            '/communities/'.$community->slug.'/members/'.$membership->id.'/suspend',
            ['reason' => 'Violation of community guidelines']
        );

        $response->assertRedirect(route('communities.members.index', $community));
        $response->assertSessionHas('status', 'Member has been suspended.');

        $this->assertSame('SUSPENDED', $membership->refresh()->status);
        $this->assertDatabaseHas('community_membership_actions', [
            'actor_id' => $creator->id,
            'community_id' => $community->id,
            'membership_id' => $membership->id,
            'action' => 'SUSPEND',
            'previous_status' => 'ACTIVE',
            'new_status' => 'SUSPENDED',
            'reason' => 'Violation of community guidelines',
        ]);
        $this->actingAs($creator)
            ->get('/communities/'.$community->slug.'/members')
            ->assertOk()
            ->assertSee('Violation of community guidelines')
            ->assertSee('SUSPEND');

        // Member is immediately blocked from community access (receives 404)
        $this->actingAs($member)->get('/communities/'.$community->slug)->assertNotFound();
        $this->actingAs($member)->get('/communities/'.$community->slug.'/posts')->assertNotFound();
    }

    public function test_creator_can_reactivate_suspended_member_and_access_restored(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);

        $member = User::factory()->create();
        $membership = $this->createMembership($community, $member, 'SUSPENDED');

        // Initially blocked
        $this->actingAs($member)->get('/communities/'.$community->slug)->assertNotFound();

        // Creator reactivates member
        $response = $this->actingAs($creator)->post(
            '/communities/'.$community->slug.'/members/'.$membership->id.'/reactivate',
            ['reason' => 'Appeal accepted']
        );

        $response->assertRedirect(route('communities.members.index', $community));
        $response->assertSessionHas('status', 'Member has been reactivated.');

        $this->assertSame('ACTIVE', $membership->refresh()->status);
        $this->assertDatabaseHas('community_membership_actions', [
            'membership_id' => $membership->id,
            'action' => 'REACTIVATE',
            'previous_status' => 'SUSPENDED',
            'new_status' => 'ACTIVE',
            'reason' => 'Appeal accepted',
        ]);

        // Member access is fully restored
        $this->actingAs($member)->get('/communities/'.$community->slug)->assertOk();
    }

    public function test_creator_can_remove_member_and_blocked_permanently(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);

        $member = User::factory()->create();
        $membership = $this->createMembership($community, $member, 'ACTIVE');

        // Creator removes member
        $response = $this->actingAs($creator)->post(
            '/communities/'.$community->slug.'/members/'.$membership->id.'/remove',
            ['reason' => 'Membership ended by creator']
        );

        $response->assertRedirect(route('communities.members.index', $community));
        $response->assertSessionHas('status', 'Member has been removed from the community.');

        $this->assertSame('REMOVED', $membership->refresh()->status);

        // Member access is permanently blocked
        $this->actingAs($member)->get('/communities/'.$community->slug)->assertNotFound();

        // Attempting to reactivate a REMOVED member is rejected by domain transition
        $this->actingAs($creator)
            ->post('/communities/'.$community->slug.'/members/'.$membership->id.'/reactivate', [
                'reason' => 'Attempted invalid restoration',
            ])
            ->assertSessionHasErrors('membership');

        $this->assertDatabaseHas('community_membership_actions', [
            'membership_id' => $membership->id,
            'action' => 'REMOVE',
            'previous_status' => 'ACTIVE',
            'new_status' => 'REMOVED',
            'reason' => 'Membership ended by creator',
        ]);
        $this->assertSame(1, CommunityMembershipAction::where('membership_id', $membership->id)->count());
    }

    public function test_cross_community_member_mutation_is_rejected(): void
    {
        $creatorA = User::factory()->create();
        $communityA = Community::factory()->create(['creator_id' => $creatorA->id]);

        $creatorB = User::factory()->create();
        $communityB = Community::factory()->create(['creator_id' => $creatorB->id]);

        $memberB = User::factory()->create();
        $membershipB = $this->createMembership($communityB, $memberB, 'ACTIVE');

        // Creator A tries to mutate member belonging to Community B via Community A URL
        $this->actingAs($creatorA)
            ->post('/communities/'.$communityA->slug.'/members/'.$membershipB->id.'/suspend', [
                'reason' => 'Cross-community mutation',
            ])
            ->assertNotFound();

        $this->assertSame('ACTIVE', $membershipB->refresh()->status);
        $this->assertDatabaseCount('community_membership_actions', 0);
    }

    public function test_pending_payment_member_cannot_be_directly_activated_by_creator(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);

        $member = User::factory()->create();
        $membership = $this->createMembership($community, $member, 'PENDING_PAYMENT');

        // Creator attempts to reactivate member in PENDING_PAYMENT
        $this->actingAs($creator)
            ->post('/communities/'.$community->slug.'/members/'.$membership->id.'/reactivate', [
                'reason' => 'Do not bypass paid access',
            ])
            ->assertSessionHasErrors('membership');

        $this->assertSame('PENDING_PAYMENT', $membership->refresh()->status);
        $this->assertDatabaseCount('community_membership_actions', 0);
    }

    public function test_reason_is_required_and_blank_reason_does_not_change_state_or_create_audit(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);
        $membership = $this->createMembership($community, User::factory()->create(), 'ACTIVE');

        $this->actingAs($creator)
            ->post('/communities/'.$community->slug.'/members/'.$membership->id.'/suspend', [
                'reason' => '   ',
            ])
            ->assertSessionHasErrors('reason');

        $this->assertSame('ACTIVE', $membership->refresh()->status);
        $this->assertDatabaseCount('community_membership_actions', 0);
    }

    public function test_repeated_transition_is_rejected_without_duplicate_audit(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);
        $membership = $this->createMembership($community, User::factory()->create(), 'ACTIVE');
        $url = '/communities/'.$community->slug.'/members/'.$membership->id.'/suspend';

        $this->actingAs($creator)->post($url, ['reason' => 'First valid suspension'])->assertRedirect();
        $this->actingAs($creator)->post($url, ['reason' => 'Duplicate suspension'])->assertSessionHasErrors('membership');

        $this->assertSame('SUSPENDED', $membership->refresh()->status);
        $this->assertSame(1, CommunityMembershipAction::where('membership_id', $membership->id)->count());
    }

    public function test_inactive_community_rejects_transition_without_audit(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'SUSPENDED',
        ]);
        $membership = $this->createMembership($community, User::factory()->create(), 'ACTIVE');

        $this->actingAs($creator)
            ->post('/communities/'.$community->slug.'/members/'.$membership->id.'/suspend', [
                'reason' => 'Must not mutate inactive tenant',
            ])
            ->assertNotFound();

        $this->assertSame('ACTIVE', $membership->refresh()->status);
        $this->assertDatabaseCount('community_membership_actions', 0);
    }
}
