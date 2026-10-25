@props([
    'community',
    'activeTab' => 'feed',
])

@php
    $user = auth()->user();
    $isCreatorTab = in_array($activeTab, ['overview', 'orders', 'reports', 'invitations', 'referrals', 'members', 'settings', 'proof-of-work']);
@endphp

<div class="space-y-4">
    <div class="bg-surface rounded-xl shadow-2xs border border-line p-4 sm:p-5">
        <!-- Compact community identity and mode controls -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <!-- Community title and access badges -->
            <div class="min-w-0 flex flex-wrap items-center gap-3">
                <div class="min-w-0 flex flex-wrap items-center gap-2.5">
                    <h2 class="min-w-0 break-words font-bold text-xl sm:text-2xl text-ink tracking-tight">
                        {{ $community->name }}
                    </h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold {{ $community->isActive() ? 'bg-emerald-500/10 text-emerald-700 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-700 border border-amber-500/20' }}">
                        @if ($community->isActive())
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                        @endif
                        {{ $community->status }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-brand-soft text-brand border border-brand/20">
                        {{ $community->isCreator($user) ? __('Creator') : __('Member') }}
                    </span>
                </div>

            </div>

            <!-- Right: Utilities & Mode Switcher -->
            <div class="flex items-center flex-wrap gap-2 text-xs">
                <!-- Creator Studio / Member View Toggle -->
                @can('update', $community)
                    @if ($isCreatorTab)
                        <a href="{{ route('communities.posts.index', $community) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-surface hover:bg-slate-50 text-ink border border-line rounded-lg font-semibold shadow-2xs transition">
                            <svg class="w-3.5 h-3.5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>{{ __('View Community Feed') }}</span>
                        </a>
                    @else
                        <a href="{{ route('communities.manage', $community) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-soft hover:bg-teal-100/70 text-brand border border-brand/30 rounded-lg font-semibold shadow-2xs transition">
                            <svg class="w-3.5 h-3.5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>{{ __('Manage Community') }}</span>
                        </a>
                    @endif
                @endcan

                <a href="{{ route('communities.index') }}" class="inline-flex items-center px-2.5 py-1.5 bg-surface hover:bg-slate-50 border border-line rounded-lg font-medium text-ink shadow-2xs transition">
                    {{ __('Back to List') }}
                </a>
            </div>
        </div>

        <!-- Tab Row: Either Member Navigation OR Creator Studio Operations -->
        <div class="mt-4 pt-2 border-t border-line/60">
            @if ($isCreatorTab)
                <!-- Creator Operations Tab Bar -->
                <div x-data="{ creatorNavOpen: false }" @keydown.escape.stop.prevent="creatorNavOpen = false; $refs.creatorNavTrigger.focus()" class="min-w-0">
                    <button x-ref="creatorNavTrigger" type="button" @click="creatorNavOpen = !creatorNavOpen" :aria-expanded="creatorNavOpen.toString()" aria-controls="creator-navigation" class="sm:hidden min-h-11 w-full rounded-lg border border-line px-3 py-2 text-left text-sm font-semibold text-brand">
                        {{ __('Manage community navigation') }} <span aria-hidden="true">⌄</span>
                    </button>
                    <nav id="creator-navigation" :class="{ 'hidden': !creatorNavOpen }" class="hidden flex sm:flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-1.5 text-xs font-semibold" aria-label="Creator Operations">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-muted font-semibold uppercase tracking-wider text-[11px] mr-1">
                            <svg class="w-3.5 h-3.5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            {{ __('Creator Tools:') }}
                        </span>

                        <a href="{{ route('communities.manage', $community) }}"
                           @if ($activeTab === 'overview') aria-current="page" @endif
                           class="px-3 py-1.5 rounded-lg transition {{ $activeTab === 'overview' ? 'bg-brand text-white shadow-2xs' : 'text-muted hover:text-ink hover:bg-slate-100/80' }}">
                            {{ __('Overview') }}
                        </a>

                        <a href="{{ route('communities.orders.index', $community) }}"
                           @if ($activeTab === 'orders') aria-current="page" @endif
                           class="px-3 py-1.5 rounded-lg transition {{ $activeTab === 'orders' ? 'bg-brand text-white shadow-2xs' : 'text-muted hover:text-ink hover:bg-slate-100/80' }}">
                            {{ __('Orders') }}
                        </a>

                        <a href="{{ route('communities.reports.index', $community) }}"
                           @if ($activeTab === 'reports') aria-current="page" @endif
                           class="px-3 py-1.5 rounded-lg transition {{ $activeTab === 'reports' ? 'bg-brand text-white shadow-2xs' : 'text-muted hover:text-ink hover:bg-slate-100/80' }}">
                            {{ __('Reports') }}
                        </a>

                        <a href="{{ route('communities.manage.proof-of-work.index', $community) }}"
                           @if ($activeTab === 'proof-of-work') aria-current="page" @endif
                           class="px-3 py-1.5 rounded-lg transition {{ $activeTab === 'proof-of-work' ? 'bg-brand text-white shadow-2xs' : 'text-muted hover:text-ink hover:bg-slate-100/80' }}">
                            {{ __('Review submissions') }}
                        </a>

                        <a href="{{ route('communities.invitations.index', $community) }}"
                           @if ($activeTab === 'invitations') aria-current="page" @endif
                           class="px-3 py-1.5 rounded-lg transition {{ $activeTab === 'invitations' ? 'bg-brand text-white shadow-2xs' : 'text-muted hover:text-ink hover:bg-slate-100/80' }}">
                            {{ __('Invitations') }}
                        </a>

                        <a href="{{ route('communities.referrals.index', $community) }}"
                           @if ($activeTab === 'referrals') aria-current="page" @endif
                           class="px-3 py-1.5 rounded-lg transition {{ $activeTab === 'referrals' ? 'bg-brand text-white shadow-2xs' : 'text-muted hover:text-ink hover:bg-slate-100/80' }}">
                            {{ __('Referrals') }}
                        </a>

                        <a href="{{ route('communities.members.index', $community) }}"
                           @if ($activeTab === 'members') aria-current="page" @endif
                           class="px-3 py-1.5 rounded-lg transition {{ $activeTab === 'members' ? 'bg-brand text-white shadow-2xs' : 'text-muted hover:text-ink hover:bg-slate-100/80' }}">
                            {{ __('Manage Members') }}
                        </a>

                        <a href="{{ route('communities.edit', $community) }}"
                           @if ($activeTab === 'settings') aria-current="page" @endif
                           class="px-3 py-1.5 rounded-lg transition {{ $activeTab === 'settings' ? 'bg-brand text-white shadow-2xs' : 'text-muted hover:text-ink hover:bg-slate-100/80' }}">
                            {{ __('Edit Settings') }}
                        </a>
                    </nav>
                </div>
            @else
                <!-- Member Navigation Tabs -->
                {{-- Keep the current tab visible on narrow screens without scrolling the page. --}}
                <nav class="relative flex min-w-0 items-center gap-1 sm:gap-2 overflow-x-auto whitespace-nowrap pb-1 -mb-px" aria-label="Member Tabs" tabindex="0"
                     x-data x-init="$el.querySelector('[aria-current=page]') && $el.scrollWidth > $el.clientWidth && ($el.scrollLeft = $el.querySelector('[aria-current=page]').offsetLeft - 16)">
                    <a href="{{ route('communities.posts.index', $community) }}"
                       @if ($activeTab === 'feed') aria-current="page" @endif
                       class="inline-flex min-h-11 shrink-0 items-center px-3.5 py-2 text-sm font-semibold border-b-2 transition {{ $activeTab === 'feed' ? 'border-brand text-brand' : 'border-transparent text-muted hover:text-ink hover:border-slate-300' }}">
                        {{ __('Community Feed') }}
                    </a>

                    <a href="{{ route('communities.classroom.index', $community) }}"
                       @if ($activeTab === 'classroom') aria-current="page" @endif
                       class="inline-flex min-h-11 shrink-0 items-center px-3.5 py-2 text-sm font-semibold border-b-2 transition {{ $activeTab === 'classroom' ? 'border-brand text-brand' : 'border-transparent text-muted hover:text-ink hover:border-slate-300' }}">
                        {{ __('Classroom') }}
                    </a>

                    <a href="{{ route('communities.events.index', $community) }}"
                       @if ($activeTab === 'events') aria-current="page" @endif
                       class="inline-flex min-h-11 shrink-0 items-center px-3.5 py-2 text-sm font-semibold border-b-2 transition {{ $activeTab === 'events' ? 'border-brand text-brand' : 'border-transparent text-muted hover:text-ink hover:border-slate-300' }}">
                        {{ __('Events') }}
                    </a>

                    <a href="{{ route('communities.directory.index', $community) }}"
                       @if ($activeTab === 'directory') aria-current="page" @endif
                       class="inline-flex min-h-11 shrink-0 items-center px-3.5 py-2 text-sm font-semibold border-b-2 transition {{ $activeTab === 'directory' ? 'border-brand text-brand' : 'border-transparent text-muted hover:text-ink hover:border-slate-300' }}">
                        {{ __('Members') }}
                    </a>

                    <a href="{{ route('communities.leaderboard', $community) }}"
                       @if ($activeTab === 'leaderboard') aria-current="page" @endif
                       class="inline-flex min-h-11 shrink-0 items-center px-3.5 py-2 text-sm font-semibold border-b-2 transition {{ $activeTab === 'leaderboard' ? 'border-brand text-brand' : 'border-transparent text-muted hover:text-ink hover:border-slate-300' }}">
                        {{ __('Leaderboard') }}
                    </a>

                    <a href="{{ route('communities.show', $community) }}"
                       @if ($activeTab === 'about') aria-current="page" @endif
                       class="inline-flex min-h-11 shrink-0 items-center px-3.5 py-2 text-sm font-semibold border-b-2 transition {{ $activeTab === 'about' ? 'border-brand text-brand' : 'border-transparent text-muted hover:text-ink hover:border-slate-300' }}">
                        {{ __('About') }}
                    </a>
                </nav>
            @endif
        </div>
    </div>
</div>
