<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 leading-tight">
            {{ $payment->purposeLabel() }}
        </h2>
    </x-slot>

    <div class="py-10" x-data="{
        status: '{{ $payment->status }}',
        checking: false,
        error: '',
        checkStatus() {
            if (this.checking) return;
            this.checking = true;
            this.error = '';
            fetch('{{ route('payments.status', $payment->external_reference) }}', {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => {
                if (!res.ok) throw new Error('Status check failed');
                return res.json();
            })
            .then(data => {
                this.status = data.status;
                if (data.is_succeeded && data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else if (data.status !== 'PENDING') {
                    window.location.href = '{{ route('payments.status', $payment->external_reference) }}';
                }
            })
            .catch(() => { this.error = '{{ __('Unable to check payment status. Try again.') }}'; })
            .finally(() => {
                this.checking = false;
            });
        }
    }" x-init="setInterval(() => { if (status === 'PENDING') checkStatus(); }, 4000)">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xs rounded-3xl p-6 sm:p-10 border border-slate-200/80">
                <div class="text-center mb-8">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-800 text-xs font-bold rounded-full uppercase tracking-wider mb-3 border border-amber-200/70">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        {{ __('SePay Test Mode') }}
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">{{ $community->name }}</h1>
                    <p class="text-xs text-slate-500 mt-1.5">{{ __('Simulate transactions in SePay Test Mode only. Do not transfer real funds.') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center bg-slate-50 rounded-2xl p-6 sm:p-8 border border-slate-200/70">
                    <!-- QR Box / Simulator Notice -->
                    <div class="flex flex-col items-center justify-center text-center">
                        <div class="p-5 bg-white rounded-2xl shadow-2xs border border-slate-200 w-full max-w-[240px]">
                            <div class="w-full aspect-square flex flex-col items-center justify-center text-xs text-slate-600 p-2 text-center leading-relaxed">
                                <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                </div>
                                <span class="font-medium">{{ __('SePay Test Mode: use the transaction simulator with the reference and exact amount shown here.') }}</span>
                            </div>
                        </div>
                        <p class="text-xs text-slate-600 mt-3 font-medium">{{ __('Do not scan QR code or use a real banking application.') }}</p>
                    </div>

                    <!-- Transfer Details -->
                    <div class="space-y-4 text-sm">
                        <div class="border-b border-slate-200/80 pb-3">
                            <span class="text-slate-600 block text-xs uppercase tracking-wider font-bold">{{ __('Bank') }}</span>
                            <span class="font-bold text-slate-900 text-base mt-0.5 block">{{ $checkoutData['bank_name'] }}</span>
                        </div>
                        <div class="border-b border-slate-200/80 pb-3">
                            <span class="text-slate-600 block text-xs uppercase tracking-wider font-bold">{{ __('Account Number') }}</span>
                            <span class="font-mono font-bold text-slate-900 text-lg tracking-wider mt-0.5 block">{{ $checkoutData['account_number'] }}</span>
                        </div>
                        <div class="border-b border-slate-200/80 pb-3">
                            <span class="text-slate-600 block text-xs uppercase tracking-wider font-bold">{{ __('Account Holder') }}</span>
                            <span class="font-bold text-slate-800 mt-0.5 block">{{ $checkoutData['account_holder'] }}</span>
                        </div>
                        <div class="border-b border-slate-200/80 pb-3">
                            <span class="text-slate-600 block text-xs uppercase tracking-wider font-bold">{{ __('Amount') }}</span>
                            <span class="font-bold text-teal-600 text-2xl mt-0.5 block">{{ $payment->formattedAmount() }}</span>
                        </div>
                        <div>
                            <span class="text-slate-600 block text-xs uppercase tracking-wider font-bold">{{ __('Transfer Content (Required)') }}</span>
                            <span class="inline-block mt-1.5 font-mono font-bold bg-amber-50 text-amber-900 px-3.5 py-1.5 rounded-xl border border-amber-200 text-base">
                                {{ $checkoutData['payment_code'] }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Status & Action -->
                <div class="mt-8 text-center">
                    <p x-show="error" x-text="error" class="mb-4 text-xs font-bold text-rose-700 bg-rose-50 p-2.5 rounded-xl border border-rose-200 max-w-md mx-auto"></p>

                    <div class="inline-flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-5">
                        <svg class="animate-spin h-4 w-4 text-teal-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>{{ __('Automatically listening for SePay test transaction...') }}</span>
                    </div>

                    <div>
                        <button type="button" @click="checkStatus()" :disabled="checking"
                                class="inline-flex items-center px-6 py-3 bg-brand hover:bg-brand-hover active:bg-brand-hover rounded-xl font-bold text-xs text-white uppercase tracking-wider shadow-sm shadow-teal-500/20 focus:outline-none transition duration-150">
                            <span x-text="checking ? '{{ __('Checking...') }}' : '{{ __('Check Status') }}'"></span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('payments.cancel', $payment->external_reference) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="text-sm font-semibold text-slate-600 hover:text-slate-900 underline transition">{{ __('Cancel this test payment attempt') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
