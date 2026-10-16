<section class="space-y-6">
    <header class="border-b border-rose-100 pb-4">
        <h2 class="text-lg font-bold text-rose-900 tracking-tight">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-xs text-rose-700/80">
            {{ __('Account deletion is permanent. Accounts with community ownership or membership records cannot be deleted; retention must be resolved first.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="px-5 py-2.5 rounded-xl font-bold text-xs tracking-wider"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" labelledby="confirm-user-deletion-title" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8">
            @csrf
            @method('delete')

            <h2 id="confirm-user-deletion-title" class="text-lg font-bold text-slate-900 tracking-tight">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-2 text-xs text-slate-500 leading-relaxed">
                {{ __('Please enter your password to confirm account deletion. Owned communities are not deleted through this action.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full sm:w-3/4 rounded-xl border-slate-200 py-2.5 text-sm"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')" class="px-4 py-2 rounded-xl text-xs font-bold">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="px-4 py-2 rounded-xl text-xs font-bold ms-3">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
