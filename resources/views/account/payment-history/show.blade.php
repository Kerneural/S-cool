<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="font-bold text-xl text-slate-900 leading-tight">
                {{ __('Payment Receipt Details') }}
            </h1>
            <a href="{{ route('account.payment-history.index') }}"
               class="text-xs font-bold text-teal-600 hover:text-teal-800">
                &larr; {{ __('Back to Payment History') }}
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <aside class="min-w-0 lg:col-span-4">
                    <x-account-navigation active="payment-history" />
                </aside>
                <div class="min-w-0 lg:col-span-8 space-y-6 break-words [overflow-wrap:anywhere]">
            <div class="bg-white shadow-xs rounded-2xl overflow-hidden border border-slate-200/80 p-6 sm:p-8">
                <!-- Receipt Header -->
                <div class="border-b border-slate-100 pb-6 flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <span class="text-xs uppercase tracking-wider font-bold text-slate-400">{{ __('Digital Receipt') }}</span>
                        <h3 class="break-all text-2xl font-bold text-slate-900 mt-1 font-mono tracking-tight">
                            {{ $payment->external_reference }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">
                            {{ __('Created at') }}: {{ $payment->created_at->toIso8601String() }} (UTC)
                        </p>
                    </div>

                    <div>
                        @if ($payment->isSucceeded())
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                {{ __('SUCCEEDED') }}
                            </span>
                        @elseif ($payment->isPending())
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                {{ __('PENDING') }}
                            </span>
                        @elseif ($payment->isCancelled())
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                {{ __('CANCELLED') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                {{ __('FAILED') }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Receipt Line Items -->
                <dl class="py-6 border-b border-slate-100 space-y-4">
                    <div class="grid grid-cols-1 gap-1 text-sm sm:grid-cols-[max-content_minmax(0,1fr)] sm:gap-4">
                        <dt class="text-slate-500">{{ __('Community:') }}</dt>
                        <dd class="min-w-0 font-bold text-slate-900 sm:text-right">{{ $payment->community->name ?? __('N/A') }}</dd>
                    </div>

                    <div class="grid grid-cols-1 gap-1 text-sm sm:grid-cols-[max-content_minmax(0,1fr)] sm:gap-4">
                        <dt class="text-slate-500">{{ __('Purpose:') }}</dt>
                        <dd class="min-w-0 font-semibold text-slate-800 sm:text-right">{{ $payment->purposeLabel() }}</dd>
                    </div>

                    <div class="grid grid-cols-1 gap-1 text-sm sm:grid-cols-[max-content_minmax(0,1fr)] sm:gap-4">
                        <dt class="text-slate-500">{{ __('Payment Gateway:') }}</dt>
                        <dd class="min-w-0 font-semibold text-slate-800 sm:text-right">{{ __('SePay Sandbox (Test Mode)') }}</dd>
                    </div>

                    @if ($payment->paid_at)
                        <div class="grid grid-cols-1 gap-1 text-sm sm:grid-cols-[max-content_minmax(0,1fr)] sm:gap-4">
                            <dt class="text-slate-500">{{ __('Confirmed at (UTC):') }}</dt>
                            <dd class="min-w-0 font-mono text-slate-900 font-bold sm:text-right">{{ $payment->paid_at->toIso8601String() }}</dd>
                        </div>
                    @endif

                    <div class="flex flex-wrap items-baseline justify-between gap-2 text-base pt-4 border-t border-slate-100">
                        <dt class="text-slate-700 font-bold">{{ __('Total Amount:') }}</dt>
                        <dd class="whitespace-nowrap text-xl sm:text-2xl text-teal-600 font-bold">{{ $payment->formattedAmount() }}</dd>
                    </div>
                </dl>

                <!-- Entitlement / Access State -->
                <div class="pt-6">
                    @if ($payment->isSucceeded())
                        @if ($payment->isCourseAccess())
                            @if ($payment->grantsCurrentAccess(auth()->user()))
                                <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div>
                                        <h4 class="text-sm font-bold text-emerald-900">{{ __('Course Unlocked') }}</h4>
                                        <p class="text-xs text-emerald-700 mt-0.5">{{ __('You have active access to :course.', ['course' => $payment->course->title]) }}</p>
                                    </div>
                                    <a href="{{ route('communities.courses.show', [$payment->community, $payment->course]) }}"
                                       class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition self-start sm:self-auto">
                                        {{ __('Go to Course') }} &rarr;
                                    </a>
                                </div>
                            @else
                                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-5">
                                    <h4 class="text-sm font-bold text-slate-800">{{ __('Course Access Inactive') }}</h4>
                                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                        {{ __('Your access to this course is currently locked or your community membership is inactive. This receipt is retained for your personal records.') }}
                                    </p>
                                </div>
                            @endif
                        @else
                            @if ($isActiveMember || $isCreator)
                                <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div>
                                        <h4 class="text-sm font-bold text-emerald-900">{{ __('Active Membership') }}</h4>
                                        <p class="text-xs text-emerald-700 mt-0.5">{{ __('You are an active member of this community.') }}</p>
                                    </div>
                                    <a href="{{ route('communities.show', $payment->community) }}"
                                       class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition self-start sm:self-auto">
                                        {{ __('Go to Community') }} &rarr;
                                    </a>
                                </div>
                            @else
                                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-5">
                                    <h4 class="text-sm font-bold text-slate-800">{{ __('Inactive Membership') }}</h4>
                                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                        {{ __('Your membership in this community is currently inactive. This receipt is retained for your personal records and does not grant community access.') }}
                                    </p>
                                </div>
                            @endif
                        @endif
                    @elseif ($payment->isPending())
                        @if ($payment->canResumeCheckout(auth()->user()))
                            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <h4 class="text-sm font-bold text-amber-900">{{ __('Pending Payment Order') }}</h4>
                                    <p class="text-xs text-amber-800 mt-0.5">{{ $payment->isMembershipRenewal() ? __('You can complete the test transfer to renew 30 UTC days of membership access.') : ($payment->isCourseAccess() ? __('You can complete the test transfer to unlock this course.') : __('You can complete the test transfer to activate your membership.')) }}</p>
                                </div>
                                <a href="{{ route('payments.checkout', $payment->external_reference) }}"
                                   class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-xs transition self-start sm:self-auto">
                                    {{ __('Open Checkout') }} &rarr;
                                </a>
                            </div>
                        @else
                            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-5">
                                <h4 class="text-sm font-bold text-slate-800">{{ __('Checkout No Longer Available') }}</h4>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                    {{ __('This order cannot be resumed because its current access requirements are no longer satisfied. This receipt is retained for your personal records.') }}
                                </p>
                            </div>
                        @endif
                    @else
                        <div class="text-xs text-slate-400 font-medium">
                            {{ __('This transaction ended with status :status.', ['status' => __($payment->status)]) }}
                        </div>
                    @endif
                </div>
            </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
