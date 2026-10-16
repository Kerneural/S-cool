<x-app-layout>
    <x-slot name="header">
        <h1 class="font-bold text-xl text-slate-900 leading-tight">
            {{ __('My Referrals & Simulated Earnings') }}
        </h1>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm shadow-xs">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <!-- Test Mode Notice -->
            <div class="p-4 bg-amber-50/90 border border-amber-200/80 rounded-2xl text-xs text-amber-900 flex items-start gap-3 shadow-xs">
                <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="space-y-0.5">
                    <p class="font-bold text-sm text-amber-950">Simulated Earnings Notice (SePay Test Mode)</p>
                    <p class="text-amber-800/90 leading-relaxed">All affiliate referral earnings are simulated balances for testing purposes only. No actual bank transfers, withdrawals, or payouts are supported.</p>
                </div>
            </div>

            <!-- Total Earnings Summary Card -->
            <div class="p-6 bg-white rounded-2xl shadow-xs border border-slate-200/80 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Simulated Referral Balance</span>
                    <p class="mt-2 text-3xl font-black text-teal-600 tracking-tight">{{ number_format($totalSimulatedEarnings) }} VND</p>
                    <p class="mt-1 text-xs text-slate-400 font-medium">All historical commissions are retained permanently.</p>
                </div>
                <div class="w-14 h-14 bg-teal-50 rounded-2xl text-teal-600 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <div class="p-6 sm:p-8 bg-white rounded-2xl shadow-xs border border-slate-200/80 space-y-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">Request Referral Invitation</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Communities in S-cool are private. When you request a referral invitation for a contact, the community creator reviews and approves it before sending the invitation email. If they join or buy within 30 days, your commission is credited.
                        </p>
                    </div>

                    <form method="POST" x-data="{ communitySlug: '{{ $accessibleCommunities->first()->slug }}' }" :action="'/communities/' + communitySlug + '/referrals/request'" class="space-y-4 max-w-xl pt-2">
                        @csrf
                        <div>
                            <x-input-label for="community_select" value="Select Community" />
                            <select id="community_select" x-model="communitySlug" class="mt-1.5 block w-full rounded-xl border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500 py-2.5">
                                @foreach ($accessibleCommunities as $c)
                                    <option value="{{ $c->slug }}">
                                        {{ $c->name }} (Commission Rate: {{ $c->referral_commission_rate }}%)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="email" value="Friend's Email Address" />
                            <x-text-input id="email" name="email" type="email" required class="mt-1.5 block w-full rounded-xl border-slate-200 text-sm py-2.5" placeholder="friend@example.com" />
                            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                        </div>

                        <x-primary-button type="submit" class="px-6 py-2.5 rounded-xl font-bold text-xs tracking-wider">
                            Submit Referral Request
                        </x-primary-button>
                    </form>
                </div>
            @endif

            <!-- Pending Requests -->
            @if ($pendingRequests->isNotEmpty())
                <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
                    <div class="p-6 border-b border-slate-100">
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">Your Referral Requests</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Track the status of your submitted invitations awaiting creator approval.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-slate-50/75 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    <th class="py-3.5 px-5">Community</th>
                                    <th class="py-3.5 px-5">Candidate Email</th>
                                    <th class="py-3.5 px-5">Status</th>
                                    <th class="py-3.5 px-5 text-right">Requested (UTC)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($pendingRequests as $req)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="py-3.5 px-5 font-bold text-slate-900">
                                            {{ $req->community->name ?? 'Community' }}
                                        </td>
                                        <td class="py-3.5 px-5 font-mono text-xs font-bold text-slate-800">
                                            {{ $req->email }}
                                        </td>
                                        <td class="py-3.5 px-5">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $req->status === 'APPROVED' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($req->status === 'REJECTED' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                                                {{ $req->status }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-5 text-right text-xs text-slate-500 font-mono">
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
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">Attributed Commission Ledger</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Retained records of commissions earned from your referred contacts.</p>
                </div>

                @if ($commissions->isEmpty())
                    <div class="p-16 text-center text-slate-400 text-sm">
                        <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="font-medium text-slate-500">No referral commissions earned yet.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-slate-50/75 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    <th class="py-3.5 px-5">Community</th>
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
                                        <td class="py-3.5 px-5 font-bold text-slate-900">
                                            {{ $comm->community->name ?? 'Community' }}
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
                                        <td class="py-3.5 px-5 text-right font-black text-teal-600">
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
