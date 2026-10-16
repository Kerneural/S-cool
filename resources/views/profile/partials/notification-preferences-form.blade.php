<section x-data="notificationPreferences({
    url: @js(route('profile.notifications.update')),
    csrf: @js(csrf_token()),
    values: { notify_replies: @js((bool) $user->notify_replies), notify_courses: @js((bool) $user->notify_courses) }
})">
    <header class="border-b border-slate-100 pb-4 mb-6">
        <h2 class="text-lg font-bold text-slate-900 tracking-tight">
            {{ __('Account Notification Preferences') }}
        </h2>

        <p class="mt-1 text-xs text-slate-500">
            {{ __('Manage the notifications you receive. These settings apply globally across all communities you join.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.notifications.update') }}" class="space-y-6" @submit="if (enhanced) { $event.preventDefault(); }">
        @csrf
        @method('patch')
        <x-input-error :messages="$errors->get('preferences')" />

        <div class="space-y-4">
            <!-- Notify Replies -->
            <div class="flex items-start p-4 bg-slate-50 rounded-2xl border border-slate-200/70">
                <div class="flex items-center h-5 mt-0.5">
                    <input type="hidden" name="notify_replies" value="0">
                    <input id="notify_replies" name="notify_replies" type="checkbox" value="1"
                           :checked="values.notify_replies"
                           @change="setPreference('notify_replies', $event.target.checked)"
                           {{ old('notify_replies', $user->notify_replies) ? 'checked' : '' }}
                           class="w-4 h-4 text-teal-600 border-slate-300 rounded focus:ring-teal-500 cursor-pointer">
                </div>
                <div class="ml-3 text-sm">
                    <label for="notify_replies" class="font-bold text-slate-800 cursor-pointer">
                        {{ __('Post reply notifications') }}
                    </label>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ __('Receive notifications when members reply to your posts.') }}
                    </p>
                    <x-input-error :messages="$errors->get('notify_replies')" />
                    <p x-show="failures.notify_replies" x-cloak role="alert" class="mt-2 text-xs text-rose-700 break-words">
                        {{ __('Reply preference is not confirmed. Your choice is retained.') }}
                        <button type="button" @click="retry('notify_replies')" :disabled="saving" class="underline font-semibold disabled:opacity-50">{{ __('Retry reply preference') }}</button>
                    </p>
                </div>
            </div>

            <!-- Notify Courses -->
            <div class="flex items-start p-4 bg-slate-50 rounded-2xl border border-slate-200/70">
                <div class="flex items-center h-5 mt-0.5">
                    <input type="hidden" name="notify_courses" value="0">
                    <input id="notify_courses" name="notify_courses" type="checkbox" value="1"
                           :checked="values.notify_courses"
                           @change="setPreference('notify_courses', $event.target.checked)"
                           {{ old('notify_courses', $user->notify_courses) ? 'checked' : '' }}
                           class="w-4 h-4 text-teal-600 border-slate-300 rounded focus:ring-teal-500 cursor-pointer">
                </div>
                <div class="ml-3 text-sm">
                    <label for="notify_courses" class="font-bold text-slate-800 cursor-pointer">
                        {{ __('New course notifications') }}
                    </label>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ __('Receive notifications when new courses are published in your communities.') }}
                    </p>
                    <x-input-error :messages="$errors->get('notify_courses')" />
                    <p x-show="failures.notify_courses" x-cloak role="alert" class="mt-2 text-xs text-rose-700 break-words">
                        {{ __('Course preference is not confirmed. Your choice is retained.') }}
                        <button type="button" @click="retry('notify_courses')" :disabled="saving" class="underline font-semibold disabled:opacity-50">{{ __('Retry course preference') }}</button>
                    </p>
                </div>
            </div>
        </div>

        <!-- Async feedback banners (AC-10) -->
        <div x-show="saving" x-cloak role="status" class="text-xs text-slate-500 flex items-center gap-1.5 font-medium">
            <svg aria-hidden="true" class="animate-spin motion-reduce:animate-none h-3.5 w-3.5 text-teal-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ __('Saving preference...') }}</span>
        </div>

        <div x-show="savedMessage" x-cloak role="status" class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200">
            {{ __('Preference saved and confirmed by server.') }}
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

        <div x-show="!enhanced" class="flex items-center gap-4 pt-2">
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
