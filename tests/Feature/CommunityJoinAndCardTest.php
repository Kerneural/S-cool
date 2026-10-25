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

    public function test_skool_community_card_calculates_real_members_online_and_admin_count(): void
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

    public function test_skool_community_card_calculates_dynamic_online_count_from_sessions(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);

        $member1 = User::factory()->create();
        $member2 = User::factory()->create();

        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member1->id,
            'status' => 'ACTIVE',
            'access_kind' => CommunityMembership::ACCESS_LIFETIME,
        ]);
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member2->id,
            'status' => 'ACTIVE',
            'access_kind' => CommunityMembership::ACCESS_LIFETIME,
        ]);

        // Insert active session for member1 (active 2 mins ago)
        DB::table('sessions')->insert([
            'id' => 'session_m1',
            'user_id' => $member1->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => serialize(['dummy' => 'payload']),
            'last_activity' => now()->subMinutes(2)->timestamp,
        ]);

        // Insert stale session for member2 (active 30 mins ago - outside 15 min window)
        DB::table('sessions')->insert([
            'id' => 'session_m2',
            'user_id' => $member2->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => serialize(['dummy' => 'payload']),
            'last_activity' => now()->subMinutes(30)->timestamp,
        ]);

        $component = new SkoolCommunityCard($community);
        // Only member1 is currently online in the 15-minute window
        $this->assertSame(1, $component->onlineCount);

        // Now member2 becomes active (active now)
        DB::table('sessions')->where('id', 'session_m2')->update([
            'last_activity' => now()->timestamp,
        ]);

        $componentAfterActivity = new SkoolCommunityCard($community);
        // Dynamic online count updates to 2!
        $this->assertSame(2, $componentAfterActivity->onlineCount);
    }

    public function test_skool_community_card_renders_across_all_community_tabs(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'name' => 'AI Automation Society',
            'slug' => 'ai-automation-society',
            'description' => 'Learn to get paid for AI solutions.',
        ]);

        $this->actingAs($creator);

        // Tab 1: Community Feed (Creator view - no join button)
        $feedResponse = $this->get(route('communities.posts.index', $community));
        $feedResponse->assertOk();
        $feedResponse->assertSee('skool.com/ai-automation-society');
        $feedResponse->assertSee('Members');
        $feedResponse->assertSee('Online');
        $feedResponse->assertSee('Admins');
        $feedResponse->assertDontSee('JOIN GROUP');

        // Tab 2: Classroom (explicitly without the community card as requested)
        $classroomResponse = $this->get(route('communities.classroom.index', $community));
        $classroomResponse->assertOk();
        $classroomResponse->assertDontSee('skool.com/ai-automation-society');

        // Tab 3: Events
        $eventsResponse = $this->get(route('communities.events.index', $community));
        $eventsResponse->assertOk();
        $eventsResponse->assertSee('skool.com/ai-automation-society');
        $eventsResponse->assertSee('Members');

        // Tab 4: Directory / Members
        $directoryResponse = $this->get(route('communities.directory.index', $community));
        $directoryResponse->assertOk();
        $directoryResponse->assertSee('skool.com/ai-automation-society');
        $directoryResponse->assertSee('Members');

        // Tab 5: Leaderboard (includes card + 9-level Skool card)
        $leaderboardResponse = $this->get(route('communities.leaderboard', $community));
        $leaderboardResponse->assertOk();
        $leaderboardResponse->assertSee('skool.com/ai-automation-society');
        $leaderboardResponse->assertSee('Members');
        $leaderboardResponse->assertSee('Level 1 - AI Explorer');
        $leaderboardResponse->assertSee('Level 2 - Automation Novice');
        $leaderboardResponse->assertSee('Level 9 - AI Grandmaster');
        $leaderboardResponse->assertSee('Distribution not measured');

        // Tab 6: About
        $aboutResponse = $this->get(route('communities.show', $community));
        $aboutResponse->assertOk();
        $aboutResponse->assertSee('skool.com/ai-automation-society');
        $aboutResponse->assertSee('Members');

        // Member account viewing community sees JOINED badge, not JOIN GROUP
        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
            'access_kind' => CommunityMembership::ACCESS_LIFETIME,
        ]);
        $memberFeedResponse = $this->actingAs($member)->get(route('communities.posts.index', $community));
        $memberFeedResponse->assertOk();
        $memberFeedResponse->assertSee('JOINED');
        $memberFeedResponse->assertDontSee('JOIN GROUP');

        // Prospective member / guest rendering component directly sees JOIN GROUP button
        $this->be(User::factory()->make(['id' => 9999]));
        $guestCardHtml = (string) $this->blade('<x-skool-community-card :community="$community" />', ['community' => $community]);
        $this->assertStringContainsString('JOIN GROUP', $guestCardHtml);
        $this->assertStringContainsString('Members', $guestCardHtml);
        $this->assertStringContainsString('Online', $guestCardHtml);
        $this->assertStringContainsString('Admins', $guestCardHtml);
    }
}
