<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityInvitation;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ExpertLearnerEntryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('scool.deployment_mode', 'expert');
        Config::set('scool.owner_email', 'owner@example.com');
    }

    public function test_ac01_learner_with_zero_memberships_sees_welcome_state_and_no_community_names(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'email_verified_at' => now(),
        ]);

        $existingCommunity = Community::factory()->create([
            'creator_id' => $owner->id,
            'name' => 'Secret Enterprise Community',
            'status' => 'ACTIVE',
        ]);

        $learner = User::factory()->create([
            'email' => 'learner@example.com',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($learner)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('communities.welcome');
        $response->assertSee('Welcome to the learning platform');
        $response->assertDontSee($existingCommunity->name);
        $response->assertDontSee(route('communities.create'));
    }

    public function test_ac02_learner_with_one_effective_membership_redirects_to_feed(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'email_verified_at' => now(),
        ]);

        $community = Community::factory()->create([
            'creator_id' => $owner->id,
            'name' => 'Laravel Mastery',
            'status' => 'ACTIVE',
        ]);

        $learner = User::factory()->create([
            'email' => 'learner@example.com',
            'email_verified_at' => now(),
        ]);

        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $learner->id,
            'status' => 'ACTIVE',
            'expires_at' => null,
        ]);

        $response = $this->actingAs($learner)->get(route('dashboard'));

        $response->assertRedirect(route('communities.posts.index', $community));
    }

    public function test_ac03_chooser_shows_exact_memberships_and_never_leaks_unjoined_communities(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'email_verified_at' => now(),
        ]);

        $comm1 = Community::factory()->create([
            'creator_id' => $owner->id,
            'name' => 'Alpha Community',
            'status' => 'ACTIVE',
        ]);

        $comm2 = Community::factory()->create([
            'creator_id' => $owner->id,
            'name' => 'Beta Community',
            'status' => 'ACTIVE',
        ]);

        $unjoined = Community::factory()->create([
            'creator_id' => $owner->id,
            'name' => 'Gamma Unjoined Hidden',
            'status' => 'ACTIVE',
        ]);

        $learner = User::factory()->create([
            'email' => 'learner@example.com',
            'email_verified_at' => now(),
        ]);

        CommunityMembership::factory()->create([
            'community_id' => $comm1->id,
            'user_id' => $learner->id,
            'status' => 'ACTIVE',
        ]);

        CommunityMembership::factory()->create([
            'community_id' => $comm2->id,
            'user_id' => $learner->id,
            'status' => 'ACTIVE',
        ]);

        // Base request on /dashboard
        $response = $this->actingAs($learner)->get(route('dashboard'));
        $response->assertOk();
        $response->assertViewIs('communities.index');
        $response->assertSee('Alpha Community');
        $response->assertSee('Beta Community');
        $response->assertDontSee('Gamma Unjoined Hidden');
        $response->assertDontSee(route('communities.create'));

        // Request with filter=all
        $filterAllResponse = $this->actingAs($learner)->get('/communities?filter=all');
        $filterAllResponse->assertOk();
        $filterAllResponse->assertSee('Alpha Community');
        $filterAllResponse->assertSee('Beta Community');
        $filterAllResponse->assertDontSee('Gamma Unjoined Hidden');
        $this->assertSame(2, $filterAllResponse->viewData('communities')->total());

        // Search query targeting the unjoined community name
        $searchResponse = $this->actingAs($learner)->get('/communities?q=Gamma');
        $searchResponse->assertOk();
        $searchResponse->assertDontSee('Gamma Unjoined Hidden');
        $this->assertSame(0, $searchResponse->viewData('communities')->total());

        // Page 2 pagination test
        $page2Response = $this->actingAs($learner)->get('/communities?page=2');
        $page2Response->assertOk();
        $page2Response->assertDontSee('Gamma Unjoined Hidden');
    }

    public function test_ac04_suspended_or_expired_membership_disappears_from_routing_and_switcher(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'email_verified_at' => now(),
        ]);

        $activeComm = Community::factory()->create([
            'creator_id' => $owner->id,
            'name' => 'Active Space',
            'status' => 'ACTIVE',
        ]);

        $suspendedComm = Community::factory()->create([
            'creator_id' => $owner->id,
            'name' => 'Suspended Space',
            'status' => 'ACTIVE',
        ]);

        $learner = User::factory()->create([
            'email' => 'learner@example.com',
            'email_verified_at' => now(),
        ]);

        // Active membership in Active Space
        CommunityMembership::factory()->create([
            'community_id' => $activeComm->id,
            'user_id' => $learner->id,
            'status' => 'ACTIVE',
            'expires_at' => null,
        ]);

        // Suspended membership in Suspended Space
        CommunityMembership::factory()->create([
            'community_id' => $suspendedComm->id,
            'user_id' => $learner->id,
            'status' => 'SUSPENDED',
            'expires_at' => null,
        ]);

        // Request to /dashboard redirects to Active Space feed only
        $response = $this->actingAs($learner)->get(route('dashboard'));
        $response->assertRedirect(route('communities.posts.index', $activeComm));

        // When viewing Active Space, switcher dropdown does NOT list Suspended Space
        $feedResponse = $this->actingAs($learner)->get(route('communities.posts.index', $activeComm));
        $feedResponse->assertOk();
        $feedResponse->assertDontSee('Suspended Space');

        // Direct access to Suspended Space is denied with 404
        $this->actingAs($learner)->get(route('communities.posts.index', $suspendedComm))->assertNotFound();
    }

    public function test_ac05_guest_opening_invitation_registers_verifies_and_returns_to_invitation(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'email_verified_at' => now(),
        ]);

        $community = Community::factory()->create([
            'creator_id' => $owner->id,
            'name' => 'Invited Community',
            'status' => 'ACTIVE',
        ]);

        $invitation = CommunityInvitation::factory()->create([
            'community_id' => $community->id,
            'inviter_id' => $owner->id,
            'normalized_email' => 'newguest@example.com',
            'status' => 'PENDING',
        ]);

        // Step 1: Guest opens invitation page
        $guestResponse = $this->get(route('invitations.show', $invitation));
        $guestResponse->assertOk();
        $this->assertSame(route('invitations.show', $invitation), session('url.intended'));

        // Step 2: Guest registers new account
        $registerResponse = $this->post(route('register'), [
            'name' => 'New Guest',
            'email' => 'newguest@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $newLearner = User::where('email', 'newguest@example.com')->firstOrFail();
        $this->assertFalse($newLearner->hasVerifiedEmail());

        // Step 3: Learner verifies email using signed verification link
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $newLearner->getKey(), 'hash' => sha1($newLearner->getEmailForVerification())]
        );

        $verifyResponse = $this->actingAs($newLearner)->get($verificationUrl);

        // Assert user returns to the invitation page
        $verifyResponse->assertRedirect(route('invitations.show', $invitation));
    }

    public function test_ac06_permitted_about_link_shows_preview_without_granting_membership(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'email_verified_at' => now(),
        ]);

        $community = Community::factory()->create([
            'creator_id' => $owner->id,
            'name' => 'Public Preview Community',
            'status' => 'ACTIVE',
            'description' => 'Preview marketing description',
        ]);

        $course = Course::factory()->published()->create([
            'community_id' => $community->id,
            'title' => 'Sample Showcase Course',
        ]);

        $learner = User::factory()->create([
            'email' => 'learner@example.com',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($learner)->get(route('communities.about', $community));

        $response->assertOk();
        $response->assertViewIs('communities.preview');
        $response->assertSee('Public Preview Community');
        $response->assertSee('Preview marketing description');
        $response->assertSee('Sample Showcase Course');

        // Assert learner has not gained membership
        $this->assertDatabaseMissing('community_memberships', [
            'community_id' => $community->id,
            'user_id' => $learner->id,
        ]);

        // Assert protected feed access is still 404
        $this->actingAs($learner)->get(route('communities.posts.index', $community))->assertNotFound();
    }

    public function test_ac07_general_mode_discovery_behavior_remains_intact(): void
    {
        Config::set('scool.deployment_mode', 'general');

        $user = User::factory()->create();
        $community = Community::factory()->create([
            'name' => 'General Discoverable Hub',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('communities.index');
        $response->assertSee('General Discoverable Hub');
        $response->assertSee('Communities');
        $response->assertSee(route('communities.create'));
    }

    public function test_non_owner_cannot_access_or_submit_community_creation_in_expert_mode(): void
    {
        $learner = User::factory()->create([
            'email' => 'learner@example.com',
            'email_verified_at' => now(),
        ]);

        // GET /communities/create returns 403
        $this->actingAs($learner)->get(route('communities.create'))->assertForbidden();

        // POST /communities returns 403
        $this->actingAs($learner)->post(route('communities.store'), [
            'name' => 'Hacker Community',
            'slug' => 'hacker-community',
        ])->assertForbidden();
    }
}
