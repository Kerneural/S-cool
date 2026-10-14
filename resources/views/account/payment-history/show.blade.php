<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Payment Receipt Details') }}
            </h2>
            <a href="{{ route('account.payment-history.index') }}"
               class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                &larr; Back to Payment History
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden border border-gray-100 p-6 sm:p-8">
                <!-- Receipt Header -->
                <div class="border-b border-gray-100 pb-6 flex items-start justify-between">
                    <div>
                        <span class="text-xs uppercase tracking-wider font-semibold text-gray-400">Digital Receipt</span>
                        <h3 class="text-2xl font-bold text-gray-900 mt-1 font-mono">
                            {{ $payment->external_reference }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Created at: {{ $payment->created_at->toIso8601String() }} (UTC)
                        </p>
                    </div>

                    <div>
                        @if ($payment->isSucceeded())
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                SUCCEEDED
                            </span>
                        @elseif ($payment->isPending())
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                PENDING
                            </span>
                        @elseif ($payment->isCancelled())
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-50 text-gray-600 border border-gray-200">
                                CANCELLED
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                FAILED
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Receipt Line Items -->
                <div class="py-6 border-b border-gray-100 space-y-4">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Community:</span>
                        <span class="font-medium text-gray-900">{{ $payment->community->name ?? 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Purpose:</span>
                        <span class="font-medium text-gray-900">{{ $payment->purposeLabel() }}</span>
                    </div>

                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Payment Gateway:</span>
                        <span class="font-medium text-gray-900">SePay Sandbox (Test Mode)</span>
                    </div>

                    @if ($payment->paid_at)
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Confirmed at (UTC):</span>
                            <span class="font-mono text-gray-900">{{ $payment->paid_at->toIso8601String() }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between items-center text-base pt-4 border-t border-gray-100 font-semibold">
                        <span class="text-gray-800">Total Amount:</span>
                        <span class="text-xl text-emerald-600 font-bold">{{ $payment->formattedAmount() }}</span>
                    </div>
                </div>

                <!-- Entitlement / Access State -->
                <div class="pt-6">
                    @if ($payment->isSucceeded())
                        @if ($payment->isCourseAccess())
                            @if ($payment->grantsCurrentAccess(auth()->user()))
                                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-semibold text-emerald-800">Course Unlocked</h4>
                                        <p class="text-xs text-emerald-700 mt-0.5">You have active access to {{ $payment->course->title }}.</p>
                                    </div>
                                    <a href="{{ route('communities.courses.show', [$payment->community, $payment->course]) }}"
                                       class="px-4 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-lg hover:bg-emerald-700 transition">
                                        Go to Course &rarr;
                                    </a>
                                </div>
                            @else
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <h4 class="text-sm font-semibold text-gray-800">Course Access Inactive</h4>
                                    <p class="text-xs text-gray-600 mt-0.5">
                                        Your access to this course is currently locked or your community membership is inactive. This receipt is retained for your personal records.
                                    </p>
                                </div>
                            @endif
                        @else
                            @if ($isActiveMember || $isCreator)
                                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-semibold text-emerald-800">Active Membership</h4>
                                        <p class="text-xs text-emerald-700 mt-0.5">You are an active member of this community.</p>
                                    </div>
                                    <a href="{{ route('communities.show', $payment->community) }}"
                                       class="px-4 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-lg hover:bg-emerald-700 transition">
                                        Go to Community &rarr;
                                    </a>
                                </div>
                            @else
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <h4 class="text-sm font-semibold text-gray-800">Inactive Membership</h4>
                                    <p class="text-xs text-gray-600 mt-0.5">
                                        Your membership in this community is currently inactive. This receipt is retained for your personal records and does not grant community access.
                                    </p>
                                </div>
                            @endif
                        @endif
                    @elseif ($payment->isPending())
                        @if ($payment->canResumeCheckout(auth()->user()))
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-semibold text-amber-800">Pending Payment Order</h4>
                                    <p class="text-xs text-amber-700 mt-0.5">{{ $payment->isMembershipRenewal() ? __('You can complete the test transfer to renew 30 UTC days of membership access.') : ($payment->isCourseAccess() ? __('You can complete the test transfer to unlock this course.') : __('You can complete the test transfer to activate your membership.')) }}</p>
                                </div>
                                <a href="{{ route('payments.checkout', $payment->external_reference) }}"
                                   class="px-4 py-2 bg-amber-600 text-white text-xs font-semibold rounded-lg hover:bg-amber-700 transition">
                                    Open Checkout &rarr;
                                </a>
                            </div>
                        @else
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-gray-800">Checkout No Longer Available</h4>
                                <p class="text-xs text-gray-600 mt-0.5">
                                    This order cannot be resumed because its current access requirements are no longer satisfied. This receipt is retained for your personal records.
                                </p>
                            </div>
                        @endif
                    @else
                        <div class="text-xs text-gray-400">
                            This transaction ended with status {{ $payment->status }}.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
