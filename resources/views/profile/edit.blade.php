<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <!-- Profile Header Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
                <div class="flex items-center gap-4">
                    <img src="{{ route('profile.avatar') }}" alt="{{ auth()->user()->name }}" class="h-16 w-16 rounded-2xl object-cover border border-slate-200 shadow-2xs bg-slate-50">
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ auth()->user()->name }}</h1>
                        <p class="text-xs text-slate-500 font-mono">{{ auth()->user()->email }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-50 text-teal-700 border border-teal-200/70">
                                S-cool Member
                            </span>
                            @if (auth()->user()->is_platform_admin)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/70">
                                    Platform Admin
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Information -->
            <div class="p-6 sm:p-8 bg-white shadow-xs rounded-2xl border border-slate-200/80">
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- Notification Preferences -->
            <div class="p-6 sm:p-8 bg-white shadow-xs rounded-2xl border border-slate-200/80">
                <div class="max-w-2xl">
                    @include('profile.partials.notification-preferences-form')
                </div>
            </div>

            <!-- Security / Password -->
            <div class="p-6 sm:p-8 bg-white shadow-xs rounded-2xl border border-slate-200/80">
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Danger Zone / Delete Account -->
            <div class="p-6 sm:p-8 bg-white shadow-xs rounded-2xl border border-rose-200/80">
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
