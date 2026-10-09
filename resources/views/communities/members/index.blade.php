<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Quản lý thành viên — ') }} {{ $community->name }}
            </h2>
            <a href="{{ route('communities.show', $community) }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                &larr; {{ __('Quay lại cộng đồng') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->has('membership'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                    {{ $errors->first('membership') }}
                </div>
            @endif

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm text-center">
                    <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Tổng thành viên</span>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $counts['total'] }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm text-center">
                    <span class="text-xs text-emerald-600 uppercase tracking-wider font-medium">Đang hoạt động</span>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $counts['active'] }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm text-center">
                    <span class="text-xs text-amber-600 uppercase tracking-wider font-medium">Đang tạm khóa</span>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ $counts['suspended'] }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm text-center">
                    <span class="text-xs text-red-600 uppercase tracking-wider font-medium">Đã xóa</span>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ $counts['removed'] }}</p>
                </div>
            </div>

            <!-- Members List -->
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden border border-gray-100">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Danh sách thành viên</h3>
                        <p class="text-sm text-gray-500">Xem và quản lý quyền truy cập của các thành viên trong cộng đồng.</p>
                    </div>
                    <a href="{{ route('communities.invitations.index', $community) }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        {{ __('Mời thành viên mới') }}
                    </a>
                </div>

                @if ($memberships->isEmpty())
                    <div class="p-12 text-center text-gray-500">
                        Chưa có thành viên nào tham gia cộng đồng này.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Thành viên</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày tham gia</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach ($memberships as $membership)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-9 w-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">
                                                    {{ strtoupper(substr($membership->user->name, 0, 1)) }}
                                                </div>
                                                <div class="ml-3">
                                                    <div class="font-medium text-gray-900">{{ $membership->user->name }}</div>
                                                    <div class="text-gray-500 text-xs">{{ $membership->user->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($membership->status === 'ACTIVE')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                    Hoạt động
                                                </span>
                                            @elseif ($membership->status === 'SUSPENDED')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                    Tạm khóa
                                                </span>
                                            @elseif ($membership->status === 'REMOVED')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Đã xóa
                                                </span>
                                            @elseif ($membership->status === 'PENDING_PAYMENT')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Chờ thanh toán
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $membership->status }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-500 text-xs">
                                            {{ $membership->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                            @if ($membership->status === 'ACTIVE')
                                                <form method="POST" action="{{ route('communities.members.suspend', [$community, $membership]) }}" class="inline-block"
                                                      onsubmit="return confirm('Bạn có chắc chắn muốn tạm khóa thành viên này?');">
                                                    @csrf
                                                    <button type="submit" class="text-amber-600 hover:text-amber-900 font-medium text-xs">
                                                        Tạm khóa
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('communities.members.remove', [$community, $membership]) }}" class="inline-block"
                                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa thành viên này khỏi cộng đồng?');">
                                                    @csrf
                                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-xs">
                                                        Xóa
                                                    </button>
                                                </form>
                                            @elseif ($membership->status === 'SUSPENDED')
                                                <form method="POST" action="{{ route('communities.members.reactivate', [$community, $membership]) }}" class="inline-block">
                                                    @csrf
                                                    <button type="submit" class="text-emerald-600 hover:text-emerald-900 font-medium text-xs">
                                                        Mở khóa
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('communities.members.remove', [$community, $membership]) }}" class="inline-block"
                                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa thành viên này khỏi cộng đồng?');">
                                                    @csrf
                                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-xs">
                                                        Xóa
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-gray-400 text-xs italic">Không khả dụng</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($memberships->hasPages())
                        <div class="p-6 border-t border-gray-100">
                            {{ $memberships->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
