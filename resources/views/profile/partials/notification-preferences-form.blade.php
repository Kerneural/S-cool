<section>
    <header class="border-b border-slate-100 pb-4 mb-6">
        <h2 class="text-lg font-bold text-slate-900 tracking-tight">
            {{ __('Account Notification Preferences') }}
        </h2>

        <p class="mt-1 text-xs text-slate-500">
            {{ __('Manage the notifications you receive. These settings apply globally across all communities you join.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.notifications.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div class="space-y-4">
            <!-- Notify Replies -->
            <div class="flex items-start p-4 bg-slate-50 rounded-2xl border border-slate-200/70">
                <div class="flex items-center h-5 mt-0.5">
                    <input id="notify_replies" name="notify_replies" type="checkbox" value="1"
                           {{ old('notify_replies', $user->notify_replies) ? 'checked' : '' }}
                           class="w-4 h-4 text-teal-600 border-slate-300 rounded focus:ring-teal-500">
                </div>
                <div class="ml-3 text-sm">
                    <label for="notify_replies" class="font-bold text-slate-800">
                        {{ __('Post reply notifications') }}
                    </label>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ __('Receive notifications when members reply to your posts.') }}
                    </p>
                </div>
            </div>

            <!-- Notify Courses -->
            <div class="flex items-start p-4 bg-slate-50 rounded-2xl border border-slate-200/70">
                <div class="flex items-center h-5 mt-0.5">
                    <input id="notify_courses" name="notify_courses" type="checkbox" value="1"
                           {{ old('notify_courses', $user->notify_courses) ? 'checked' : '' }}
                           class="w-4 h-4 text-teal-600 border-slate-300 rounded focus:ring-teal-500">
                </div>
                <div class="ml-3 text-sm">
                    <label for="notify_courses" class="font-bold text-slate-800">
                        {{ __('New course notifications') }}
                    </label>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ __('Receive notifications when new courses are published in your communities.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Mandatory Operational Notices Info -->
        <div class="p-4 bg-amber-50/90 rounded-2xl border border-amber-200/80 text-xs text-amber-900 space-y-1">
            <div class="font-bold flex items-center gap-1.5 text-amber-950">
                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ __('Mandatory Creator Operational Notifications') }}</span>
            </div>
            <p class="text-amber-800/90 leading-relaxed">
                {{ __('Operational notices such as new member activations and content reports are required for community creators to maintain platform safety and cannot be disabled.') }}
            </p>
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button class="px-6 py-2.5 rounded-xl font-bold text-xs tracking-wider">
                {{ __('Save Preferences') }}
            </x-primary-button>

            @if (session('status') === 'notification-preferences-updated')
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
