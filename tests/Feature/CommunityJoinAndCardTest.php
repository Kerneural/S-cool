<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\User;
use App\View\Components\SkoolCommunityCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CommunityJoinAndCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_skool_community_card_calculates_real_member_and_admin_count(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'name' => 'AI Automation Society',
            'slug' => 'ai-automation-society',
            'description' => 'Learn to get paid for AI solutions.',
        ]);

        // Create 3 active members
        $members = User::factory()->count(3)->create();
        foreach ($members as $member) {
            CommunityMembership::factory()->create([
                'community_id' => $community->id,
                'user_id' => $member->id,
                'status' => 'ACTIVE',
                'access_kind' => CommunityMembership::ACCESS_LIFETIME,
            ]);
        }

        // Add 1 pending payment member (must not be counted in real effective members)
        $pendingUser = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $pendingUser->id,
            'status' => 'PENDING_PAYMENT',
        ]);

        $component = new SkoolCommunityCard($community);

        // Real members count must be exactly 3 (non-mocked, real DB)
        $this->assertSame(3, $component->memberCount);
        $this->assertSame('3', $component->formattedMemberCount);
        $this->assertSame(1, $component->adminCount);

        // Format count helper tests
        $this->assertSame('474.7k', SkoolCommunityCard::formatCount(474700));
        $this->assertSame('1k', SkoolCommunityCard::formatCount(1000));
        $this->assertSame('1.5M', SkoolCommunityCard::formatCount(1500000));
        $this->assertSame('16', SkoolCommunityCard::formatCount(16));
    }

    public function test_skool_community_card_renders_s_cool_identity_across_all_community_tabs(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'name' => 'AI Automation Society',
            'slug' => 'ai-automation-society',
            'description' => 'Learn to get paid for AI solutions.',
        ]);
        // Live sessions are not presented as an "Online" count anywhere.
        DB::table('sessions')->insert([
            'id' => 'session_creator', 'user_id' => $creator->id, 'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit', 'payload' => serialize([]), 'last_activity' => now()->timestamp,
        ]);
        $removedPresentation = fn ($response) => $response->assertDontSee('skool.com')->assertDontSee('>Online<', false)
            ->assertDontSee('JOIN GROUP')->assertDontSee('#F5C347');

        $this->actingAs($creator);

        // Feed: compact card inside the collapsed Community details disclosure.
        $feedResponse = $this->get(route('communities.posts.index', $community))->assertOk()
            ->assertSeeInOrder(['Community details', 'AI Automation Society', 'Invitation-only community', 'Members', 'Admins', 'You host this community', 'About this community'])
            ->assertDontSee('Join community');
        $removedPresentation($feedResponse);

        // Classroom intentionally has no community card.
        $this->get(route('communities.classroom.index', $community))->assertOk()
            ->assertDontSee('Invitation-only community');

        foreach (['communities.events.index', 'communities.directory.index', 'communities.leaderboard'] as $tab) {
            $response = $this->get(route($tab, $community))->assertOk()
                ->assertSee('Invitation-only community')->assertSee('Members')->assertSee('You host this community')
                ->assertSee('href="'.route('communities.show', $community).'"', false);
            $removedPresentation($response);
        }
        $this->get(route('communities.leaderboard', $community))->assertOk()
            ->assertSee('Level 1 - AI Explorer')->assertSee('Level 2 - Automation Novice')
            ->assertSee('Level 9 - AI Grandmaster')->assertSee('Distribution not measured');

        // About consolidates identity into one context panel instead of repeating the card.
        $about = $this->get(route('communities.show', $community))->assertOk()
            ->assertSee('Active members')->assertSee('Creator host access');
        $removedPresentation($about);
        $this->assertSame(1, substr_count($about->getContent(), 'Learn to get paid for AI solutions.'));

        // Member sees the Joined state, never a join control.
        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
            'access_kind' => CommunityMembership::ACCESS_LIFETIME,
        ]);
        $this->actingAs($member)->get(route('communities.posts.index', $community))->assertOk()
            ->assertSee('Joined')->assertDontSee('Join community')->assertDontSee('You host this community');

        // A prospective member gets the teal sentence-case join control and the existing join form.
        $this->be(User::factory()->make(['id' => 9999]));
        $guestCardHtml = (string) $this->blade('<x-skool-community-card :community="$community" />', ['community' => $community]);
        $this->assertStringContainsString('Join community', $guestCardHtml);
        $this->assertStringContainsString('action="'.route('communities.join', $community).'"', $guestCardHtml);
        $this->assertStringContainsString('name="email"', $guestCardHtml);
        $this->assertStringContainsString('bg-brand', $guestCardHtml);
        foreach (['JOIN GROUP', 'skool.com', 'Online', 'uppercase'] as $removed) {
            $this->assertStringNotContainsString($removed, $guestCardHtml);
        }
    }

    public function test_card_never_invents_a_description(): void
    {
        $community = Community::factory()->create(['description' => null]);
        $this->be($community->creator);
        $html = (string) $this->withViewErrors([])->blade('<x-skool-community-card :community="$community" variant="compact" />', ['community' => $community]);
        $this->assertStringNotContainsString('Learn to get paid', $html);
        $this->assertStringContainsString('Invitation-only community', $html);
    }
}
