@props([
    'community',
    'activeTab' => 'feed',
])

@php
    $user = auth()->user();
    $unreadNotificationsCount = $user ? \App\Models\AppNotification::unreadCountFor($user, $community) : 0;
    $switcherCommunities = $user
        ? \App\Models\Community::query()
            ->accessibleTo($user)
            ->active()
            ->where('id', '!=', $community->id)
            ->orderBy('name')
            ->get()
        : collect();
    $isCreatorTab = in_array($activeTab, ['overview', 'orders', 'reports', 'invitations', 'referrals', 'members', 'settings']);
@endphp

<div class="space-y-4">
    @if ($community->cover_path && ! $isCreatorTab)
        <div class="relative w-full h-32 sm:h-44 rounded-2xl overflow-hidden shadow-xs border border-slate-200/80">
            <img src="{{ route('communities.cover.show', $community) }}" alt="{{ $community->name }}" class="w-full h-full object-cover">
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 sm:p-5">
        <!-- Top Row: Identity, Badges, Switcher & Utilities -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <!-- Left: Community Title, Badges, Switcher -->
            <div class="min-w-0 flex flex-wrap items-center gap-3">
                <div class="min-w-0 flex flex-wrap items-center gap-2.5">
                    <h2 class="min-w-0 break-words font-bold text-xl sm:text-2xl text-slate-900 tracking-tight">
                        {{ $community->name }}
                    </h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $community->isActive() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                        @if ($community->isActive())
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                        @endif
                        {{ $community->status }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700 border border-brand-200">
                        {{ $community->isCreator($user) ? __('Creator') : __('Member') }}
                    </span>
                </div>

                <!-- Community Switcher Dropdown -->
                <div x-data="{ open: false }" class="relative inline-block text-left" @keydown.escape.window="open = false">
                    <button x-ref="switcherTrigger" @click="open = !open" type="button" :aria-expanded="open.toString()" aria-controls="community-switcher" class="inline-flex min-h-11 items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200/80 rounded-xl text-sm font-semibold text-slate-700 transition focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <span>{{ __('Switch Community') }}</span>
                        <svg class="w-3.5 h-3.5 text-slate-500 transition-transform duration-150" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="community-switcher" x-show="open" @keydown.escape.stop.prevent="open = false; $refs.switcherTrigger.focus()" @click.outside="open = false" x-cloak x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="origin-top-left absolute left-0 mt-2 w-64 max-w-[calc(100vw-4rem)] rounded-2xl shadow-xl bg-white border border-slate-200/80 divide-y divide-slate-100 focus:outline-none z-50 overflow-hidden">
                        <div class="py-1">
                            <div class="px-4 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                                {{ __('Your Active Communities') }}
                            </div>
                            @if ($switcherCommunities->isEmpty())
                                <div class="px-4 py-3 text-xs text-slate-500 italic">
                                    {{ __('No other active communities') }}
                                </div>
                            @else
                                @foreach ($switcherCommunities as $other)
                                    <a href="{{ route('communities.posts.index', $other) }}" class="flex items-center justify-between px-4 py-2.5 text-sm text-slate-800 hover:bg-brand-50 hover:text-brand-700 transition">
                                        <span class="min-w-0 break-words font-medium">{{ $other->name }}</span>
                                    </a>
                                @endforeach
                            @endif
                        </div>
                        <div class="p-2 bg-slate-50">
                            <a href="{{ route('communities.index') }}" class="block px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 rounded-lg hover:bg-white transition text-center">
                                &larr; {{ __('View All Communities') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Utilities & Mode Switcher -->
            <div class="flex items-center flex-wrap gap-2 text-xs">
                <!-- Live Search Combobox Island -->
                <livewire:community-search-typeahead :community="$community" />

                <!-- Notifications Utility -->
                <a href="{{ route('communities.notifications.index', $community) }}"
                   title="{{ __('Notifications') }}" aria-label="{{ __('Notifications') }}"
                   class="inline-flex min-h-11 min-w-11 items-center justify-center gap-1 px-2.5 py-1.5 rounded-xl border {{ $activeTab === 'notifications' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'bg-slate-50 hover:bg-slate-100 text-slate-600 border-slate-200/70' }} font-medium transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="hidden sm:inline">{{ __('Notifications') }}</span>
                    @if ($unreadNotificationsCount > 0)
                        <span class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-rose-500 text-white">
                            {{ $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>

                <!-- Messages Utility -->
                <a href="{{ route('communities.messages.index', $community) }}"
                   title="{{ __('Messages') }}" aria-label="{{ __('Messages') }}"
                   class="inline-flex min-h-11 min-w-11 items-center justify-center gap-1 px-2.5 py-1.5 rounded-xl border {{ $activeTab === 'messages' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'bg-slate-50 hover:bg-slate-100 text-slate-600 border-slate-200/70' }} font-medium transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <span class="hidden sm:inline">{{ __('Messages') }}</span>
                </a>

                <!-- Creator Studio / Member View Toggle -->
                @can('update', $community)
                    @if ($isCreatorTab)
                        <a href="{{ route('communities.posts.index', $community) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>{{ __('View Community Feed') }}</span>
                        </a>
                    @else
                        <a href="{{ route('communities.manage', $community) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 border border-brand-200/80 rounded-xl font-semibold transition">
                            <svg class="w-3.5 h-3.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>{{ __('Manage Community') }}</span>
                        </a>
                    @endif
                @endcan

                <a href="{{ route('communities.index') }}" class="inline-flex items-center px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-xl font-medium text-slate-700 transition">
                    {{ __('Back to List') }}
                </a>
            </div>
        </div>

        <!-- Tab Row: Either Member Navigation OR Creator Studio Operations -->
        <div class="mt-4 pt-2 border-t border-slate-100">
            @if ($isCreatorTab)
                <!-- Creator Operations Tab Bar -->
                <div x-data="{ creatorNavOpen: false }" @keydown.escape.stop.prevent="creatorNavOpen = false; $refs.creatorNavTrigger.focus()" class="min-w-0">
                    <button x-ref="creatorNavTrigger" type="button" @click="creatorNavOpen = !creatorNavOpen" :aria-expanded="creatorNavOpen.toString()" aria-controls="creator-navigation" class="sm:hidden min-h-11 w-full rounded-lg border border-line px-3 py-2 text-left text-sm font-semibold text-brand">
                        {{ __('Manage community navigation') }} <span aria-hidden="true">⌄</span>
                    </button>
                    <nav id="creator-navigation" :class="{ 'hidden': !creatorNavOpen }" class="hidden flex sm:flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-1.5 text-sm" aria-label="Creator Operations">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-slate-400 font-semibold uppercase tracking-wider text-[11px] mr-1">
                            <svg class="w-3.5 h-3.5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            {{ __('Creator Tools:') }}
                        </span>

                        <a href="{{ route('communities.manage', $community) }}"
                           class="px-3 py-1.5 rounded-xl font-medium transition {{ $activeTab === 'overview' ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            {{ __('Overview') }}
                        </a>

                        <a href="{{ route('communities.orders.index', $community) }}"
                           class="px-3 py-1.5 rounded-xl font-medium transition {{ $activeTab === 'orders' ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            {{ __('Orders') }}
                        </a>

                        <a href="{{ route('communities.reports.index', $community) }}"
                           class="px-3 py-1.5 rounded-xl font-medium transition {{ $activeTab === 'reports' ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            {{ __('Reports') }}
                        </a>

                        <a href="{{ route('communities.invitations.index', $community) }}"
                           class="px-3 py-1.5 rounded-xl font-medium transition {{ $activeTab === 'invitations' ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            {{ __('Invitations') }}
                        </a>

                        <a href="{{ route('communities.referrals.index', $community) }}"
                           class="px-3 py-1.5 rounded-xl font-medium transition {{ $activeTab === 'referrals' ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            {{ __('Referrals') }}
                        </a>

                        <a href="{{ route('communities.members.index', $community) }}"
                           class="px-3 py-1.5 rounded-xl font-medium transition {{ $activeTab === 'members' ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            {{ __('Manage Members') }}
                        </a>

                        <a href="{{ route('communities.edit', $community) }}"
                           class="px-3 py-1.5 rounded-xl font-medium transition {{ $activeTab === 'settings' ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            {{ __('Edit Settings') }}
                        </a>
                    </nav>
                </div>
            @else
                <!-- Member Navigation Tabs -->
                <nav class="flex min-w-0 items-center gap-1 sm:gap-2 overflow-x-auto whitespace-nowrap pb-1 -mb-px" aria-label="Member Tabs" tabindex="0">
                    <a href="{{ route('communities.posts.index', $community) }}"
                       class="inline-flex min-h-11 shrink-0 items-center px-3 py-2 text-sm font-medium border-b-2 transition {{ $activeTab === 'feed' ? 'border-brand-600 text-brand-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-900 hover:border-slate-300' }}">
                        {{ __('Community Feed') }}
                    </a>

                    <a href="{{ route('communities.classroom.index', $community) }}"
                       class="inline-flex min-h-11 shrink-0 items-center px-3 py-2 text-sm font-medium border-b-2 transition {{ $activeTab === 'classroom' ? 'border-brand-600 text-brand-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-900 hover:border-slate-300' }}">
                        {{ __('Classroom') }}
                    </a>

                    <a href="{{ route('communities.events.index', $community) }}"
                       class="inline-flex min-h-11 shrink-0 items-center px-3 py-2 text-sm font-medium border-b-2 transition {{ $activeTab === 'events' ? 'border-brand-600 text-brand-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-900 hover:border-slate-300' }}">
                        {{ __('Events') }}
                    </a>

                    <a href="{{ route('communities.directory.index', $community) }}"
                       class="inline-flex min-h-11 shrink-0 items-center px-3 py-2 text-sm font-medium border-b-2 transition {{ $activeTab === 'directory' ? 'border-brand-600 text-brand-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-900 hover:border-slate-300' }}">
                        {{ __('Members') }}
                    </a>

                    <a href="{{ route('communities.leaderboard', $community) }}"
                       class="inline-flex min-h-11 shrink-0 items-center px-3 py-2 text-sm font-medium border-b-2 transition {{ $activeTab === 'leaderboard' ? 'border-brand-600 text-brand-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-900 hover:border-slate-300' }}">
                        {{ __('Leaderboard') }}
                    </a>

                    <a href="{{ route('communities.show', $community) }}"
                       class="inline-flex min-h-11 shrink-0 items-center px-3 py-2 text-sm font-medium border-b-2 transition {{ $activeTab === 'about' ? 'border-brand-600 text-brand-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-900 hover:border-slate-300' }}">
                        {{ __('About') }}
                    </a>
                </nav>
            @endif
        </div>
    </div>
</div>
