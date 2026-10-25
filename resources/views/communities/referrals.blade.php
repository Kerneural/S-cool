<x-app-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="referrals" />

            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold tracking-tight text-ink">{{ __('Referral Program & Simulated Ledger') }}</h1>
            </div>

            @if (session('status'))
                <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm shadow-xs">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm shadow-xs">
                    <x-input-error :messages="$errors->all()" />
                </div>
            @endif

            <!-- Disclaimer Banner -->
            <div class="p-4 bg-amber-50/90 border border-amber-200/80 rounded-2xl text-xs text-amber-900 flex items-start gap-3 shadow-xs">
                <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="space-y-0.5">
                    <p class="font-bold text-sm text-amber-950">Simulated Referral Ledger (SePay Test Mode)</p>
                    <p class="text-amber-800/90">All affiliate referral commissions are simulated for testing purposes only. No real bank transfers or payouts are supported in this environment.</p>
                </div>
            </div>

            <!-- Metric Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="p-6 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Referral Commission Rate</span>
                    <p class="mt-2 text-3xl font-bold text-slate-900 tracking-tight">{{ (int) $community->referral_commission_rate }}%</p>
                    <p class="mt-1 text-xs text-slate-500 font-medium">
                        {{ $community->referral_commission_rate > 0 ? 'Active on admission and course sales' : 'Disabled (0%)' }}
                    </p>
                </div>

                <div class="p-6 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Attributed Referrals</span>
                    <p class="mt-2 text-3xl font-bold text-slate-900 tracking-tight">{{ $totalAttributedCount }}</p>
                    <p class="mt-1 text-xs text-slate-500 font-medium">Total eligible transactions credited</p>
                </div>

                <div class="p-6 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Simulated Commissions</span>
                    <p class="mt-2 text-3xl font-bold text-teal-600 tracking-tight">{{ number_format($totalSimulatedCommissions) }} VND</p>
                    <p class="mt-1 text-xs text-slate-500 font-medium">Simulated / Unpaid balance</p>
                </div>
            </div>

            <!-- Pending Referral Invitation Requests -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">Pending Referral Requests</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Member-submitted referral requests awaiting creator approval before invitation delivery.</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                        {{ $pendingRequests->count() }} pending
                    </span>
                </div>

                @if ($pendingRequests->isEmpty())
                    <div class="p-12 text-center text-slate-400 text-sm">
                        No pending referral requests awaiting review.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-slate-50/75 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    <th class="py-3.5 px-5">Referring Member</th>
                                    <th class="py-3.5 px-5">Candidate Email</th>
                                    <th class="py-3.5 px-5">Requested (UTC)</th>
                                    <th class="py-3.5 px-5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($pendingRequests as $req)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="py-3.5 px-5">
                                            <p class="font-bold text-slate-900">{{ $req->referrer->name ?? 'Unknown' }}</p>
                                            <p class="text-xs text-slate-400">{{ $req->referrer->email ?? '' }}</p>
                                        </td>
                                        <td class="py-3.5 px-5 font-mono text-xs text-slate-800 font-bold">
                                            {{ $req->email }}
                                        </td>
                                        <td class="py-3.5 px-5 text-xs text-slate-500 font-medium">
                                            {{ $req->created_at->format('Y-m-d H:i') }}
                                        </td>
                                        <td class="py-3.5 px-5 text-right space-x-2">
                                            <form method="POST" action="{{ route('communities.referrals.approve', [$community, $req]) }}" class="inline-block">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-xs transition duration-150">
                                                    Approve & Invite
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('communities.referrals.reject', [$community, $req]) }}" class="inline-block">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition duration-150">
                                                    Reject
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Attributed Referral Commission Ledger -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">Attributed Referral Commissions</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Permanent financial records for verified payments within the 30-day invitation window.</p>
                </div>

                @if ($commissions->isEmpty())
                    <div class="p-12 text-center text-slate-400 text-sm">
                        No referral commissions attributed yet.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-slate-50/75 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    <th class="py-3.5 px-5">Referrer</th>
                                    <th class="py-3.5 px-5">Payer</th>
                                    <th class="py-3.5 px-5">Purpose</th>
                                    <th class="py-3.5 px-5 text-right">Payment</th>
                                    <th class="py-3.5 px-5 text-right">Rate</th>
                                    <th class="py-3.5 px-5 text-right">Commission</th>
                                    <th class="py-3.5 px-5 text-center">Status</th>
                                    <th class="py-3.5 px-5 text-right">Date (UTC)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($commissions as $comm)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="py-3.5 px-5">
                                            <p class="font-bold text-slate-900">{{ $comm->referrer->name ?? 'User #'.$comm->referrer_id }}</p>
                                        </td>
                                        <td class="py-3.5 px-5 text-xs text-slate-600 font-medium">
                                            {{ $comm->payer->name ?? 'User #'.$comm->payer_id }}
                                        </td>
                                        <td class="py-3.5 px-5 text-xs font-mono text-slate-700">
                                            {{ $comm->purpose }}
                                        </td>
                                        <td class="py-3.5 px-5 text-right font-medium text-slate-900">
                                            {{ number_format($comm->payment_amount) }} {{ $comm->currency }}
                                        </td>
                                        <td class="py-3.5 px-5 text-right text-xs text-slate-600 font-semibold">
                                            {{ $comm->commission_rate }}%
                                        </td>
                                        <td class="py-3.5 px-5 text-right font-bold text-teal-600">
                                            {{ number_format($comm->commission_amount) }} {{ $comm->currency }}
                                        </td>
                                        <td class="py-3.5 px-5 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                {{ $comm->status }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-5 text-right text-xs text-slate-500 font-mono">
                                            {{ $comm->created_at->format('Y-m-d H:i') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $commissions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
