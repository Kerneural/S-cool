<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Event;
use App\Support\EventWallTime;
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
        $events = $community->events()->with('creator:id,name')->orderBy('starts_at')->orderBy('id')->paginate(15);

        return view('communities.events.index', compact('community', 'events'));
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
