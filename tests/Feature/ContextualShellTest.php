<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Community;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContextualShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_contextual_shell_renders_on_community_routes(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'name' => 'Contextual Devs',
            'slug' => 'contextual-devs',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($creator);

        // Community Feed
        $response = $this->get(route('communities.posts.index', $community));
        $response->assertOk()
            ->assertSee('shell-community-switcher')
            ->assertSee('Contextual Devs')
            ->assertSee(route('communities.notifications.index', $community))
            ->assertSee(route('communities.messages.index', $community))
            ->assertSee(route('profile.edit'));

        // Community Leaderboard
        $leaderboardRes = $this->get(route('communities.leaderboard', $community));
        $leaderboardRes->assertOk()
            ->assertSee('shell-community-switcher')
            ->assertSee('Contextual Devs');

        // Community About
        $aboutRes = $this->get(route('communities.about', $community));
        $aboutRes->assertOk()
            ->assertSee('shell-community-switcher')
            ->assertSee('Contextual Devs');
    }

    public function test_global_shell_renders_on_general_and_account_routes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 1. Dashboard
        $dashRes = $this->get(route('dashboard'));
        $dashRes->assertOk()
            ->assertSee('Dashboard')
            ->assertSee(route('communities.index'))
            ->assertSee(route('help.index'))
            ->assertDontSee('shell-community-switcher');

        // 2. Communities index
        $commRes = $this->get(route('communities.index'));
        $commRes->assertOk()
            ->assertSee('Dashboard')
            ->assertSee(route('help.index'))
            ->assertDontSee('shell-community-switcher');

        // 3. Profile edit
        $profileRes = $this->get(route('profile.edit'));
        $profileRes->assertOk()
            ->assertSee('Dashboard')
            ->assertSee(route('help.index'))
            ->assertDontSee('shell-community-switcher');

        // 4. Account memberships
        $memberRes = $this->get(route('account.memberships.index'));
        $memberRes->assertOk()
            ->assertSee('Dashboard')
            ->assertSee(route('help.index'))
            ->assertDontSee('shell-community-switcher');
    }

    public function test_contextual_shell_displays_unread_notifications_badge(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($creator);

        // Initially 0 unread -> badge count not displayed
        $res1 = $this->get(route('communities.posts.index', $community));
        $res1->assertOk();

        // Create an unread notification for the user in this community
        $course = Course::factory()->create([
            'community_id' => $community->id,
            'status' => 'PUBLISHED',
        ]);
        AppNotification::createNotification(
            $creator->id,
            $community->id,
            'course_published',
            'course_published_'.$course->id,
            ['course_id' => $course->id],
        );

        $res2 = $this->get(route('communities.posts.index', $community));
        $res2->assertOk()
            ->assertSee('1'); // Unread notification badge
    }
}
