@props([
    'community',
    'activeTab' => 'feed',
])

@php
    $user = auth()->user();
    $isCreatorTab = in_array($activeTab, ['overview', 'orders', 'reports', 'invitations', 'referrals', 'members', 'settings', 'proof-of-work']);
@endphp

<div class="space-y-4">
    @if ($community->cover_path && $activeTab === 'about')
        <div class="relative w-full h-32 sm:h-44 rounded-2xl overflow-hidden shadow-xs border border-slate-200/80">
            <img src="{{ route('communities.cover.show', $community) }}" alt="{{ $community->name }}" class="w-full h-full object-cover">
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 sm:p-5">
        <!-- Compact community identity and mode controls -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <!-- Community title and access badges -->
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

            </div>

            <!-- Right: Utilities & Mode Switcher -->
            <div class="flex items-center flex-wrap gap-2 text-xs">
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

                        <a href="{{ route('communities.manage.proof-of-work.index', $community) }}"
                           @if ($activeTab === 'proof-of-work') aria-current="page" @endif
                           class="px-3 py-1.5 rounded-xl font-medium transition {{ $activeTab === 'proof-of-work' ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            {{ __('Review submissions') }}
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
