<x-app-layout>
    <x-slot name="header">
        <h1 class="font-bold text-xl text-slate-900 leading-tight">
            {{ __('My Access') }}
        </h1>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Sidebar Nav (4 cols on lg) -->
                <div class="min-w-0 lg:col-span-4 space-y-4">
                    <x-account-navigation active="memberships" />
                </div>

                <!-- Main Content (8 cols on lg) -->
                <div class="min-w-0 lg:col-span-8 space-y-4 break-words [overflow-wrap:anywhere]">
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs mb-4">
                        <h2 class="text-lg font-bold text-slate-900 tracking-tight">{{ __('Your Community Memberships') }}</h2>
                        <p class="text-xs text-slate-500 mt-1">{{ __('View your own membership terms and renew eligible 30-day access. Retained purchase records do not override community restrictions.') }}</p>
                    </div>

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

                    @if ($memberships->hasPages())
                        <div class="pt-4">
                            {{ $memberships->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
