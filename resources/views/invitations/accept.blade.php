<x-guest-layout>
    <h1 class="text-xl font-semibold mb-4">Private invitation</h1>
    <p class="text-sm text-gray-600 mb-4">Sign in with the invited email and verify it. After signing in or verifying your email, open the original community invitation email again. The verification email is not the community invitation. Invalid, expired or revoked invitations cannot grant access.</p>
    @guest
        <a class="underline mr-4" href="{{ route('login') }}">Sign in</a>
        <a class="underline" href="{{ route('register') }}">Register</a>
    @else
        @if (! auth()->user()->hasVerifiedEmail())
            <a class="underline" href="{{ route('verification.notice') }}">Verify your email</a>
        @else
            <form method="POST" action="{{ route('invitations.accept', $invitationId) }}"
                  x-data="invitationAcceptance" @submit="if (!token) $event.preventDefault()">
                @csrf
                <input type="hidden" name="token" :value="token">
                <x-input-error :messages="$errors->get('invitation')" class="mb-4" />
                <div class="flex items-center space-x-3">
                    <x-primary-button disabled x-bind:disabled="!token" class="disabled:opacity-50 disabled:cursor-not-allowed">Accept invitation (Free)</x-primary-button>
                    <button type="submit" disabled formaction="{{ route('invitations.checkout', $invitationId) }}" x-bind:disabled="!token"
                            class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        Paid Checkout (SePay)
                    </button>
                </div>
                <p class="mt-4 text-sm text-amber-800" role="status" x-show="!token">Invitation token missing or malformed. Both buttons are disabled. Reopen the original community invitation link from your email after signing in or verifying your email. Reloading this page removes the token; reopen the email link instead of typing the invitation URL.</p>
                <p class="mt-4 text-xs text-gray-500" x-show="token" x-cloak>Choose Accept invitation for a free community, or Paid Checkout for paid access in SePay Test Mode. No real bank transfer is required.</p>
                <noscript><p class="mt-4 text-sm text-amber-800">JavaScript is required to securely read the invitation link. Enable it, then reopen the original community invitation email.</p></noscript>
            </form>
        @endif
    @endguest
</x-guest-layout>
