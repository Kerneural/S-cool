<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="leaderboard" />

            <!-- Top Row: User Progress + Points Guide Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- User Progress Card (8 cols on lg) -->
                <div class="lg:col-span-8 bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 sm:p-6 space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="relative shrink-0">
                                @if (auth()->user()->avatar_path)
                                    <img src="{{ route('communities.directory.avatar', [$community, auth()->user()]) }}" alt="{{ auth()->user()->name }}" class="w-14 h-14 rounded-full object-cover border-2 border-brand-200">
                                @else
                                    <div class="w-14 h-14 rounded-full bg-brand-50 text-brand-700 flex items-center justify-center font-bold text-xl border-2 border-brand-200">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                @endif
                                <span class="absolute -bottom-1 -right-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold shadow-sm {{ $userProgress['level'] === 5 ? 'bg-amber-500 text-white' : ($userProgress['level'] >= 3 ? 'bg-brand-600 text-white' : 'bg-slate-700 text-white') }}">
                                    L{{ $userProgress['level'] }}
                                </span>
                            </div>

                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-lg font-bold text-slate-900">{{ auth()->user()->name }}</h2>
                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $userProgress['level'] === 5 ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-brand-50 text-brand-700 border border-brand-200' }}">
                                        {{ __('Level :level', ['level' => $userProgress['level']]) }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ __('Lifetime: :pts points', ['pts' => $lifetimePoints]) }} &bull;
                                    {{ __('This Month (UTC): :pts points', ['pts' => $monthPoints]) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-6 sm:text-right">
                            <div>
                                <span class="block text-xs uppercase tracking-wider font-semibold text-slate-400">{{ __('Your Rank') }}</span>
                                <span class="text-2xl font-black text-slate-900 tabular-nums">
                                    {{ $userRank ? '#' . $userRank : __('Unranked') }}
                                </span>
                                <span class="block text-[11px] text-slate-400 font-medium">
                                    @if ($metric === 'verified_skills')
                                        {{ __(':count verified works', ['count' => $userMetricScore]) }}
                                    @elseif ($metric === 'helpful_answers')
                                        {{ __(':count helpful answers', ['count' => $userMetricScore]) }}
                                    @else
                                        {{ __(':count points (:p)', ['count' => $userMetricScore, 'p' => $period === 'month' ? 'month' : 'lifetime']) }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Level Progress Bar -->
                    <div class="pt-4 border-t border-slate-100">
                        <div class="flex items-center justify-between text-xs mb-1.5 font-medium">
                            <span class="text-slate-600">
                                @if ($userProgress['level'] === 5)
                                    <span class="text-amber-600 font-bold">{{ __('Maximum Level Reached (60+ points)') }}</span>
                                @else
                                    {{ __('Progress to Level :next (:needed points needed)', ['next' => $userProgress['level'] + 1, 'needed' => $userProgress['points_needed']]) }}
                                @endif
                            </span>
                            <span class="text-slate-900 font-bold tabular-nums">{{ $userProgress['progress_pct'] }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                            <div class="h-2.5 rounded-full transition-all duration-500 {{ $userProgress['level'] === 5 ? 'bg-gradient-to-r from-amber-400 to-amber-500' : 'bg-gradient-to-r from-brand-500 to-brand-600' }}"
                                 style="width: {{ $userProgress['progress_pct'] }}%"></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-slate-400 mt-1.5 font-mono">
                            <span>L1 (0)</span>
                            <span>L2 (5)</span>
                            <span>L3 (15)</span>
                            <span>L4 (30)</span>
                            <span>L5 (60+)</span>
                        </div>
                    </div>
                </div>

                <!-- Metric Explanation Guide (4 cols on lg) -->
                <div class="lg:col-span-4 bg-gradient-to-br from-white via-slate-50/50 to-amber-50/30 rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3 text-xs text-slate-600">
                    <div class="flex items-center gap-2 text-slate-900 font-bold text-sm">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-amber-500 text-white shrink-0">
                            ★
                        </span>
                        <span>{{ __('Leaderboard & Scoring') }}</span>
                    </div>

                    @if ($metric === 'verified_skills')
                        <p class="leading-relaxed">
                            {{ __('Lifetime ranking by current eligible creator-verified practical assignments, not distinct skill labels. Ties use member ID ascending.') }}
                        </p>
                    @elseif ($metric === 'helpful_answers')
                        <p class="leading-relaxed">
                            {{ __('Lifetime ranking by eligible community creator-accepted helpful answers in contextual discussions. Unavailable sources do not count. Creator cannot accept own answers.') }}
                        </p>
                    @else
                        <p class="leading-relaxed">
                            {{ __('Authors receive +1 point per valid like from other members. Self-likes do not count. Unliking removes the point.') }}
                        </p>
                    @endif

                    <div class="pt-2 border-t border-slate-200/60 text-[11px] text-slate-500 space-y-1">
                        <div>&bull; {{ __('Distinct rankings for engagement, verified practice, and answers.') }}</div>
                        <div>&bull; {{ __('Tie-breaking: highest score first, then member ID ascending.') }}</div>
                    </div>
                </div>
            </div>

            <!-- Leaderboard Table and Filter Tabs (AC-03) -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
                <!-- Metric Navigation Tabs -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-6 py-4 border-b border-slate-100 gap-3">
                    <div class="inline-flex flex-wrap rounded-xl bg-slate-100 p-0.5 text-xs">
                        <a href="{{ route('communities.leaderboard', [$community, 'metric' => 'engagement', 'period' => $period]) }}"
                           class="px-3 py-1.5 rounded-lg font-semibold transition {{ $metric === 'engagement' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                            {{ __('Engagement Points') }}
                        </a>
                        <a href="{{ route('communities.leaderboard', [$community, 'metric' => 'verified_skills']) }}"
                           class="px-3 py-1.5 rounded-lg font-semibold transition {{ $metric === 'verified_skills' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                            {{ __('Verified Skills') }}
                        </a>
                        <a href="{{ route('communities.leaderboard', [$community, 'metric' => 'helpful_answers']) }}"
                           class="px-3 py-1.5 rounded-lg font-semibold transition {{ $metric === 'helpful_answers' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                            {{ __('Helpful Answers') }}
                        </a>
                    </div>

                    @if ($metric === 'engagement')
                        <!-- Period Toggle Tabs -->
                        <div class="inline-flex rounded-xl bg-slate-100 p-0.5 text-xs">
                            <a href="{{ route('communities.leaderboard', [$community, 'metric' => 'engagement', 'period' => 'lifetime']) }}"
                               class="px-3 py-1.5 rounded-lg font-semibold transition {{ $period === 'lifetime' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                                {{ __('All Time') }}
                            </a>
                            <a href="{{ route('communities.leaderboard', [$community, 'metric' => 'engagement', 'period' => 'month']) }}"
                               class="px-3 py-1.5 rounded-lg font-semibold transition {{ $period === 'month' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                                {{ __('This Month (UTC)') }}
                            </a>
                        </div>
                    @endif
                </div>

                @if ($leaderboard->isEmpty())
                    <div class="p-12 text-center text-slate-500">
                        <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 mx-auto flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h4 class="text-base font-bold text-slate-900">{{ __('No rankings recorded for this metric yet') }}</h4>
                        <p class="text-xs text-slate-400 mt-1">{{ __('Member activity and contributions will appear here once recorded.') }}</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] uppercase tracking-wider font-semibold text-slate-400">
                                    <th class="py-3 px-6 w-16 text-center">{{ __('Rank') }}</th>
                                    <th class="py-3 px-6">{{ __('Member') }}</th>
                                    @if ($metric === 'engagement')
                                        <th class="py-3 px-6 text-center">{{ __('Level') }}</th>
                                        <th class="py-3 px-6 text-right">{{ __('Points') }}</th>
                                    @elseif ($metric === 'verified_skills')
                                        <th class="py-3 px-6 text-right">{{ __('Verified Works') }}</th>
                                    @else
                                        <th class="py-3 px-6 text-right">{{ __('Helpful Answers') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($leaderboard as $entry)
                                    <tr class="hover:bg-slate-50/60 transition {{ $entry->user && auth()->id() === $entry->user->id ? 'bg-brand-50/30' : '' }}">
                                        <td class="py-4 px-6 text-center font-bold">
                                            @if ($entry->rank === 1)
                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-800 text-xs shadow-sm font-black">
                                                    🥇 1
                                                </span>
                                            @elseif ($entry->rank === 2)
                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-200 text-slate-800 text-xs shadow-sm font-black">
                                                    🥈 2
                                                </span>
                                            @elseif ($entry->rank === 3)
                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-50 text-amber-900 border border-amber-200 text-xs shadow-sm font-black">
                                                    🥉 3
                                                </span>
                                            @else
                                                <span class="text-xs text-slate-400 font-mono">
                                                    #{{ $entry->rank }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-3">
                                                @if ($entry->user && $entry->user->avatar_path)
                                                    <img src="{{ route('communities.directory.avatar', [$community, $entry->user]) }}" alt="{{ $entry->user->name }}" class="w-9 h-9 rounded-full object-cover">
                                                @else
                                                    <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-semibold text-xs">
                                                        {{ strtoupper(substr($entry->user->name ?? '?', 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="font-semibold text-slate-900">
                                                        <a href="{{ route('communities.directory.show', [$community, $entry->user]) }}" class="hover:text-brand-600 hover:underline">
                                                            {{ $entry->user->name ?? __('Unknown Member') }}
                                                        </a>
                                                        @if ($entry->user && auth()->id() === $entry->user->id)
                                                            <span class="ml-1.5 text-[10px] font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded-full border border-brand-200">
                                                                {{ __('You') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        @if ($metric === 'engagement')
                                            <td class="py-4 px-6 text-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $entry->level === 5 ? 'bg-amber-100 text-amber-800 border border-amber-200' : ($entry->level >= 3 ? 'bg-brand-50 text-brand-700' : 'bg-slate-100 text-slate-600') }}">
                                                    {{ __('Level :l', ['l' => $entry->level]) }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-6 text-right font-black text-slate-900 text-base tabular-nums">
                                                {{ $entry->points }} <span class="text-xs font-normal text-slate-400">pts</span>
                                            </td>
                                        @elseif ($metric === 'verified_skills')
                                            <td class="py-4 px-6 text-right font-black text-slate-900 text-base tabular-nums">
                                                {{ $entry->points }} <span class="text-xs font-normal text-slate-400">verified</span>
                                            </td>
                                        @else
                                            <td class="py-4 px-6 text-right font-black text-slate-900 text-base tabular-nums">
                                                {{ $entry->points }} <span class="text-xs font-normal text-slate-400">helpful</span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($leaderboard->hasPages())
                        <div class="p-4 border-t border-slate-100">
                            {{ $leaderboard->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
