<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Thanh toán truy cập cộng đồng') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{
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
                } else if (data.status === 'FAILED' || data.status === 'CANCELLED') {
                    window.location.href = '{{ route('payments.status', $payment->external_reference) }}';
                }
            })
            .catch(() => { this.error = 'Unable to check payment status. Try again.'; })
            .finally(() => {
                this.checking = false;
            });
        }
    }" x-init="setInterval(() => { if (status === 'PENDING') checkStatus(); }, 4000)">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 sm:p-8">
                <div class="text-center mb-6">
                    <span class="inline-block px-3 py-1 bg-amber-100 text-amber-800 text-xs font-semibold rounded-full uppercase tracking-wider mb-2">
                        SePay Test Mode
                    </span>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $community->name }}</h1>
                    <p class="text-sm text-gray-500 mt-1">Chỉ giả lập giao dịch trong SePay Test Mode. Không chuyển tiền thật.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center bg-gray-50 rounded-xl p-6 border border-gray-100">
                    <!-- QR Code -->
                    <div class="flex flex-col items-center justify-center text-center">
                        <div class="p-3 bg-white rounded-xl shadow-sm border border-gray-200">
                            <div class="w-56 h-56 flex items-center justify-center text-sm text-gray-700 p-4">SePay Test Mode: use the transaction simulator with the reference and exact amount shown here.</div>
                        </div>
                        <p class="text-xs text-gray-500 mt-3">Không quét QR hay dùng ứng dụng ngân hàng thật.</p>
                    </div>

                    <!-- Transfer Details -->
                    <div class="space-y-4 text-sm">
                        <div class="border-b pb-2">
                            <span class="text-gray-500 block text-xs uppercase">Ngân hàng</span>
                            <span class="font-semibold text-gray-800 text-base">{{ $checkoutData['bank_name'] }}</span>
                        </div>
                        <div class="border-b pb-2">
                            <span class="text-gray-500 block text-xs uppercase">Số tài khoản</span>
                            <span class="font-mono font-bold text-gray-900 text-lg tracking-wider">{{ $checkoutData['account_number'] }}</span>
                        </div>
                        <div class="border-b pb-2">
                            <span class="text-gray-500 block text-xs uppercase">Chủ tài khoản</span>
                            <span class="font-semibold text-gray-800">{{ $checkoutData['account_holder'] }}</span>
                        </div>
                        <div class="border-b pb-2">
                            <span class="text-gray-500 block text-xs uppercase">Số tiền</span>
                            <span class="font-bold text-emerald-600 text-xl">{{ $payment->formattedAmount() }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs uppercase">Nội dung chuyển khoản (bắt buộc)</span>
                            <span class="inline-block mt-1 font-mono font-bold bg-amber-50 text-amber-900 px-3 py-1 rounded border border-amber-200 text-base">
                                {{ $checkoutData['payment_code'] }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Status & Action -->
                <div class="mt-8 text-center">
                    <p x-show="error" x-text="error" class="mb-4 text-sm text-red-700"></p>
                    <div class="inline-flex items-center space-x-2 text-sm text-gray-600 mb-4">
                        <svg class="animate-spin h-4 w-4 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Hệ thống đang tự động lắng nghe giao dịch SePay...</span>
                    </div>

                    <div>
                        <button type="button" @click="checkStatus()" :disabled="checking"
                                class="inline-flex items-center px-5 py-2.5 bg-emerald-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 active:bg-emerald-800 focus:outline-none transition ease-in-out duration-150">
                            <span x-text="checking ? 'Đang kiểm tra...' : 'Kiểm tra trạng thái'"></span>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('payments.cancel', $payment->external_reference) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="text-sm text-gray-600 underline">Cancel this test payment attempt</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
