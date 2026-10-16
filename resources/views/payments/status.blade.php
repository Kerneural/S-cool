<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 leading-tight">
            {{ __('Payment Status') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xs rounded-3xl p-8 sm:p-10 text-center border border-slate-200/80">
                @if ($payment->isSucceeded() && ! $accessAvailable)
                    <div class="w-16 h-16 bg-slate-100 text-slate-500 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-2xs">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Payment Recorded — Access Inactive') }}</h1>
                    <p class="mt-3 text-slate-600 text-sm leading-relaxed">{{ __('This successful test payment is retained as a receipt. Current membership, community or course restrictions still apply.') }}</p>
                    <div class="mt-6">
                        <a class="inline-flex items-center px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition" href="{{ route('account.payment-history.show', $payment) }}">{{ __('View Receipt') }} &rarr;</a>
                    </div>
                @elseif ($payment->isSucceeded())
                    <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-2xs border border-emerald-200/60">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mb-2">{{ __('Payment Successful!') }}</h1>
                    @if ($payment->isCourseAccess() && $payment->course)
                        <p class="text-slate-600 text-sm mb-6 leading-relaxed">{{ __('You have unlocked full access to :course.', ['course' => $payment->course->title]) }}</p>
                        <a href="{{ route('communities.courses.show', [$community, $payment->course]) }}" class="inline-flex items-center px-6 py-3 bg-brand hover:bg-brand-hover text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-sm shadow-teal-500/20 transition duration-150">
                            {{ __('Go to Course') }} &rarr;
                        </a>
                    @else
                        <p class="text-slate-600 text-sm mb-6 leading-relaxed">{{ __('You have been granted active membership in :community.', ['community' => $community->name]) }}</p>
                        <a href="{{ route('communities.show', $community) }}" class="inline-flex items-center px-6 py-3 bg-brand hover:bg-brand-hover text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-sm shadow-teal-500/20 transition duration-150">
                            {{ __('Go to Community') }} &rarr;
                        </a>
                    @endif
                @elseif ($payment->isPending())
                    <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-2xs border border-amber-200/60">
                        <svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mb-2">{{ __('Awaiting Bank Transfer') }}</h1>
                    <p class="text-slate-600 text-xs mb-6 leading-relaxed max-w-sm mx-auto">{{ __('SePay Test Mode: transaction is awaiting confirmation. Simulate this payment in SePay Test Mode. Do not make a real transfer.') }}</p>
                    <a href="{{ route('payments.checkout', $payment->external_reference) }}" class="inline-flex items-center px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-xs transition duration-150">
                        {{ __('Return to Test Checkout') }}
                    </a>
                @else
                    <div class="w-16 h-16 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-2xs border border-rose-200/60">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mb-2">{{ __('Transaction Ended') }}</h1>
                    <p class="text-slate-600 text-xs mb-6 leading-relaxed">{{ __('Payment was not completed or was cancelled.') }}</p>
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-xs transition duration-150">
                        {{ __('Return to Dashboard') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
