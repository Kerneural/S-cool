<x-guest-layout>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">
                {{ __('Reset your password') }}
            </h1>
            <p class="text-sm text-muted mt-1.5">
                {{ __('Enter your email address and we will send you a password reset link.') }}
            </p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email Address')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus placeholder="name@example.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="pt-2">
                <x-primary-button class="w-full justify-center py-2.5">
                    {{ __('Email Password Reset Link') }}
                </x-primary-button>
            </div>
        </form>

        <div class="text-center pt-4 border-t border-line text-xs text-muted">
            <a class="font-semibold text-brand hover:text-brand-hover hover:underline inline-flex items-center gap-1" href="{{ route('login') }}">
                <span>&larr;</span> {{ __('Back to sign in') }}
            </a>
        </div>
    </div>
</x-guest-layout>
