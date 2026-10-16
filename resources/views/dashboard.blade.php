<x-app-layout>
    <x-slot name="header">
        <h1 class="font-bold text-xl text-slate-900 leading-tight">
            {{ __('Dashboard') }}
        </h1>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xs rounded-2xl border border-slate-200/80 p-8 text-center space-y-4">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900">{{ __("You're logged in!") }}</h3>
                <p class="text-sm text-slate-500 max-w-md mx-auto">Explore your learning communities or launch a new classroom on S-cool.</p>
                <div class="pt-2">
                    <a href="{{ route('communities.index') }}" class="inline-flex items-center px-5 py-2.5 bg-brand hover:bg-brand-hover text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-xs transition">
                        {{ __('Go to Communities') }} &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
