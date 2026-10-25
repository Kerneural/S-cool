<x-app-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="members" />

            @if (session('status'))
                <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm shadow-xs">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->has('membership'))
                <div class="flex items-center gap-3 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm shadow-xs">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $errors->first('membership') }}</span>
                </div>
            @endif

            @if ($errors->has('reason'))
                <div class="flex items-center gap-3 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm shadow-xs">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $errors->first('reason') }}</span>
                </div>
            @endif

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Tổng thành viên</span>
                        <p class="text-2xl font-bold text-slate-900 tracking-tight">{{ $counts['total'] }}</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs text-emerald-600 uppercase tracking-wider font-semibold">Đang hoạt động</span>
                        <p class="text-2xl font-bold text-emerald-700 tracking-tight">{{ $counts['active'] }}</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs text-amber-600 uppercase tracking-wider font-semibold">Đang tạm khóa</span>
                        <p class="text-2xl font-bold text-amber-700 tracking-tight">{{ $counts['suspended'] }}</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs text-rose-600 uppercase tracking-wider font-semibold">Đã xóa</span>
                        <p class="text-2xl font-bold text-rose-700 tracking-tight">{{ $counts['removed'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Members List Table -->
            <div class="bg-white shadow-xs rounded-2xl overflow-hidden border border-slate-200/80">
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-lg font-bold text-slate-900 tracking-tight">Danh sách thành viên</h1>
                        <p class="text-sm text-slate-500 mt-0.5">Xem và quản lý quyền truy cập của các thành viên trong cộng đồng.</p>
                    </div>
                    <a href="{{ route('communities.invitations.index', $community) }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-xl font-semibold text-xs tracking-wide shadow-sm shadow-teal-500/20 transition duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>{{ __('Mời thành viên mới') }}</span>
                    </a>
                </div>

                @if ($memberships->isEmpty())
                    <div class="p-16 text-center">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <p class="text-slate-600 font-medium">Chưa có thành viên nào tham gia cộng đồng này.</p>
                        <p class="text-slate-400 text-xs mt-1">Gửi lời mời để bắt đầu xây dựng cộng đồng của bạn.</p>
                    </div>
                @else
                    <div class="relative overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead class="bg-slate-50/75">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Thành viên</th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Trạng thái</th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Ngày tham gia</th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Thay đổi gần nhất</th>
                                    <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                @foreach ($memberships as $membership)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                                                    {{ strtoupper(substr($membership->user->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-900">{{ $membership->user->name }}</div>
                                                    <div class="text-slate-400 text-xs">{{ $membership->user->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($membership->status === 'ACTIVE')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Hoạt động
                                                </span>
                                            @elseif ($membership->status === 'SUSPENDED')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    Tạm khóa
                                                </span>
                                            @elseif ($membership->status === 'REMOVED')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200/80">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                    Đã xóa
                                                </span>
                                            @elseif ($membership->status === 'PENDING_PAYMENT')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200/80">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                                    Chờ thanh toán
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200/80">
                                                    {{ $membership->status }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-slate-500 text-xs font-medium">
                                            {{ $membership->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 text-slate-600 text-xs max-w-xs">
                                            @if ($membership->latestAction)
                                                <div class="space-y-0.5">
                                                    <p class="font-bold text-slate-800">{{ $membership->latestAction->action }}</p>
                                                    <p class="text-slate-600 break-words">{{ $membership->latestAction->reason }}</p>
                                                    <p class="text-[11px] text-slate-400">
                                                        {{ $membership->latestAction->actor->name }} · {{ $membership->latestAction->created_at->format('d/m/Y H:i') }}
                                                    </p>
                                                </div>
                                            @else
                                                <span class="text-slate-400 italic">Chưa có thay đổi</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            @if ($membership->status === 'ACTIVE')
                                                <div class="space-y-2">
                                                    <form method="POST" action="{{ route('communities.members.suspend', [$community, $membership]) }}" class="flex items-center justify-end gap-2"
                                                          onsubmit="return confirm('Bạn có chắc chắn muốn tạm khóa thành viên này?');">
                                                        @csrf
                                                        <label for="suspend-reason-{{ $membership->id }}" class="sr-only">Lý do tạm khóa</label>
                                                        <input id="suspend-reason-{{ $membership->id }}" name="reason" type="text" required minlength="3" maxlength="500"
                                                               class="w-44 rounded-lg border-slate-200 text-xs shadow-2xs focus:border-amber-500 focus:ring-amber-500 py-1.5 px-2.5"
                                                               placeholder="Lý do tạm khóa">
                                                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200/80 rounded-lg font-bold text-xs transition duration-150 whitespace-nowrap">
                                                            Tạm khóa
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('communities.members.remove', [$community, $membership]) }}" class="flex items-center justify-end gap-2"
                                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa thành viên này khỏi cộng đồng?');">
                                                        @csrf
                                                        <label for="remove-reason-{{ $membership->id }}" class="sr-only">Lý do xóa</label>
                                                        <input id="remove-reason-{{ $membership->id }}" name="reason" type="text" required minlength="3" maxlength="500"
                                                               class="w-44 rounded-lg border-slate-200 text-xs shadow-2xs focus:border-rose-500 focus:ring-rose-500 py-1.5 px-2.5"
                                                               placeholder="Lý do xóa">
                                                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200/80 rounded-lg font-bold text-xs transition duration-150 whitespace-nowrap">
                                                            Xóa
                                                        </button>
                                                    </form>
                                                </div>
                                            @elseif ($membership->status === 'SUSPENDED')
                                                <div class="space-y-2">
                                                    <form method="POST" action="{{ route('communities.members.reactivate', [$community, $membership]) }}" class="flex items-center justify-end gap-2">
                                                        @csrf
                                                        <label for="reactivate-reason-{{ $membership->id }}" class="sr-only">Lý do mở khóa</label>
                                                        <input id="reactivate-reason-{{ $membership->id }}" name="reason" type="text" required minlength="3" maxlength="500"
                                                               class="w-44 rounded-lg border-slate-200 text-xs shadow-2xs focus:border-emerald-500 focus:ring-emerald-500 py-1.5 px-2.5"
                                                               placeholder="Lý do mở khóa">
                                                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200/80 rounded-lg font-bold text-xs transition duration-150 whitespace-nowrap">
                                                            Mở khóa
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('communities.members.remove', [$community, $membership]) }}" class="flex items-center justify-end gap-2"
                                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa thành viên này khỏi cộng đồng?');">
                                                        @csrf
                                                        <label for="remove-reason-{{ $membership->id }}" class="sr-only">Lý do xóa</label>
                                                        <input id="remove-reason-{{ $membership->id }}" name="reason" type="text" required minlength="3" maxlength="500"
                                                               class="w-44 rounded-lg border-slate-200 text-xs shadow-2xs focus:border-rose-500 focus:ring-rose-500 py-1.5 px-2.5"
                                                               placeholder="Lý do xóa">
                                                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200/80 rounded-lg font-bold text-xs transition duration-150 whitespace-nowrap">
                                                            Xóa
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-slate-400 text-xs italic">Không khả dụng</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($memberships->hasPages())
                        <div class="p-5 border-t border-slate-100 bg-slate-50/50">
                            {{ $memberships->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
