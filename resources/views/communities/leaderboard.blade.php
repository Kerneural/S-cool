<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="leaderboard" />
            <h1 class="sr-only">{{ __('Community leaderboard') }}</h1>

            <!-- User Level Progress Card (Figure Matching Skool Specs - Full Width) -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 sm:p-8">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-8 lg:gap-14">
                    <!-- Left Column: Circular Avatar with Progress Arc & Level Badge -->
                    <div class="flex flex-col items-center shrink-0 text-center pt-2" style="width: 180px; min-width: 180px;">
                        <div class="relative flex items-center justify-center shrink-0" style="width: 160px; height: 160px;">
                            <!-- Outer SVG Progress Circle -->
                            <svg class="-rotate-90 transform" viewBox="0 0 160 160" style="width: 160px; height: 160px;">
                                <circle cx="80" cy="80" r="68" class="text-slate-100" stroke-width="4" stroke="currentColor" fill="transparent" />
                                <circle cx="80" cy="80" r="68" class="text-[#3B82F6] transition-all duration-700 ease-out"
                                        stroke-width="5"
                                        stroke-dasharray="427.26"
                                        stroke-dashoffset="{{ 427.26 * (1 - max(0.12, ($userProgress['progress_pct'] / 100))) }}"
                                        stroke-linecap="round"
                                        stroke="currentColor"
                                        fill="transparent" />
                            </svg>

                            <!-- Avatar Inside Circle -->
                            <div class="absolute rounded-full overflow-hidden bg-slate-900 border-2 border-white shadow-inner flex items-center justify-center" style="width: 126px; height: 126px;">
                                @if (auth()->user()->avatar_path)
                                    <img src="{{ route('communities.directory.avatar', [$community, auth()->user()]) }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gradient-to-tr from-slate-800 to-indigo-900 text-white flex items-center justify-center font-bold text-3xl">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>

                            <!-- Bottom Right Circular Level Badge (like '0' in user's image) -->
                            <div class="absolute bottom-1 right-2 rounded-full bg-[#3B82F6] text-white font-extrabold text-sm flex items-center justify-center shadow-md ring-4 ring-white" style="width: 36px; height: 36px;">
                                {{ $userLevel }}
                            </div>
                        </div>

                        <!-- User Name & Level Subtitle -->
                        <h3 class="text-lg font-bold text-slate-900 mt-4 leading-tight">
                            {{ auth()->user()->name }}
                        </h3>
                        <p class="text-xs font-semibold text-[#3B82F6] mt-1">
                            {{ __('Level :level', ['level' => $userLevel]) }}
                        </p>
                        @if ($userLevel >= 5 || ($userProgress['level'] ?? 1) >= 5)
                            <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                {{ __('Maximum Level Reached') }}
                            </span>
                        @endif
                    </div>

                    <!-- Right Column: 2-Column Grid of 9 Levels -->
                    <div class="flex-1 min-w-0 w-full grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4 items-start pt-1">
                        <!-- Column 1: Levels 1 to 5 -->
                        <div class="space-y-4">
                            @foreach (array_slice($communityLevels, 0, 5, true) as $lvlNum => $lvl)
                                @php($isUnlocked = $userLevel >= $lvlNum)
                                <div class="flex items-start gap-3.5">
                                    <!-- Icon Circle (lock or unlocked) -->
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 {{ $isUnlocked ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-400' }}">
                                        @if ($isUnlocked)
                                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                            </svg>
                                        @endif
                                    </div>

                                    <!-- Level Title & Perks in English -->
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-semibold text-slate-900 leading-snug">
                                            {{ __('Level :num - :title', ['num' => $lvlNum, 'title' => $lvl['title']]) }}
                                        </div>
                                        <div class="text-xs text-slate-500 leading-normal mt-0.5">
                                            @if (! empty($lvl['perk']))
                                                {{ __('Unlocks') }} <span class="text-blue-600 font-medium">{{ $lvl['perk'] }}</span> {{ $lvl['members_pct'] }} {{ __('of members') }}
                                            @else
                                                {{ $lvl['members_pct'] }} {{ __('of members') }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Column 2: Levels 6 to 9 -->
                        <div class="space-y-4">
                            @foreach (array_slice($communityLevels, 5, 4, true) as $lvlNum => $lvl)
                                @php($isUnlocked = $userLevel >= $lvlNum)
                                <div class="flex items-start gap-3.5">
                                    <!-- Icon Circle -->
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 {{ $isUnlocked ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-400' }}">
                                        @if ($isUnlocked)
                                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                            </svg>
                                        @endif
                                    </div>

                                    <!-- Level Title & Perks in English -->
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-semibold text-slate-900 leading-snug">
                                            {{ __('Level :num - :title', ['num' => $lvlNum, 'title' => $lvl['title']]) }}
                                        </div>
                                        <div class="text-xs text-slate-500 leading-normal mt-0.5">
                                            @if (! empty($lvl['perk']))
                                                {{ __('Unlocks') }} <span class="text-blue-600 font-medium">{{ $lvl['perk'] }}</span> {{ $lvl['members_pct'] }} {{ __('of members') }}
                                            @else
                                                {{ $lvl['members_pct'] }} {{ __('of members') }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Leaderboard 2-Column Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Main Content Column (8 cols on lg) -->
                <div class="min-w-0 lg:col-span-8 space-y-6">
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
                        <p class="text-xs text-slate-600 mt-1">{{ __('Member activity and contributions will appear here once recorded.') }}</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] uppercase tracking-wider font-semibold text-slate-600">
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
                                                <span class="text-xs text-slate-600 font-mono">
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
                                                {{ $entry->points }} <span class="text-xs font-normal text-slate-600">pts</span>
                                            </td>
                                        @elseif ($metric === 'verified_skills')
                                            <td class="py-4 px-6 text-right font-black text-slate-900 text-base tabular-nums">
                                                {{ $entry->points }} <span class="text-xs font-normal text-slate-600">verified</span>
                                            </td>
                                        @else
                                            <td class="py-4 px-6 text-right font-black text-slate-900 text-base tabular-nums">
                                                {{ $entry->points }} <span class="text-xs font-normal text-slate-600">helpful</span>
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

                <!-- Context Sidebar Column with Skool Community Card & Metric Guide -->
                <aside class="min-w-0 lg:col-span-4 space-y-6">
                    <x-skool-community-card :community="$community" />

                    <!-- Metric Explanation Guide (4 cols on lg) -->
                    <div class="bg-gradient-to-br from-white via-slate-50/50 to-amber-50/30 rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3 text-xs text-slate-600">
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
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
