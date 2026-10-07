<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Event;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * Display a listing of community events.
     */
    public function index(Request $request, Community $community): View
    {
        Gate::authorize('viewAny', [Event::class, $community]);

        $events = $community->events()
            ->with('creator:id,name')
            ->orderBy('starts_at', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(15);

        return view('communities.events.index', [
            'community' => $community,
            'events' => $events,
        ]);
    }

    /**
     * Show the form for creating a new event.
     */
    public function create(Community $community): View
    {
        Gate::authorize('create', [Event::class, $community]);

        return view('communities.events.create', [
            'community' => $community,
            'timezones' => DateTimeZone::listIdentifiers(),
        ]);
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(Request $request, Community $community): RedirectResponse
    {
        Gate::authorize('create', [Event::class, $community]);

        $timezones = DateTimeZone::listIdentifiers();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'timezone' => ['required', 'string', Rule::in($timezones)],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date'],
            'meeting_url' => ['nullable', 'url:http,https', 'max:2048'],
        ]);

        try {
            $startLocal = Carbon::parse($validated['starts_at'], $validated['timezone']);
            $endLocal = Carbon::parse($validated['ends_at'], $validated['timezone']);
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'starts_at' => 'Invalid date or timezone.',
            ]);
        }

        if ($endLocal->lessThanOrEqualTo($startLocal)) {
            throw ValidationException::withMessages([
                'ends_at' => 'The end time must be after the start time.',
            ]);
        }

        $community->events()->create([
            'creator_id' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'starts_at' => $startLocal->copy()->setTimezone('UTC'),
            'ends_at' => $endLocal->copy()->setTimezone('UTC'),
            'timezone' => $validated['timezone'],
            'meeting_url' => $validated['meeting_url'] ?? null,
            'status' => 'SCHEDULED',
        ]);

        return redirect()->route('communities.events.index', $community)->with('status', 'event-created');
    }

    /**
     * Display the specified event.
     */
    public function show(Community $community, Event $event): View
    {
        Gate::authorize('view', $event);

        $event->load('creator:id,name');

        return view('communities.events.show', [
            'community' => $community,
            'event' => $event,
        ]);
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Community $community, Event $event): View
    {
        Gate::authorize('update', $event);

        if ($event->isCancelled()) {
            abort(400, 'Cancelled events cannot be modified.');
        }

        return view('communities.events.edit', [
            'community' => $community,
            'event' => $event,
            'timezones' => DateTimeZone::listIdentifiers(),
        ]);
    }

    /**
     * Update the specified event in storage.
     */
    public function update(Request $request, Community $community, Event $event): RedirectResponse
    {
        Gate::authorize('update', $event);

        if ($event->isCancelled()) {
            throw ValidationException::withMessages([
                'title' => 'Cancelled events cannot be modified.',
            ]);
        }

        $timezones = DateTimeZone::listIdentifiers();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'timezone' => ['required', 'string', Rule::in($timezones)],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date'],
            'meeting_url' => ['nullable', 'url:http,https', 'max:2048'],
        ]);

        try {
            $startLocal = Carbon::parse($validated['starts_at'], $validated['timezone']);
            $endLocal = Carbon::parse($validated['ends_at'], $validated['timezone']);
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'starts_at' => 'Invalid date or timezone.',
            ]);
        }

        if ($endLocal->lessThanOrEqualTo($startLocal)) {
            throw ValidationException::withMessages([
                'ends_at' => 'The end time must be after the start time.',
            ]);
        }

        $event->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'starts_at' => $startLocal->copy()->setTimezone('UTC'),
            'ends_at' => $endLocal->copy()->setTimezone('UTC'),
            'timezone' => $validated['timezone'],
            'meeting_url' => $validated['meeting_url'] ?? null,
        ]);

        return redirect()->route('communities.events.show', [$community, $event])->with('status', 'event-updated');
    }

    /**
     * Cancel the specified event (idempotent, Creator only).
     */
    public function cancel(Request $request, Community $community, Event $event): RedirectResponse
    {
        Gate::authorize('cancel', $event);

        $event->update(['status' => 'CANCELLED']);

        return redirect()->route('communities.events.show', [$community, $event])->with('status', 'event-cancelled');
    }
}
