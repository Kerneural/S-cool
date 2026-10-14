<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Referrals & Simulated Earnings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Test Mode Notice -->
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 flex items-start space-x-2">
                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="font-semibold text-sm">Simulated Earnings Notice (SePay Test Mode)</p>
                    <p>All affiliate referral earnings are simulated balances for testing purposes only. No actual bank transfers, withdrawals, or payouts are supported.</p>
                </div>
            </div>

            <!-- Total Earnings Summary Card -->
            <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Simulated Referral Balance</p>
                    <p class="mt-2 text-3xl font-bold text-indigo-600">{{ number_format($totalSimulatedEarnings) }} VND</p>
                    <p class="mt-1 text-xs text-gray-400">All historical commissions are retained permanently.</p>
                </div>
                <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <!-- Request a Referral Invitation -->
            @php
                $user = auth()->user();
                $accessibleCommunities = \App\Models\Community::query()
                    ->accessibleTo($user)
                    ->where('referral_commission_rate', '>', 0)
                    ->get();
            @endphp

            @if ($accessibleCommunities->isNotEmpty())
                <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-100 space-y-4">
                    <h3 class="text-base font-semibold text-gray-900">Request Referral Invitation</h3>
                    <p class="text-xs text-gray-500">
                        Communities in S-cool are private. When you request a referral invitation for a contact, the community creator reviews and approves it before sending the invitation email. If they join or buy within 30 days, your commission is credited.
                    </p>

                    <form method="POST" x-data="{ communitySlug: '{{ $accessibleCommunities->first()->slug }}' }" :action="'/communities/' + communitySlug + '/referrals/request'" class="space-y-4 max-w-xl">
                        @csrf
                        <div>
                            <x-input-label for="community_select" value="Select Community" />
                            <select id="community_select" x-model="communitySlug" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                @foreach ($accessibleCommunities as $c)
                                    <option value="{{ $c->slug }}">
                                        {{ $c->name }} (Commission Rate: {{ $c->referral_commission_rate }}%)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="email" value="Friend's Email Address" />
                            <x-text-input id="email" name="email" type="email" required class="mt-1 block w-full" placeholder="friend@example.com" />
                            <x-input-error :messages="$errors->get('email')" />
                        </div>

                        <x-primary-button type="submit">
                            Submit Referral Request
                        </x-primary-button>
                    </form>
                </div>
            @endif

            <!-- Pending Requests -->
            @if ($pendingRequests->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-gray-900">Your Referral Requests</h3>
                        <p class="text-xs text-gray-500">Track the status of your submitted invitations awaiting creator approval.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="py-3 px-4">Community</th>
                                    <th class="py-3 px-4">Candidate Email</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-right">Requested (UTC)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($pendingRequests as $req)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="py-3 px-4 font-medium text-gray-900">
                                            {{ $req->community->name ?? 'Community' }}
                                        </td>
                                        <td class="py-3 px-4 font-mono text-xs text-gray-800">
                                            {{ $req->email }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $req->status === 'APPROVED' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($req->status === 'REJECTED' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                                                {{ $req->status }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right text-xs text-gray-500">
                                            {{ $req->created_at->format('Y-m-d H:i') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Commission History -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-900">Attributed Commission Ledger</h3>
                    <p class="text-xs text-gray-500">Retained records of commissions earned from your referred contacts.</p>
                </div>

                @if ($commissions->isEmpty())
                    <div class="p-12 text-center text-gray-400 text-sm">
                        No referral commissions earned yet.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="py-3 px-4">Community</th>
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
                                        <td class="py-3 px-4 font-medium text-gray-900">
                                            {{ $comm->community->name ?? 'Community' }}
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
    </div>
</x-app-layout>
