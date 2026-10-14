<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('My Access') }}</h2></x-slot>
    <div class="mx-auto max-w-4xl space-y-4 px-4 py-8 sm:px-6">
        <p class="text-sm text-gray-600">{{ __('View your own membership terms and renew eligible 30-day access. Retained purchase records do not override community restrictions.') }}</p>
        @forelse ($memberships as $membership)
            <article class="rounded-lg border bg-white p-5">
                <h3 class="font-semibold">{{ $membership->community->name }}</h3>
                <p class="text-sm text-gray-600">{{ $membership->accessStateLabel() }} — {{ $membership->access_kind === 'LIFETIME' ? __('Lifetime access') : __('Fixed 30-day term') }}</p>
                @if ($membership->expires_at)<p class="text-sm">{{ __('Expires') }}: {{ $membership->expires_at->format('Y-m-d H:i:s') }} UTC</p>@endif
                <a class="mt-2 inline-block text-sm text-indigo-700 underline" href="{{ route('account.memberships.show', $membership) }}">{{ __('Access details and renewal') }}</a>
            </article>
        @empty
            <p class="rounded-lg bg-white p-5 text-gray-600">{{ __('No memberships yet. Join through an invitation from a creator.') }}</p>
        @endforelse
        {{ $memberships->links() }}
    </div>
</x-app-layout>
