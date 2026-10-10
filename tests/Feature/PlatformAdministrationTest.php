<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PlatformAdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_schema_has_is_platform_admin_and_platform_admin_actions_table(): void
    {
        $this->assertTrue(
            Schema::hasColumn('users', 'is_platform_admin'),
            'Column is_platform_admin missing on users table.'
        );

        $this->assertTrue(
            Schema::hasTable('platform_admin_actions'),
            'Table platform_admin_actions does not exist.'
        );

        $expectedColumns = [
            'id',
            'admin_id',
            'community_id',
            'action',
            'reason',
            'created_at',
        ];

        foreach ($expectedColumns as $col) {
            $this->assertTrue(
                Schema::hasColumn('platform_admin_actions', $col),
                "Column {$col} missing on platform_admin_actions table."
            );
        }
    }

    public function test_unauthenticated_and_regular_users_cannot_access_platform_admin(): void
    {
        // 1. Guest redirected to login
        $response = $this->get(route('admin.communities.index'));
        $response->assertRedirect(route('login'));

        // 2. Verified regular member gets 403 Forbidden
        $regularUser = User::factory()->create(['is_platform_admin' => false]);
        $response = $this->actingAs($regularUser)->get(route('admin.communities.index'));
        $response->assertForbidden();

        // 3. Community creator gets 403 Forbidden
        $creator = User::factory()->create(['is_platform_admin' => false]);
        $community = Community::factory()->create(['creator_id' => $creator->id]);

        $response = $this->actingAs($creator)->get(route('admin.communities.index'));
        $response->assertForbidden();

        $response = $this->actingAs($creator)->post(route('admin.communities.suspend', $community), [
            'reason' => 'Unauthorized attempt',
        ]);
        $response->assertForbidden();

        $response = $this->actingAs($creator)->post(route('admin.communities.reactivate', $community), [
            'reason' => 'Unauthorized attempt',
        ]);
        $response->assertForbidden();
    }

    public function test_platform_admin_can_view_communities_listing_and_filter_by_status(): void
    {
        $admin = User::factory()->platformAdmin()->create();

        $activeCommunity = Community::factory()->create(['name' => 'Active Comm', 'status' => 'ACTIVE']);
        $suspendedCommunity = Community::factory()->create(['name' => 'Suspended Comm', 'status' => 'SUSPENDED']);
        $archivedCommunity = Community::factory()->create(['name' => 'Archived Comm', 'status' => 'ARCHIVED']);

        // Default: see all
        $response = $this->actingAs($admin)->get(route('admin.communities.index'));
        $response->assertOk();
        $response->assertSee('Active Comm');
        $response->assertSee('Suspended Comm');
        $response->assertSee('Archived Comm');

        // Filter ACTIVE
        $response = $this->actingAs($admin)->get(route('admin.communities.index', ['status' => 'ACTIVE']));
        $response->assertOk();
        $response->assertSee('Active Comm');
        $response->assertDontSee('Suspended Comm');
        $response->assertDontSee('Archived Comm');

        // Filter SUSPENDED
        $response = $this->actingAs($admin)->get(route('admin.communities.index', ['status' => 'SUSPENDED']));
        $response->assertOk();
        $response->assertSee('Suspended Comm');
        $response->assertDontSee('Active Comm');
        $response->assertDontSee('Archived Comm');
    }

    public function test_platform_admin_can_suspend_active_community_with_mandatory_reason(): void
    {
        $admin = User::factory()->platformAdmin()->create(['name' => 'Root Admin']);
        $community = Community::factory()->create(['status' => 'ACTIVE']);

        $response = $this->actingAs($admin)->post(route('admin.communities.suspend', $community), [
            'reason' => 'Spam content and terms violation',
        ]);

        $response->assertRedirect(route('admin.communities.index'));
        $response->assertSessionHas('status');

        $this->assertEquals('SUSPENDED', $community->fresh()->status);

        $this->assertDatabaseHas('platform_admin_actions', [
            'admin_id' => $admin->id,
            'community_id' => $community->id,
            'action' => 'SUSPEND',
            'reason' => 'Spam content and terms violation',
        ]);
    }

    public function test_suspending_requires_mandatory_valid_reason(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $community = Community::factory()->create(['status' => 'ACTIVE']);

        // Missing reason
        $response = $this->actingAs($admin)->post(route('admin.communities.suspend', $community), []);
        $response->assertSessionHasErrors(['reason']);
        $this->assertEquals('ACTIVE', $community->fresh()->status);

        // Reason too short
        $response = $this->actingAs($admin)->post(route('admin.communities.suspend', $community), [
            'reason' => 'ab',
        ]);
        $response->assertSessionHasErrors(['reason']);
        $this->assertEquals('ACTIVE', $community->fresh()->status);
    }

    public function test_cannot_suspend_already_suspended_or_archived_community(): void
    {
        $admin = User::factory()->platformAdmin()->create();

        $suspendedCommunity = Community::factory()->create(['status' => 'SUSPENDED']);
        $response = $this->actingAs($admin)->post(route('admin.communities.suspend', $suspendedCommunity), [
            'reason' => 'Should fail',
        ]);
        $response->assertSessionHasErrors(['community']);

        $archivedCommunity = Community::factory()->create(['status' => 'ARCHIVED']);
        $response = $this->actingAs($admin)->post(route('admin.communities.suspend', $archivedCommunity), [
            'reason' => 'Should fail',
        ]);
        $response->assertSessionHasErrors(['community']);
    }

    public function test_suspended_community_denies_member_and_creator_access_across_modules(): void
    {
        $creator = User::factory()->create();
        $member = User::factory()->create();

        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'SUSPENDED',
        ]);

        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $course = Course::factory()->create([
            'community_id' => $community->id,
            'status' => 'PUBLISHED',
        ]);

        // 1. Creator cannot view or edit community
        $response = $this->actingAs($creator)->get(route('communities.show', $community));
        $response->assertNotFound();

        $response = $this->actingAs($creator)->get(route('communities.edit', $community));
        $response->assertNotFound();

        $response = $this->actingAs($creator)->put(route('communities.update', $community), [
            'name' => 'New Name',
        ]);
        $response->assertNotFound();

        // 2. Member cannot view community or classroom
        $response = $this->actingAs($member)->get(route('communities.show', $community));
        $response->assertNotFound();

        $response = $this->actingAs($member)->get(route('communities.classroom.index', $community));
        $response->assertNotFound();

        $response = $this->actingAs($member)->get(route('communities.courses.show', [$community, $course]));
        $response->assertNotFound();
    }

    public function test_platform_admin_can_reactivate_suspended_community(): void
    {
        $creator = User::factory()->create();
        $member = User::factory()->create();
        $admin = User::factory()->platformAdmin()->create();

        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'SUSPENDED',
        ]);

        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $course = Course::factory()->create([
            'community_id' => $community->id,
            'status' => 'PUBLISHED',
        ]);

        // Reactivate
        $response = $this->actingAs($admin)->post(route('admin.communities.reactivate', $community), [
            'reason' => 'Issue rectified by community creator',
        ]);

        $response->assertRedirect(route('admin.communities.index'));
        $response->assertSessionHas('status');

        $this->assertEquals('ACTIVE', $community->fresh()->status);

        $this->assertDatabaseHas('platform_admin_actions', [
            'admin_id' => $admin->id,
            'community_id' => $community->id,
            'action' => 'REACTIVATE',
            'reason' => 'Issue rectified by community creator',
        ]);

        // Access restored for creator and member without data loss!
        $this->actingAs($creator)->get(route('communities.show', $community))->assertOk();
        $this->actingAs($member)->get(route('communities.show', $community))->assertOk();
        $this->actingAs($member)->get(route('communities.courses.show', [$community, $course]))->assertOk();
    }

    public function test_cannot_reactivate_already_active_or_archived_community(): void
    {
        $admin = User::factory()->platformAdmin()->create();

        $activeCommunity = Community::factory()->create(['status' => 'ACTIVE']);
        $response = $this->actingAs($admin)->post(route('admin.communities.reactivate', $activeCommunity), [
            'reason' => 'Should fail',
        ]);
        $response->assertSessionHasErrors(['community']);

        $archivedCommunity = Community::factory()->create(['status' => 'ARCHIVED']);
        $response = $this->actingAs($admin)->post(route('admin.communities.reactivate', $archivedCommunity), [
            'reason' => 'Should fail',
        ]);
        $response->assertSessionHasErrors(['community']);
    }

    public function test_platform_admin_has_no_blanket_bypass_for_creator_actions(): void
    {
        $creator = User::factory()->create();
        $admin = User::factory()->platformAdmin()->create();

        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        // Platform admin cannot edit creator's community via standard creator routes
        $response = $this->actingAs($admin)->get(route('communities.edit', $community));
        $response->assertNotFound();

        $response = $this->actingAs($admin)->put(route('communities.update', $community), [
            'name' => 'Admin Hijacked Name',
        ]);
        $response->assertNotFound();
    }

    public function test_user_cannot_escalate_is_platform_admin_via_registration_or_profile_update(): void
    {
        // Registration attempt with is_platform_admin = 1
        $response = $this->post(route('register'), [
            'name' => 'Sneaky User',
            'email' => 'sneaky@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_platform_admin' => 1,
            'is_platform_admin' => true,
        ]);

        $user = User::where('email', 'sneaky@example.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->is_platform_admin);

        // Profile update attempt
        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Sneaky User 2',
            'email' => 'sneaky@example.com',
            'is_platform_admin' => 1,
        ]);

        $this->assertFalse($user->fresh()->is_platform_admin);
    }
}
