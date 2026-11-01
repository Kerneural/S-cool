<x-app-layout>
    <div class="py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="invitations" />

            @if (session('status'))
                <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm shadow-xs">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <!-- Send Invitation Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-slate-900 tracking-tight">{{ __('Invite by email') }}</h1>
                        <p class="text-xs text-slate-500">{{ __('Send an exclusive onboarding invitation to join :community.', ['community' => $community->name]) }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('communities.invitations.store', $community) }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="email" :value="__('Invite by email')" class="sr-only" />
                        <div class="flex flex-col sm:flex-row gap-3">
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                    </svg>
                                </div>
                                <x-text-input id="email" name="email" type="email" :value="old('email')" required
                                    class="w-full pl-10 rounded-xl border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500 py-2.5"
                                    placeholder="colleague@example.com" />
                            </div>
                            <x-primary-button class="sm:w-auto justify-center px-6 py-2.5 rounded-xl font-bold text-xs tracking-wide">
                                {{ __('Send invitation') }}
                            </x-primary-button>
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ __('Valid for seven days. Only the invited, verified email can accept.') }}</span>
                    </div>
                </form>
            </div>

            <!-- Invitations History -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h4 class="text-base font-bold text-slate-900 tracking-tight">{{ __('Sent Invitations') }}</h4>
                        <p class="text-xs text-slate-500">{{ __('Track delivery status and manage active invitations.') }}</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                        {{ $invitations->total() ?? $invitations->count() }} {{ __('total') }}
                    </span>
                </div>

                @forelse ($invitations as $invitation)
                    @php
                        $displayStatus = $invitation->status === 'PENDING' && ! $invitation->isPending() ? 'EXPIRED' : $invitation->status;
                    @endphp
                    <div class="p-5 border-b last:border-b-0 border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/50 transition duration-150">
                        <div class="flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-sm shrink-0">
                                {{ strtoupper(substr($invitation->normalized_email, 0, 1)) }}
                            </div>
                            <div class="space-y-1">
                                <p class="font-bold text-slate-900 text-sm font-mono">{{ $invitation->normalized_email }}</p>
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="inline-flex items-center gap-1 font-semibold
                                        {{ $displayStatus === 'PENDING' ? 'text-amber-700' : ($displayStatus === 'ACCEPTED' ? 'text-emerald-700' : 'text-slate-600') }}">
                                        {{ __($displayStatus) }}
                                    </span>
                                    <span class="text-slate-300">&bull;</span>
                                    <span class="inline-flex items-center gap-1 text-slate-500">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $invitation->sent_at ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $invitation->sent_at ? __('Email delivered') : __('Not delivered') }}
                                    </span>
                                    <span class="text-slate-300">&bull;</span>
                                    <span class="text-slate-400">
                                        {{ $invitation->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if ($invitation->isPending())
                            <div class="sm:self-center">
                                <form method="POST" action="{{ route('communities.invitations.revoke', [$community, $invitation]) }}"
                                      onsubmit="return confirm('{{ __('Are you sure you want to revoke this invitation?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <x-secondary-button type="submit" class="text-xs px-3 py-1.5 font-bold text-rose-600 hover:text-rose-700 hover:bg-rose-50 border-rose-200">
                                        {{ __('Revoke') }}
                                    </x-secondary-button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-12 text-center text-slate-400 text-sm">
                        <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <p class="font-medium text-slate-500">{{ __('No invitations yet.') }}</p>
                    </div>
                @endforelse

                @if ($invitations->hasPages())
                    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $invitations->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
