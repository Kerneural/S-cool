<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Account Notification Preferences') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Manage the notifications you receive. These settings apply globally across all communities you join.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.notifications.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="space-y-4">
            <!-- Notify Replies -->
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="notify_replies" name="notify_replies" type="checkbox" value="1"
                           {{ old('notify_replies', $user->notify_replies) ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                </div>
                <div class="ml-3 text-sm">
                    <label for="notify_replies" class="font-medium text-gray-700">
                        {{ __('Post reply notifications') }}
                    </label>
                    <p class="text-xs text-gray-500">
                        {{ __('Receive notifications when members reply to your posts.') }}
                    </p>
                </div>
            </div>

            <!-- Notify Courses -->
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="notify_courses" name="notify_courses" type="checkbox" value="1"
                           {{ old('notify_courses', $user->notify_courses) ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                </div>
                <div class="ml-3 text-sm">
                    <label for="notify_courses" class="font-medium text-gray-700">
                        {{ __('New course notifications') }}
                    </label>
                    <p class="text-xs text-gray-500">
                        {{ __('Receive notifications when new courses are published in your communities.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Mandatory Operational Notices Info -->
        <div class="p-4 bg-amber-50 rounded-lg border border-amber-200 text-xs text-amber-800 space-y-1">
            <div class="font-semibold flex items-center gap-1.5">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ __('Mandatory Creator Operational Notifications') }}</span>
            </div>
            <p>
                {{ __('Operational notices such as new member activations and content reports are required for community creators to maintain platform safety and cannot be disabled.') }}
            </p>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save Preferences') }}</x-primary-button>

            @if (session('status') === 'notification-preferences-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
