<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ __('Communities') }}
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    {{ __('Explore, collaborate, and manage your learning spaces.') }}
                </p>
            </div>
            <a href="{{ route('communities.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-medium text-sm rounded-xl shadow-sm shadow-brand-500/20 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Create Community') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="flex items-center p-4 text-sm font-medium text-emerald-800 bg-emerald-50 rounded-xl border border-emerald-200/70">
                    <svg class="w-5 h-5 text-emerald-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <!-- Search and Filter Toolbar -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-sm">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <!-- Filter Tabs -->
                    <nav class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl" aria-label="Filters">
                        <a href="{{ route('communities.index', array_filter(['filter' => 'all', 'q' => $searchQuery ?? ''])) }}"
                           class="px-3.5 py-1.5 rounded-lg text-xs font-semibold tracking-wide transition-all {{ ($currentFilter ?? 'all') === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}">
                            {{ __('All') }}
                        </a>
                        <a href="{{ route('communities.index', array_filter(['filter' => 'owned', 'q' => $searchQuery ?? ''])) }}"
                           class="px-3.5 py-1.5 rounded-lg text-xs font-semibold tracking-wide transition-all {{ ($currentFilter ?? '') === 'owned' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}">
                            {{ __('Owned (Creator)') }}
                        </a>
                        <a href="{{ route('communities.index', array_filter(['filter' => 'joined', 'q' => $searchQuery ?? ''])) }}"
                           class="px-3.5 py-1.5 rounded-lg text-xs font-semibold tracking-wide transition-all {{ ($currentFilter ?? '') === 'joined' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}">
                            {{ __('Joined (Member)') }}
                        </a>
                    </nav>

                    <!-- Search Form -->
                    <form method="GET" action="{{ route('communities.index') }}" class="flex items-center gap-2">
                        <input type="hidden" name="filter" value="{{ $currentFilter ?? 'all' }}">
                        <div class="relative flex-1 sm:w-72">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" name="q" value="{{ $searchQuery ?? '' }}" placeholder="{{ __('Search communities...') }}"
                                   class="w-full text-sm rounded-xl border-slate-200 pl-9 pr-3 py-2 bg-slate-50/50 focus:bg-white focus:border-brand-500 focus:ring-brand-500 transition-colors">
                        </div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs rounded-xl transition shadow-sm">
                            {{ __('Search') }}
                        </button>
                        @if (!empty($searchQuery))
                            <a href="{{ route('communities.index', ['filter' => $currentFilter ?? 'all']) }}" class="text-xs text-slate-500 hover:text-slate-800 underline ml-1">
                                {{ __('Clear') }}
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Communities Grid -->
            <div>
                @if ($communities->isEmpty())
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-12 text-center shadow-sm">
                        <div class="mx-auto w-14 h-14 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-600 mb-4">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        @if (!empty($searchQuery))
                            <h3 class="text-base font-semibold text-slate-900 mb-1">{{ __('No matching communities') }}</h3>
                            <p class="text-sm text-slate-500 mb-6 max-w-md mx-auto">{{ __('No communities found matching ":query". Try searching with a different term.', ['query' => $searchQuery]) }}</p>
                            <a href="{{ route('communities.index', ['filter' => $currentFilter ?? 'all']) }}" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs rounded-xl transition">
                                {{ __('Clear Search') }}
                            </a>
                        @elseif (($currentFilter ?? 'all') === 'owned')
                            <h3 class="text-base font-semibold text-slate-900 mb-1">{{ __('No created communities yet') }}</h3>
                            <p class="text-sm text-slate-500 mb-6 max-w-md mx-auto">{{ __('You have not created any communities yet. Build your first learning hub in seconds.') }}</p>
                            <a href="{{ route('communities.create') }}" class="inline-flex items-center px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-medium text-xs rounded-xl transition shadow-sm shadow-brand-500/20">
                                {{ __('Create a Community') }}
                            </a>
                        @elseif (($currentFilter ?? 'all') === 'joined')
                            <h3 class="text-base font-semibold text-slate-900 mb-1">{{ __('No memberships found') }}</h3>
                            <p class="text-sm text-slate-500 mb-6 max-w-md mx-auto">{{ __('You have not joined any communities as a member yet.') }}</p>
                        @else
                            <h3 class="text-base font-semibold text-slate-900 mb-1">{{ __('Welcome to S-cool') }}</h3>
                            <p class="text-sm text-slate-500 mb-6 max-w-md mx-auto">{{ __('No active communities are available yet. Create the first learning community.') }}</p>
                            <a href="{{ route('communities.create') }}" class="inline-flex items-center px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-medium text-xs rounded-xl transition shadow-sm shadow-brand-500/20">
                                {{ __('Get Started — Create a Community') }}
                            </a>
                        @endif
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($communities as $community)
                            @php
                                $isCreator = $community->isCreator(auth()->user());
                                $isMember = (bool) $community->has_effective_membership;
                                $canOpen = $community->isActive() && ($isCreator || $isMember);
                            @endphp
                            <article class="group relative flex flex-col bg-surface rounded-xl border border-line hover:border-brand/40 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 overflow-hidden shadow-2xs">
                                <!-- Cover / Educational Graphic Fallback -->
                                <div class="relative aspect-[16/9] w-full overflow-hidden bg-slate-900">
                                    @if ($community->isActive() && $community->cover_path)
                                        <img src="{{ route('communities.cover.show', $community) }}"
                                             alt="{{ $community->name }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                             loading="lazy">
                                    @else
                                        <!-- Purposeful Educational Fallback with Branded Gradient and Monogram -->
                                        <div class="w-full h-full flex flex-col items-center justify-center p-6 bg-gradient-to-br from-slate-900 via-slate-800 to-brand-950 relative overflow-hidden group-hover:from-slate-850 group-hover:to-brand-900 transition-colors duration-500">
                                            <div class="absolute inset-0 opacity-15 pointer-events-none" style="background-image: radial-gradient(circle at 1px 1px, #14b8a6 1px, transparent 0); background-size: 16px 16px;"></div>
                                            <div class="relative w-14 h-14 rounded-xl bg-brand-500/15 border border-brand-500/25 text-brand-300 flex items-center justify-center shadow-inner group-hover:scale-105 group-hover:border-brand-400/40 transition-transform duration-300">
                                                <span class="text-lg font-bold tracking-tight">{{ strtoupper(mb_substr($community->name, 0, 2)) }}</span>
                                            </div>
                                            <span class="relative mt-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400">
                                                {{ __('Learning Community') }}
                                            </span>
                                        </div>
                                    @endif

                                    <!-- Cover Overlay Badges -->
                                    <div class="absolute inset-x-3 top-3 flex items-center justify-between pointer-events-none">
                                        <!-- Role Pill -->
                                        @if ($isCreator)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-900/85 backdrop-blur-md text-brand-300 border border-brand-500/30 shadow-xs">
                                                <svg class="w-3 h-3 text-brand-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 11-1z" />
                                                </svg>
                                                {{ __('Creator') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-slate-900/85 backdrop-blur-md text-slate-200 border border-white/10 shadow-xs">
                                                {{ $isMember ? __('Member') : __('Not joined') }}
                                            </span>
                                        @endif

                                        <!-- Status Pill -->
                                        @if ($community->isActive())
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-500/20 backdrop-blur-md text-emerald-300 border border-emerald-500/30 shadow-xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5"></span>
                                                {{ $community->status }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-500/20 backdrop-blur-md text-amber-300 border border-amber-500/40 shadow-xs">
                                                {{ $community->status }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Card Body -->
                                <div class="p-5 flex flex-col flex-1">
                                    <div class="mb-2">
                                        <h3 class="text-base font-bold text-ink group-hover:text-brand transition-colors line-clamp-1">
                                            @if ($canOpen)
                                                <a href="{{ route('communities.show', $community) }}" class="focus:outline-none focus:underline">
                                                    {{ $community->name }}
                                                </a>
                                            @else
                                                <span>{{ $community->name }}</span>
                                            @endif
                                        </h3>
                                    </div>

                                    <p class="text-sm text-muted line-clamp-2 leading-relaxed flex-1">
                                        {{ $community->description ?: __('No description provided.') }}
                                    </p>

                                    <!-- Bottom Action Strip -->
                                    <div class="flex items-center justify-between text-xs pt-4 mt-4 border-t border-line/60">
                                        <span class="text-muted font-medium">
                                            {{ $isCreator ? __('Role: Creator') : ($isMember ? __('Role: Member') : __('Not joined')) }}
                                        </span>
                                        @if ($canOpen)
                                            <a href="{{ route('communities.show', $community) }}"
                                               class="inline-flex items-center font-semibold text-brand hover:text-brand-hover group-hover:translate-x-0.5 transition-all focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-1 rounded-md px-1">
                                                <span>{{ __('Open Community') }}</span>
                                                <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                </svg>
                                            </a>
                                        @else
                                            <span class="text-muted font-medium">
                                                {{ $community->isActive() ? __('Invitation required') : __('Access unavailable') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if ($communities->hasPages())
                        <div class="mt-8">
                            {{ $communities->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
