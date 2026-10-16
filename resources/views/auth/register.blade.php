<x-guest-layout>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">
                {{ __('Create an account') }}
            </h1>
            <p class="text-sm text-muted mt-1.5">
                {{ __('Join S-cool to explore communities, courses, and discussions.') }}
            </p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Full Name')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="John Doe" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email Address')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="name@example.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="pt-2">
                <x-primary-button class="w-full justify-center py-2.5">
                    {{ __('Create Account') }}
                </x-primary-button>
            </div>
        </form>

        <div class="text-center pt-4 border-t border-line text-xs text-muted">
            <span>{{ __('Already have an account?') }}</span>
            <a class="font-semibold text-brand hover:text-brand-hover hover:underline ms-1" href="{{ route('login') }}">
                {{ __('Sign in') }}
            </a>
        </div>
    </div>
</x-guest-layout>
