<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="font-bold text-xl text-slate-900 leading-tight">
                {{ __('Payment Receipt Details') }}
            </h1>
            <a href="{{ route('account.payment-history.index') }}"
               class="text-xs font-bold text-teal-600 hover:text-teal-800">
                &larr; Back to Payment History
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-xs rounded-2xl overflow-hidden border border-slate-200/80 p-6 sm:p-8">
                <!-- Receipt Header -->
                <div class="border-b border-slate-100 pb-6 flex items-start justify-between">
                    <div>
                        <span class="text-xs uppercase tracking-wider font-bold text-slate-400">Digital Receipt</span>
                        <h3 class="text-2xl font-black text-slate-900 mt-1 font-mono tracking-tight">
                            {{ $payment->external_reference }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Created at: {{ $payment->created_at->toIso8601String() }} (UTC)
                        </p>
                    </div>

                    <div>
                        @if ($payment->isSucceeded())
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                SUCCEEDED
                            </span>
                        @elseif ($payment->isPending())
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                PENDING
                            </span>
                        @elseif ($payment->isCancelled())
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                CANCELLED
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                FAILED
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Receipt Line Items -->
                <div class="py-6 border-b border-slate-100 space-y-4">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500">Community:</span>
                        <span class="font-bold text-slate-900">{{ $payment->community->name ?? 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500">Purpose:</span>
                        <span class="font-semibold text-slate-800">{{ $payment->purposeLabel() }}</span>
                    </div>

                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500">Payment Gateway:</span>
                        <span class="font-semibold text-slate-800">SePay Sandbox (Test Mode)</span>
                    </div>

                    @if ($payment->paid_at)
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500">Confirmed at (UTC):</span>
                            <span class="font-mono text-slate-900 font-bold">{{ $payment->paid_at->toIso8601String() }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between items-center text-base pt-4 border-t border-slate-100">
                        <span class="text-slate-700 font-bold">Total Amount:</span>
                        <span class="text-2xl text-teal-600 font-black">{{ $payment->formattedAmount() }}</span>
                    </div>
                </div>

                <!-- Entitlement / Access State -->
                <div class="pt-6">
                    @if ($payment->isSucceeded())
                        @if ($payment->isCourseAccess())
                            @if ($payment->grantsCurrentAccess(auth()->user()))
                                <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div>
                                        <h4 class="text-sm font-bold text-emerald-900">Course Unlocked</h4>
                                        <p class="text-xs text-emerald-700 mt-0.5">You have active access to {{ $payment->course->title }}.</p>
                                    </div>
                                    <a href="{{ route('communities.courses.show', [$payment->community, $payment->course]) }}"
                                       class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition self-start sm:self-auto">
                                        Go to Course &rarr;
                                    </a>
                                </div>
                            @else
                                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-5">
                                    <h4 class="text-sm font-bold text-slate-800">Course Access Inactive</h4>
                                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                        Your access to this course is currently locked or your community membership is inactive. This receipt is retained for your personal records.
                                    </p>
                                </div>
                            @endif
                        @else
                            @if ($isActiveMember || $isCreator)
                                <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div>
                                        <h4 class="text-sm font-bold text-emerald-900">Active Membership</h4>
                                        <p class="text-xs text-emerald-700 mt-0.5">You are an active member of this community.</p>
                                    </div>
                                    <a href="{{ route('communities.show', $payment->community) }}"
                                       class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition self-start sm:self-auto">
                                        Go to Community &rarr;
                                    </a>
                                </div>
                            @else
                                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-5">
                                    <h4 class="text-sm font-bold text-slate-800">Inactive Membership</h4>
                                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                        Your membership in this community is currently inactive. This receipt is retained for your personal records and does not grant community access.
                                    </p>
                                </div>
                            @endif
                        @endif
                    @elseif ($payment->isPending())
                        @if ($payment->canResumeCheckout(auth()->user()))
                            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <h4 class="text-sm font-bold text-amber-900">Pending Payment Order</h4>
                                    <p class="text-xs text-amber-800 mt-0.5">{{ $payment->isMembershipRenewal() ? __('You can complete the test transfer to renew 30 UTC days of membership access.') : ($payment->isCourseAccess() ? __('You can complete the test transfer to unlock this course.') : __('You can complete the test transfer to activate your membership.')) }}</p>
                                </div>
                                <a href="{{ route('payments.checkout', $payment->external_reference) }}"
                                   class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-xs transition self-start sm:self-auto">
                                    Open Checkout &rarr;
                                </a>
                            </div>
                        @else
                            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-5">
                                <h4 class="text-sm font-bold text-slate-800">Checkout No Longer Available</h4>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                    This order cannot be resumed because its current access requirements are no longer satisfied. This receipt is retained for your personal records.
                                </p>
                            </div>
                        @endif
                    @else
                        <div class="text-xs text-slate-400 font-medium">
                            This transaction ended with status {{ $payment->status }}.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
