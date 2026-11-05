@php
    $currentRouteCommunity = request()->route('community');
    $authCommunity = ($currentRouteCommunity instanceof \App\Models\Community && $currentRouteCommunity->exists)
        ? $currentRouteCommunity
        : null;

    $currentUser = Auth::user();
    $hasCommunityContext = $authCommunity && $authCommunity->isActive() && $currentUser && ($authCommunity->isCreator($currentUser) || $authCommunity->memberships()->where('user_id', $currentUser->id)->effective()->exists());

    $switcherCommunities = ($hasCommunityContext && $currentUser)
        ? \App\Models\Community::query()
            ->accessibleTo($currentUser)
            ->active()
            ->withCount(['memberships' => function ($q) {
                $q->effective();
            }])
            ->orderBy('name')
            ->get()
        : collect();
@endphp

<nav x-data="{ open: false, switcherOpen: false }" class="bg-surface/90 backdrop-blur-md border-b border-line sticky top-0 z-40 transition-all">
    <!-- Primary Navigation Menu (Contextual Shell / Global Shell) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex min-h-16 flex-wrap items-center justify-between gap-x-3 gap-y-2 py-2">
            <!-- Left: Brand Logo & Switcher or Global Links -->
            <div class="order-1 flex min-w-0 flex-1 items-center gap-2 md:flex-none md:gap-3">
                <!-- S-cool Home / Brand Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('communities.index') }}" aria-label="{{ __('S-cool home') }}" class="group rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2">
                        <x-application-logo class="block h-8 w-auto group-hover:scale-105 transition-transform" />
                    </a>
                </div>

                @if ($hasCommunityContext)
                    <!-- Contextual Shell: Community Switcher Dropdown (AC-08) -->
                    <div class="relative min-w-0 flex-1 text-left" @keydown.escape.stop.prevent="if (switcherOpen) { switcherOpen = false; $refs.shellSwitcherTrigger.focus() }">
                        <button type="button"
                                x-ref="shellSwitcherTrigger" aria-label="{{ __('Switch Community') }}" @click="switcherOpen = !switcherOpen"
                                :aria-expanded="switcherOpen.toString()"
                                aria-controls="shell-community-switcher"
                                class="inline-flex min-h-10 items-center gap-2 px-3 py-1.5 bg-slate-50 hover:bg-slate-100 border border-line rounded-lg text-xs sm:text-sm font-semibold text-ink transition focus:outline-none focus:ring-2 focus:ring-brand min-w-0 max-w-full md:max-w-[200px] lg:max-w-[260px] shadow-2xs">
                            <span class="truncate">{{ $authCommunity->name }}</span>
                            <svg class="w-3.5 h-3.5 text-muted shrink-0 transition-transform duration-150" :class="{ 'rotate-180': switcherOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div id="shell-community-switcher"
                             x-show="switcherOpen"
                             @click.outside="switcherOpen = false"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="transform opacity-0 -translate-y-1"
                             x-transition:enter-end="transform opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="transform opacity-100 translate-y-0"
                             x-transition:leave-end="transform opacity-0 -translate-y-1"
                             class="origin-top-left absolute left-0 mt-2 w-80 max-w-[calc(100vw-3rem)] max-h-[75vh] overflow-y-auto rounded-xl shadow-xl shadow-slate-900/10 bg-surface border border-line focus:outline-none z-50">
                            <div class="px-3.5 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-slate-50/80 border-b border-line/60">
                                {{ __('Switch Community') }}
                            </div>
                            <div class="p-1.5 space-y-1">
                                @foreach ($switcherCommunities as $comm)
                                    @php
                                        $isCurrent = $authCommunity && $comm->id === $authCommunity->id;
                                    @endphp
                                    <a href="{{ route('communities.posts.index', $comm) }}"
                                       class="flex items-center justify-between gap-3 px-3 py-2 rounded-lg transition {{ $isCurrent ? 'bg-teal-500/10 border border-teal-500/20 text-teal-950 font-bold shadow-2xs' : 'hover:bg-slate-50 text-slate-800' }}">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            @if ($comm->cover_path)
                                                <img src="{{ route('communities.cover.show', $comm) }}" alt="{{ $comm->name }}" class="w-9 h-9 rounded-lg object-cover border border-slate-200 shrink-0">
                                            @else
                                                <div class="w-9 h-9 rounded-lg {{ $isCurrent ? 'bg-brand text-white shadow-2xs' : 'bg-brand-soft text-brand' }} font-bold text-sm flex items-center justify-center border border-brand/20 shrink-0">
                                                    {{ strtoupper(substr($comm->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <div class="truncate text-sm {{ $isCurrent ? 'font-bold text-teal-950' : 'font-semibold text-slate-800' }}">
                                                    {{ $comm->name }}
                                                </div>
                                                <div class="text-xs {{ $isCurrent ? 'text-teal-700/80 font-medium' : 'text-slate-500' }}">
                                                    {{ $comm->memberships_count ?? 0 }} {{ __('thành viên') }}
                                                </div>
                                            </div>
                                        </div>
                                        @if ($isCurrent)
                                            <svg class="w-5 h-5 text-brand shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                            <div class="p-1.5 bg-slate-50/90 border-t border-line/60">
                                <a href="{{ route('communities.index') }}"
                                   class="flex items-center justify-center gap-2 w-full px-3 py-2 text-xs sm:text-sm font-semibold text-slate-700 hover:text-brand hover:bg-white rounded-lg border border-transparent hover:border-line shadow-2xs transition group">
                                    <svg class="w-4 h-4 text-brand shrink-0 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    <span>{{ __('Explore more') }}</span>
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
                        @if (Auth::user()?->isDeploymentOwner())
                            <x-nav-link :href="route('website-hub')" :active="request()->routeIs('website-hub*')">
                                {{ __('Website Management') }}
                            </x-nav-link>
                        @endif
                        @if (config('scool.deployment_mode', 'general') === 'general' && Auth::user()?->isPlatformAdmin())
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
                    <livewire:inbox-center :community="$authCommunity" />
                @endif

                <!-- Account Dropdown / Auth Links -->
                @auth
                    <x-dropdown align="right" width="64">
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
                            <!-- User Identity Card (AC-05) -->
                            <div class="px-3.5 py-3 border-b border-line/70 mb-1 bg-slate-50/70 rounded-t-lg">
                                <div class="flex items-center gap-3">
                                    @if (Auth::user()->avatar_path)
                                        <img src="{{ route('profile.avatar') }}" alt="{{ Auth::user()->name }}" class="w-10 h-10 rounded-full object-cover border border-slate-200 shrink-0">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-brand-soft text-brand font-bold text-sm flex items-center justify-center border border-brand/20 shrink-0">
                                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-semibold text-ink truncate">{{ Auth::user()->name }}</div>
                                        <div class="text-xs text-muted truncate">{{ Auth::user()->email }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-0.5">
                                @if (Auth::user()?->isDeploymentOwner())
                                    <x-dropdown-link :href="route('website-hub')">
                                        {{ __('Website Management') }}
                                    </x-dropdown-link>
                                @endif

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
                            </div>

                            <div class="border-t border-line/70 my-1 pt-1">
                                <!-- Authentication (Safe POST Form) -->
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();"
                                            class="text-rose-600 hover:text-rose-700 hover:bg-rose-50 focus:bg-rose-50 focus:text-rose-700">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </div>
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
                @if (Auth::user()?->isDeploymentOwner())
                    <x-responsive-nav-link :href="route('website-hub')" :active="request()->routeIs('website-hub*')">
                        {{ __('Website Management') }}
                    </x-responsive-nav-link>
                @endif
                @if (config('scool.deployment_mode', 'general') === 'general' && Auth::user()?->isPlatformAdmin())
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
                    @if (Auth::user()?->isDeploymentOwner())
                        <x-responsive-nav-link :href="route('website-hub')" :active="request()->routeIs('website-hub*')">
                            {{ __('Website Management') }}
                        </x-responsive-nav-link>
                    @endif
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
                    <x-responsive-nav-link :href="route('help.index')">
                        {{ __('Help Center') }}
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
