<x-app-layout>
    <div class="py-10">
        <div class="min-w-0 break-words [overflow-wrap:anywhere] max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="orders" />

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Total Attempts</span>
                        <p class="text-2xl font-bold text-slate-900 tracking-tight">{{ $summary['total'] }}</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs text-emerald-600 uppercase tracking-wider font-semibold">Succeeded</span>
                        <p class="text-2xl font-bold text-emerald-700 tracking-tight">{{ $summary['succeeded'] }}</p>
                        <span class="text-xs text-slate-500 font-medium block mt-0.5">{{ number_format($summary['succeeded_sum'], 0, ',', '.') }} VND</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs text-amber-600 uppercase tracking-wider font-semibold">Pending</span>
                        <p class="text-2xl font-bold text-amber-700 tracking-tight">{{ $summary['pending'] }}</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Cancelled / Failed</span>
                        <p class="text-2xl font-bold text-slate-600 tracking-tight">{{ $summary['cancelled'] + $summary['failed'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Orders Table Container -->
            <div class="bg-white shadow-xs rounded-2xl overflow-hidden border border-slate-200/80">
                <div class="p-6 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-lg font-bold text-slate-900 tracking-tight">Orders & Payment Attempts</h1>
                        <p class="text-sm text-slate-500 mt-0.5">
                            Track community access, renewal and course payment attempts for <span class="font-semibold text-slate-700">{{ $community->name }}</span> (SePay Sandbox - Test Mode).
                        </p>
                    </div>

                    <!-- Filter Controls (Status & Date Range) -->
                    <div class="flex flex-wrap items-center gap-2.5">
                        <!-- Status Tabs -->
                        <div class="flex flex-wrap items-center gap-1 bg-slate-100/80 p-1 rounded-xl border border-slate-200/60">
                            @foreach (['ALL' => 'All Statuses', 'SUCCEEDED' => 'Succeeded', 'PENDING' => 'Pending', 'CANCELLED' => 'Cancelled', 'FAILED' => 'Failed'] as $key => $label)
                                <a href="{{ route('communities.orders.index', array_merge(request()->query(), ['community' => $community, 'status' => $key])) }}"
                                   class="px-2.5 py-1 text-xs font-semibold rounded-lg transition {{ $statusFilter === $key ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>

                        <!-- Date Range Tabs (UTC) -->
                        <div class="flex flex-wrap items-center gap-1 bg-slate-100/80 p-1 rounded-xl border border-slate-200/60">
                            @foreach (['ALL' => 'All Time', '7' => '7 Days', '30' => '30 Days', '90' => '90 Days'] as $rKey => $rLabel)
                                <a href="{{ route('communities.orders.index', array_merge(request()->query(), ['community' => $community, 'range' => $rKey])) }}"
                                   class="px-2.5 py-1 text-xs font-semibold rounded-lg transition {{ $rangeFilter === $rKey ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                                    {{ $rLabel }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if ($orders->isEmpty())
                    <div class="p-16 text-center text-slate-400 text-sm">
                        <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="font-medium text-slate-500">No orders found matching the selected filters.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-slate-50/75 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    <th class="py-3.5 px-5">Reference</th>
                                    <th class="py-3.5 px-5">Payer</th>
                                    <th class="py-3.5 px-5">Purpose</th>
                                    <th class="py-3.5 px-5">Status</th>
                                    <th class="py-3.5 px-5 text-right">Amount</th>
                                    <th class="py-3.5 px-5 text-right">Time (UTC)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($orders as $order)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="py-3.5 px-5 font-mono text-xs text-slate-800">
                                            <div class="font-bold">{{ $order->external_reference }}</div>
                                            @if ($order->invitation)
                                                <div class="text-[11px] text-slate-400 mt-0.5">Invitation #{{ $order->invitation->id }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-5">
                                            <div class="font-bold text-slate-900">{{ $order->user->name ?? 'Unknown user' }}</div>
                                            <div class="text-xs text-slate-400">{{ $order->user->email ?? '' }}</div>
                                        </td>
                                        <td class="py-3.5 px-5 text-xs text-slate-600 font-medium">
                                            {{ $order->purposeLabel() }}
                                        </td>
                                        <td class="py-3.5 px-5">
                                            @if ($order->isSucceeded())
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    SUCCEEDED
                                                </span>
                                            @elseif ($order->isPending())
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    PENDING
                                                </span>
                                            @elseif ($order->isCancelled())
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                    CANCELLED
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                    FAILED
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-5 text-right font-bold text-slate-900">
                                            {{ $order->formattedAmount() }}
                                        </td>
                                        <td class="py-3.5 px-5 text-right text-xs text-slate-500 font-mono">
                                            {{ $order->created_at->toIso8601String() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($orders->hasPages())
                        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                            {{ $orders->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
