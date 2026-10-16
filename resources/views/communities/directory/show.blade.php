<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="directory" />

            <!-- Main Member Profile Card -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 sm:p-8 space-y-6">
                <!-- Header: Avatar, Name, Role, Actions -->
                <div class="flex flex-col sm:flex-row sm:items-start gap-6">
                    <img src="{{ route('communities.directory.avatar', [$community, $member]) }}"
                         alt="{{ $member->name }}"
                         class="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover border-2 border-slate-200 shadow-sm shrink-0 bg-slate-100">

                    <div class="space-y-4 flex-1 min-w-0">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="min-w-0">
                                <h1 class="text-2xl font-bold text-slate-900 break-words">{{ $member->name }}</h1>
                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $role === 'Creator' ? 'bg-brand-50 text-brand-700 border border-brand-200' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $role === 'Creator' ? __('Community Creator') : __('Community Member') }}
                                    </span>
                                    @if ($joinedAt)
                                        <span class="text-xs text-slate-400">
                                            {{ __('Joined') }} {{ $joinedAt->format('M d, Y') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                @if (auth()->id() !== $member->id)
                                    <form method="POST" action="{{ route('communities.messages.start', $community) }}">
                                        @csrf
                                        <input type="hidden" name="recipient_id" value="{{ $member->id }}">
                                        <button type="submit" class="inline-flex items-center px-3.5 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold uppercase tracking-wider transition shadow-sm active:scale-[0.99]">
                                            {{ __('Message') }}
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('communities.directory.index', $community) }}"
                                   class="inline-flex items-center px-3.5 py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-semibold text-slate-700 uppercase tracking-wider transition">
                                    &larr; {{ __('Back to Directory') }}
                                </a>
                            </div>
                        </div>

                        <!-- Bio -->
                        <div class="pt-3 border-t border-slate-100">
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">{{ __('About') }}</h4>
                            <p class="text-slate-700 text-sm leading-relaxed whitespace-pre-line break-words">
                                {{ $member->bio ?: __('This member has not written a bio yet.') }}
                            </p>
                        </div>

                        <!-- Optional External Links (GitHub / Portfolio) - Escaped and Safe -->
                        @if ($member->github_url || $member->portfolio_url)
                            <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center gap-3 text-xs">
                                @if ($member->github_url)
                                    <a href="{{ $member->github_url }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-medium transition">
                                        <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                                            <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                                        </svg>
                                        <span>GitHub</span>
                                    </a>
                                @endif
                                @if ($member->portfolio_url)
                                    <a href="{{ $member->portfolio_url }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-medium transition">
                                        <svg class="w-4 h-4 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        <span>Portfolio</span>
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Verified Skills & Contribution Signals Section (AC-01, AC-02) -->
                <div class="pt-5 border-t border-slate-100 space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                            <span>{{ __('Demonstrated Contributions & Skills') }}</span>
                        </h3>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-teal-50 text-teal-800 border border-teal-200">
                                <svg class="w-3.5 h-3.5 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                {{ __(':count Verified Work(s)', ['count' => $verifiedSkills['count']]) }}
                            </span>

                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                {{ __(':count Helpful Answer(s)', ['count' => $helpfulAnswersCount]) }}
                            </span>
                        </div>
                    </div>

                    <!-- Skills List -->
                    @if ($verifiedSkills['count'] > 0)
                        <div class="space-y-2">
                            <p class="text-xs text-slate-600">{{ __(':count distinct skill labels. Showing up to 20 recent verified works; private submissions are never shared.', ['count' => $verifiedSkills['distinct_count']]) }}</p>
                            <span class="text-xs font-medium text-slate-500 block">{{ __('Proven practical skills in this community:') }}</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($verifiedSkills['skills'] as $skill)
                                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $skill }}
                                    </span>
                                @endforeach
                            </div>
                            <ul class="space-y-2 text-sm text-slate-600">
                                @foreach ($verifiedSkills['evidence'] as $evidence)
                                    <li class="break-words">
                                        <span class="font-semibold text-slate-800">{{ $evidence['skill'] ?: __('Unlabelled verified work') }}</span>
                                        <span>{{ __('Verified by community creator · Revision :revision', ['revision' => $evidence['revision']]) }}</span>
                                        @if ($evidence['reviewed_at'])
                                            <time datetime="{{ $evidence['reviewed_at']->toIso8601String() }}">{{ $evidence['reviewed_at']->utc()->format('Y-m-d') }} UTC</time>
                                        @endif
                                        @if ($evidence['url'])
                                            <a href="{{ $evidence['url'] }}" class="block text-brand-700 underline">{{ $evidence['course'] }} · {{ $evidence['lesson'] }}</a>
                                        @else
                                            <span class="block">{{ __('Source context is not available to you.') }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <p class="text-xs text-slate-400 italic">
                            {{ __('No verified practical skills recorded in this community yet.') }}
                        </p>
                    @endif
                </div>

                <!-- 90-Day UTC Activity Heatmap (AC-11) -->
                <div class="pt-5 border-t border-slate-100 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800">
                            {{ __('90-Day Activity Heatmap (UTC)') }}
                        </h3>
                        <span class="text-xs text-slate-400 font-mono">
                            {{ $activityHeatmap['start_date'] }} &mdash; {{ $activityHeatmap['end_date'] }}
                        </span>
                    </div>

                    <!-- Screen-reader accessible alternative -->
                    <div>
                        {{ $activityHeatmap['accessible_summary'] }}
                    </div>

                    <!-- Heatmap Grid -->
                    <div class="bg-slate-50/80 rounded-xl p-4 border border-slate-200/70 overflow-x-auto">
                        <div class="min-w-[500px]">
                            <!-- Calendar Grid: 90 days wrapped in 13 weekly columns of 7 rows -->
                            <div class="grid grid-flow-col grid-rows-7 gap-1.5 w-max" aria-hidden="true">
                                @foreach ($activityHeatmap['days'] as $day)
                                    @php
                                        $bgColor = match ($day['level']) {
                                            4 => 'bg-teal-700 border-teal-800',
                                            3 => 'bg-teal-500 border-teal-600',
                                            2 => 'bg-teal-300 border-teal-400',
                                            1 => 'bg-teal-100 border-teal-200',
                                            default => 'bg-white border-slate-200/80',
                                        };
                                    @endphp
                                    <div class="w-3.5 h-3.5 rounded-xs border {{ $bgColor }} transition-colors cursor-default"
                                         title="{{ $day['date'] }}: {{ $day['total_count'] }} contribution(s) ({{ $day['post_count'] }} posts, {{ $day['comment_count'] }} comments)">
                                    </div>
                                @endforeach
                            </div>

                            <!-- Heatmap Legend & Totals -->
                            <div class="flex flex-wrap items-center justify-between text-xs text-slate-500 mt-3 pt-2 border-t border-slate-200/60">
                                <div>
                                    <span class="font-bold text-slate-800">{{ $activityHeatmap['total_contributions'] }}</span>
                                    <span>{{ __('total contributions (:p posts, :c comments)', ['p' => $activityHeatmap['total_posts'], 'c' => $activityHeatmap['total_comments']]) }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[10px] text-slate-400">{{ __('Less') }}</span>
                                    <div class="w-2.5 h-2.5 rounded-xs bg-white border border-slate-200"></div>
                                    <div class="w-2.5 h-2.5 rounded-xs bg-teal-100 border border-teal-200"></div>
                                    <div class="w-2.5 h-2.5 rounded-xs bg-teal-300 border border-teal-400"></div>
                                    <div class="w-2.5 h-2.5 rounded-xs bg-teal-500 border border-teal-600"></div>
                                    <div class="w-2.5 h-2.5 rounded-xs bg-teal-700 border border-teal-800"></div>
                                    <span class="text-[10px] text-slate-400">{{ __('More') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <details class="text-sm text-slate-600">
                        <summary class="cursor-pointer min-h-11 py-3 font-medium text-brand-700">{{ __('Daily activity counts (UTC)') }}</summary>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <caption class="sr-only">{{ __('Daily posts and comments for all 90 UTC dates') }}</caption>
                                <thead><tr><th scope="col" class="p-2">{{ __('Date (UTC)') }}</th><th scope="col" class="p-2">{{ __('Posts') }}</th><th scope="col" class="p-2">{{ __('Comments') }}</th><th scope="col" class="p-2">{{ __('Total') }}</th></tr></thead>
                                <tbody>
                                    @foreach ($activityHeatmap['days'] as $day)
                                        <tr><th scope="row" class="p-2 font-normal">{{ $day['date'] }}</th><td class="p-2">{{ $day['post_count'] }}</td><td class="p-2">{{ $day['comment_count'] }}</td><td class="p-2">{{ $day['total_count'] }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>

                    @if ($activityHeatmap['total_contributions'] === 0)
                        <p class="text-xs text-slate-400 italic">
                            {{ __('No posts or comments authored in this community over the last 90 UTC days.') }}
                        </p>
                    @endif
                </div>

                <section class="pt-5 border-t border-slate-100 space-y-3" aria-labelledby="recent-activity-heading">
                    <h3 id="recent-activity-heading" class="text-sm font-bold text-slate-800">{{ __('Recent community activity') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('Up to 10 visible posts and comments from the last 90 UTC days. Access changes may hide earlier activity.') }}</p>
                    <ul class="space-y-3">
                        @forelse ($activityHistory as $activity)
                            <li class="text-sm break-words">
                                <span class="text-slate-500">{{ __($activity['kind']) }} · <time datetime="{{ $activity['at']->toIso8601String() }}">{{ $activity['at']->utc()->format('Y-m-d H:i') }} UTC</time></span>
                                <a href="{{ $activity['url'] }}" class="block text-brand-700 underline">{{ $activity['title'] }}</a>
                            </li>
                        @empty
                            <li class="text-sm text-slate-500">{{ __('No visible community activity yet.') }}</li>
                        @endforelse
                    </ul>
                </section>

                <div class="pt-4 border-t border-slate-100 text-xs text-slate-400">
                    {{ __('Viewing community profile in :community.', ['community' => $community->name]) }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
