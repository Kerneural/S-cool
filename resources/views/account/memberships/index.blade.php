<x-app-layout>
    <x-slot name="header">
        <h1 class="font-bold text-xl text-slate-900 leading-tight">
            {{ __('My Access') }}
        </h1>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="border-b border-slate-200/80 pb-4">
                <p class="text-sm text-slate-600">{{ __('View your own membership terms and renew eligible 30-day access. Retained purchase records do not override community restrictions.') }}</p>
            </div>

            <div class="space-y-4">
                @forelse ($memberships as $membership)
                    <article class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:border-slate-300 transition duration-150">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <h3 class="text-lg font-bold text-slate-900 tracking-tight">{{ $membership->community->name }}</h3>
                                <p class="text-sm text-slate-600 font-medium">
                                    {{ $membership->accessStateLabel() }} — {{ $membership->access_kind === 'LIFETIME' ? __('Lifetime access') : __('Fixed 30-day term') }}
                                </p>
                                @if ($membership->expires_at)
                                    <p class="text-xs text-slate-500 font-mono">
                                        {{ __('Expires') }}: {{ $membership->expires_at->format('Y-m-d H:i:s') }} UTC
                                    </p>
                                @endif
                            </div>

                            <div>
                                <a class="inline-flex items-center px-4 py-2 bg-teal-50 hover:bg-teal-100 text-teal-700 font-bold text-xs rounded-xl border border-teal-200/80 transition"
                                   href="{{ route('account.memberships.show', $membership) }}">
                                    {{ __('Access details and renewal') }} &rarr;
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="bg-white rounded-2xl p-16 text-center border border-slate-200/80 shadow-xs">
                        <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <p class="text-slate-600 font-medium">{{ __('No memberships yet. Join through an invitation from a creator.') }}</p>
                    </div>
                @endforelse
            </div>

            @if ($memberships->hasPages())
                <div class="pt-4">
                    {{ $memberships->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
