<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                {{ __('Welcome to S-cool') }}
            </h1>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl border border-slate-200/80 p-8 sm:p-12 text-center shadow-xs">
                <!-- Welcome Icon -->
                <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-3xl bg-brand-50 border border-brand-200/60 flex items-center justify-center text-brand mb-6 shadow-xs">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>

                <!-- Welcome Title -->
                <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                    {{ __('Welcome to the learning platform') }}
                </h2>

                <!-- Guidance Paragraph (AC-01: No pre-existing community name) -->
                <p class="text-sm sm:text-base text-slate-600 mt-3 max-w-xl mx-auto leading-relaxed">
                    {{ __('You have not joined any communities yet. Please check your email for an invitation link to start learning, or contact your administrator for access.') }}
                </p>

                <!-- Helpful Steps Card -->
                <div class="mt-8 p-5 rounded-2xl bg-slate-50 border border-slate-100 text-left max-w-lg mx-auto">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">
                        {{ __('Next steps') }}
                    </h3>
                    <ul class="space-y-3 text-xs sm:text-sm text-slate-700">
                        <li class="flex items-start gap-2.5">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand font-bold text-xs">1</span>
                            <span>{{ __('Check your inbox for a private invitation link.') }}</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand font-bold text-xs">2</span>
                            <span>{{ __('Click the link in your email to open the invitation page.') }}</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand font-bold text-xs">3</span>
                            <span>{{ __('Accept the invitation to enter your community.') }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Secondary Actions -->
                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('help.index') }}"
                       class="min-h-[44px] inline-flex items-center justify-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition">
                        {{ __('Help Center') }}
                    </a>
                    <a href="{{ route('profile.edit') }}"
                       class="min-h-[44px] inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-semibold rounded-xl shadow-2xs transition">
                        {{ __('Profile & Settings') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

