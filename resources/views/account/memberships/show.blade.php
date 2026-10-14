<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('Membership Access') }}</h2></x-slot>
    <div class="mx-auto max-w-3xl space-y-5 px-4 py-8 sm:px-6">
        <section class="space-y-3 rounded-lg bg-white p-6 shadow-sm">
            <h1 class="break-words text-2xl font-semibold">{{ $membership->community->name }}</h1>
            <p role="status">{{ $membership->accessStateLabel() }}</p>
            <p>{{ $membership->access_kind === 'LIFETIME' ? __('Lifetime access. Community pricing changes do not expire this membership.') : __('Fixed 30 UTC days per verified purchase. There is no grace period or automatic debit.') }}</p>
            @if ($membership->expires_at)<p>{{ __('Expires') }}: {{ $membership->expires_at->format('Y-m-d H:i:s') }} UTC</p>@endif
            @if ($membership->hasEffectiveAccess())
                <a class="text-indigo-700 underline" href="{{ route('communities.show', $membership->community) }}">{{ __('Open Community') }}</a>
            @endif
        </section>
        @if ($membership->canRenew(auth()->user()))
            <section class="space-y-3 rounded-lg border border-amber-200 bg-amber-50 p-6">
                <h2 class="font-semibold">{{ __('Manual 30-day Renewal') }}</h2>
                <p>{{ __('Renewal adds 30 UTC days from the later of payment time and your current expiry.') }}</p>
                <p>{{ __('Current renewal price') }}: {{ number_format($membership->community->price_vnd, 0, ',', '.') }} VND</p>
                <p class="font-medium">{{ __('SePay Test Mode only. Do not transfer real funds or use a real banking application.') }}</p>
                <form method="POST" action="{{ route('account.memberships.renew', $membership) }}">
                    @csrf
                    <button type="submit" class="min-h-[44px] rounded bg-indigo-600 px-4 py-2 font-medium text-white">{{ __('Start Test Renewal') }}</button>
                </form>
            </section>
        @else
            <p class="rounded-lg bg-white p-5 text-sm text-gray-600">{{ __('Renewal is unavailable for lifetime, pending or revoked memberships, or when the community is inactive or no longer offers paid access.') }}</p>
        @endif
        <div class="flex flex-wrap gap-4 text-sm">
            <a class="text-indigo-700 underline" href="{{ route('account.memberships.index') }}">{{ __('My Access') }}</a>
            <a class="text-indigo-700 underline" href="{{ route('account.payment-history.index') }}">{{ __('Payment History') }}</a>
        </div>
    </div>
</x-app-layout>
