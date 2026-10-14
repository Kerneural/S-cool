<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityEventCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_near_utc_midnight_appears_on_correct_day_in_display_timezone_and_details_show_original_zone(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        // Event starting Oct 15 at 23:30 UTC. In Asia/Ho_Chi_Minh (+7), this is Oct 16 at 06:30.
        $event = Event::factory()->create([
            'community_id' => $community->id,
            'creator_id' => $creator->id,
            'title' => 'Near Midnight Global Session',
            'starts_at' => Carbon::createFromFormat('Y-m-d H:i:s', '2026-10-15 23:30:00', 'UTC'),
            'ends_at' => Carbon::createFromFormat('Y-m-d H:i:s', '2026-10-16 01:00:00', 'UTC'),
            'timezone' => 'America/New_York',
            'status' => 'SCHEDULED',
        ]);

        $this->actingAs($member);

        // 1. Month grid view in Asia/Ho_Chi_Minh
        $monthResponse = $this->get(route('communities.events.index', [
            'community' => $community,
            'view' => 'month',
            'month' => '2026-10',
            'display_timezone' => 'Asia/Ho_Chi_Minh',
        ]));

        $monthResponse->assertOk();
        $grid = $monthResponse->viewData('calendarGrid');

        // Locate cell for day 15 and day 16
        $day15Events = collect();
        $day16Events = collect();
        foreach ($grid as $week) {
            foreach ($week as $dayData) {
                if ($dayData !== null) {
                    if ($dayData['day'] === 15) {
                        $day15Events = $dayData['events'];
                    } elseif ($dayData['day'] === 16) {
                        $day16Events = $dayData['events'];
                    }
                }
            }
        }

        // Must appear on Oct 16, NOT Oct 15 in Asia/Ho_Chi_Minh
        $this->assertTrue($day16Events->contains('id', $event->id));
        $this->assertFalse($day15Events->contains('id', $event->id));

        // 2. List view in Asia/Ho_Chi_Minh
        $listResponse = $this->get(route('communities.events.index', [
            'community' => $community,
            'view' => 'list',
            'month' => '2026-10',
            'display_timezone' => 'Asia/Ho_Chi_Minh',
        ]));

        $listResponse->assertOk()
            ->assertSee('Near Midnight Global Session')
            ->assertSee('Oct 16, 2026')
            ->assertSee('06:30 - 08:00 (Asia/Ho_Chi_Minh)')
            ->assertSee('Original: America/New_York');

        // 3. Event details view shows original timezone
        $showResponse = $this->get(route('communities.events.show', [$community, $event]));
        $showResponse->assertOk()
            ->assertSee('Timezone: America/New_York');
    }

    public function test_next_previous_month_and_list_switching_preserve_tenant_and_timezone(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($member);

        $response = $this->get(route('communities.events.index', [
            'community' => $community,
            'view' => 'month',
            'month' => '2026-10',
            'display_timezone' => 'Asia/Tokyo',
        ]));

        $response->assertOk();
        $this->assertSame('2026-09', $response->viewData('prevMonth'));
        $this->assertSame('2026-11', $response->viewData('nextMonth'));
        $this->assertSame('Asia/Tokyo', $response->viewData('displayTimezone'));

        // Next month URL preserves Asia/Tokyo
        $nextMonthUrl = route('communities.events.index', [
            'community' => $community,
            'month' => '2026-11',
            'view' => 'month',
            'display_timezone' => 'Asia/Tokyo',
        ]);
        $response->assertSee($nextMonthUrl);

        // Previous month URL preserves Asia/Tokyo
        $prevMonthUrl = route('communities.events.index', [
            'community' => $community,
            'month' => '2026-09',
            'view' => 'month',
            'display_timezone' => 'Asia/Tokyo',
        ]);
        $response->assertSee($prevMonthUrl);

        // Switch to List view preserves 2026-10 and Asia/Tokyo
        $listViewUrl = route('communities.events.index', [
            'community' => $community,
            'view' => 'list',
            'month' => '2026-10',
            'display_timezone' => 'Asia/Tokyo',
        ]);
        $response->assertSee($listViewUrl);
    }

    public function test_overlapping_event_is_included_in_both_months_and_invalid_month_or_zone_is_rejected(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        // Event spanning across Oct 31 and Nov 1 (UTC)
        $spanningEvent = Event::factory()->create([
            'community_id' => $community->id,
            'creator_id' => $creator->id,
            'title' => 'Halloween Spanning Midnight Party',
            'starts_at' => Carbon::createFromFormat('Y-m-d H:i:s', '2026-10-31 22:00:00', 'UTC'),
            'ends_at' => Carbon::createFromFormat('Y-m-d H:i:s', '2026-11-01 02:00:00', 'UTC'),
            'timezone' => 'UTC',
        ]);

        $this->actingAs($member);

        // Included in October
        $octResp = $this->get(route('communities.events.index', [
            'community' => $community,
            'view' => 'month',
            'month' => '2026-10',
            'display_timezone' => 'UTC',
        ]));
        $octResp->assertOk();
        $this->assertTrue($octResp->viewData('events')->contains('id', $spanningEvent->id));

        // Included in November
        $novResp = $this->get(route('communities.events.index', [
            'community' => $community,
            'view' => 'month',
            'month' => '2026-11',
            'display_timezone' => 'UTC',
        ]));
        $novResp->assertOk();
        $this->assertTrue($novResp->viewData('events')->contains('id', $spanningEvent->id));

        // Invalid month format rejected
        $badMonthResp = $this->get(route('communities.events.index', [
            'community' => $community,
            'month' => '2026-13',
        ]));
        $badMonthResp->assertSessionHasErrors('month');

        // Invalid display timezone rejected
        $badTzResp = $this->get(route('communities.events.index', [
            'community' => $community,
            'display_timezone' => 'Invalid/Zone_Name',
        ]));
        $badTzResp->assertSessionHasErrors('display_timezone');

        // Invalid view rejected
        $badViewResp = $this->get(route('communities.events.index', [
            'community' => $community,
            'view' => 'unsupported_calendar_mode',
        ]));
        $badViewResp->assertSessionHasErrors('view');
    }

    public function test_cancelled_event_omits_meeting_url_and_outsider_or_removed_member_receives_404(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $suspendedMember = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $suspendedMember->id,
            'status' => 'SUSPENDED',
        ]);

        $outsider = User::factory()->create();

        $cancelledEvent = Event::factory()->create([
            'community_id' => $community->id,
            'creator_id' => $creator->id,
            'title' => 'Cancelled Workshop Session',
            'meeting_url' => 'https://meet.example.com/secret-room-123',
            'status' => 'CANCELLED',
        ]);

        // Active member sees Cancelled badge but meeting link is omitted
        $this->actingAs($member);
        $memberResp = $this->get(route('communities.events.index', [
            'community' => $community,
            'view' => 'list',
        ]));
        $memberResp->assertOk()
            ->assertSee('CANCELLED')
            ->assertDontSee('https://meet.example.com/secret-room-123')
            ->assertDontSee('Join Meeting');

        // Outsider -> 404
        $this->actingAs($outsider);
        $this->get(route('communities.events.index', $community))->assertNotFound();

        // Suspended member -> 404
        $this->actingAs($suspendedMember);
        $this->get(route('communities.events.index', $community))->assertNotFound();
    }

    public function test_month_view_with_more_than_100_events_caps_grid_and_renders_overflow_warning_with_list_link(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        // Bulk insert 105 events in the same month (Oct 2026)
        $eventsData = [];
        $baseDate = Carbon::createFromFormat('Y-m-d H:i:s', '2026-10-05 10:00:00', 'UTC');
        for ($i = 1; $i <= 105; $i++) {
            $eventsData[] = [
                'community_id' => $community->id,
                'creator_id' => $creator->id,
                'title' => "Batch Event #{$i}",
                'description' => "Description for event {$i}",
                'starts_at' => $baseDate->copy()->addMinutes($i * 10)->format('Y-m-d H:i:s'),
                'ends_at' => $baseDate->copy()->addMinutes($i * 10 + 30)->format('Y-m-d H:i:s'),
                'timezone' => 'Asia/Ho_Chi_Minh',
                'status' => 'SCHEDULED',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        Event::insert($eventsData);

        $this->actingAs($member);

        $response = $this->get(route('communities.events.index', [
            'community' => $community,
            'view' => 'month',
            'month' => '2026-10',
            'display_timezone' => 'Asia/Ho_Chi_Minh',
        ]));

        $response->assertOk();
        $this->assertTrue($response->viewData('hasMoreEvents'));

        // Distinct events passed to view are capped at 100
        $this->assertCount(100, $response->viewData('events'));

        // Warning message and switch to list link are present
        $response->assertSee('This month has more than 100 events. The calendar grid displays the first 100 events.');
        $response->assertSee(route('communities.events.index', [
            'community' => $community,
            'view' => 'list',
            'month' => '2026-10',
            'display_timezone' => 'Asia/Ho_Chi_Minh',
        ]));
    }
}
