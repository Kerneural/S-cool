<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payment Status') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 sm:p-8 text-center">
                @if ($payment->isSucceeded() && ! $accessAvailable)
                    <h1 class="text-2xl font-bold text-gray-900">{{ __('Payment Recorded — Access Inactive') }}</h1>
                    <p class="mt-3 text-gray-600">{{ __('This successful test payment is retained as a receipt. Current membership, community or course restrictions still apply.') }}</p>
                    <a class="mt-4 inline-block text-indigo-700 underline" href="{{ route('account.payment-history.show', $payment) }}">{{ __('View Receipt') }}</a>
                @elseif ($payment->isSucceeded())
                    <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ __('Payment Successful!') }}</h1>
                    @if ($payment->isCourseAccess() && $payment->course)
                        <p class="text-gray-600 text-sm mb-6">{{ __('You have unlocked full access to :course.', ['course' => $payment->course->title]) }}</p>
                        <a href="{{ route('communities.courses.show', [$community, $payment->course]) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700">
                            {{ __('Go to Course') }}
                        </a>
                    @else
                        <p class="text-gray-600 text-sm mb-6">{{ __('You have been granted active membership in :community.', ['community' => $community->name]) }}</p>
                        <a href="{{ route('communities.show', $community) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700">
                            {{ __('Go to Community') }}
                        </a>
                    @endif
                @elseif ($payment->isPending())
                    <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ __('Awaiting Bank Transfer') }}</h1>
                    <p class="text-gray-600 text-sm mb-6">{{ __('SePay Test Mode: transaction is awaiting confirmation. Simulate this payment in SePay Test Mode. Do not make a real transfer.') }}</p>
                    <a href="{{ route('payments.checkout', $payment->external_reference) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        {{ __('Return to Test Checkout') }}
                    </a>
                @else
                    <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ __('Transaction Ended') }}</h1>
                    <p class="text-gray-600 text-sm mb-6">{{ __('Payment was not completed or was cancelled.') }}</p>
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        {{ __('Return to Dashboard') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
