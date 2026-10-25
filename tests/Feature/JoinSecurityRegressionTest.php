<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\User;
use Database\Seeders\RealCommunitySampleSeeder;
use Database\Seeders\RealFreeCommunitySeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JoinSecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_authenticate_by_submitting_an_existing_email(): void
    {
        $victim = User::factory()->create();
        $community = Community::factory()->create();
        $this->post(route('communities.join', $community), ['email' => $victim->email])
            ->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseMissing('community_memberships', ['user_id' => $victim->id]);
    }

    public function test_private_and_paid_communities_require_invitation_and_checkout(): void
    {
        $member = User::factory()->create();
        foreach (['FREE', 'PAID'] as $mode) {
            $community = Community::factory()->create(['access_mode' => $mode]);
            $this->actingAs($member)->post(route('communities.join', $community), ['email' => $member->email])
                ->assertSessionHasErrors('email');
            $this->assertSame(0, $community->memberships()->count());
        }
    }

    public function test_join_cannot_restore_suspended_pending_or_expired_access(): void
    {
        $member = User::factory()->create();
        foreach (['SUSPENDED', 'PENDING_PAYMENT', 'ACTIVE'] as $status) {
            $community = Community::factory()->create();
            $membership = CommunityMembership::factory()->create([
                'community_id' => $community->id, 'user_id' => $member->id,
                'status' => $status, 'access_kind' => CommunityMembership::ACCESS_TERM,
                'expires_at' => now()->subDay(),
            ]);
            $before = $membership->fresh()->getAttributes();
            $this->actingAs($member)->post(route('communities.join', $community), ['email' => $member->email])
                ->assertSessionHasErrors('email');
            $this->assertSame($before, $membership->fresh()->getAttributes());
        }
    }

    public function test_free_catalog_preserves_existing_applied_catalog_content_and_access(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(RealCommunitySampleSeeder::class);
        $community = Community::where('slug', 'ai-video-bootcamp')->firstOrFail();
        $course = $community->courses()->firstOrFail();
        $lesson = $course->lessons()->firstOrFail();
        $lesson->forceFill(['content' => 'Retained learner edits'])->save();
        $membership = $community->memberships()->firstOrFail();
        $membership->forceFill(['status' => 'SUSPENDED'])->save();
        $this->seed(RealFreeCommunitySeeder::class);
        $this->seed(RealFreeCommunitySeeder::class);
        $this->assertNotNull($community->fresh());
        $this->assertSame('Retained learner edits', $lesson->fresh()->content);
        $this->assertSame('SUSPENDED', $membership->fresh()->status);
        $this->assertDatabaseHas('communities', ['slug' => 'ai-automation-society']);
    }
}
