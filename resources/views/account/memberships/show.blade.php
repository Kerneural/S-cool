<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 leading-tight">
            {{ __('Membership Access') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <aside class="min-w-0 lg:col-span-4">
                    <x-account-navigation active="memberships" />
                </aside>
                <div class="min-w-0 lg:col-span-8 space-y-6 break-words [overflow-wrap:anywhere]">
            <section class="space-y-4 rounded-2xl bg-white p-6 sm:p-8 border border-slate-200/80 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-5">
                    <div>
                        <h1 class="break-words text-2xl font-bold text-slate-900 tracking-tight">{{ $membership->community->name }}</h1>
                        <p class="text-xs text-slate-500 mt-1">{{ __('Community membership profile and entitlement details') }}</p>
                    </div>
                    <div>
                        <span role="status" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                            {{ $membership->hasEffectiveAccess() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                            {{ $membership->accessStateLabel() }}
                        </span>
                    </div>
                </div>

                <p class="text-sm text-slate-600 leading-relaxed font-medium">
                    {{ $membership->access_kind === 'LIFETIME' ? __('Lifetime access. Community pricing changes do not expire this membership.') : __('Fixed 30 UTC days per verified purchase. There is no grace period or automatic debit.') }}
                </p>

                @if ($membership->expires_at)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/70 text-xs font-mono text-slate-600">
                        <span class="font-bold text-slate-700">{{ __('Expires') }}:</span> {{ $membership->expires_at->format('Y-m-d H:i:s') }} UTC
                    </div>
                @endif

                @if ($membership->hasEffectiveAccess())
                    <div class="pt-2">
                        <a class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand hover:bg-brand-hover text-white font-bold text-xs rounded-xl shadow-xs transition"
                           href="{{ route('communities.show', $membership->community) }}">
                            <span>{{ __('Open Community') }}</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                @endif
            </section>

            @if ($membership->canRenew(auth()->user()))
                <section class="space-y-4 rounded-2xl border border-amber-300/80 bg-amber-50/90 p-6 sm:p-8 shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <h2 class="font-bold text-amber-950 text-base">{{ __('Manual 30-day Renewal') }}</h2>
                    </div>

                    <p class="text-xs text-amber-900 leading-relaxed">{{ __('Renewal adds 30 UTC days from the later of payment time and your current expiry.') }}</p>

                    <div class="p-3 bg-white/70 rounded-xl border border-amber-200 text-sm">
                        <span class="font-semibold text-slate-700">{{ __('Current renewal price') }}:</span>
                        <span class="font-bold text-teal-700 ml-1">{{ number_format($membership->community->price_vnd, 0, ',', '.') }} VND</span>
                    </div>

                    <p class="text-xs font-semibold text-amber-800">{{ __('SePay Test Mode only. Do not transfer real funds or use a real banking application.') }}</p>

                    <form method="POST" action="{{ route('account.memberships.renew', $membership) }}" class="pt-1">
                        @csrf
                        <button type="submit" class="min-h-[44px] inline-flex items-center justify-center rounded-xl bg-brand hover:bg-brand-hover px-6 py-2.5 font-bold text-xs uppercase tracking-wider text-white shadow-xs transition">
                            {{ __('Start Test Renewal') }}
                        </button>
                    </form>
                </section>
            @else
                <div class="rounded-2xl bg-white p-6 border border-slate-200/80 shadow-xs">
                    <p class="text-xs text-slate-500 leading-relaxed">{{ __('Renewal is unavailable for lifetime, pending or revoked memberships, or when the community is inactive or no longer offers paid access.') }}</p>
                </div>
            @endif

            <div class="flex flex-wrap items-center gap-4 text-xs font-bold pt-2">
                <a class="text-teal-600 hover:text-teal-800" href="{{ route('account.memberships.index') }}">&larr; {{ __('My Access') }}</a>
                <span class="text-slate-300">&bull;</span>
                <a class="text-teal-600 hover:text-teal-800" href="{{ route('account.payment-history.index') }}">{{ __('Payment History') }} &rarr;</a>
            </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
