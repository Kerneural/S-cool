<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="overview" />

            <!-- Overview Header & Date Range Filter -->
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Community Overview & Operational Metrics</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Truthful operational metrics for <span class="font-medium text-gray-700">{{ $community->name }}</span>.
                    </p>
                    <div class="text-xs text-gray-400 mt-1">
                        Date range: <span class="font-mono text-gray-600">{{ $startUtc->format('Y-m-d') }}</span> to <span class="font-mono text-gray-600">{{ $endUtcExclusive->format('Y-m-d') }}</span> (Timezone: <span class="font-semibold text-gray-700">UTC</span>)
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mr-1">Date range:</span>
                    <a href="{{ route('communities.manage', ['community' => $community, 'range' => 7]) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $range === 7 ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        7 days
                    </a>
                    <a href="{{ route('communities.manage', ['community' => $community, 'range' => 30]) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $range === 30 ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        30 days
                    </a>
                    <a href="{{ route('communities.manage', ['community' => $community, 'range' => 90]) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $range === 90 ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        90 days
                    </a>
                </div>
            </div>

            <!-- Truthful Metrics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Members Card (Creator shown separately) -->
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <span>Active Members</span>
                            <span class="text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded text-[11px] font-bold">ACTIVE</span>
                        </div>
                        <div class="text-3xl font-extrabold text-gray-900 mt-2">
                            {{ $activeMembersCount }}
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-50 text-xs text-gray-500 flex items-center justify-between">
                        <span>Creator:</span>
                        <span class="font-medium text-gray-800">{{ $creator->name ?? '1' }}</span>
                    </div>
                </div>

                <!-- Content & Courses Card -->
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <span>Content & Courses</span>
                            <span class="text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded text-[11px] font-bold">Content</span>
                        </div>
                        <div class="flex items-baseline gap-4 mt-2">
                            <div>
                                <span class="text-3xl font-extrabold text-gray-900">{{ $postsCount }}</span>
                                <span class="text-xs text-gray-500 ml-1">posts</span>
                            </div>
                            <div class="border-l border-gray-200 pl-4">
                                <span class="text-2xl font-bold text-gray-800">{{ $publishedCoursesCount }}</span>
                                <span class="text-xs text-gray-500 ml-1">courses</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-50 text-xs text-gray-500">
                        <span>Published lessons: <strong class="text-gray-700">{{ $publishedLessonsCount }}</strong></span>
                    </div>
                </div>

                <!-- Simulated Receipts in Test Mode Card -->
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <span>Simulated Test Mode Receipts</span>
                            <span class="text-amber-700 bg-amber-50 px-2 py-0.5 rounded text-[11px] font-bold">Test Mode</span>
                        </div>
                        <div class="text-2xl font-extrabold text-emerald-600 mt-2">
                            {{ $formattedSimulatedReceipts }}
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-50 text-xs text-gray-500">
                        <span>Simulated successful receipts (Test Mode)</span>
                    </div>
                </div>

                <!-- Learning Completion Ratio Card -->
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <span>Learning Completion Ratio</span>
                            <span class="text-sky-600 bg-sky-50 px-2 py-0.5 rounded text-[11px] font-bold">Learning</span>
                        </div>
                        <div class="text-3xl font-extrabold text-gray-900 mt-2">
                            @if ($hasCompletionData)
                                {{ $completionRatio }}%
                            @else
                                <span class="text-gray-400 text-xl font-normal">No data</span>
                            @endif
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-50 text-xs text-gray-500">
                        @if ($hasCompletionData)
                            <span>{{ $completedLessonsCount }} completions / {{ $accessibleLessonPairsCount }} eligible member-lesson pairs</span>
                        @else
                            <span>No active members or published lessons</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Navigation Bar -->
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Community Management Shortcuts</h3>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    <a href="{{ route('communities.classroom.index', $community) }}"
                       class="flex flex-col items-center justify-center p-3 rounded-lg border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/50 transition text-center group">
                        <span class="text-sm font-semibold text-gray-800 group-hover:text-indigo-600">Classroom</span>
                        <span class="text-xs text-gray-400 mt-0.5">Courses & lessons</span>
                    </a>
                    <a href="{{ route('communities.members.index', $community) }}"
                       class="flex flex-col items-center justify-center p-3 rounded-lg border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/50 transition text-center group">
                        <span class="text-sm font-semibold text-gray-800 group-hover:text-indigo-600">Members</span>
                        <span class="text-xs text-gray-400 mt-0.5">Manage access & roles</span>
                    </a>
                    <a href="{{ route('communities.invitations.index', $community) }}"
                       class="flex flex-col items-center justify-center p-3 rounded-lg border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/50 transition text-center group">
                        <span class="text-sm font-semibold text-gray-800 group-hover:text-indigo-600">Invitations</span>
                        <span class="text-xs text-gray-400 mt-0.5">Generate invite links</span>
                    </a>
                    <a href="{{ route('communities.orders.index', $community) }}"
                       class="flex flex-col items-center justify-center p-3 rounded-lg border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/50 transition text-center group">
                        <span class="text-sm font-semibold text-gray-800 group-hover:text-indigo-600">Orders</span>
                        <span class="text-xs text-gray-400 mt-0.5">Payment attempts & orders</span>
                    </a>
                    <a href="{{ route('communities.reports.index', $community) }}"
                       class="flex flex-col items-center justify-center p-3 rounded-lg border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/50 transition text-center group">
                        <span class="text-sm font-semibold text-gray-800 group-hover:text-indigo-600">Reports</span>
                        <span class="text-xs text-gray-400 mt-0.5">Moderation queue</span>
                    </a>
                    <a href="{{ route('communities.edit', $community) }}"
                       class="flex flex-col items-center justify-center p-3 rounded-lg border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/50 transition text-center group">
                        <span class="text-sm font-semibold text-gray-800 group-hover:text-indigo-600">Settings</span>
                        <span class="text-xs text-gray-400 mt-0.5">Community details & rules</span>
                    </a>
                </div>
            </div>

            <!-- Activity Table: Payments & Transactions in Range -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Recent Payment Activity (Last {{ $range }} Days)</h3>
                        <p class="text-xs text-gray-500 mt-0.5">All payment attempts within the selected time window.</p>
                    </div>
                    <a href="{{ route('communities.orders.index', $community) }}"
                       class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                        View all orders &rarr;
                    </a>
                </div>

                @if ($recentPayments->isEmpty())
                    <div class="p-12 text-center text-gray-400 text-sm">
                        No payment attempts recorded in the selected {{ $range }}-day window.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-gray-50/75 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="py-3 px-4">Reference</th>
                                    <th class="py-3 px-4">Payer</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-right">Amount</th>
                                    <th class="py-3 px-4 text-right">Time (UTC)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($recentPayments as $payment)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="py-3 px-4 font-mono text-xs text-gray-800">
                                            {{ $payment->external_reference }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-medium text-gray-900">{{ $payment->user->name ?? 'Unknown user' }}</div>
                                            <div class="text-xs text-gray-400">{{ $payment->user->email ?? '' }}</div>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if ($payment->isSucceeded())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    SUCCEEDED
                                                </span>
                                            @elseif ($payment->isPending())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                    PENDING
                                                </span>
                                            @elseif ($payment->isCancelled())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200">
                                                    CANCELLED
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                    FAILED
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right font-medium text-gray-900">
                                            {{ $payment->formattedAmount() }}
                                        </td>
                                        <td class="py-3 px-4 text-right text-xs text-gray-500 font-mono">
                                            {{ $payment->created_at->toIso8601String() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($recentPayments->hasPages())
                        <div class="p-4 border-t border-gray-100">
                            {{ $recentPayments->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
