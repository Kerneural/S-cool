<div class="relative w-full sm:w-64 md:w-72 lg:w-80"
     x-data="{ isFocused: false }"
     data-request-id="{{ $requestId }}"
     data-last-processed-id="{{ $lastProcessedRequestId }}"
     @keydown.escape.window="$wire.closeDropdown()">

    <!-- Combobox Input Form with Progressive Fallback -->
    <form method="GET" action="{{ $community ? route('communities.search', $community) : '#' }}" class="relative m-0 p-0">
        <label for="community-typeahead-input" class="sr-only">
            {{ __('Search :name', ['name' => $community?->name ?? 'Community']) }}
        </label>

        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
            <!-- Search Icon (hidden when loading) -->
            <svg wire:loading.remove wire:target="query" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <!-- Loading Indicator -->
            <svg wire:loading wire:target="query" class="w-4 h-4 text-brand-600 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <input type="text"
               name="q"
               id="community-typeahead-input"
               role="combobox"
               aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
               aria-haspopup="listbox"
               aria-controls="community-search-results"
               aria-autocomplete="list"
               aria-activedescendant="{{ $selectedIndex >= 0 && isset($flattenedResults[$selectedIndex]) ? 'search-option-' . $selectedIndex : '' }}"
               placeholder="{{ __('Search community...') }}"
               autocomplete="off"
               spellcheck="false"
               wire:model.live.debounce.300ms="query"
               wire:keydown.arrow-down.prevent="selectNext"
               wire:keydown.arrow-up.prevent="selectPrevious"
               wire:keydown.enter.prevent="selectActive"
               wire:keydown.escape="closeDropdown"
               @focus="isFocused = true; $wire.openDropdown()"
               @click.outside="isFocused = false; $wire.closeDropdown()"
               class="block w-full pl-9 pr-8 py-2 bg-slate-50 border border-slate-200/90 rounded-xl text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all">

        <!-- Clear Query Affordance -->
        @if ($query !== '')
            <button type="button"
                    wire:click="clearQuery"
                    aria-label="{{ __('Clear search') }}"
                    class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        @endif
    </form>

    <!-- Screen Reader Status Announcements -->
    <div class="sr-only" role="status" aria-live="polite" aria-atomic="true">
        @if ($errorMessage)
            {{ $errorMessage }}
        @elseif ($isOpen && $totalResultsCount > 0)
            {{ trans_choice('{1} :count result found.|[2,*] :count results found.', $totalResultsCount, ['count' => $totalResultsCount]) }}
        @elseif ($isOpen && mb_strlen(trim($query)) >= 2 && $totalResultsCount === 0)
            {{ __('No results found for :query', ['query' => $query]) }}
        @endif
    </div>

    <!-- Floating Dropdown Suggestions Panel -->
    @if ($isOpen)
        <div id="community-search-results"
             role="listbox"
             aria-label="{{ __('Search suggestions') }}"
             class="absolute left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-slate-200/90 overflow-hidden z-50 max-h-[75vh] flex flex-col focus:outline-none">

            <!-- Error State -->
            @if ($errorMessage)
                <div class="p-4 bg-rose-50 border-b border-rose-100 flex items-center justify-between text-xs text-rose-800">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>{{ $errorMessage }}</span>
                    </div>
                    <button type="button" wire:click="updatedQuery" class="font-semibold text-rose-700 underline hover:text-rose-900 ml-2">
                        {{ __('Retry') }}
                    </button>
                </div>
            @endif

            <!-- Empty State -->
            @if (! $errorMessage && $totalResultsCount === 0 && mb_strlen(trim($query)) >= 2)
                <div class="p-6 text-center">
                    <div class="mx-auto w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-800">{{ __('No matching results') }}</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">{{ __('No posts, courses, members or events match ":query".', ['query' => $query]) }}</p>
                    @if ($community)
                        <div class="mt-3">
                            <a href="{{ route('communities.search', [$community, 'q' => trim($query)]) }}"
                               class="inline-flex items-center text-xs font-semibold text-brand-600 hover:text-brand-700 underline">
                                {{ __('Try full search &rarr;') }}
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Populated Grouped Results -->
            @if ($totalResultsCount > 0)
                <div class="overflow-y-auto divide-y divide-slate-100 scrollbar-thin">
                    @php($currentIndex = 0)

                    <!-- 1. Posts Section -->
                    @if (count($results['posts']) > 0)
                        <div class="py-1">
                            <div class="px-3.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50/70 flex items-center justify-between">
                                <span>{{ __('Discussions') }}</span>
                                <span class="font-normal">{{ count($results['posts']) }}</span>
                            </div>
                            @foreach ($results['posts'] as $post)
                                @php($itemIndex = $currentIndex++)
                                <a id="search-option-{{ $itemIndex }}"
                                   role="option"
                                   aria-selected="{{ $selectedIndex === $itemIndex ? 'true' : 'false' }}"
                                   href="{{ $post['url'] }}"
                                   class="flex items-center justify-between px-3.5 py-2.5 text-xs transition {{ $selectedIndex === $itemIndex ? 'bg-brand-50/80 text-brand-900 font-semibold' : 'text-slate-800 hover:bg-slate-50' }}">
                                    <div class="min-w-0 pr-2">
                                        <div class="truncate text-slate-900 font-medium">{{ $post['title'] }}</div>
                                        <div class="text-[11px] text-slate-400 truncate">{{ $post['subtitle'] }}</div>
                                    </div>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 shrink-0">
                                        {{ __('Post') }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <!-- 2. Courses & Lessons Section -->
                    @if (count($results['courses_lessons']) > 0)
                        <div class="py-1">
                            <div class="px-3.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50/70 flex items-center justify-between">
                                <span>{{ __('Classroom') }}</span>
                                <span class="font-normal">{{ count($results['courses_lessons']) }}</span>
                            </div>
                            @foreach ($results['courses_lessons'] as $courseLesson)
                                @php($itemIndex = $currentIndex++)
                                <a id="search-option-{{ $itemIndex }}"
                                   role="option"
                                   aria-selected="{{ $selectedIndex === $itemIndex ? 'true' : 'false' }}"
                                   href="{{ $courseLesson['url'] }}"
                                   class="flex items-center justify-between px-3.5 py-2.5 text-xs transition {{ $selectedIndex === $itemIndex ? 'bg-brand-50/80 text-brand-900 font-semibold' : 'text-slate-800 hover:bg-slate-50' }}">
                                    <div class="min-w-0 pr-2">
                                        <div class="truncate text-slate-900 font-medium">{{ $courseLesson['title'] }}</div>
                                        <div class="text-[11px] text-slate-400 truncate">{{ $courseLesson['subtitle'] }}</div>
                                    </div>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $courseLesson['badge'] === 'Course' ? 'bg-brand-50 text-brand-700' : 'bg-emerald-50 text-emerald-700' }} shrink-0">
                                        {{ $courseLesson['badge'] }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <!-- 3. Members Section -->
                    @if (count($results['members']) > 0)
                        <div class="py-1">
                            <div class="px-3.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50/70 flex items-center justify-between">
                                <span>{{ __('Members') }}</span>
                                <span class="font-normal">{{ count($results['members']) }}</span>
                            </div>
                            @foreach ($results['members'] as $member)
                                @php($itemIndex = $currentIndex++)
                                <a id="search-option-{{ $itemIndex }}"
                                   role="option"
                                   aria-selected="{{ $selectedIndex === $itemIndex ? 'true' : 'false' }}"
                                   href="{{ $member['url'] }}"
                                   class="flex items-center justify-between px-3.5 py-2.5 text-xs transition {{ $selectedIndex === $itemIndex ? 'bg-brand-50/80 text-brand-900 font-semibold' : 'text-slate-800 hover:bg-slate-50' }}">
                                    <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                        <img src="{{ $member['avatar_url'] }}"
                                             alt="{{ $member['title'] }}"
                                             class="w-6 h-6 rounded-full object-cover bg-slate-100 border border-slate-200 shrink-0">
                                        <div class="min-w-0">
                                            <div class="truncate text-slate-900 font-medium">{{ $member['title'] }}</div>
                                            <div class="text-[11px] text-slate-400 truncate">{{ $member['subtitle'] }}</div>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $member['badge'] === 'Creator' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600' }} shrink-0">
                                        {{ $member['badge'] }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <!-- 4. Events Section -->
                    @if (count($results['events']) > 0)
                        <div class="py-1">
                            <div class="px-3.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50/70 flex items-center justify-between">
                                <span>{{ __('Events') }}</span>
                                <span class="font-normal">{{ count($results['events']) }}</span>
                            </div>
                            @foreach ($results['events'] as $event)
                                @php($itemIndex = $currentIndex++)
                                <a id="search-option-{{ $itemIndex }}"
                                   role="option"
                                   aria-selected="{{ $selectedIndex === $itemIndex ? 'true' : 'false' }}"
                                   href="{{ $event['url'] }}"
                                   class="flex items-center justify-between px-3.5 py-2.5 text-xs transition {{ $selectedIndex === $itemIndex ? 'bg-brand-50/80 text-brand-900 font-semibold' : 'text-slate-800 hover:bg-slate-50' }}">
                                    <div class="min-w-0 pr-2">
                                        <div class="truncate text-slate-900 font-medium">{{ $event['title'] }}</div>
                                        <div class="text-[11px] text-slate-400 truncate">{{ $event['subtitle'] }}</div>
                                    </div>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 shrink-0">
                                        {{ __('Event') }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Footer with Full Search and Keyboard Instructions -->
                <div class="px-3.5 py-2 bg-slate-50/80 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 text-[11px] text-slate-500">
                    @if ($community && mb_strlen(trim($query)) >= 2)
                        <a href="{{ route('communities.search', [$community, 'q' => trim($query)]) }}"
                           class="font-semibold text-brand-600 hover:text-brand-700 underline truncate">
                            {{ __('See all results for ":q"', ['q' => trim($query)]) }} &rarr;
                        </a>
                    @else
                        <span></span>
                    @endif
                    <div class="hidden sm:flex items-center gap-2 text-[10px] text-slate-400">
                        <span><kbd class="px-1 py-0.5 bg-white border border-slate-200 rounded text-[9px]">↑</kbd> <kbd class="px-1 py-0.5 bg-white border border-slate-200 rounded text-[9px]">↓</kbd> navigate</span>
                        <span><kbd class="px-1 py-0.5 bg-white border border-slate-200 rounded text-[9px]">Enter</kbd> open</span>
                        <span><kbd class="px-1 py-0.5 bg-white border border-slate-200 rounded text-[9px]">Esc</kbd> close</span>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
