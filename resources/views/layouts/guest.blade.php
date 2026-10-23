<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'S-cool') }}</title>

        <link rel="icon" type="image/svg+xml" href="/favicon.svg">

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-ink bg-canvas antialiased h-full">
        <div class="min-h-screen flex flex-col lg:flex-row">
            <!-- Left Branding & Value Proposition Panel (Desktop) -->
            <div class="hidden lg:flex lg:w-1/2 bg-slate-900 text-white p-12 xl:p-16 flex-col justify-between relative overflow-hidden">
                <!-- Subtle decorative background pattern -->
                <div class="absolute inset-0 bg-radial from-teal-900/30 via-slate-900 to-slate-950 pointer-events-none" aria-hidden="true"></div>
                <div class="absolute -top-24 -left-24 w-96 h-96 bg-brand/10 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>

                <!-- Top: Brand Header -->
                <div class="relative z-10 flex items-center gap-3">
                    <a href="/" class="flex items-center gap-3 group">
                        <x-application-logo class="w-10 h-10 shadow-sm" />
                        <div>
                            <span class="text-xl font-bold tracking-tight text-white group-hover:text-teal-200 transition">S-cool</span>
                            <span class="block text-xs text-slate-400 font-medium">{{ __('Private Learning Communities') }}</span>
                        </div>
                    </a>
                </div>

                <!-- Middle: Value Proposition & Highlights -->
                <div class="relative z-10 my-auto py-12 max-w-lg space-y-8">
                    <div class="space-y-3">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-brand/20 text-teal-300 border border-brand/30">
                            {{ __('Knowledge Sharing Reimagined') }}
                        </span>
                        <h2 class="text-3xl xl:text-4xl font-extrabold text-white tracking-tight leading-tight">
                            {{ __('Learn, discuss, and build together in private communities.') }}
                        </h2>
                        <p class="text-slate-300 text-sm leading-relaxed">
                            {{ __('Join structured courses, engage in focused member discussions, and access curated knowledge in a distraction-free environment.') }}
                        </p>
                    </div>

                    <div class="space-y-4 pt-2">
                        <div class="flex items-start gap-3.5">
                            <div class="w-8 h-8 rounded-lg bg-teal-800/40 border border-teal-500/30 flex items-center justify-center text-teal-300 shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-white">{{ __('Classrooms with Course Tracking') }}</h3>
                                <p class="text-xs text-slate-400 mt-0.5">{{ __('Video embeds, detailed lesson notes, and persistent completion tracking.') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3.5">
                            <div class="w-8 h-8 rounded-lg bg-teal-800/40 border border-teal-500/30 flex items-center justify-center text-teal-300 shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-white">{{ __('Conversational Feeds') }}</h3>
                                <p class="text-xs text-slate-400 mt-0.5">{{ __('Categorized discussions, pinned creator notices, likes, and bookmarks.') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3.5">
                            <div class="w-8 h-8 rounded-lg bg-teal-800/40 border border-teal-500/30 flex items-center justify-center text-teal-300 shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-white">{{ __('Verified Private Communities') }}</h3>
                                <p class="text-xs text-slate-400 mt-0.5">{{ __('Safe, creator-managed memberships with honest metrics and order tracking.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom: Footer info -->
                <div class="relative z-10 text-xs text-slate-500 flex items-center justify-between border-t border-slate-800/80 pt-6">
                    <span>&copy; {{ date('Y') }} {{ config('app.name', 'S-cool') }}</span>
                    <span>{{ __('Multi-community Platform') }}</span>
                </div>
            </div>

            <!-- Right Form Panel -->
            <div class="flex-1 flex flex-col justify-center py-10 px-6 sm:px-12 lg:px-16 xl:px-24 bg-surface">
                <!-- Mobile Brand Header -->
                <div class="lg:hidden mb-8 text-center sm:text-left">
                    <a href="/" class="inline-flex items-center gap-2.5">
                        <x-application-logo class="w-9 h-9" />
                        <span class="text-xl font-bold text-ink">S-cool</span>
                    </a>
                </div>

                <div class="w-full max-w-md mx-auto">
                    {{ $slot }}
                </div>
            </div>
        </div>
        {{-- Invitation acceptance needs the same single Alpine runtime as authenticated pages. --}}
        @livewireScripts
    </body>
</html>
