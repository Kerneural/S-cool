<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ExpertOwnerModeTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Config::set('scool.deployment_mode', 'general');
        Config::set('scool.owner_email', null);
        parent::tearDown();
    }

    public function test_ac01_expert_mode_verified_owner_can_create_communities_and_is_creator(): void
    {
        Config::set('scool.deployment_mode', 'expert');
        Config::set('scool.owner_email', 'Owner@Example.Com');

        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'email_verified_at' => now(),
            'is_platform_admin' => false,
        ]);

        $this->assertTrue($owner->isDeploymentOwner());

        // Create first community
        $response1 = $this->actingAs($owner)->post(route('communities.store'), [
            'name' => 'First Expert Community',
            'slug' => 'first-expert-community',
            'description' => 'Test community 1',
        ]);

        $response1->assertRedirect();
        $this->assertDatabaseHas('communities', [
            'slug' => 'first-expert-community',
            'creator_id' => $owner->id,
        ]);

        // Create second community
        $response2 = $this->actingAs($owner)->post(route('communities.store'), [
            'name' => 'Second Expert Community',
            'slug' => 'second-expert-community',
            'description' => 'Test community 2',
        ]);

        $response2->assertRedirect();
        $this->assertDatabaseHas('communities', [
            'slug' => 'second-expert-community',
            'creator_id' => $owner->id,
        ]);
    }

    public function test_ac02_expert_mode_learner_and_verified_non_owner_receive_403_and_cannot_see_create_links(): void
    {
        Config::set('scool.deployment_mode', 'expert');
        Config::set('scool.owner_email', 'owner@example.com');

        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'email_verified_at' => now(),
        ]);

        $nonOwner = User::factory()->create([
            'email' => 'other-user@example.com',
            'email_verified_at' => now(),
            'is_platform_admin' => false,
        ]);

        $learner = User::factory()->create([
            'email' => 'learner@example.com',
            'email_verified_at' => now(),
            'is_platform_admin' => false,
        ]);

        // Non-owner gets 403 on create form & store
        $this->actingAs($nonOwner)
            ->get(route('communities.create'))
            ->assertForbidden();

        $this->actingAs($nonOwner)
            ->post(route('communities.store'), [
                'name' => 'Unauthorized Community',
                'slug' => 'unauthorized-community',
            ])
            ->assertForbidden();

        // Learner gets 403 on create form & store
        $this->actingAs($learner)
            ->get(route('communities.create'))
            ->assertForbidden();

        $this->actingAs($learner)
            ->post(route('communities.store'), [
                'name' => 'Learner Community',
                'slug' => 'learner-community',
            ])
            ->assertForbidden();

        // Create links are absent for non-owner and learner
        $responseIndex = $this->actingAs($nonOwner)->get(route('communities.index'));
        $responseIndex->assertDontSee(route('communities.create'));

        $responseOwned = $this->actingAs($nonOwner)->get(route('communities.index', ['filter' => 'owned']));
        $responseOwned->assertDontSee(route('communities.create'));
    }

    public function test_ac03_unverified_account_matching_owner_email_is_not_deployment_owner(): void
    {
        Config::set('scool.deployment_mode', 'expert');
        Config::set('scool.owner_email', 'owner@example.com');

        $unverified = User::factory()->create([
            'email' => 'owner@example.com',
            'email_verified_at' => null,
        ]);

        $this->assertFalse($unverified->isDeploymentOwner());

        // Unverified user attempting to create community is denied
        $response = $this->actingAs($unverified)->get(route('communities.create'));
        $response->assertForbidden();
    }

    public function test_ac04_expert_mode_owner_can_access_platform_admin_while_learners_and_legacy_admins_receive_403(): void
    {
        Config::set('scool.deployment_mode', 'expert');
        Config::set('scool.owner_email', 'owner@example.com');

        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'email_verified_at' => now(),
            'is_platform_admin' => false, // Owner does NOT need legacy is_platform_admin flag
        ]);

        $legacyAdmin = User::factory()->create([
            'email' => 'old-admin@example.com',
            'email_verified_at' => now(),
            'is_platform_admin' => true, // Legacy platform admin
        ]);

        $learner = User::factory()->create([
            'email' => 'learner@example.com',
            'email_verified_at' => now(),
            'is_platform_admin' => false,
        ]);

        // Owner opens /admin/communities without is_platform_admin
        $this->actingAs($owner)
            ->get(route('admin.communities.index'))
            ->assertOk();

        // In expert mode, legacy admin receives 403
        $this->actingAs($legacyAdmin)
            ->get(route('admin.communities.index'))
            ->assertForbidden();

        // Learner receives 403
        $this->actingAs($learner)
            ->get(route('admin.communities.index'))
            ->assertForbidden();
    }

    public function test_ac04_general_mode_retains_legacy_platform_admin_authorization(): void
    {
        Config::set('scool.deployment_mode', 'general');

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'is_platform_admin' => true,
        ]);

        $regularUser = User::factory()->create([
            'email' => 'regular@example.com',
            'email_verified_at' => now(),
            'is_platform_admin' => false,
        ]);

        // Platform admin accesses admin
        $this->actingAs($admin)
            ->get(route('admin.communities.index'))
            ->assertOk();

        // Regular user receives 403
        $this->actingAs($regularUser)
            ->get(route('admin.communities.index'))
            ->assertForbidden();
    }

    public function test_ac05_general_mode_verified_user_can_create_community(): void
    {
        Config::set('scool.deployment_mode', 'general');

        $user = User::factory()->create([
            'email' => 'user@example.com',
            'email_verified_at' => now(),
            'is_platform_admin' => false,
        ]);

        $this->actingAs($user)
            ->get(route('communities.create'))
            ->assertOk();

        $response = $this->actingAs($user)->post(route('communities.store'), [
            'name' => 'General Community',
            'slug' => 'general-community',
            'description' => 'Created in general mode',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('communities', [
            'slug' => 'general-community',
            'creator_id' => $user->id,
        ]);
    }

    public function test_ac06_expert_mode_empty_or_unmatched_owner_email_denies_creation_and_command_fails(): void
    {
        // 1. Empty owner email
        Config::set('scool.deployment_mode', 'expert');
        Config::set('scool.owner_email', '');

        $user = User::factory()->create([
            'email' => 'any@example.com',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('communities.store'), [
                'name' => 'Denied Community',
                'slug' => 'denied-community',
            ])
            ->assertForbidden();

        $this->artisan('scool:owner:check')
            ->expectsOutputToContain('SCOOL_OWNER_EMAIL is not configured or empty')
            ->assertExitCode(1);

        // 2. Unmatched owner email
        Config::set('scool.owner_email', 'missing@example.com');

        $this->artisan('scool:owner:check')
            ->expectsOutputToContain('Owner account not found')
            ->assertExitCode(1);
    }

    public function test_ac07_registration_and_profile_updates_cannot_set_mode_or_owner(): void
    {
        Config::set('scool.deployment_mode', 'expert');
        Config::set('scool.owner_email', 'legit-owner@example.com');

        // Attempt to escalate via registration
        $this->post('/register', [
            'name' => 'Attacker',
            'email' => 'attacker@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'deployment_mode' => 'general',
            'is_deployment_owner' => true,
            'owner_email' => 'attacker@example.com',
        ]);

        $user = User::where('email', 'attacker@example.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->isDeploymentOwner());
        $this->assertSame('expert', config('scool.deployment_mode'));

        // Attempt to escalate via profile update
        $user->forceFill(['email_verified_at' => now()])->save();

        $this->actingAs($user)->patch('/profile', [
            'name' => 'Attacker Updated',
            'email' => 'attacker@example.com',
            'deployment_mode' => 'general',
            'is_deployment_owner' => true,
            'owner_email' => 'attacker@example.com',
        ]);

        $user->refresh();
        $this->assertFalse($user->isDeploymentOwner());
    }

    public function test_ac08_invalid_deployment_mode_fails_closed_and_owner_check_command_fails(): void
    {
        // Set invalid mode (typo or unsupported value)
        Config::set('scool.deployment_mode', 'expertt');
        Config::set('scool.owner_email', 'owner@example.com');

        $user = User::factory()->create([
            'email' => 'owner@example.com',
            'email_verified_at' => now(),
            'is_platform_admin' => true,
        ]);

        // Policy fails closed
        $this->actingAs($user)
            ->post(route('communities.store'), [
                'name' => 'Fail Closed Community',
                'slug' => 'fail-closed-community',
            ])
            ->assertForbidden();

        // Middleware fails closed
        $this->actingAs($user)
            ->get(route('admin.communities.index'))
            ->assertForbidden();

        // Command fails with non-zero exit code
        $this->artisan('scool:owner:check')
            ->expectsOutputToContain('Invalid deployment mode')
            ->assertExitCode(1);
    }

    public function test_ac08_active_community_owned_by_another_creator_causes_owner_check_command_to_fail(): void
    {
        Config::set('scool.deployment_mode', 'expert');
        Config::set('scool.owner_email', 'owner@example.com');

        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'email_verified_at' => now(),
        ]);

        $otherCreator = User::factory()->create([
            'email' => 'other@example.com',
            'email_verified_at' => now(),
        ]);

        // Active community owned by someone else
        $community = Community::factory()->create([
            'creator_id' => $otherCreator->id,
            'status' => 'ACTIVE',
            'name' => 'Foreign Community',
            'slug' => 'foreign-community',
        ]);

        // Command detects mismatch and exits non-zero
        $this->artisan('scool:owner:check')
            ->expectsOutputToContain('active release community/communities not created by the deployment owner')
            ->assertExitCode(1);

        // When the community is archived, check passes
        $community->forceFill(['status' => 'ARCHIVED'])->save();

        $this->artisan('scool:owner:check')
            ->expectsOutputToContain('Expert deployment mode verified')
            ->assertExitCode(0);
    }
}
