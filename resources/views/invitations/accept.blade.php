<x-guest-layout>
    <div class="space-y-6">
        <div>
            <div class="w-12 h-12 rounded-2xl bg-brand-50 border border-brand-200/60 flex items-center justify-center text-brand-600 mb-4 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">{{ __('Private invitation') }}</h1>
            <p class="text-sm text-muted mt-2 leading-relaxed">
                {{ __('Sign in with the invited email and verify it. After signing in or verifying your email, open the original community invitation email again. The verification email is not the community invitation. Invalid, expired or revoked invitations cannot grant access.') }}
            </p>
        </div>

        @guest
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                <p class="text-xs text-muted font-medium">{{ __('An account is required to accept this community invitation.') }}</p>
                <div class="flex items-center gap-3">
                    <a class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-hover active:scale-[0.98] transition shadow-xs underline mr-4" href="{{ route('login') }}">{{ __('Sign in') }}</a>
                    <a class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-xl font-semibold text-xs text-slate-700 uppercase tracking-widest hover:bg-slate-50 active:scale-[0.98] transition shadow-xs underline" href="{{ route('register') }}">{{ __('Register') }}</a>
                </div>
            </div>
        @else
            @if (! auth()->user()->hasVerifiedEmail())
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-sm text-amber-800 space-y-2">
                    <p class="font-medium">{{ __('Your email address is not verified.') }}</p>
                    <a class="inline-flex items-center font-semibold text-brand-700 hover:text-brand-800 underline" href="{{ route('verification.notice') }}">{{ __('Verify your email') }}</a>
                </div>
            @else
                <form method="POST" action="{{ route('invitations.accept', $invitationId) }}"
                      x-data="invitationAcceptance" @submit="if (!token) $event.preventDefault()"
                      class="space-y-4">
                    @csrf
                    <input type="hidden" name="token" :value="token">
                    <x-input-error :messages="$errors->get('invitation')" class="mb-4" />

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <x-primary-button disabled x-bind:disabled="!token" class="disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ __('Accept invitation (Free)') }}
                        </x-primary-button>
                        <button type="submit" disabled formaction="{{ route('invitations.checkout', $invitationId) }}" x-bind:disabled="!token"
                                class="inline-flex items-center px-4 py-2.5 bg-brand-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-hover active:scale-[0.98] transition disabled:opacity-50 disabled:cursor-not-allowed shadow-xs">
                            {{ __('Paid Checkout (SePay)') }}
                        </button>
                    </div>

                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-sm text-amber-800" role="status" x-show="!token">
                        <p>{{ __('Invitation token missing or malformed. Both buttons are disabled. Reopen the original community invitation link from your email after signing in or verifying your email. Reloading this page removes the token; reopen the email link instead of typing the invitation URL.') }}</p>
                    </div>

                    <p class="mt-4 text-xs text-muted" x-show="token" x-cloak>
                        {{ __('Choose Accept invitation for a free community, or Paid Checkout for paid access in SePay Test Mode. No real bank transfer is required.') }}
                    </p>

                    <noscript>
                        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-sm text-amber-800">
                            <p>{{ __('JavaScript is required to securely read the invitation link. Enable it, then reopen the original community invitation email.') }}</p>
                        </div>
                    </noscript>
                </form>
            @endif
        @endguest
    </div>
</x-guest-layout>
