<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>S-cool — Private Learning & Community Platform</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans antialiased text-slate-100 bg-slate-900 selection:bg-teal-500 selection:text-slate-950 flex flex-col justify-between">
    <!-- Top Navigation -->
    <header class="border-b border-slate-800/80 bg-slate-900/80 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <x-application-logo class="w-10 h-10 shadow-sm" />
                <div>
                    <span class="font-display font-black text-xl text-white tracking-tight">S-cool</span>
                    <span class="hidden sm:inline-block ml-2 text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full bg-teal-500/15 text-teal-300 border border-teal-500/30">
                        Community Engine
                    </span>
                </div>
            </div>

            <nav class="flex items-center gap-3 sm:gap-4">
                <a href="{{ route('help.index') }}" class="text-xs font-semibold text-slate-400 hover:text-white transition">
                    Help Center
                </a>

                @auth
                    <a href="{{ route('communities.index') }}"
                       class="px-4 py-2 bg-brand hover:bg-brand-hover text-white rounded-xl text-xs font-bold tracking-wide shadow-md shadow-teal-600/20 transition">
                        Dashboard &rarr;
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-3.5 py-2 text-xs font-bold text-slate-300 hover:text-white transition">
                        Log In
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="px-4 py-2 bg-brand hover:bg-brand-hover text-white rounded-xl text-xs font-bold tracking-wide shadow-md shadow-teal-600/20 transition">
                            Get Started
                        </a>
                    @endif
                @endauth
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-1 flex flex-col justify-center py-16 sm:py-24 relative overflow-hidden">
        <!-- Background decorative gradients -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10 space-y-8">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-800/80 border border-slate-700/80 text-xs font-semibold text-teal-300 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                <span>All-in-one cohort learning & membership suite</span>
            </div>

            <h1 class="text-4xl sm:text-6xl font-black font-display text-white tracking-tight leading-[1.1]">
                Private communities, structured classrooms, and creator economies.
            </h1>

            <p class="max-w-2xl mx-auto text-base sm:text-lg text-slate-400 leading-relaxed">
                Build high-engagement spaces with course curriculums, event calendars, direct messaging, gamified leaderboards, and simulated SePay sandbox checkout.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
                <a href="{{ route('communities.index') }}"
                   class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-teal-500 to-emerald-600 hover:from-teal-400 hover:to-emerald-500 text-slate-950 font-black text-sm uppercase tracking-wider rounded-xl shadow-lg shadow-teal-500/20 transition duration-150">
                    Explore Communities
                </a>
                <a href="{{ route('help.index') }}"
                   class="w-full sm:w-auto px-6 py-3.5 bg-slate-800 hover:bg-slate-700 text-white font-bold text-sm rounded-xl border border-slate-700 transition duration-150">
                    Platform Guidelines
                </a>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-16 text-left">
                <div class="p-6 bg-slate-800/60 rounded-2xl border border-slate-700/60 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-teal-500/15 text-teal-300 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-white tracking-tight">Private Cohort Communities</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Exclusive membership invitations, custom categories, role-based access, and member moderation queue.
                    </p>
                </div>

                <div class="p-6 bg-slate-800/60 rounded-2xl border border-slate-700/60 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/15 text-emerald-300 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-white tracking-tight">Interactive Classroom</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Organized courses, video lessons, syllabi, step-by-step progress retention, and one-time purchases.
                    </p>
                </div>

                <div class="p-6 bg-slate-800/60 rounded-2xl border border-slate-700/60 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-500/15 text-sky-300 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-white tracking-tight">Events & Direct Messaging</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        UTC-synchronized event schedules with secure meeting links and real-time private conversations.
                    </p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 bg-slate-950/60 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span>&copy; {{ date('Y') }} S-cool Platform.</span>
                <span>SePay Sandbox Environment.</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('help.index') }}" class="hover:text-teal-400 transition">Help Center</a>
                <a href="{{ route('communities.index') }}" class="hover:text-teal-400 transition">Communities</a>
                <a href="{{ route('login') }}" class="hover:text-teal-400 transition">Account</a>
            </div>
        </div>
    </footer>
</body>
</html>
