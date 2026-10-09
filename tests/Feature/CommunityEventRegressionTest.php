<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class CommunityEventRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function validInput(array $overrides = []): array
    {
        return [...[
            'title' => 'Synthetic workshop', 'timezone' => 'Asia/Ho_Chi_Minh',
            'starts_at' => '2026-10-15T14:00', 'ends_at' => '2026-10-15T15:00',
            'meeting_url' => 'https://example.com/meeting',
        ], ...$overrides];
    }

    public function test_gap_and_fold_are_rejected_on_create_and_update_without_partial_writes(): void
    {
        $community = Community::factory()->create();
        $event = Event::factory()->create(['community_id' => $community->id, 'title' => 'Original']);
        $this->actingAs($community->creator);
        foreach ([
            ['America/New_York', '2026-03-08T02:30', '2026-03-08T04:00'],
            ['America/New_York', '2026-11-01T01:30', '2026-11-01T03:00'],
            ['Australia/Lord_Howe', '2026-10-04T02:15', '2026-10-04T04:00'],
            ['Australia/Lord_Howe', '2026-04-05T01:45', '2026-04-05T03:00'],
        ] as [$timezone, $start, $end]) {
            $input = $this->validInput(['timezone' => $timezone, 'starts_at' => $start, 'ends_at' => $end]);
            $this->post("/communities/{$community->slug}/events", $input)->assertSessionHasErrors('starts_at');
            $this->put("/communities/{$community->slug}/events/{$event->id}", $input)->assertSessionHasErrors('starts_at');
            $this->assertDatabaseCount('events', 1);
            $this->assertSame('Original', $event->refresh()->title);
        }
    }

    public function test_end_gap_or_fold_invalid_dates_and_offsets_are_rejected(): void
    {
        $community = Community::factory()->create();
        $this->actingAs($community->creator);
        foreach ([
            ['timezone' => 'America/New_York', 'starts_at' => '2026-03-08T01:00', 'ends_at' => '2026-03-08T02:30'],
            ['timezone' => 'America/New_York', 'starts_at' => '2026-11-01T00:00', 'ends_at' => '2026-11-01T01:30'],
        ] as $input) {
            $this->post("/communities/{$community->slug}/events", $this->validInput($input))->assertSessionHasErrors('ends_at');
        }
        foreach (['2026-02-30T14:00', '2026-10-15T14:00+07:00', 'tomorrow', '2026-10-15T25:00'] as $start) {
            $this->post("/communities/{$community->slug}/events", $this->validInput(['starts_at' => $start]))->assertSessionHasErrors('starts_at');
        }
        $this->assertDatabaseCount('events', 0);
    }

    public function test_valid_times_around_transition_round_trip_without_guessing(): void
    {
        $community = Community::factory()->create();
        $this->actingAs($community->creator)->post("/communities/{$community->slug}/events", $this->validInput([
            'timezone' => 'America/New_York', 'starts_at' => '2026-03-08T01:30', 'ends_at' => '2026-03-08T03:30',
        ]))->assertRedirect()->assertSessionHasNoErrors();
        $event = Event::firstOrFail();
        $this->assertSame('2026-03-08 06:30:00', $event->getRawOriginal('starts_at'));
        $this->assertSame('2026-03-08 07:30:00', $event->getRawOriginal('ends_at'));
        $this->assertSame('01:30', $event->localStartsAt()->format('H:i'));
        $this->assertSame('03:30', $event->localEndsAt()->format('H:i'));
    }

    public function test_credentials_and_unsafe_urls_are_rejected_on_both_write_paths(): void
    {
        $community = Community::factory()->create();
        $event = Event::factory()->create(['community_id' => $community->id, 'meeting_url' => 'https://example.com/original']);
        $this->actingAs($community->creator);
        foreach (['https://user:password@example.com/meeting', 'https://user@example.com', 'https://@example.com', 'javascript:alert(1)', 'data:text/html,x', '//example.com', 'not-a-url', ['https://example.com']] as $url) {
            $input = $this->validInput(['meeting_url' => $url]);
            $this->post("/communities/{$community->slug}/events", $input)->assertSessionHasErrors('meeting_url');
            $this->put("/communities/{$community->slug}/events/{$event->id}", $input)->assertSessionHasErrors('meeting_url');
            $this->assertSame('https://example.com/original', $event->refresh()->meeting_url);
            $this->assertDatabaseCount('events', 1);
        }
    }

    public function test_non_utc_application_timezone_does_not_shift_create_update_reload_or_render(): void
    {
        $community = Community::factory()->create();
        $previous = date_default_timezone_get();
        config(['app.timezone' => 'Asia/Ho_Chi_Minh']);
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        try {
            $this->actingAs($community->creator)->post("/communities/{$community->slug}/events", $this->validInput())->assertRedirect();
            $event = Event::firstOrFail();
            $this->assertSame('2026-10-15 07:00:00', $event->getRawOriginal('starts_at'));
            $this->assertSame('UTC', $event->starts_at->timezoneName);
            $this->assertSame('14:00', $event->localStartsAt()->format('H:i'));
            $this->put("/communities/{$community->slug}/events/{$event->id}", $this->validInput(['starts_at' => '2026-10-15T16:00', 'ends_at' => '2026-10-15T17:00']))->assertRedirect();
            $event->refresh();
            $this->assertSame('2026-10-15 09:00:00', $event->getRawOriginal('starts_at'));
            $this->assertSame('16:00', $event->localStartsAt()->format('H:i'));
            date_default_timezone_set('America/Los_Angeles');
            config(['app.timezone' => 'America/Los_Angeles']);
            $event = Event::findOrFail($event->id);
            $this->assertSame('16:00', $event->localStartsAt()->format('H:i'));
            $this->get("/communities/{$community->slug}/events/{$event->id}")->assertOk()->assertSee('16:00')->assertSee('Asia/Ho_Chi_Minh');
            $this->get("/communities/{$community->slug}/events/{$event->id}/edit")->assertOk()->assertSee('2026-10-15T16:00');
        } finally {
            date_default_timezone_set($previous);
        }
    }

    public function test_cast_normalizes_non_utc_datetime_objects_to_utc(): void
    {
        $event = Event::factory()->create([
            'starts_at' => Carbon::parse('2026-10-15 14:00:00', 'Asia/Ho_Chi_Minh'),
            'ends_at' => Carbon::parse('2026-10-15 15:00:00', 'Asia/Ho_Chi_Minh'),
            'timezone' => 'Asia/Ho_Chi_Minh',
        ]);
        $event->refresh();
        $this->assertSame('2026-10-15 07:00:00', $event->getRawOriginal('starts_at'));
        $this->assertSame('14:00', $event->localStartsAt()->format('H:i'));
    }

    public function test_cancellation_between_authorization_and_update_prevents_write(): void
    {
        $community = Community::factory()->create();
        $event = Event::factory()->create(['community_id' => $community->id, 'title' => 'Original']);
        $changed = false;
        Gate::after(function ($user, $ability) use ($event, &$changed): void {
            if (! $changed && $ability === 'update') {
                $changed = true;
                Event::query()->whereKey($event->id)->update(['status' => 'CANCELLED']);
            }
        });
        $this->actingAs($community->creator)->put("/communities/{$community->slug}/events/{$event->id}", $this->validInput())->assertSessionHasErrors('title');
        $this->assertTrue($changed);
        $this->assertSame('CANCELLED', $event->refresh()->status);
        $this->assertSame('Original', $event->title);
    }
}
