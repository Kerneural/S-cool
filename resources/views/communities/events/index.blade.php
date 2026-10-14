<x-app-layout>
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="events" />

            @if (session('status'))
                <div class="p-4 font-medium text-sm text-green-700 bg-green-50 rounded-lg border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Calendar Control Toolbar -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Month Navigator -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('communities.events.index', [$community, 'month' => $prevMonth, 'view' => $view, 'display_timezone' => $displayTimezone]) }}"
                       class="inline-flex items-center px-3 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-300 rounded-md text-xs font-semibold text-gray-700 transition"
                       title="{{ __('Previous month') }}">
                        &larr; {{ __('Prev') }}
                    </a>

                    <h3 class="text-lg font-bold text-gray-900 tracking-tight min-w-[140px] text-center">
                        {{ $monthLabel }}
                    </h3>

                    <a href="{{ route('communities.events.index', [$community, 'month' => $nextMonth, 'view' => $view, 'display_timezone' => $displayTimezone]) }}"
                       class="inline-flex items-center px-3 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-300 rounded-md text-xs font-semibold text-gray-700 transition"
                       title="{{ __('Next month') }}">
                        {{ __('Next') }} &rarr;
                    </a>
                </div>

                <!-- Right controls: View Toggle, Timezone Dropdown, Create Button -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- View Toggle -->
                    <div class="inline-flex rounded-md shadow-sm" role="group">
                        <a href="{{ route('communities.events.index', [$community, 'view' => 'month', 'month' => $month, 'display_timezone' => $displayTimezone]) }}"
                           class="px-3.5 py-1.5 text-xs font-semibold rounded-l-md border transition {{ $view === 'month' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}">
                            {{ __('Month') }}
                        </a>
                        <a href="{{ route('communities.events.index', [$community, 'view' => 'list', 'month' => $month, 'display_timezone' => $displayTimezone]) }}"
                           class="px-3.5 py-1.5 text-xs font-semibold rounded-r-md border-t border-b border-r transition {{ $view === 'list' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}">
                            {{ __('List') }}
                        </a>
                    </div>

                    <!-- Timezone Selector -->
                    <form method="GET" action="{{ route('communities.events.index', $community) }}" class="inline-flex items-center">
                        <input type="hidden" name="view" value="{{ $view }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <label for="display_timezone" class="sr-only">{{ __('Display Timezone') }}</label>
                        <select id="display_timezone"
                                name="display_timezone"
                                onchange="this.form.submit()"
                                class="text-xs bg-gray-50 border border-gray-300 rounded-md py-1.5 pl-2.5 pr-7 font-medium text-gray-700 focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach ($timezones as $tz)
                                <option value="{{ $tz }}" {{ $tz === $displayTimezone ? 'selected' : '' }}>
                                    {{ $tz }}
                                </option>
                            @endforeach
                        </select>
                    </form>

                    <!-- Create Event -->
                    @can('create', [App\Models\Event::class, $community])
                        <a href="{{ route('communities.events.create', $community) }}"
                           class="inline-flex items-center px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-semibold text-xs uppercase tracking-wider transition">
                            + {{ __('Create Event') }}
                        </a>
                    @endcan
                </div>
            </div>

            <!-- Active Calendar Zone Label -->
            <div class="flex items-center justify-between text-xs text-gray-500 px-1">
                <div>
                    {{ __('Calendar timezone:') }} <span class="font-semibold text-gray-800">{{ $displayTimezone }}</span>
                </div>
                <div>
                    @if ($view === 'month')
                        <span>{{ __('Showing month grid for :month', ['month' => $monthLabel]) }}</span>
                    @else
                        <span>{{ __('Showing chronological list') }}</span>
                    @endif
                </div>
            </div>

            <!-- Main Content Area -->
            @if ($view === 'month')
                @if (! empty($hasMoreEvents))
                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>{{ __('This month has more than 100 events. The calendar grid displays the first 100 events.') }}</span>
                        </div>
                        <a href="{{ route('communities.events.index', [$community, 'view' => 'list', 'month' => $month, 'display_timezone' => $displayTimezone]) }}"
                           class="inline-flex items-center text-xs font-semibold text-amber-900 underline hover:text-amber-700 shrink-0">
                            {{ __('Switch to List view to see all events &rarr;') }}
                        </a>
                    </div>
                @endif

                <!-- Month Grid -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Day of week headers -->
                    <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50 text-center text-xs font-semibold text-gray-600 py-2.5">
                        <div>{{ __('Mon') }}</div>
                        <div>{{ __('Tue') }}</div>
                        <div>{{ __('Wed') }}</div>
                        <div>{{ __('Thu') }}</div>
                        <div>{{ __('Fri') }}</div>
                        <div>{{ __('Sat') }}</div>
                        <div>{{ __('Sun') }}</div>
                    </div>

                    <!-- Calendar Weeks -->
                    <div class="divide-y divide-gray-200">
                        @foreach ($calendarGrid as $week)
                            <div class="grid grid-cols-7 divide-x divide-gray-200 min-h-[110px]">
                                @foreach ($week as $dayData)
                                    @if ($dayData === null)
                                        <div class="bg-gray-50/50 p-2"></div>
                                    @else
                                        <div class="p-2 flex flex-col justify-between transition hover:bg-slate-50/80 {{ $dayData['is_today'] ? 'bg-indigo-50/40 ring-1 ring-inset ring-indigo-300' : '' }}">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-xs font-bold {{ $dayData['is_today'] ? 'text-indigo-600 font-extrabold' : 'text-gray-700' }}">
                                                    {{ $dayData['day'] }}
                                                </span>
                                                @if ($dayData['is_today'])
                                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                                                @endif
                                            </div>

                                            <div class="space-y-1 overflow-y-auto max-h-[85px]">
                                                @foreach ($dayData['events'] as $event)
                                                    <a href="{{ route('communities.events.show', [$community, $event]) }}"
                                                       class="block p-1 rounded text-[11px] leading-tight truncate transition {{ $event->isCancelled() ? 'bg-red-50 text-red-700 line-through' : 'bg-indigo-50 hover:bg-indigo-100 text-indigo-900 font-medium' }}"
                                                       title="{{ $event->title }} ({{ $event->startsAtInTimezone($displayTimezone)->format('H:i') }} {{ $displayTimezone }})">
                                                        <span class="font-semibold">{{ $event->startsAtInTimezone($displayTimezone)->format('H:i') }}</span>
                                                        {{ $event->title }}
                                                        @if ($event->isCancelled())
                                                            <span class="text-[9px] font-bold uppercase text-red-600">({{ __('Cancelled') }})</span>
                                                        @endif
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <!-- List View -->
                <div class="space-y-4">
                    @forelse ($events as $event)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border {{ $event->isCancelled() ? 'border-red-200 bg-red-50/20' : 'border-gray-200' }} p-6">
                            <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-start mb-3">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        @if ($event->isCancelled())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800">
                                                {{ __('CANCELLED') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                {{ __('SCHEDULED') }}
                                            </span>
                                        @endif
                                        <span class="text-xs font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">
                                            {{ $displayTimezone }}
                                        </span>
                                        <span class="text-[11px] text-gray-500 bg-gray-100 px-2 py-0.5 rounded">
                                            {{ __('Original: :tz', ['tz' => $event->timezone]) }}
                                        </span>
                                    </div>
                                    <a href="{{ route('communities.events.show', [$community, $event]) }}" class="group">
                                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition {{ $event->isCancelled() ? 'line-through text-gray-500' : '' }}">
                                            {{ $event->title }}
                                        </h3>
                                    </a>
                                </div>

                                <div class="text-left sm:text-right">
                                    <div class="text-sm font-bold text-gray-900">
                                        {{ $event->startsAtInTimezone($displayTimezone)->format('M d, Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $event->startsAtInTimezone($displayTimezone)->format('H:i') }} - {{ $event->endsAtInTimezone($displayTimezone)->format('H:i') }} ({{ $displayTimezone }})
                                    </div>
                                </div>
                            </div>

                            @if ($event->description)
                                <p class="text-gray-700 text-sm whitespace-pre-line line-clamp-3 mb-4 leading-relaxed">
                                    {{ $event->description }}
                                </p>
                            @endif

                            <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center pt-3 border-t border-gray-100 text-xs text-gray-500">
                                <div>
                                    {{ __('Organized by:') }} <span class="font-medium text-gray-700">{{ $event->creator->name ?? __('Creator') }}</span>
                                </div>

                                <div class="flex flex-wrap items-center gap-3">
                                    <a href="{{ route('communities.events.show', [$community, $event]) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                        {{ __('View Details &rarr;') }}
                                    </a>

                                    @if (! $event->isCancelled() && $event->meeting_url)
                                        <a href="{{ $event->meeting_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-3 py-1 bg-green-600 text-white rounded text-xs font-medium hover:bg-green-700 transition">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            {{ __('Join Meeting') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 text-center text-gray-500 border border-gray-200">
                            <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <p class="text-base font-medium text-gray-900">{{ __('No events scheduled yet') }}</p>
                            <p class="text-sm text-gray-500 mt-1">{{ __('Stay tuned! Community events, workshops and coaching sessions will appear here.') }}</p>
                        </div>
                    @endforelse

                    <div class="mt-6">
                        {{ $events->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
