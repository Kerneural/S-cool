<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="events" />

            @if (session('status'))
                <div class="p-4 font-medium text-sm text-emerald-800 bg-emerald-50 rounded-xl border border-emerald-200 shadow-xs flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            <!-- Calendar Control Toolbar -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 sm:p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Month Navigator -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('communities.events.index', [$community, 'month' => $prevMonth, 'view' => $view, 'display_timezone' => $displayTimezone]) }}"
                       class="inline-flex items-center px-3 py-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 transition"
                       title="{{ __('Previous month') }}">
                        &larr; {{ __('Prev') }}
                    </a>

                    <h1 class="text-lg font-bold text-slate-900 tracking-tight min-w-[140px] text-center">
                        {{ $monthLabel }}
                    </h1>

                    <a href="{{ route('communities.events.index', [$community, 'month' => $nextMonth, 'view' => $view, 'display_timezone' => $displayTimezone]) }}"
                       class="inline-flex items-center px-3 py-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 transition"
                       title="{{ __('Next month') }}">
                        {{ __('Next') }} &rarr;
                    </a>
                </div>

                <!-- Right controls: View Toggle, Timezone Dropdown, Create Button -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- View Toggle -->
                    <div class="inline-flex rounded-xl bg-slate-100 p-0.5 border border-slate-200/60" role="group">
                        <a href="{{ route('communities.events.index', [$community, 'view' => 'month', 'month' => $month, 'display_timezone' => $displayTimezone]) }}"
                           class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition {{ $view === 'month' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                            {{ __('Month') }}
                        </a>
                        <a href="{{ route('communities.events.index', [$community, 'view' => 'list', 'month' => $month, 'display_timezone' => $displayTimezone]) }}"
                           class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition {{ $view === 'list' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
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
                                class="text-xs bg-slate-50 border border-slate-200 rounded-xl py-1.5 pl-2.5 pr-7 font-medium text-slate-700 focus:ring-brand-500 focus:border-brand-500">
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
                           class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl font-semibold text-xs transition shadow-sm active:scale-[0.99]">
                            + {{ __('Create Event') }}
                        </a>
                    @endcan
                </div>
            </div>

            <!-- Active Calendar Zone Label -->
            <div class="flex items-center justify-between text-xs text-slate-400 px-1">
                <div>
                    {{ __('Calendar timezone:') }} <span class="font-semibold text-slate-700">{{ $displayTimezone }}</span>
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
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <div class="min-w-0 lg:col-span-8 space-y-6">
                    @if ($view === 'month')
                        @if (! empty($hasMoreEvents))
                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200/80 text-amber-800 text-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
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
                <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
                    <!-- Day of week headers -->
                    <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50/75 text-center text-xs font-bold text-slate-500 py-3 uppercase tracking-wider">
                        <div>{{ __('Mon') }}</div>
                        <div>{{ __('Tue') }}</div>
                        <div>{{ __('Wed') }}</div>
                        <div>{{ __('Thu') }}</div>
                        <div>{{ __('Fri') }}</div>
                        <div>{{ __('Sat') }}</div>
                        <div>{{ __('Sun') }}</div>
                    </div>

                    <!-- Calendar Weeks -->
                    <div class="divide-y divide-slate-100">
                        @foreach ($calendarGrid as $week)
                            <div class="grid grid-cols-7 divide-x divide-slate-100 min-h-[110px]">
                                @foreach ($week as $dayData)
                                    @if ($dayData === null)
                                        <div class="bg-slate-50/40 p-2"></div>
                                    @else
                                        <div class="p-2.5 flex flex-col justify-between transition hover:bg-slate-50/60 {{ $dayData['is_today'] ? 'bg-brand-50/30 ring-1 ring-inset ring-brand-300' : '' }}">
                                            <div class="flex items-center justify-between mb-1.5">
                                                <span class="text-xs font-bold {{ $dayData['is_today'] ? 'text-brand-700 font-extrabold' : 'text-slate-700' }}">
                                                    {{ $dayData['day'] }}
                                                </span>
                                                @if ($dayData['is_today'])
                                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-brand-600"></span>
                                                @endif
                                            </div>

                                            <div class="space-y-1 overflow-y-auto max-h-[85px] pr-0.5">
                                                @foreach ($dayData['events'] as $event)
                                                    <a href="{{ route('communities.events.show', [$community, $event]) }}"
                                                       class="block p-1.5 rounded-lg text-[11px] leading-tight truncate transition {{ $event->isCancelled() ? 'bg-rose-50 text-rose-700 line-through' : 'bg-brand-50/70 hover:bg-brand-100 text-brand-900 font-medium' }}"
                                                       title="{{ $event->title }} ({{ $event->startsAtInTimezone($displayTimezone)->format('H:i') }} {{ $displayTimezone }})">
                                                        <span class="font-semibold">{{ $event->startsAtInTimezone($displayTimezone)->format('H:i') }}</span>
                                                        {{ $event->title }}
                                                        @if ($event->isCancelled())
                                                            <span class="text-[9px] font-bold uppercase text-rose-600">({{ __('Cancelled') }})</span>
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
                        <article class="bg-white rounded-2xl shadow-xs border {{ $event->isCancelled() ? 'border-rose-200 bg-rose-50/20' : 'border-slate-200/80 hover:border-brand-500/30 hover:shadow-md' }} p-5 sm:p-6 transition flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
                            <!-- Left: Calendar Date Badge & Time -->
                            <div class="flex items-center gap-4 shrink-0">
                                <div class="w-16 h-16 rounded-2xl flex flex-col items-center justify-center text-center shadow-xs border {{ $event->isCancelled() ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-brand-50/70 border-brand-200/80 text-brand-900' }}">
                                    <span class="text-[10px] font-bold uppercase tracking-wider {{ $event->isCancelled() ? 'text-rose-600' : 'text-brand-600' }}">
                                        {{ $event->startsAtInTimezone($displayTimezone)->format('M') }}
                                    </span>
                                    <span class="text-xl font-black leading-none mt-0.5">
                                        {{ $event->startsAtInTimezone($displayTimezone)->format('d') }}
                                    </span>
                                    <span class="text-[9px] font-medium text-slate-500 mt-0.5">
                                        {{ $event->startsAtInTimezone($displayTimezone)->format('D') }}
                                    </span>
                                </div>

                                <div class="md:hidden">
                                    <div class="text-xs font-semibold text-slate-900">
                                        {{ $event->startsAtInTimezone($displayTimezone)->format('M d, Y') }}
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ $event->startsAtInTimezone($displayTimezone)->format('H:i') }} - {{ $event->endsAtInTimezone($displayTimezone)->format('H:i') }} ({{ $displayTimezone }})
                                    </div>
                                </div>
                            </div>

                            <!-- Middle: Event Details & Content -->
                            <div class="min-w-0 flex-1 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($event->isCancelled())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            {{ __('CANCELLED') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ __('SCHEDULED') }}
                                        </span>
                                    @endif
                                    <span class="text-[11px] font-semibold text-brand-700 bg-brand-50 px-2 py-0.5 rounded-full border border-brand-200">
                                        {{ $displayTimezone }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">
                                        {{ __('Original: :tz', ['tz' => $event->timezone]) }}
                                    </span>
                                </div>

                                <h3 class="text-base sm:text-lg font-bold text-slate-900">
                                    <a href="{{ route('communities.events.show', [$community, $event]) }}" class="hover:text-brand-600 transition {{ $event->isCancelled() ? 'line-through text-slate-400' : '' }}">
                                        {{ $event->title }}
                                    </a>
                                </h3>

                                @if ($event->description)
                                    <p class="text-slate-600 text-xs sm:text-sm line-clamp-2 leading-relaxed">
                                        {{ $event->description }}
                                    </p>
                                @endif

                                <div class="flex items-center gap-2 text-xs text-slate-500 pt-1">
                                    <span class="text-slate-400">{{ __('Organized by:') }}</span>
                                    <span class="font-medium text-slate-800">{{ $event->creator->name ?? __('Creator') }}</span>
                                    <span class="hidden md:inline text-slate-300">&bull;</span>
                                    <span class="hidden md:inline text-slate-500">
                                        {{ $event->startsAtInTimezone($displayTimezone)->format('H:i') }} - {{ $event->endsAtInTimezone($displayTimezone)->format('H:i') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Right: Participation Actions -->
                            <div class="flex flex-row md:flex-col items-center md:items-end justify-between w-full md:w-auto gap-3 shrink-0 pt-3 md:pt-0 border-t md:border-t-0 border-slate-100">
                                @if (! $event->isCancelled() && $event->meeting_url)
                                    <a href="{{ $event->meeting_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-semibold hover:bg-emerald-500 transition shadow-sm">
                                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        {{ __('Join Meeting') }}
                                    </a>
                                @endif

                                <a href="{{ route('communities.events.show', [$community, $event]) }}" class="inline-flex items-center text-xs font-semibold text-brand-600 hover:text-brand-700">
                                    <span>{{ __('View Details') }}</span>
                                    <span class="ml-1">&rarr;</span>
                                </a>
                            </div>
                        </article>
                    @empty
                        <div class="bg-white rounded-2xl p-12 text-center text-slate-500 border border-slate-200/80 shadow-xs">
                            <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-base font-semibold text-slate-900">{{ __('No events scheduled yet') }}</p>
                            <p class="text-sm text-slate-500 mt-1">{{ __('Stay tuned! Community events, workshops and coaching sessions will appear here.') }}</p>
                        </div>
                    @endforelse

                    <div class="mt-6">
                        {{ $events->links() }}
                    </div>
                </div>
            @endif
                </div>

                <!-- Context Sidebar Column with Skool Community Card -->
                <aside class="min-w-0 lg:col-span-4 space-y-6">
                    <x-skool-community-card :community="$community" />
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
