<x-guest-layout>
    <h1 class="text-xl font-semibold mb-4">Private invitation</h1>
    <p class="text-sm text-gray-600 mb-4">Sign in with the invited email, verify it, then reopen the invitation from your email. Invalid, expired or revoked invitations cannot grant access.</p>
    @guest
        <a class="underline mr-4" href="{{ route('login') }}">Sign in</a>
        <a class="underline" href="{{ route('register') }}">Register</a>
    @else
        @if (! auth()->user()->hasVerifiedEmail())
            <a class="underline" href="{{ route('verification.notice') }}">Verify your email</a>
        @else
            <form method="POST" action="{{ route('invitations.accept', $invitationId) }}"
                  x-data="{ token: '' }"
                  x-init="token = new URLSearchParams(window.location.hash.slice(1)).get('token') || ''; window.history.replaceState(null, '', window.location.pathname)">
                @csrf
                <input type="hidden" name="token" :value="token">
                <x-input-error :messages="$errors->get('invitation')" class="mb-4" />
                <x-primary-button x-bind:disabled="!token">Accept invitation</x-primary-button>
                <p class="mt-4 text-xs text-gray-500" x-show="!token">Open the complete invitation link from your email.</p>
            </form>
        @endif
    @endguest
</x-guest-layout>
