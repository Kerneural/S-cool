<?php

namespace Tests\Feature;

use App\Models\CommunityInvitation;
use App\Models\CommunityMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MembershipInvitationSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_membership_defaults_fail_closed_and_relationships_work(): void
    {
        $membership = CommunityMembership::factory()->create();
        $this->assertSame('PENDING_PAYMENT', $membership->status);
        $this->assertTrue($membership->community->memberships->contains($membership));
        $this->assertTrue($membership->user->memberships->contains($membership));
        $invite = CommunityInvitation::factory()->create(['community_id' => $membership->community_id]);
        $this->assertSame($membership->community->creator_id, $invite->inviter_id);
        $this->assertTrue($invite->isPending());
        $this->assertArrayNotHasKey('token_hash', $invite->toArray());
    }

    public function test_same_user_can_belong_to_two_communities_but_not_duplicate_one(): void
    {
        $user = User::factory()->create();
        $first = CommunityMembership::factory()->active()->create(['user_id' => $user->id]);
        CommunityMembership::factory()->active()->create(['user_id' => $user->id]);
        $this->assertSame(2, $user->memberships()->count());
        $this->expectException(UniqueConstraintViolationException::class);
        CommunityMembership::factory()->create(['community_id' => $first->community_id, 'user_id' => $user->id]);
    }

    public function test_actual_membership_foreign_key_and_status_constraints(): void
    {
        $member = CommunityMembership::factory()->create();
        foreach ([['user_id' => 999999], ['community_id' => 999999], ['status' => 'INVALID']] as $invalid) {
            try {
                DB::table('community_memberships')->where('id', $member->id)->update($invalid);
                $this->fail('Invalid membership data was accepted by MySQL.');
            } catch (QueryException) {
                $this->assertSame('PENDING_PAYMENT', $member->refresh()->status);
            }
        }
    }

    public function test_invitation_hash_uniqueness_and_state_constraints(): void
    {
        $invite = CommunityInvitation::factory()->create();
        try {
            DB::table('community_invitations')->where('id', $invite->id)->update(['status' => 'INVALID']);
            $this->fail('Invalid invitation state was accepted.');
        } catch (QueryException) {
            $this->assertSame('PENDING', $invite->refresh()->status);
        }
        $this->expectException(UniqueConstraintViolationException::class);
        CommunityInvitation::factory()->create(['token_hash' => $invite->token_hash]);
    }

    public function test_state_transitions_lock_current_state_and_reject_terminal_or_paid_activation(): void
    {
        $member = CommunityMembership::factory()->active()->create();
        $stale = CommunityMembership::findOrFail($member->id);
        $member->transitionTo('SUSPENDED');
        $this->assertSame('SUSPENDED', $member->status);
        $member->transitionTo('ACTIVE');
        $member->transitionTo('REMOVED');
        $this->assertSame('REMOVED', $member->status);
        try {
            $stale->transitionTo('SUSPENDED');
            $this->fail('A stale model bypassed the current terminal state.');
        } catch (ValidationException) {
            $this->assertSame('REMOVED', $stale->refresh()->status);
        }
        foreach (['REMOVED', 'LEFT', 'PENDING_PAYMENT'] as $state) {
            $other = CommunityMembership::factory()->create(['status' => $state]);
            try {
                $other->transitionTo('ACTIVE');
                $this->fail('An unsafe activation was allowed.');
            } catch (ValidationException) {
                $this->assertSame($state, $other->refresh()->status);
            }
        }
    }

    public function test_membership_and_invitation_mass_assignment_is_guarded(): void
    {
        $member = CommunityMembership::factory()->create();
        $invite = CommunityInvitation::factory()->create();
        foreach ([$member, $invite] as $record) {
            $before = $record->getAttributes();
            try {
                $record->fill(['community_id' => 999999, 'status' => 'ACTIVE']);
            } catch (MassAssignmentException) {
                // Either silently discarded or rejected, never persisted.
            }
            $this->assertSame($before, $record->getAttributes());
        }
    }

    public function test_expiry_is_checked_even_without_scheduled_state_updates(): void
    {
        $this->freezeTime();
        $invite = CommunityInvitation::factory()->create(['expires_at' => now()->addSecond()]);
        $this->assertTrue($invite->isPending());
        $this->travel(1)->seconds();
        $this->assertFalse($invite->isPending());
        $this->assertSame('PENDING', $invite->status);
        $this->travelBack();
        $this->assertFalse(CommunityInvitation::factory()->revoked()->create()->isPending());
        $this->assertFalse(CommunityInvitation::factory()->accepted()->create()->isPending());
    }

    public function test_member_account_deletion_preserves_retained_records(): void
    {
        $member = CommunityMembership::factory()->left()->create();
        $this->actingAs($member->user)->from('/profile')->delete('/profile', ['password' => 'password'])
            ->assertRedirect('/profile')->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertAuthenticatedAs($member->user);
        $this->assertDatabaseHas('community_memberships', ['id' => $member->id, 'status' => 'LEFT']);
        $this->expectException(QueryException::class);
        $member->user->delete();
    }
}
