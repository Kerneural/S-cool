<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="directory" />

            <!-- Directory Toolbar -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="font-bold text-xl text-slate-900 tracking-tight">{{ __('Community Members') }}</h1>
                        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">{{ __('Discover fellow members and creators in this community.') }}</p>
                    </div>

                    <!-- Search Members -->
                    <form method="GET" action="{{ route('communities.directory.index', $community) }}" class="flex items-center gap-2">
                        <div class="relative flex-1 sm:w-64">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" name="q" value="{{ $searchQuery ?? '' }}" placeholder="{{ __('Search members by name...') }}"
                                   class="text-xs sm:text-sm rounded-xl border-slate-300 shadow-xs focus:border-brand-500 focus:ring-brand-500 w-full py-2 pl-9 pr-3 bg-slate-50/50 focus:bg-white transition">
                        </div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold uppercase tracking-wider transition shadow-sm">
                            {{ __('Search') }}
                        </button>
                        @if (!empty($searchQuery))
                            <a href="{{ route('communities.directory.index', $community) }}" class="text-xs text-slate-500 hover:text-slate-800 underline ml-1">
                                {{ __('Clear') }}
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Members Grid -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6">
                @if ($members->isEmpty())
                    <div class="text-center py-12">
                        <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400 mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        @if (!empty($searchQuery))
                            <p class="text-slate-500 text-sm mb-3">{{ __('No community members match ":query".', ['query' => $searchQuery]) }}</p>
                            <a href="{{ route('communities.directory.index', $community) }}" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-semibold text-slate-700 transition">
                                {{ __('Clear Search') }}
                            </a>
                        @else
                            <p class="text-slate-500 text-sm">{{ __('No active members found in this community.') }}</p>
                        @endif
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                        @foreach ($members as $member)
                            @php
                                $isCreator = $member->id === $community->creator_id;
                            @endphp
                            <article class="border border-slate-200/80 rounded-2xl p-5 hover:border-brand-500/30 hover:shadow-lg transition-all bg-white flex flex-col justify-between group">
                                <div class="flex items-start space-x-4">
                                    <img src="{{ route('communities.directory.avatar', [$community, $member]) }}"
                                         alt="{{ $member->name }}"
                                         class="w-14 h-14 rounded-full object-cover border border-slate-200 shadow-xs shrink-0 bg-slate-100 group-hover:scale-105 transition-transform">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <a href="{{ route('communities.directory.show', [$community, $member]) }}" class="font-bold text-slate-900 group-hover:text-brand-600 transition truncate text-base">
                                                {{ $member->name }}
                                            </a>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $isCreator ? 'bg-brand-50 text-brand-700 border border-brand-200' : 'bg-slate-100 text-slate-600' }}">
                                                {{ $isCreator ? __('Creator') : __('Member') }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-2 line-clamp-2 leading-relaxed">
                                            {{ $member->bio ?: __('No bio provided yet.') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                                    <span class="font-medium text-slate-400">{{ __('Community Member') }}</span>
                                    <a href="{{ route('communities.directory.show', [$community, $member]) }}" class="font-semibold text-brand-600 hover:text-brand-700 inline-flex items-center group-hover:translate-x-0.5 transition-transform">
                                        <span>{{ __('View Profile') }}</span>
                                        <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $members->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
