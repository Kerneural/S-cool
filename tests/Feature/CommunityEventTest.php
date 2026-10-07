<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_creator_can_create_event_and_view_in_calendar(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);

        $response = $this->actingAs($creator)->post("/communities/{$community->slug}/events", [
            'title' => 'Weekly Mastermind Call',
            'description' => 'Discussion on community progress',
            'timezone' => 'Asia/Ho_Chi_Minh',
            'starts_at' => '2026-10-15 14:00',
            'ends_at' => '2026-10-15 15:30',
            'meeting_url' => 'https://meet.google.com/abc-defg-hij',
        ]);

        $response->assertRedirect("/communities/{$community->slug}/events");

        $event = Event::first();
        $this->assertNotNull($event);
        $this->assertSame('Weekly Mastermind Call', $event->title);
        $this->assertSame('Asia/Ho_Chi_Minh', $event->timezone);
        $this->assertSame('SCHEDULED', $event->status);

        // Verify UTC conversion: 14:00 in +07:00 is 07:00 UTC
        $this->assertSame('2026-10-15 07:00:00', $event->starts_at->toDateTimeString());
        $this->assertSame('2026-10-15 08:30:00', $event->ends_at->toDateTimeString());

        // Creator views calendar
        $this->actingAs($creator)->get("/communities/{$community->slug}/events")
            ->assertOk()
            ->assertSee('Weekly Mastermind Call')
            ->assertSee('Asia/Ho_Chi_Minh')
            ->assertSee('14:00')
            ->assertSee('15:30')
            ->assertSee('https://meet.google.com/abc-defg-hij');
    }

    public function test_wall_time_round_trips_through_utc_storage_to_labelled_event_time(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);

        // Create an event with US Eastern timezone (UTC-4 in daylight saving)
        $this->actingAs($creator)->post("/communities/{$community->slug}/events", [
            'title' => 'Global Workshop',
            'timezone' => 'America/New_York',
            'starts_at' => '2026-10-20 10:00',
            'ends_at' => '2026-10-20 12:00',
            'meeting_url' => 'https://zoom.us/j/1234567890',
        ]);

        $event = Event::where('title', 'Global Workshop')->first();
        $this->assertNotNull($event);

        // Member views event
        $member = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;
        $this->actingAs($member)->get("/communities/{$community->slug}/events/{$event->id}")
            ->assertOk()
            ->assertSee('Global Workshop')
            ->assertSee('America/New_York')
            ->assertSee('10:00')
            ->assertSee('12:00')
            ->assertSee('https://zoom.us/j/1234567890');
    }

    public function test_event_validation_rejects_invalid_timezone_end_before_start_and_unsafe_urls(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);

        // Invalid IANA timezone
        $this->actingAs($creator)->post("/communities/{$community->slug}/events", [
            'title' => 'Bad Timezone',
            'timezone' => 'Invalid/Timezone',
            'starts_at' => '2026-10-15 14:00',
            'ends_at' => '2026-10-15 15:00',
        ])->assertSessionHasErrors(['timezone']);

        // End time <= start time
        $this->actingAs($creator)->post("/communities/{$community->slug}/events", [
            'title' => 'Invalid Times',
            'timezone' => 'Asia/Ho_Chi_Minh',
            'starts_at' => '2026-10-15 16:00',
            'ends_at' => '2026-10-15 15:00',
        ])->assertSessionHasErrors(['ends_at']);

        // Unsafe URL scheme (javascript:)
        $this->actingAs($creator)->post("/communities/{$community->slug}/events", [
            'title' => 'Malicious URL',
            'timezone' => 'Asia/Ho_Chi_Minh',
            'starts_at' => '2026-10-15 14:00',
            'ends_at' => '2026-10-15 15:00',
            'meeting_url' => 'javascript:alert(1)',
        ])->assertSessionHasErrors(['meeting_url']);

        $this->assertDatabaseCount('events', 0);
    }

    public function test_cancellation_is_idempotent_and_omits_meeting_url_from_member_view(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);
        $member = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;

        $secretMeetingUrl = 'https://meet.google.com/secret-room-token';
        $event = Event::factory()->create([
            'community_id' => $community->id,
            'creator_id' => $creator->id,
            'meeting_url' => $secretMeetingUrl,
            'status' => 'SCHEDULED',
        ]);

        // Creator cancels event
        $this->actingAs($creator)
            ->post("/communities/{$community->slug}/events/{$event->id}/cancel")
            ->assertRedirect("/communities/{$community->slug}/events/{$event->id}");

        $this->assertSame('CANCELLED', $event->refresh()->status);

        // Repeating cancel is safe and idempotent
        $this->actingAs($creator)
            ->post("/communities/{$community->slug}/events/{$event->id}/cancel")
            ->assertRedirect("/communities/{$community->slug}/events/{$event->id}");
        $this->assertSame('CANCELLED', $event->refresh()->status);

        // Member views cancelled event: sees CANCELLED label, but NO meeting URL in HTML
        $memberResponse = $this->actingAs($member)->get("/communities/{$community->slug}/events/{$event->id}");
        $memberResponse->assertOk()
            ->assertSee('CANCELLED')
            ->assertSee('This event has been cancelled')
            ->assertDontSee($secretMeetingUrl);

        // In calendar view, meeting link is also omitted
        $calendarResponse = $this->actingAs($member)->get("/communities/{$community->slug}/events");
        $calendarResponse->assertOk()
            ->assertSee('CANCELLED')
            ->assertDontSee($secretMeetingUrl);
    }

    public function test_cancelled_event_cannot_be_reopened_or_modified_via_update(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);

        $event = Event::factory()->cancelled()->create([
            'community_id' => $community->id,
            'creator_id' => $creator->id,
            'title' => 'Cancelled Session',
        ]);

        // Attempting to edit a cancelled event is rejected
        $this->actingAs($creator)
            ->get("/communities/{$community->slug}/events/{$event->id}/edit")
            ->assertStatus(400);

        // Attempting to update a cancelled event returns validation error
        $this->actingAs($creator)
            ->put("/communities/{$community->slug}/events/{$event->id}", [
                'title' => 'Reopened Session',
                'timezone' => 'Asia/Ho_Chi_Minh',
                'starts_at' => '2026-10-15 14:00',
                'ends_at' => '2026-10-15 15:00',
            ])
            ->assertSessionHasErrors(['title']);

        $this->assertSame('CANCELLED', $event->refresh()->status);
        $this->assertSame('Cancelled Session', $event->title);
    }

    public function test_member_cannot_create_update_or_cancel_events(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);
        $member = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;

        $event = Event::factory()->create(['community_id' => $community->id, 'creator_id' => $creator->id]);

        // Member cannot open create page
        $this->actingAs($member)
            ->get("/communities/{$community->slug}/events/create")
            ->assertNotFound();

        // Member cannot post new event
        $this->actingAs($member)
            ->post("/communities/{$community->slug}/events", [
                'title' => 'Member event',
                'timezone' => 'Asia/Ho_Chi_Minh',
                'starts_at' => '2026-10-15 14:00',
                'ends_at' => '2026-10-15 15:00',
            ])
            ->assertNotFound();

        // Member cannot edit
        $this->actingAs($member)
            ->get("/communities/{$community->slug}/events/{$event->id}/edit")
            ->assertNotFound();

        // Member cannot update
        $this->actingAs($member)
            ->put("/communities/{$community->slug}/events/{$event->id}", [
                'title' => 'Hacked',
                'timezone' => 'Asia/Ho_Chi_Minh',
                'starts_at' => '2026-10-15 14:00',
                'ends_at' => '2026-10-15 15:00',
            ])
            ->assertNotFound();

        // Member cannot cancel
        $this->actingAs($member)
            ->post("/communities/{$community->slug}/events/{$event->id}/cancel")
            ->assertNotFound();

        $this->assertSame('SCHEDULED', $event->refresh()->status);
    }

    public function test_outsider_and_inactive_membership_cannot_access_events(): void
    {
        $community = Community::factory()->create();
        $event = Event::factory()->create(['community_id' => $community->id, 'title' => 'Private Strategy']);

        $outsider = User::factory()->create();
        $this->actingAs($outsider)
            ->get("/communities/{$community->slug}/events")
            ->assertNotFound();

        $this->actingAs($outsider)
            ->get("/communities/{$community->slug}/events/{$event->id}")
            ->assertNotFound();

        foreach (['PENDING_PAYMENT', 'SUSPENDED', 'REMOVED', 'LEFT'] as $status) {
            $inactiveUser = User::factory()->create();
            CommunityMembership::factory()->create([
                'community_id' => $community->id,
                'user_id' => $inactiveUser->id,
                'status' => $status,
            ]);

            $this->actingAs($inactiveUser)
                ->get("/communities/{$community->slug}/events/{$event->id}")
                ->assertNotFound();
        }
    }

    public function test_cross_community_event_access_is_prevented(): void
    {
        $communityA = Community::factory()->create();
        $communityB = Community::factory()->create();
        $user = CommunityMembership::factory()->active()->create(['community_id' => $communityA->id])->user;
        CommunityMembership::factory()->active()->create(['community_id' => $communityB->id, 'user_id' => $user->id]);

        $eventA = Event::factory()->create(['community_id' => $communityA->id]);

        // Attempting to access eventA under communityB slug fails with 404
        $this->actingAs($user)
            ->get("/communities/{$communityB->slug}/events/{$eventA->id}")
            ->assertNotFound();
    }

    public function test_events_are_ordered_chronologically_and_scoped_to_community(): void
    {
        $community = Community::factory()->create();
        $member = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;

        $eventTomorrow = Event::factory()->create([
            'community_id' => $community->id,
            'title' => 'Tomorrow Event',
            'starts_at' => Carbon::now()->addDay(),
            'ends_at' => Carbon::now()->addDay()->addHour(),
        ]);

        $eventNextWeek = Event::factory()->create([
            'community_id' => $community->id,
            'title' => 'Next Week Event',
            'starts_at' => Carbon::now()->addDays(7),
            'ends_at' => Carbon::now()->addDays(7)->addHour(),
        ]);

        $otherCommunityEvent = Event::factory()->create(['title' => 'Other Tenant Event']);

        $response = $this->actingAs($member)->get("/communities/{$community->slug}/events");
        $response->assertOk();

        $eventsOnPage = $response->viewData('events');
        $this->assertCount(2, $eventsOnPage);
        $this->assertSame($eventTomorrow->id, $eventsOnPage->first()->id);
        $this->assertSame($eventNextWeek->id, $eventsOnPage->last()->id);
        $response->assertDontSee('Other Tenant Event');
    }

    public function test_xss_content_in_title_and_description_is_escaped(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);

        Event::factory()->create([
            'community_id' => $community->id,
            'creator_id' => $creator->id,
            'title' => '<script>alert("xss-event")</script>',
            'description' => '<img src=x onerror=alert(1)>',
        ]);

        $response = $this->actingAs($creator)->get("/communities/{$community->slug}/events");
        $response->assertOk();
        $response->assertDontSee('<script>alert("xss-event")</script>', false);
        $response->assertSee('&lt;script&gt;alert(&quot;xss-event&quot;)&lt;/script&gt;', false);
    }
}
