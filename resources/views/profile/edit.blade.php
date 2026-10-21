<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 leading-tight">
            {{ __('Account Settings') }}
        </h2>
    </x-slot>

    @php
        $sections = ['profile', 'security', 'notifications'];
        $requested = request()->query('section');
        $forceInitial = true;
        if ($errors->getBag('updatePassword')->isNotEmpty() || $errors->getBag('userDeletion')->isNotEmpty() || session('status') === 'password-updated') {
            $initialSection = 'security';
        } elseif ($errors->hasAny(['notify_replies', 'notify_courses', 'preferences']) || session('status') === 'notification-preferences-updated') {
            $initialSection = 'notifications';
        } elseif ($errors->any() || in_array(session('status'), ['profile-updated', 'verification-link-sent'], true)) {
            $initialSection = 'profile';
        } else {
            $initialSection = in_array($requested, $sections, true) ? $requested : 'profile';
            $forceInitial = false;
        }
    @endphp

    <div class="py-8 sm:py-10" x-data="accountSettings({ initial: @js($initialSection), forceInitial: @js($forceInitial) })">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Profile Identity Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="min-w-0 flex items-center gap-4">
                        <img src="{{ route('profile.avatar') }}" alt="{{ auth()->user()->name }}" class="h-16 w-16 rounded-2xl object-cover border border-slate-200 shadow-2xs bg-slate-50 shrink-0">
                        <div class="min-w-0">
                            <h1 class="text-xl font-bold text-slate-900 tracking-tight break-words">{{ auth()->user()->name }}</h1>
                            <p class="text-xs text-slate-500 font-mono break-all">{{ auth()->user()->email }}</p>
                            <div class="flex items-center gap-2 mt-1.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-50 text-teal-700 border border-teal-200/70">
                                    {{ __('S-cool Member') }}
                                </span>
                                @if (auth()->user()->is_platform_admin)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/70">
                                        {{ __('Platform Admin') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2-Column Responsive Account Hub Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Sidebar Nav (4 cols on lg) -->
                <div class="lg:col-span-4 space-y-4">
                    <x-account-navigation :active="$initialSection" :local="true" />
                </div>

                <!-- Main Content Area (8 cols on lg) -->
                <div class="min-w-0 lg:col-span-8 space-y-6">
                    <!-- Tab: Profile Information -->
                    <div x-show="currentTab === 'profile'" :hidden="currentTab !== 'profile'" @if ($initialSection !== 'profile') hidden @endif id="profile" class="min-w-0 p-6 sm:p-8 bg-white shadow-xs rounded-2xl border border-slate-200/80">
                        <div class="max-w-2xl">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>

                    <!-- Tab: Notification Preferences -->
                    <div x-show="currentTab === 'notifications'" :hidden="currentTab !== 'notifications'" @if ($initialSection !== 'notifications') hidden @endif id="notifications" class="min-w-0 p-6 sm:p-8 bg-white shadow-xs rounded-2xl border border-slate-200/80">
                        <div class="max-w-2xl">
                            @include('profile.partials.notification-preferences-form')
                        </div>
                    </div>

                    <!-- Tab: Security & Password + Danger Zone -->
                    <div x-show="currentTab === 'security'" :hidden="currentTab !== 'security'" @if ($initialSection !== 'security') hidden @endif id="security" class="min-w-0 space-y-6">
                        <div class="p-6 sm:p-8 bg-white shadow-xs rounded-2xl border border-slate-200/80">
                            <div class="max-w-2xl">
                                @include('profile.partials.update-password-form')
                            </div>
                        </div>

                        <div class="p-6 sm:p-8 bg-white shadow-xs rounded-2xl border border-rose-200/80">
                            <div class="max-w-2xl">
                                @include('profile.partials.delete-user-form')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
