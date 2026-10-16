<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="overview" />

            <!-- Overview Header & Date Range Filter -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                        {{ __('Community Overview & Operational Metrics') }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        {{ __('Truthful operational metrics for') }} <span class="font-semibold text-slate-900">{{ $community->name }}</span>.
                    </p>
                    <div class="text-xs text-slate-400 mt-1.5 flex flex-wrap items-center gap-2">
                        <span>{{ __('Date window:') }}</span>
                        <span class="font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-700 text-[11px]">{{ $startUtc->format('Y-m-d') }}</span>
                        <span>{{ __('to') }}</span>
                        <span class="font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-700 text-[11px]">{{ $endUtcExclusive->format('Y-m-d') }}</span>
                        <span>({{ __('Timezone: UTC') }})</span>
                    </div>
                </div>

                <!-- Range Selector Pills -->
                <div class="flex items-center gap-1.5 self-start md:self-center shrink-0">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider mr-1">{{ __('Range:') }}</span>
                    <a href="{{ route('communities.manage', ['community' => $community, 'range' => 7]) }}"
                       class="px-3 py-1.5 rounded-xl text-xs font-semibold transition active:scale-[0.98] {{ $range === 7 ? 'bg-brand-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        {{ __('7 days') }}
                    </a>
                    <a href="{{ route('communities.manage', ['community' => $community, 'range' => 30]) }}"
                       class="px-3 py-1.5 rounded-xl text-xs font-semibold transition active:scale-[0.98] {{ $range === 30 ? 'bg-brand-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        {{ __('30 days') }}
                    </a>
                    <a href="{{ route('communities.manage', ['community' => $community, 'range' => 90]) }}"
                       class="px-3 py-1.5 rounded-xl text-xs font-semibold transition active:scale-[0.98] {{ $range === 90 ? 'bg-brand-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        {{ __('90 days') }}
                    </a>
                </div>
            </div>

            <!-- Truthful Metrics Cards Grid (4 Actionable Cards) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Members Card (Creator shown separately) -->
                <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <span>{{ __('Active Members') }}</span>
                            <span class="text-brand-700 bg-brand-50 px-2 py-0.5 rounded-full text-[11px] font-bold border border-brand-200">ACTIVE</span>
                        </div>
                        <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-3 tabular-nums">
                            {{ $activeMembersCount }}
                        </div>
                        <div class="mt-2 text-xs text-slate-500">
                            <span>{{ __('Creator:') }}</span>
                            <span class="font-semibold text-slate-800">{{ $creator->name ?? '1' }}</span>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <a href="{{ route('communities.members.index', $community) }}"
                           class="inline-flex items-center text-xs font-semibold text-brand-600 hover:text-brand-700 group transition">
                            <span>{{ __('Manage Members') }}</span>
                            <svg class="w-3.5 h-3.5 ml-1 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Content & Courses Card -->
                <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <span>{{ __('Content & Courses') }}</span>
                            <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full text-[11px] font-bold border border-emerald-200">Content</span>
                        </div>
                        <div class="flex items-baseline gap-4 mt-3">
                            <div>
                                <span class="text-3xl sm:text-4xl font-extrabold text-slate-900 tabular-nums">{{ $postsCount }}</span>
                                <span class="text-xs text-slate-500 font-medium ml-1">{{ __('posts') }}</span>
                            </div>
                            <div class="border-l border-slate-200 pl-4">
                                <span class="text-2xl sm:text-3xl font-bold text-slate-900 tabular-nums">{{ $publishedCoursesCount }}</span>
                                <span class="text-xs text-slate-500 font-medium ml-1">{{ __('courses') }}</span>
                            </div>
                        </div>
                        <div class="mt-2 text-xs text-slate-500">
                            <span>{{ __('Published lessons:') }} <strong class="text-slate-800 font-semibold">{{ $publishedLessonsCount }}</strong></span>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <a href="{{ route('communities.classroom.index', $community) }}"
                           class="inline-flex items-center text-xs font-semibold text-brand-600 hover:text-brand-700 group transition">
                            <span>{{ __('Manage Courses') }}</span>
                            <svg class="w-3.5 h-3.5 ml-1 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Simulated Receipts in Test Mode Card -->
                <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <span>{{ __('Simulated Receipts') }}</span>
                            <span class="text-amber-800 bg-amber-50 px-2 py-0.5 rounded-full text-[11px] font-bold border border-amber-200">Test Mode</span>
                        </div>
                        <div class="text-2xl sm:text-3xl font-extrabold text-brand-700 mt-3 tabular-nums">
                            {{ $formattedSimulatedReceipts }}
                        </div>
                        <div class="mt-2 text-xs text-slate-500">
                            <span>{{ __('Simulated successful receipts (Test Mode)') }}</span>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <a href="{{ route('communities.orders.index', $community) }}"
                           class="inline-flex items-center text-xs font-semibold text-brand-600 hover:text-brand-700 group transition">
                            <span>{{ __('View Orders & Receipts') }}</span>
                            <svg class="w-3.5 h-3.5 ml-1 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Learning Completion Ratio Card -->
                <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <span>{{ __('Completion Ratio') }}</span>
                            <span class="text-sky-700 bg-sky-50 px-2 py-0.5 rounded-full text-[11px] font-bold border border-sky-200">Learning</span>
                        </div>
                        <div class="mt-3">
                            @if ($hasCompletionData)
                                <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 tabular-nums">
                                    {{ $completionRatio }}%
                                </div>
                            @else
                                <div class="text-slate-400 text-xl font-normal py-1">
                                    {{ __('No data') }}
                                </div>
                            @endif
                        </div>
                        <div class="mt-2 text-xs text-slate-500">
                            @if ($hasCompletionData)
                                <span>{{ $completedLessonsCount }} {{ __('completions') }} / {{ $accessibleLessonPairsCount }} {{ __('eligible member-lesson pairs') }}</span>
                            @else
                                <span>{{ __('No active members or published lessons') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <a href="{{ route('communities.reports.index', $community) }}"
                           class="inline-flex items-center text-xs font-semibold text-brand-600 hover:text-brand-700 group transition">
                            <span>{{ __('View Reports') }}</span>
                            <svg class="w-3.5 h-3.5 ml-1 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Activity Table: Payments & Transactions in Range -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">
                            {{ __('Recent Payment Activity (Last :range Days)', ['range' => $range]) }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ __('All recorded payment attempts within the selected operational time window.') }}
                        </p>
                    </div>
                    <a href="{{ route('communities.orders.index', $community) }}"
                       class="text-xs font-semibold text-brand-600 hover:text-brand-700 inline-flex items-center gap-1">
                        <span>{{ __('View all orders') }}</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                @if ($recentPayments->isEmpty())
                    <div class="p-12 text-center text-slate-500 text-sm">
                        <svg class="mx-auto h-10 w-10 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p>{{ __('No payment attempts recorded in the selected :range-day window.', ['range' => $range]) }}</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-slate-50/75 border-b border-slate-100 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                    <th scope="col" class="py-3 px-4 sm:px-6">{{ __('Reference') }}</th>
                                    <th scope="col" class="py-3 px-4 sm:px-6">{{ __('Payer') }}</th>
                                    <th scope="col" class="py-3 px-4 sm:px-6">{{ __('Status') }}</th>
                                    <th scope="col" class="py-3 px-4 sm:px-6 text-right">{{ __('Amount') }}</th>
                                    <th scope="col" class="py-3 px-4 sm:px-6 text-right">{{ __('Time (UTC)') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($recentPayments as $payment)
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-3.5 px-4 sm:px-6 font-mono text-xs text-slate-800">
                                            {{ $payment->external_reference }}
                                        </td>
                                        <td class="py-3.5 px-4 sm:px-6">
                                            <div class="font-semibold text-slate-900 text-xs sm:text-sm">{{ $payment->user->name ?? __('Unknown user') }}</div>
                                            <div class="text-xs text-slate-400">{{ $payment->user->email ?? '' }}</div>
                                        </td>
                                        <td class="py-3.5 px-4 sm:px-6">
                                            @if ($payment->isSucceeded())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    SUCCEEDED
                                                </span>
                                            @elseif ($payment->isPending())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                    PENDING
                                                </span>
                                            @elseif ($payment->isCancelled())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-50 text-slate-600 border border-slate-200">
                                                    CANCELLED
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                    FAILED
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 sm:px-6 text-right font-bold text-slate-900 tabular-nums">
                                            {{ $payment->formattedAmount() }}
                                        </td>
                                        <td class="py-3.5 px-4 sm:px-6 text-right text-xs text-slate-400 font-mono">
                                            {{ $payment->created_at->toIso8601String() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($recentPayments->hasPages())
                        <div class="p-4 sm:px-6 border-t border-slate-100">
                            {{ $recentPayments->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
