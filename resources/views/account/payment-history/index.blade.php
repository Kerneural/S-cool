<x-app-layout>
    <x-slot name="header">
        <h1 class="font-bold text-xl text-slate-900 leading-tight">
            {{ __('Personal Payment History') }}
        </h1>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Sidebar Nav (4 cols on lg) -->
                <div class="min-w-0 lg:col-span-4 space-y-4">
                    <x-account-navigation active="payment-history" />
                </div>

                <!-- Main Content (8 cols on lg) -->
                <div class="min-w-0 lg:col-span-8 space-y-4 break-words [overflow-wrap:anywhere]">
                    <div class="bg-white shadow-xs rounded-2xl overflow-hidden border border-slate-200/80">
                        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 tracking-tight">{{ __('Your Payment Attempts') }}</h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    {{ __('Your community access, renewal and course payment attempts and receipts (SePay Sandbox - Test Mode).') }}
                                </p>
                            </div>
                        </div>

                @if ($payments->isEmpty())
                    <div class="p-16 text-center text-slate-400 text-sm">
                        <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="font-medium text-slate-500">{{ __('No payment attempts recorded for your account.') }}</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-slate-50/75 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    <th class="py-3.5 px-5">{{ __('Reference') }}</th>
                                    <th class="py-3.5 px-5">{{ __('Community') }}</th>
                                    <th class="py-3.5 px-5">{{ __('Purpose') }}</th>
                                    <th class="py-3.5 px-5">{{ __('Status') }}</th>
                                    <th class="py-3.5 px-5 text-right">{{ __('Amount') }}</th>
                                    <th class="py-3.5 px-5 text-right">{{ __('Time (UTC)') }}</th>
                                    <th class="py-3.5 px-5 text-center">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($payments as $payment)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="py-3.5 px-5 font-mono text-xs font-bold text-slate-800">
                                            {{ $payment->external_reference }}
                                        </td>
                                        <td class="py-3.5 px-5 font-bold text-slate-900">
                                            {{ $payment->community->name ?? 'Unknown Community' }}
                                        </td>
                                        <td class="py-3.5 px-5 text-xs text-slate-600 font-medium">
                                            {{ $payment->purposeLabel() }}
                                        </td>
                                        <td class="py-3.5 px-5">
                                            @if ($payment->isSucceeded())
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    {{ __('SUCCEEDED') }}
                                                </span>
                                            @elseif ($payment->isPending())
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    {{ __('PENDING') }}
                                                </span>
                                            @elseif ($payment->isCancelled())
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                    {{ __('CANCELLED') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                    {{ __('FAILED') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-5 text-right font-bold text-slate-900">
                                            {{ $payment->formattedAmount() }}
                                        </td>
                                        <td class="py-3.5 px-5 text-right text-xs text-slate-500 font-mono">
                                            {{ $payment->created_at->toIso8601String() }}
                                        </td>
                                        <td class="py-3.5 px-5 text-center">
                                            <div class="inline-flex items-center gap-2">
                                                <a href="{{ route('account.payment-history.show', $payment) }}"
                                                   class="text-xs font-bold text-teal-600 hover:text-teal-800">
                                                    {{ __('View Receipt') }}
                                                </a>
                                                @if ($payment->canResumeCheckout(auth()->user()))
                                                    <span class="text-slate-300">|</span>
                                                    <a href="{{ route('payments.checkout', $payment->external_reference) }}"
                                                       class="text-xs font-bold text-amber-600 hover:text-amber-800">
                                                        {{ __('Resume Checkout') }}
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
                        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                            {{ $payments->links() }}
                        </div>
                    @endif
                @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
