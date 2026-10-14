<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Personal Payment History') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden border border-gray-100">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Your Payment Attempts</h3>
                        <p class="text-sm text-gray-500">
                            History of community admission payment attempts and receipts on your account (SePay Sandbox - Test Mode).
                        </p>
                    </div>
                </div>

                @if ($payments->isEmpty())
                    <div class="p-12 text-center text-gray-400 text-sm">
                        No payment attempts recorded for your account.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-gray-50/75 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="py-3 px-4">Reference</th>
                                    <th class="py-3 px-4">Community</th>
                                    <th class="py-3 px-4">Purpose</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-right">Amount</th>
                                    <th class="py-3 px-4 text-right">Time (UTC)</th>
                                    <th class="py-3 px-4 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($payments as $payment)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="py-3 px-4 font-mono text-xs text-gray-800">
                                            {{ $payment->external_reference }}
                                        </td>
                                        <td class="py-3 px-4 font-medium text-gray-900">
                                            {{ $payment->community->name ?? 'Unknown Community' }}
                                        </td>
                                        <td class="py-3 px-4 text-xs text-gray-600">
                                            {{ $payment->purposeLabel() }}
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
                                        <td class="py-3 px-4 text-center">
                                            <div class="inline-flex items-center gap-2">
                                                <a href="{{ route('account.payment-history.show', $payment) }}"
                                                   class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                                    View Receipt
                                                </a>
                                                @if ($payment->canResumeCheckout(auth()->user()))
                                                    <span class="text-gray-300">|</span>
                                                    <a href="{{ route('payments.checkout', $payment->external_reference) }}"
                                                       class="text-xs font-semibold text-amber-600 hover:text-amber-800">
                                                        Resume Checkout
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($payments->hasPages())
                        <div class="p-4 border-t border-gray-100">
                            {{ $payments->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
