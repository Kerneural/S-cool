<x-guest-layout>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">
                {{ __('Welcome back') }}
            </h1>
            <p class="text-sm text-muted mt-1.5">
                {{ __('Sign in to access your communities and courses.') }}
            </p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="name@example.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="current-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Remember Me & Forgot Password Row -->
            <div class="flex items-center justify-between text-sm pt-1">
                <label for="remember_me" class="inline-flex items-center cursor-pointer">
                    <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-brand shadow-xs focus:ring-brand focus:border-brand" name="remember">
                    <span class="ms-2 text-xs text-muted font-medium">{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-xs text-brand hover:text-brand-hover font-semibold transition" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>

            <div class="pt-2">
                <x-primary-button class="w-full justify-center py-2.5">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="text-center pt-4 border-t border-line text-xs text-muted">
                <span>{{ __("Don't have an account?") }}</span>
                <a href="{{ route('register') }}" class="font-semibold text-brand hover:text-brand-hover hover:underline ms-1">
                    {{ __('Create an account') }}
                </a>
            </div>
        @endif
    </div>
</x-guest-layout>
