<section>
    @php
        $profileValue = static function (string $field) use ($user): string {
            $value = old($field, $user->{$field});
            return is_string($value) ? $value : '';
        };
    @endphp
    <header class="border-b border-slate-100 pb-4 mb-6">
        <h2 class="text-lg font-bold text-slate-900 tracking-tight">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-xs text-slate-500">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('patch')

        <div class="flex items-center gap-5 p-4 bg-slate-50 rounded-2xl border border-slate-200/70">
            <img src="{{ route('profile.avatar') }}" alt="{{ $user->name }}" class="h-16 w-16 rounded-2xl object-cover border border-slate-200 shadow-2xs bg-white shrink-0">
            <div class="flex-1 min-w-0">
                <x-input-label for="avatar" :value="__('Profile Photo (JPEG, PNG, WebP, max 2MB)')" class="font-bold text-slate-800 text-xs" />
                <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp"
                    class="mt-1.5 block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer" />
                <x-input-error class="mt-1" :messages="$errors->get('avatar')" />
            </div>
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1.5 block w-full rounded-xl border-slate-200 py-2.5" :value="$profileValue('name')" required autocomplete="name" />
            <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="bio" :value="__('Bio (Community-visible profile, max 500 characters)')" />
            <textarea id="bio" name="bio" rows="3" maxlength="500" class="mt-1.5 block w-full border-slate-200 focus:border-teal-500 focus:ring-teal-500 rounded-xl shadow-2xs text-sm py-2.5" placeholder="{{ __('Tell community members a bit about yourself...') }}">{{ $profileValue('bio') }}</textarea>
            <x-input-error class="mt-1.5" :messages="$errors->get('bio')" />
            <p class="mt-1.5 text-xs text-slate-500">{{ __('Notice: Your name, photo, and bio are visible to fellow members across communities you belong to. Emails and account credentials remain strictly private.') }}</p>
        </div>

        <div>
            <x-input-label for="github_url" :value="__('GitHub Profile URL (Optional, HTTPS)')" />
            <x-text-input id="github_url" name="github_url" type="url" class="mt-1.5 block w-full rounded-xl border-slate-200 py-2.5 font-mono text-xs" :value="$profileValue('github_url')" placeholder="https://github.com/username" maxlength="2048" />
            <x-input-error class="mt-1.5" :messages="$errors->get('github_url')" />
        </div>

        <div>
            <x-input-label for="portfolio_url" :value="__('Portfolio / Website URL (Optional, HTTPS)')" />
            <x-text-input id="portfolio_url" name="portfolio_url" type="url" class="mt-1.5 block w-full rounded-xl border-slate-200 py-2.5 font-mono text-xs" :value="$profileValue('portfolio_url')" placeholder="https://yourportfolio.com" maxlength="2048" />
            <x-input-error class="mt-1.5" :messages="$errors->get('portfolio_url')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1.5 block w-full rounded-xl border-slate-200 py-2.5 font-mono text-sm" :value="$profileValue('email')" required autocomplete="username" />
            <x-input-error class="mt-1.5" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs">
                    <p class="text-amber-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-teal-700 hover:text-teal-900 font-bold ml-1">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-bold text-emerald-700">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button class="px-6 py-2.5 rounded-xl font-bold text-xs tracking-wider">
                {{ __('Save') }}
            </x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
