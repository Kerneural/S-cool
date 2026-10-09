<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Thanh toán truy cập cộng đồng') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{
        status: '{{ $payment->status }}',
        checking: false,
        checkStatus() {
            this.checking = true;
            fetch('{{ route('payments.status', $payment->external_reference) }}', {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                this.status = data.status;
                if (data.is_succeeded && data.redirect_url) {
                    window.location.href = data.redirect_url;
                }
            })
            .finally(() => {
                this.checking = false;
            });
        }
    }" x-init="setInterval(() => checkStatus(), 4000)">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 sm:p-8">
                <div class="text-center mb-6">
                    <span class="inline-block px-3 py-1 bg-amber-100 text-amber-800 text-xs font-semibold rounded-full uppercase tracking-wider mb-2">
                        SePay Sandbox Demo
                    </span>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $community->name }}</h1>
                    <p class="text-sm text-gray-500 mt-1">Hoàn tất chuyển khoản để kích hoạt quyền thành viên ngay lập tức.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center bg-gray-50 rounded-xl p-6 border border-gray-100">
                    <!-- QR Code -->
                    <div class="flex flex-col items-center justify-center text-center">
                        <div class="p-3 bg-white rounded-xl shadow-sm border border-gray-200">
                            <img src="{{ $checkoutData['qr_url'] }}" alt="SePay QR Code" class="w-56 h-56 object-contain rounded-lg">
                        </div>
                        <p class="text-xs text-gray-500 mt-3">Quét mã QR bằng ứng dụng ngân hàng bất kỳ</p>
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
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
