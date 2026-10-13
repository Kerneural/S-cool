<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Event;
use App\Support\EventWallTime;
use Carbon\Carbon;
use Closure;
use DateTimeZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request, Community $community): View
    {
        Gate::authorize('viewAny', [Event::class, $community]);

        // 1. Validate view parameter (allowlist: month|list, default: list)
        $view = $request->query('view', 'list');
        if (! is_string($view) || ! in_array($view, ['month', 'list'], true)) {
            throw ValidationException::withMessages([
                'view' => 'The selected view is invalid.',
            ]);
        }

        // 2. Validate display_timezone parameter (default: Asia/Ho_Chi_Minh)
        $displayTimezone = $request->query('display_timezone', 'Asia/Ho_Chi_Minh');
        if (! is_string($displayTimezone) || ! in_array($displayTimezone, DateTimeZone::listIdentifiers(), true)) {
            throw ValidationException::withMessages([
                'display_timezone' => 'The selected timezone is invalid.',
            ]);
        }

        // 3. Validate month parameter (strict YYYY-MM)
        $nowInZone = Carbon::now($displayTimezone);
        $month = $request->query('month');
        $hasExplicitMonth = $month !== null;

        if ($hasExplicitMonth) {
            if (! is_string($month) || ! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
                throw ValidationException::withMessages([
                    'month' => 'The selected month must be in YYYY-MM format.',
                ]);
            }
        } else {
            $month = $nowInZone->format('Y-m');
        }

        $startOfMonth = Carbon::createFromFormat('!Y-m-d H:i:s', "{$month}-01 00:00:00", $displayTimezone);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $startUtc = $startOfMonth->copy()->setTimezone('UTC');
        $endUtc = $endOfMonth->copy()->setTimezone('UTC');

        $prevMonth = $startOfMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $startOfMonth->copy()->addMonth()->format('Y-m');
        $monthLabel = $startOfMonth->format('F Y');

        $eventsQuery = $community->events()
            ->with('creator:id,name');

        if ($view === 'month' || $hasExplicitMonth) {
            $eventsQuery->where('starts_at', '<=', $endUtc)
                ->where('ends_at', '>=', $startUtc);
        }

        $eventsQuery->orderBy('starts_at')->orderBy('id');

        $calendarGrid = [];
        if ($view === 'month') {
            // Cap month grid payload to prevent memory exhaustion (max 100 events)
            $events = $eventsQuery->take(100)->get();

            $daysInMonth = $startOfMonth->daysInMonth;
            $startOfWeekIso = $startOfMonth->dayOfWeekIso; // 1 = Mon, 7 = Sun
            $leadingEmptyDays = $startOfWeekIso - 1;

            $days = [];
            for ($i = 0; $i < $leadingEmptyDays; $i++) {
                $days[] = null;
            }

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateStr = sprintf('%s-%02d', $month, $d);
                $dayStart = Carbon::createFromFormat('!Y-m-d H:i:s', "{$dateStr} 00:00:00", $displayTimezone);
                $dayEnd = Carbon::createFromFormat('!Y-m-d H:i:s', "{$dateStr} 23:59:59", $displayTimezone);

                $dayEvents = $events->filter(function (Event $event) use ($dayStart, $dayEnd, $displayTimezone): bool {
                    $startInTz = $event->startsAtInTimezone($displayTimezone);
                    $endInTz = $event->endsAtInTimezone($displayTimezone);

                    return $startInTz->lte($dayEnd) && $endInTz->gte($dayStart);
                })->values();

                $days[] = [
                    'day' => $d,
                    'date' => $dateStr,
                    'is_today' => $dateStr === $nowInZone->format('Y-m-d'),
                    'events' => $dayEvents,
                ];
            }

            $remainder = count($days) % 7;
            if ($remainder > 0) {
                for ($i = 0; $i < (7 - $remainder); $i++) {
                    $days[] = null;
                }
            }

            $calendarGrid = array_chunk($days, 7);
        } else {
            $events = $eventsQuery->paginate(15)->withQueryString();
        }

        return view('communities.events.index', [
            'community' => $community,
            'events' => $events,
            'view' => $view,
            'month' => $month,
            'monthLabel' => $monthLabel,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'displayTimezone' => $displayTimezone,
            'timezones' => DateTimeZone::listIdentifiers(),
            'calendarGrid' => $calendarGrid,
        ]);
    }

    public function create(Community $community): View
    {
        Gate::authorize('create', [Event::class, $community]);

        return view('communities.events.create', ['community' => $community, 'timezones' => DateTimeZone::listIdentifiers()]);
    }

    public function store(Request $request, Community $community): RedirectResponse
    {
        Gate::authorize('create', [Event::class, $community]);
        $validated = $this->validateInput($request);
        DB::transaction(function () use ($request, $community, $validated): void {
            $current = Community::query()->lockForUpdate()->findOrFail($community->id);
            Gate::authorize('create', [Event::class, $current]);
            $current->events()->create(['creator_id' => $request->user()->id, ...$validated, 'status' => 'SCHEDULED']);
        }, 3);

        return redirect()->route('communities.events.index', $community)->with('status', 'event-created');
    }

    public function show(Community $community, Event $event): View
    {
        Gate::authorize('view', $event);
        $event->load('creator:id,name');

        return view('communities.events.show', compact('community', 'event'));
    }

    public function edit(Community $community, Event $event): View
    {
        Gate::authorize('update', $event);
        abort_if($event->isCancelled(), 400, 'Cancelled events cannot be modified.');

        return view('communities.events.edit', ['community' => $community, 'event' => $event, 'timezones' => DateTimeZone::listIdentifiers()]);
    }

    public function update(Request $request, Community $community, Event $event): RedirectResponse
    {
        Gate::authorize('update', $event);
        $this->assertEditable($event);
        $validated = $this->validateInput($request);
        DB::transaction(function () use ($community, $event, $validated): void {
            $currentCommunity = Community::query()->lockForUpdate()->findOrFail($community->id);
            $current = $currentCommunity->events()->lockForUpdate()->findOrFail($event->id);
            $current->setRelation('community', $currentCommunity);
            Gate::authorize('update', $current);
            $this->assertEditable($current);
            $current->update($validated);
        }, 3);

        return redirect()->route('communities.events.show', [$community, $event])->with('status', 'event-updated');
    }

    public function cancel(Request $request, Community $community, Event $event): RedirectResponse
    {
        Gate::authorize('cancel', $event);
        DB::transaction(function () use ($community, $event): void {
            $currentCommunity = Community::query()->lockForUpdate()->findOrFail($community->id);
            $current = $currentCommunity->events()->lockForUpdate()->findOrFail($event->id);
            $current->setRelation('community', $currentCommunity);
            Gate::authorize('cancel', $current);
            if (! $current->isCancelled()) {
                $current->update(['status' => 'CANCELLED']);
            }
        }, 3);

        return redirect()->route('communities.events.show', [$community, $event])->with('status', 'event-cancelled');
    }

    private function assertEditable(Event $event): void
    {
        if ($event->isCancelled()) {
            throw ValidationException::withMessages(['title' => 'Cancelled events cannot be modified.']);
        }
    }

    private function validateInput(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'timezone' => ['required', 'string', Rule::in(DateTimeZone::listIdentifiers())],
            'starts_at' => ['required', 'string'],
            'ends_at' => ['required', 'string'],
            'meeting_url' => ['bail', 'nullable', 'string', 'url:http,https', 'max:2048', function (string $attribute, mixed $value, Closure $fail): void {
                $parts = parse_url($value);
                if ($parts === false || isset($parts['user']) || isset($parts['pass'])) {
                    $fail('The meeting link must not contain credentials.');
                }
            }],
        ]);
        $start = EventWallTime::toUtc($validated['starts_at'], $validated['timezone'], 'starts_at');
        $end = EventWallTime::toUtc($validated['ends_at'], $validated['timezone'], 'ends_at');
        if ($end->lessThanOrEqualTo($start)) {
            throw ValidationException::withMessages(['ends_at' => 'The end time must be after the start time.']);
        }

        return [...$validated, 'starts_at' => $start, 'ends_at' => $end];
    }
}
