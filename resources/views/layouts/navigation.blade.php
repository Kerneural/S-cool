@php
    $currentRouteCommunity = request()->route('community');
    $authCommunity = ($currentRouteCommunity instanceof \App\Models\Community && $currentRouteCommunity->exists)
        ? $currentRouteCommunity
        : null;

    $currentUser = Auth::user();
    $hasCommunityContext = $authCommunity && $authCommunity->isActive() && $currentUser && ($authCommunity->isCreator($currentUser) || $authCommunity->memberships()->where('user_id', $currentUser->id)->effective()->exists());

    $unreadNotificationsCount = ($hasCommunityContext && $currentUser)
        ? \App\Models\AppNotification::unreadCountFor($currentUser, $authCommunity)
        : 0;

    $switcherCommunities = ($hasCommunityContext && $currentUser)
        ? \App\Models\Community::query()
            ->accessibleTo($currentUser)
            ->active()
            ->where('id', '!=', $authCommunity->id)
            ->orderBy('name')
            ->get()
        : collect();
@endphp

<nav x-data="{ open: false, switcherOpen: false }" class="bg-white border-b border-slate-200/80 sticky top-0 z-40">
    <!-- Primary Navigation Menu (Contextual Shell / Global Shell) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex min-h-16 flex-wrap items-center justify-between gap-x-3 gap-y-2 py-2">
            <!-- Left: Brand Logo & Switcher or Global Links -->
            <div class="order-1 flex min-w-0 flex-1 items-center gap-2 md:flex-none md:gap-3">
                <!-- S-cool Home / Brand Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" aria-label="{{ __('S-cool home') }}">
                        <x-application-logo class="block h-8 w-auto fill-current text-slate-800" />
                    </a>
                </div>

                @if ($hasCommunityContext)
                    <!-- Contextual Shell: Community Switcher Dropdown (AC-08) -->
                    <div class="relative min-w-0 flex-1 text-left" @keydown.escape.stop.prevent="if (switcherOpen) { switcherOpen = false; $refs.shellSwitcherTrigger.focus() }">
                        <button type="button"
                                x-ref="shellSwitcherTrigger" aria-label="{{ __('Switch Community') }}" @click="switcherOpen = !switcherOpen"
                                :aria-expanded="switcherOpen.toString()"
                                aria-controls="shell-community-switcher"
                                class="inline-flex min-h-11 items-center gap-2 px-3 py-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200/80 rounded-xl text-xs sm:text-sm font-bold text-slate-900 transition focus:outline-none focus:ring-2 focus:ring-brand-500 min-w-0 max-w-full md:max-w-[200px] lg:max-w-[260px]">
                            <span class="truncate">{{ $authCommunity->name }}</span>
                            <svg class="w-3.5 h-3.5 text-slate-600 shrink-0 transition-transform duration-150" :class="{ 'rotate-180': switcherOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div id="shell-community-switcher"
                             x-show="switcherOpen"
                             @click.outside="switcherOpen = false"
                             x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="origin-top-left absolute left-0 mt-2 w-72 max-w-[calc(100vw-5rem)] max-h-[70vh] overflow-y-auto rounded-2xl shadow-xl bg-white border border-slate-200/80 divide-y divide-slate-100 focus:outline-none z-50">
                            <div class="py-1">
                                <div class="px-4 py-2 text-xs font-semibold text-slate-600 uppercase tracking-wider bg-slate-50">
                                    {{ __('Your Active Communities') }}
                                </div>
                                @if ($switcherCommunities->isEmpty())
                                    <div class="px-4 py-3 text-xs text-slate-500 italic">
                                        {{ __('No other active communities') }}
                                    </div>
                                @else
                                    @foreach ($switcherCommunities as $other)
                                        <a href="{{ route('communities.posts.index', $other) }}" class="flex items-center justify-between px-4 py-2.5 text-sm text-slate-800 hover:bg-brand-50 hover:text-brand-700 transition">
                                            <span class="truncate font-medium">{{ $other->name }}</span>
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
                @else
                    <!-- Global Shell Navigation Links -->
                    <div class="hidden space-x-6 sm:-my-px sm:ms-6 sm:flex">
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('communities.index')" :active="request()->routeIs('communities.*') && !request()->routeIs('admin.*')">
                            {{ __('Communities') }}
                        </x-nav-link>
                        <x-nav-link :href="route('help.index')" :active="request()->routeIs('help.*')">
                            {{ __('Help') }}
                        </x-nav-link>
                        @if (Auth::user()?->isPlatformAdmin())
                            <x-nav-link :href="route('admin.communities.index')" :active="request()->routeIs('admin.*')">
                                {{ __('Platform Admin') }}
                            </x-nav-link>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Center: Scoped Search Island (Contextual Shell) -->
            @if ($hasCommunityContext)
                <div class="order-3 flex w-full basis-full md:order-2 md:w-auto md:basis-0 md:flex-1 md:justify-center md:max-w-md">
                    <livewire:community-search-typeahead :community="$authCommunity" />
                </div>
            @endif

            <!-- Right: Utilities & Avatar Dropdown -->
            <div class="order-2 flex shrink-0 items-center gap-1 md:order-3 md:gap-2.5">
                @if ($hasCommunityContext)
                    <!-- Tenant Notifications Utility -->
                    <a href="{{ route('communities.notifications.index', $authCommunity) }}"
                       title="{{ __('Notifications') }}" aria-label="{{ __('Notifications') }}"
                       class="inline-flex min-h-11 min-w-11 items-center justify-center relative p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @if ($unreadNotificationsCount > 0)
                            <span class="absolute top-1.5 right-1.5 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500 text-white">
                                {{ $unreadNotificationsCount }}
                            </span>
                        @endif
                    </a>

                    <!-- Tenant Messages Utility -->
                    <a href="{{ route('communities.messages.index', $authCommunity) }}"
                       title="{{ __('Messages') }}" aria-label="{{ __('Messages') }}"
                       class="inline-flex min-h-11 min-w-11 items-center justify-center p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </a>
                @endif

                <!-- Account Dropdown / Auth Links -->
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button type="button" :aria-expanded="open.toString()" aria-haspopup="true" aria-label="{{ __('Account menu for :name', ['name' => Auth::user()->name]) }}" class="inline-flex min-h-11 items-center gap-2 p-1 rounded-full hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand transition">
                                @if (Auth::user()->avatar_path)
                                    <img src="{{ route('profile.avatar') }}" alt="{{ Auth::user()->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-200">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-brand-50 text-brand-700 font-bold text-xs flex items-center justify-center border border-brand-200">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                @endif
                                <span class="hidden sm:inline text-xs font-semibold text-slate-800 max-w-[120px] truncate">{{ Auth::user()->name }}</span>
                                <svg class="hidden sm:inline fill-current h-3.5 w-3.5 text-slate-600" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile & Settings') }}
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('account.memberships.index')">
                                {{ __('My Access') }}
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('account.payment-history.index')">
                                {{ __('Payment History') }}
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('account.referrals')">
                                {{ __('Referrals') }}
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('help.index')">
                                {{ __('Help Center') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <div class="flex items-center space-x-3 text-xs font-semibold">
                        <a href="{{ route('login') }}" class="text-slate-600 hover:text-slate-900">{{ __('Log in') }}</a>
                        <a href="{{ route('register') }}" class="px-3 py-1.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl shadow-xs transition">{{ __('Register') }}</a>
                    </div>
                @endauth

                @unless ($hasCommunityContext)
                <!-- Hamburger Mobile Toggle -->
                <div class="flex items-center sm:hidden">
                    <button type="button" @click="open = ! open" :aria-expanded="open.toString()" aria-controls="mobile-navigation" aria-label="{{ __('Open navigation') }}" class="inline-flex min-h-11 min-w-11 items-center justify-center p-2 rounded-xl text-slate-600 hover:text-slate-700 hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand transition">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                @endunless
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu for Mobile (AC-08) -->
    @unless ($hasCommunityContext)
    <div id="mobile-navigation" :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-slate-200/80 bg-white" @keydown.escape.stop.prevent="open = false; $root.querySelector('[aria-controls=mobile-navigation]').focus()">
        @if ($hasCommunityContext)
            <!-- Mobile Community Quick Links -->
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link :href="route('communities.posts.index', $authCommunity)" :active="request()->routeIs('communities.posts.*')">
                    {{ __('Community Feed') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('communities.classroom.index', $authCommunity)" :active="request()->routeIs('communities.classroom.*')">
                    {{ __('Classroom') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('communities.events.index', $authCommunity)" :active="request()->routeIs('communities.events.*')">
                    {{ __('Events') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('communities.directory.index', $authCommunity)" :active="request()->routeIs('communities.members.*') || request()->routeIs('communities.directory.*')">
                    {{ __('Members & Directory') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('communities.leaderboard', $authCommunity)" :active="request()->routeIs('communities.leaderboard')">
                    {{ __('Leaderboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('communities.about', $authCommunity)" :active="request()->routeIs('communities.about')">
                    {{ __('About') }}
                </x-responsive-nav-link>
            </div>
        @else
            <!-- Global Mobile Links -->
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('communities.index')" :active="request()->routeIs('communities.*') && !request()->routeIs('admin.*')">
                    {{ __('Communities') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('help.index')" :active="request()->routeIs('help.*')">
                    {{ __('Help') }}
                </x-responsive-nav-link>
                @if (Auth::user()?->isPlatformAdmin())
                    <x-responsive-nav-link :href="route('admin.communities.index')" :active="request()->routeIs('admin.*')">
                        {{ __('Platform Admin') }}
                    </x-responsive-nav-link>
                @endif
            </div>
        @endif

        <!-- Responsive Settings & Profile Options -->
        <div class="pt-4 pb-2 border-t border-slate-200">
            @auth
                <div class="px-4">
                    <div class="font-bold text-sm text-slate-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-xs text-slate-600 font-mono">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile & Settings') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('account.memberships.index')">
                        {{ __('My Access') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('account.payment-history.index')">
                        {{ __('Payment History') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('account.referrals')">
                        {{ __('Referrals') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            @else
                <div class="px-4 py-2 space-y-2">
                    <a href="{{ route('login') }}" class="block text-sm font-medium text-slate-700 hover:text-slate-900">{{ __('Log in') }}</a>
                    <a href="{{ route('register') }}" class="block text-sm font-medium text-brand-600 hover:text-brand-800">{{ __('Register') }}</a>
                </div>
            @endauth
        </div>
    </div>
    @endunless
</nav>
