<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        <x-community-workspace :community="$community" active-tab="referrals" />

        @if (session('status'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="rounded-lg border border-red-200 bg-red-50 p-4">
                <x-input-error :messages="$errors->all()" />
            </div>
        @endif

        <!-- Disclaimer Banner -->
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 flex items-start space-x-2">
            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <p class="font-semibold text-sm">Simulated Referral Ledger (SePay Test Mode)</p>
                <p>All affiliate referral commissions are simulated for testing purposes only. No real bank transfers or payouts are supported in this environment.</p>
            </div>
        </div>

        <!-- Metric Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-200">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Referral Commission Rate</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ (int) $community->referral_commission_rate }}%</p>
                <p class="mt-1 text-xs text-gray-500">
                    {{ $community->referral_commission_rate > 0 ? 'Active on admission and course sales' : 'Disabled (0%)' }}
                </p>
            </div>

            <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-200">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Attributed Referrals</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalAttributedCount }}</p>
                <p class="mt-1 text-xs text-gray-500">Total eligible transactions credited</p>
            </div>

            <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-200">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Simulated Commissions</p>
                <p class="mt-2 text-3xl font-bold text-indigo-600">{{ number_format($totalSimulatedCommissions) }} VND</p>
                <p class="mt-1 text-xs text-gray-500">Simulated / Unpaid balance</p>
            </div>
        </div>

        <!-- Pending Referral Invitation Requests -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Pending Referral Requests</h3>
                    <p class="text-xs text-gray-500">Member-submitted referral requests awaiting creator approval before invitation delivery.</p>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                    {{ $pendingRequests->count() }} pending
                </span>
            </div>

            @if ($pendingRequests->isEmpty())
                <div class="p-8 text-center text-gray-400 text-sm">
                    No pending referral requests awaiting review.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="py-3 px-4">Referring Member</th>
                                <th class="py-3 px-4">Candidate Email</th>
                                <th class="py-3 px-4">Requested (UTC)</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($pendingRequests as $req)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="py-3 px-4">
                                        <p class="font-medium text-gray-900">{{ $req->referrer->name ?? 'Unknown' }}</p>
                                        <p class="text-xs text-gray-500">{{ $req->referrer->email ?? '' }}</p>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-xs text-gray-800">
                                        {{ $req->email }}
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-500">
                                        {{ $req->created_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="py-3 px-4 text-right space-x-2">
                                        <form method="POST" action="{{ route('communities.referrals.approve', [$community, $req]) }}" class="inline-block">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-medium transition">
                                                Approve & Invite
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('communities.referrals.reject', [$community, $req]) }}" class="inline-block">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-xs font-medium transition">
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
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-900">Attributed Referral Commissions</h3>
                <p class="text-xs text-gray-500">Permanent financial records for verified payments within the 30-day invitation window.</p>
            </div>

            @if ($commissions->isEmpty())
                <div class="p-8 text-center text-gray-400 text-sm">
                    No referral commissions attributed yet.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="py-3 px-4">Referrer</th>
                                <th class="py-3 px-4">Payer</th>
                                <th class="py-3 px-4">Purpose</th>
                                <th class="py-3 px-4 text-right">Payment</th>
                                <th class="py-3 px-4 text-right">Rate</th>
                                <th class="py-3 px-4 text-right">Commission</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Date (UTC)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($commissions as $comm)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="py-3 px-4">
                                        <p class="font-medium text-gray-900">{{ $comm->referrer->name ?? 'User #'.$comm->referrer_id }}</p>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-600">
                                        {{ $comm->payer->name ?? 'User #'.$comm->payer_id }}
                                    </td>
                                    <td class="py-3 px-4 text-xs font-mono text-gray-700">
                                        {{ $comm->purpose }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-medium text-gray-900">
                                        {{ number_format($comm->payment_amount) }} {{ $comm->currency }}
                                    </td>
                                    <td class="py-3 px-4 text-right text-xs text-gray-600">
                                        {{ $comm->commission_rate }}%
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-indigo-600">
                                        {{ number_format($comm->commission_amount) }} {{ $comm->currency }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            {{ $comm->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right text-xs text-gray-500">
                                        {{ $comm->created_at->format('Y-m-d H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    {{ $commissions->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
