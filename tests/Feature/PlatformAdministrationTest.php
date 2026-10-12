<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\PlatformAdminAction;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_invalid_status_filters_fall_back_to_the_unfiltered_listing(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        Community::factory()->create(['name' => 'Synthetic active filter', 'status' => 'ACTIVE']);
        Community::factory()->create(['name' => 'Synthetic suspended filter', 'status' => 'SUSPENDED']);

        foreach ([['ACTIVE'], ['nested' => ['ACTIVE']], 'UNKNOWN', ''] as $status) {
            $this->actingAs($admin)->get(route('admin.communities.index', ['status' => $status]))
                ->assertOk()
                ->assertViewHas('statusFilter', null)
                ->assertSee('Synthetic active filter')
                ->assertSee('Synthetic suspended filter');
        }
        $this->assertDatabaseCount('platform_admin_actions', 0);
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

    public function test_profile_deletion_cannot_erase_admin_audit_history(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $community = Community::factory()->create();
        $this->actingAs($admin)->post(route('admin.communities.suspend', $community), [
            'reason' => 'Synthetic audit retention test',
        ])->assertSessionHasNoErrors();

        $this->from('/profile')->delete('/profile', ['password' => 'password'])
            ->assertRedirect('/profile')
            ->assertSessionHasErrorsIn('userDeletion', 'password');

        $this->assertNotNull($admin->fresh());
        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseCount('platform_admin_actions', 1);
        $this->assertSame('SUSPENDED', $community->fresh()->status);
    }

    public function test_listing_loads_only_the_latest_audit_action_per_community(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $community = Community::factory()->create();
        foreach (range(1, 4) as $number) {
            PlatformAdminAction::create([
                'admin_id' => $admin->id,
                'community_id' => $community->id,
                'action' => 'SUSPEND',
                'reason' => 'Synthetic history '.$number,
                'created_at' => now(),
            ]);
        }

        $this->actingAs($admin)->get(route('admin.communities.index'))
            ->assertOk()
            ->assertSee('Synthetic history 4')
            ->assertDontSee('Synthetic history 1')
            ->assertViewHas('communities', function ($communities) {
                $community = $communities->first();

                return $community->relationLoaded('latestPlatformAdminAction')
                    && ! $community->relationLoaded('platformAdminActions');
            });
    }

    public function test_fresh_seed_grants_only_the_demo_admin_role_and_rerun_never_promotes_existing_users(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@scool.local')->firstOrFail();
        $this->assertTrue($admin->isPlatformAdmin());
        $this->assertSame(1, User::where('is_platform_admin', true)->count());
        $admin->forceFill(['is_platform_admin' => false])->save();
        $attributes = $admin->fresh()->getAttributes();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($attributes, $admin->fresh()->getAttributes());
        $this->assertFalse($admin->fresh()->isPlatformAdmin());
    }

    public function test_unverified_admin_cannot_read_or_change_platform_state(): void
    {
        $admin = User::factory()->platformAdmin()->unverified()->create();
        $community = Community::factory()->create();
        $this->actingAs($admin)->get(route('admin.communities.index'))
            ->assertRedirect(route('verification.notice'));
        foreach (['suspend', 'reactivate'] as $action) {
            $this->post(route('admin.communities.'.$action, $community), ['reason' => 'Synthetic reason'])
                ->assertRedirect(route('verification.notice'));
        }
        $this->assertSame('ACTIVE', $community->fresh()->status);
        $this->assertDatabaseCount('platform_admin_actions', 0);
    }

    public function test_failed_state_transitions_and_invalid_reasons_create_no_audit_records(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $this->actingAs($admin);
        foreach (['ACTIVE' => 'suspend', 'SUSPENDED' => 'reactivate'] as $status => $action) {
            $community = Community::factory()->create(['status' => $status]);
            foreach (['', '   ', 'ab', str_repeat('x', 1001)] as $reason) {
                $this->post(route('admin.communities.'.$action, $community), ['reason' => $reason])
                    ->assertSessionHasErrors('reason');
                $this->assertSame($status, $community->fresh()->status);
                $this->assertDatabaseCount('platform_admin_actions', 0);
            }
        }
        $archived = Community::factory()->create(['status' => 'ARCHIVED']);
        foreach (['suspend', 'reactivate'] as $action) {
            $this->post(route('admin.communities.'.$action, $archived), ['reason' => 'Synthetic reason'])
                ->assertSessionHasErrors('community');
        }
        $this->assertSame('ARCHIVED', $archived->fresh()->status);
        $this->assertDatabaseCount('platform_admin_actions', 0);
    }

    public function test_duplicate_suspend_is_rejected_without_a_second_audit_record(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $community = Community::factory()->create();
        $this->actingAs($admin)->post(route('admin.communities.suspend', $community), [
            'reason' => 'Synthetic first request',
        ])->assertSessionHasNoErrors();
        $this->post(route('admin.communities.suspend', $community), [
            'reason' => 'Synthetic repeated request',
        ])->assertSessionHasErrors('community');
        $this->assertSame('SUSPENDED', $community->fresh()->status);
        $this->assertDatabaseCount('platform_admin_actions', 1);
    }

    public function test_admin_role_is_not_mass_assignable(): void
    {
        $user = User::factory()->create();
        $user->fill(['is_platform_admin' => true])->save();
        $this->assertFalse($user->fresh()->isPlatformAdmin());
    }

    public function test_forward_audit_retention_migration_preserves_rows_and_restricts_direct_deletion(): void
    {
        // RefreshDatabase is guarded by TestCase before this test uses scool_test.
        $admin = User::factory()->platformAdmin()->create();
        $community = Community::factory()->create();
        $this->actingAs($admin)->post(route('admin.communities.suspend', $community), [
            'reason' => 'Synthetic retained audit record',
        ])->assertSessionHasNoErrors();
        $original = PlatformAdminAction::firstOrFail()->getAttributes();

        $migration = require database_path('migrations/2026_10_12_010001_preserve_platform_admin_audit_records.php');
        // Simulate an installation that already ran the original CASCADE migration.
        $migration->down();
        $migration->up();
        $this->assertSame($original, PlatformAdminAction::firstOrFail()->getAttributes());

        foreach (['users' => $admin->id, 'communities' => $community->id] as $table => $id) {
            try {
                DB::table($table)->where('id', $id)->delete();
                $this->fail('Audit parent deletion must be rejected by the database.');
            } catch (QueryException $exception) {
                $this->assertSame(1451, (int) $exception->errorInfo[1]);
            }
            $this->assertDatabaseHas($table, ['id' => $id]);
            $this->assertDatabaseCount('platform_admin_actions', 1);
        }
        $this->assertSame($original, PlatformAdminAction::firstOrFail()->getAttributes());
    }
}
