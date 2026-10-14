<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="orders" />

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm text-center">
                    <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Total Attempts</span>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $summary['total'] }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm text-center">
                    <span class="text-xs text-emerald-600 uppercase tracking-wider font-medium">Succeeded</span>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $summary['succeeded'] }}</p>
                    <span class="text-xs text-gray-500 font-medium block mt-0.5">{{ number_format($summary['succeeded_sum'], 0, ',', '.') }} VND</span>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm text-center">
                    <span class="text-xs text-amber-600 uppercase tracking-wider font-medium">Pending</span>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ $summary['pending'] }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm text-center">
                    <span class="text-xs text-slate-500 uppercase tracking-wider font-medium">Cancelled / Failed</span>
                    <p class="text-2xl font-bold text-slate-600 mt-1">{{ $summary['cancelled'] + $summary['failed'] }}</p>
                </div>
            </div>

            <!-- Orders Table Container -->
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden border border-gray-100">
                <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Orders & Payment Attempts</h3>
                        <p class="text-sm text-gray-500">
                            Track community admission payment attempts for <span class="font-medium text-gray-700">{{ $community->name }}</span> (SePay Sandbox - Test Mode).
                        </p>
                    </div>

                    <!-- Filter Controls (Status & Date Range) -->
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Status Tabs -->
                        <div class="flex items-center gap-1 bg-gray-50 p-1 rounded-lg border border-gray-200">
                            @foreach (['ALL' => 'All Statuses', 'SUCCEEDED' => 'Succeeded', 'PENDING' => 'Pending', 'CANCELLED' => 'Cancelled', 'FAILED' => 'Failed'] as $key => $label)
                                <a href="{{ route('communities.orders.index', array_merge(request()->query(), ['community' => $community, 'status' => $key])) }}"
                                   class="px-2.5 py-1 text-xs font-medium rounded-md transition {{ $statusFilter === $key ? 'bg-white text-gray-900 shadow-sm font-semibold' : 'text-gray-500 hover:text-gray-900' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>

                        <!-- Date Range Tabs (UTC) -->
                        <div class="flex items-center gap-1 bg-gray-50 p-1 rounded-lg border border-gray-200">
                            @foreach (['ALL' => 'All Time', '7' => '7 Days', '30' => '30 Days', '90' => '90 Days'] as $rKey => $rLabel)
                                <a href="{{ route('communities.orders.index', array_merge(request()->query(), ['community' => $community, 'range' => $rKey])) }}"
                                   class="px-2.5 py-1 text-xs font-medium rounded-md transition {{ $rangeFilter === $rKey ? 'bg-white text-gray-900 shadow-sm font-semibold' : 'text-gray-500 hover:text-gray-900' }}">
                                    {{ $rLabel }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if ($orders->isEmpty())
                    <div class="p-12 text-center text-gray-400 text-sm">
                        No orders found matching the selected filters.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-gray-50/75 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="py-3 px-4">Reference</th>
                                    <th class="py-3 px-4">Payer</th>
                                    <th class="py-3 px-4">Purpose</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-right">Amount</th>
                                    <th class="py-3 px-4 text-right">Time (UTC)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($orders as $order)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="py-3 px-4 font-mono text-xs text-gray-800">
                                            <div>{{ $order->external_reference }}</div>
                                            @if ($order->invitation)
                                                <div class="text-[11px] text-gray-400 mt-0.5">Invitation #{{ $order->invitation->id }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-medium text-gray-900">{{ $order->user->name ?? 'Unknown user' }}</div>
                                            <div class="text-xs text-gray-400">{{ $order->user->email ?? '' }}</div>
                                        </td>
                                        <td class="py-3 px-4 text-xs text-gray-600">
                                            {{ $order->purposeLabel() }}
                                        </td>
                                        <td class="py-3 px-4">
                                            @if ($order->isSucceeded())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    SUCCEEDED
                                                </span>
                                            @elseif ($order->isPending())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                    PENDING
                                                </span>
                                            @elseif ($order->isCancelled())
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
                                            {{ $order->formattedAmount() }}
                                        </td>
                                        <td class="py-3 px-4 text-right text-xs text-gray-500 font-mono">
                                            {{ $order->created_at->toIso8601String() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($orders->hasPages())
                        <div class="p-4 border-t border-gray-100">
                            {{ $orders->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
