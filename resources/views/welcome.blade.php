<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>S-cool — The Private Learning & Community Platform</title>
    <meta name="description" content="S-cool enables every community to own their learning and knowledge sharing. Structured classrooms, private discussions, and proof of work reviews.">

    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen w-full font-sans antialiased text-ink bg-canvas selection:bg-brand-soft selection:text-brand flex flex-col overflow-x-hidden">
    <!-- Top Navigation / Header -->
    <header x-data="{ mobileMenuOpen: false, productsOpen: false, learnOpen: false, scrolled: false }"
            x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 20 })"
            :class="scrolled ? 'bg-surface/90 backdrop-blur-md border-b border-line shadow-xs' : 'bg-surface/75 backdrop-blur-sm border-b border-line/60'"
            class="sticky top-0 z-50 transition-all duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Left: Brand Logo & Links -->
            <div class="flex items-center gap-8">
                <a href="/" class="flex items-center gap-2.5 group focus:outline-none" aria-label="S-cool Home">
                    <x-application-logo class="w-8 h-8 rounded-lg shadow-xs group-hover:scale-105 transition-transform" />
                    <span class="font-bold text-xl text-ink tracking-tight font-sans">S-cool</span>
                </a>

                <!-- Desktop Navigation Menu -->
                <nav class="hidden md:flex items-center gap-1">
                    <!-- Products Dropdown -->
                    <div class="relative" @click.outside="productsOpen = false">
                        <button type="button"
                                @click="productsOpen = !productsOpen; learnOpen = false"
                                class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-muted hover:text-ink hover:bg-slate-100/60 rounded-lg transition-colors">
                            <span>Products</span>
                            <svg class="w-3.5 h-3.5 text-muted transition-transform duration-200" :class="productsOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="productsOpen"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             class="absolute left-0 mt-1.5 w-72 bg-surface rounded-xl border border-line shadow-lg p-2 z-50">
                            <a href="{{ route('communities.index') }}" class="flex items-start gap-3 p-2.5 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="w-8 h-8 rounded-lg bg-brand-soft text-brand flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-ink">Community Engine</div>
                                    <div class="text-[11px] text-muted">Private feeds, member roles & moderation</div>
                                </div>
                            </a>
                            <a href="{{ route('communities.index') }}" class="flex items-start gap-3 p-2.5 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-ink">Classroom Suite</div>
                                    <div class="text-[11px] text-muted">Curriculums, video lessons & tracking</div>
                                </div>
                            </a>
                            <a href="{{ route('communities.index') }}" class="flex items-start gap-3 p-2.5 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="w-8 h-8 rounded-lg bg-amber-50 text-reward flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-ink">Proof of Work</div>
                                    <div class="text-[11px] text-muted">Peer review rubric & submission feedback</div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Learn Dropdown -->
                    <div class="relative" @click.outside="learnOpen = false">
                        <button type="button"
                                @click="learnOpen = !learnOpen; productsOpen = false"
                                class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-muted hover:text-ink hover:bg-slate-100/60 rounded-lg transition-colors">
                            <span>Learn</span>
                            <svg class="w-3.5 h-3.5 text-muted transition-transform duration-200" :class="learnOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="learnOpen"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             class="absolute left-0 mt-1.5 w-64 bg-surface rounded-xl border border-line shadow-lg p-2 z-50">
                            <a href="{{ route('help.index') }}" class="block p-2.5 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="text-xs font-semibold text-ink">Platform Documentation</div>
                                <div class="text-[11px] text-muted">Technical setup and community guides</div>
                            </a>
                            <a href="{{ route('help.index') }}" class="block p-2.5 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="text-xs font-semibold text-ink">Cohort Playbooks</div>
                                <div class="text-[11px] text-muted">Best practices for engaged student groups</div>
                            </a>
                        </div>
                    </div>

                    <a href="{{ route('help.index') }}" class="px-3 py-2 text-sm font-medium text-muted hover:text-ink hover:bg-slate-100/60 rounded-lg transition-colors">
                        Docs
                    </a>
                    <a href="{{ route('communities.index') }}" class="px-3 py-2 text-sm font-medium text-muted hover:text-ink hover:bg-slate-100/60 rounded-lg transition-colors">
                        Communities
                    </a>
                    <a href="#features" class="px-3 py-2 text-sm font-medium text-muted hover:text-ink hover:bg-slate-100/60 rounded-lg transition-colors">
                        Features
                    </a>
                </nav>
            </div>

            <!-- Right: Action Buttons -->
            <div class="flex items-center gap-2.5 sm:gap-3">
                @auth
                    <a href="{{ route('communities.index') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand hover:bg-brand-hover text-white rounded-lg text-xs font-semibold shadow-xs transition-colors">
                        <span>Dashboard</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-2 text-xs font-medium text-muted hover:text-ink rounded-lg transition-colors">
                        Log In
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand hover:bg-brand-hover text-white rounded-lg text-xs font-semibold shadow-xs transition-colors">
                            <span>Get Started</span>
                            <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="none">
                                <path d="M11.9852 4L12 10.7704H10.9314V6.60741L10.9462 5.80741L4.74211 12L4 11.2444L10.2041 5.03704L9.47681 5.05185L5.21707 5.05185V4L11.9852 4Z" fill="currentColor"/>
                            </svg>
                        </a>
                    @endif
                @endauth

                <!-- Mobile Menu Button -->
                <button type="button"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="md:hidden p-2 rounded-lg text-muted hover:text-ink hover:bg-slate-100 focus:outline-none"
                        aria-label="Toggle Menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Drawer -->
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden border-b border-line bg-surface px-4 py-3 space-y-2">
            <a href="{{ route('communities.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-ink hover:bg-slate-50">Communities</a>
            <a href="{{ route('help.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-ink hover:bg-slate-50">Docs & Help Center</a>
            <a href="#features" class="block px-3 py-2 rounded-lg text-sm font-medium text-ink hover:bg-slate-50">Features</a>
            @guest
                <div class="pt-2 border-t border-line flex flex-col gap-2">
                    <a href="{{ route('login') }}" class="w-full text-center py-2 text-sm font-medium text-muted hover:text-ink">Log In</a>
                    <a href="{{ route('register') }}" class="w-full text-center py-2 bg-brand text-white rounded-lg text-sm font-semibold">Get Started Free</a>
                </div>
            @endguest
        </div>
    </header>

    <!-- Main Landing Page Content -->
    <main class="flex-1">

        <!-- 1. Hero Section -->
        <section x-data="{
                    activeSet: 0,
                    sets: [
                        { id: 'community', name: 'Community', headline: 'Put insights to work', badge: 'Continuous Improvement', desc: 'Autonomous peer discussion, structured categories, and real-time moderation that elevates member experience.' },
                        { id: 'classroom', name: 'Classroom', headline: 'Smarter after every launch', badge: 'Compound Knowledge', desc: 'Turn progressive course curriculums and video modules into structured completion rubrics that scale effortlessly.' },
                        { id: 'verification', name: 'Verification', headline: 'We catch your drift', badge: 'Proof of Work', desc: 'Verifiable deliverable submissions, peer reviews, and transparent scoring that validate genuine student outcomes.' }
                    ]
                 }"
                 x-init="setInterval(() => { activeSet = (activeSet + 1) % sets.length }, 8000)"
                 class="relative overflow-hidden pt-12 pb-20 md:pt-20 md:pb-28 bg-canvas">

            <!-- Atmospheric Subtle Brand Glows (per design.md restrained accents) -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[900px] h-[450px] bg-gradient-to-b from-brand/10 via-brand-soft to-transparent blur-[110px] pointer-events-none rounded-full" aria-hidden="true"></div>
            <div class="absolute top-40 right-[-80px] w-[450px] h-[350px] bg-teal-500/5 blur-[120px] pointer-events-none rounded-full" aria-hidden="true"></div>

            <!-- Fine Decorative Grid Pattern -->
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#e2e8f080_1px,transparent_1px),linear-gradient(to_bottom,#e2e8f080_1px,transparent_1px)] bg-[size:3.5rem_3.5rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)] pointer-events-none" aria-hidden="true"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Theme / Set Switcher Pill -->
                <div class="flex flex-col items-center justify-center text-center">
                    <div class="inline-flex items-center gap-1.5 p-1 rounded-full bg-surface border border-line shadow-xs mb-6">
                        <span class="px-3 py-1 text-xs font-semibold uppercase tracking-wider text-brand bg-brand-soft rounded-full">
                            Engine v2.0
                        </span>
                        <div class="flex items-center gap-1">
                            <template x-for="(set, idx) in sets" :key="set.id">
                                <button type="button"
                                        @click="activeSet = idx"
                                        :class="activeSet === idx ? 'bg-brand text-white shadow-xs' : 'text-muted hover:text-ink hover:bg-slate-100'"
                                        class="px-3 py-1 text-xs font-medium rounded-full transition-all"
                                        x-text="set.name">
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Main Dynamic Headline -->
                    <div class="max-w-4xl mx-auto space-y-4">
                        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-bold tracking-tight text-ink font-sans">
                            Meet <span class="bg-gradient-to-r from-brand via-teal-700 to-emerald-600 bg-clip-text text-transparent">S-cool Engine</span>
                            <span class="inline-block text-brand text-3xl sm:text-5xl lg:text-6xl">&nearr;</span>
                        </h1>

                        <!-- Dynamic Alternating Sub-heading with smooth transition -->
                        <div class="min-h-[44px] sm:min-h-[56px] flex items-center justify-center">
                            <h2 class="text-2xl sm:text-4xl lg:text-5xl font-semibold tracking-tight text-brand transition-all duration-300"
                                x-text="sets[activeSet].headline">
                            </h2>
                        </div>

                        <p class="max-w-2xl mx-auto text-base sm:text-lg text-muted font-normal leading-relaxed"
                           x-text="sets[activeSet].desc">
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-8 flex flex-wrap items-center justify-center gap-3.5">
                        <a href="{{ route('communities.index') }}"
                           class="inline-flex items-center justify-center gap-2 px-7 py-3.5 text-sm font-semibold text-white bg-brand hover:bg-brand-hover rounded-lg shadow-sm transition-all group">
                            <span>Explore Communities</span>
                            <svg class="w-4 h-4 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform" viewBox="0 0 16 16" fill="none">
                                <path d="M11.9852 4L12 10.7704H10.9314V6.60741L10.9462 5.80741L4.74211 12L4 11.2444L10.2041 5.03704L9.47681 5.05185L5.21707 5.05185V4L11.9852 4Z" fill="currentColor"/>
                            </svg>
                        </a>

                        <a href="{{ route('help.index') }}"
                           class="inline-flex items-center justify-center gap-2 px-7 py-3.5 text-sm font-medium text-ink bg-surface hover:bg-slate-50 border border-line rounded-lg shadow-xs transition-all">
                            <span>Platform Guidelines</span>
                            <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    </div>
            </div>

            <!-- Continuous Infinite Logo Marquee Flow (Full-Width Edge-to-Edge) -->
            <div class="w-full pt-16 border-t border-line/60 mt-16 text-center overflow-hidden">
                <p class="text-xs uppercase tracking-widest text-muted font-medium mb-8 px-4">
                    Trusted by leading learning teams, universities & cohorts worldwide
                </p>

                    <!-- Scoped styles to guarantee marquee animation regardless of browser cache or reduced-motion defaults -->
                    <style>
                        @keyframes marquee {
                            0% { transform: translate3d(0, 0, 0); }
                            100% { transform: translate3d(-50%, 0, 0); }
                        }
                        @keyframes marquee-reverse {
                            0% { transform: translate3d(-50%, 0, 0); }
                            100% { transform: translate3d(0, 0, 0); }
                        }
                        .animate-marquee {
                            display: flex !important;
                            width: max-content !important;
                            will-change: transform;
                            animation: marquee 35s linear infinite !important;
                        }
                        .animate-marquee-reverse {
                            display: flex !important;
                            width: max-content !important;
                            will-change: transform;
                            animation: marquee-reverse 35s linear infinite !important;
                        }
                        .animate-marquee:hover, .animate-marquee-reverse:hover {
                            animation-play-state: paused !important;
                        }
                    </style>

                    <!-- Row 1: Leftward infinite flow with side gradient fade transition overlays -->
                    <div class="relative overflow-hidden group/marquee"
                         style="-webkit-mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent); mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent);">
                        <!-- Left fade transition overlay -->
                        <div class="pointer-events-none absolute inset-y-0 left-0 w-24 sm:w-44 md:w-64 bg-gradient-to-r from-[#F8FAFC] via-[#F8FAFC]/90 to-transparent z-10 transition-opacity duration-300"></div>
                        <!-- Right fade transition overlay -->
                        <div class="pointer-events-none absolute inset-y-0 right-0 w-24 sm:w-44 md:w-64 bg-gradient-to-l from-[#F8FAFC] via-[#F8FAFC]/90 to-transparent z-10 transition-opacity duration-300"></div>

                        <div class="animate-marquee py-2 items-center text-muted flex w-max shrink-0"
                             style="display: flex; width: max-content; animation: marquee 35s linear infinite; will-change: transform;">
                            <!-- Group 1 -->
                            <div class="flex items-center gap-6 sm:gap-8 pr-6 sm:pr-8 shrink-0">
                                <a href="https://ocw.mit.edu"  target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   title="MIT OpenCourseWare — Official Website">
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 321 166" fill="none" aria-hidden="true">
        <path d="M17.5 0v166M74.5 0v113M131.5 0v166M188.5 0v33M246.5 53v113" stroke="#A31F34" stroke-width="35"/>
        <path d="M188.5 53v113" stroke="#8A8B8C" stroke-width="35"/>
        <path d="M229 16.5h92" stroke="#A31F34" stroke-width="33"/>
    </svg>
                                    <span class="font-mono font-bold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">MIT OCW</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>

                                <a href="https://vnu.edu.vn/eng/"  target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   title="Vietnam National University (VNU) — Official Website">
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <circle cx="12" cy="12" r="11" fill="#00803D" stroke="#005A2B" stroke-width="0.8"/>
        <path d="M12 16.5c-2.5-1.2-5-0.8-6.5 0v-6c1.5-0.8 4-1.2 6.5 0 2.5-1.2 5-0.8 6.5 0v6c-1.5-0.8-4-1.2-6.5 0z" fill="#FFFFFF"/>
        <path d="M12 10.5v6" stroke="#00803D" stroke-width="0.8"/>
        <path d="M5.5 6C6.8 4 9.2 3 12 3s5.2 1 6.5 3" stroke="#FFFFFF" stroke-width="1.2" stroke-linecap="round" fill="none"/>
        <circle cx="12" cy="5.8" r="1.3" fill="#FACC15"/>
    </svg>
                                    <span class="font-semibold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">VNU Vietnam</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>

                                <a href="https://www.youtube.com/@stanfordonline"  target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   title="Stanford Online — Featured Video">
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 162 248" fill="none" aria-hidden="true">
        <path fill="#8C1515" d="M35.87 1.12 1.31 34.95v90.91l30.04 30.67h13.78c-.34.92-.78 2.35.18 3.74 1.04 1.54 2.84 1.54 3.52 1.54.81 0 1.93-.1 3.66-.34s.72.64-1.48 1.36c-1.81.64-3.39 1.69-3.91 3.5-.03.13-.05.27-.08.39H1.3l-.01 46.47 34.56 33.95h90.89l34.56-33.9v-87.08l-30.05-30.62h-25.48c-1.12-.67-2.28-1.38-3.33-2.07l-.39-.25-.07-.04c-.57-.35-1.17-.79-1.83-1.36-.57-.45-1.04-.91-1.4-1.31l-.1-.1c-.67-.7-1.26-1.38-1.71-1.97 3.98 1.14 8.31 1.43 10.39 1.5h.25c.13 0 .27.02.39.02l.18.01c.5.03.89.12 1.14.2.27.1.51.22.72.32l1.07.4c.25.07.52.08.81.08 1.56 0 3.02-.94 3.69-2.42.22-.5.55-1.51.3-2.83l45.44.03v-50.76L126.76 1.12H35.87z"/>
        <path fill="#FFFFFF" d="M157.19 36.8 125.09 5.31H37.54L5.43 36.75v87.38l27.63 28.18 17.82.02c-2 1.79-2.62 3.66-2.94 4.56-.69 2-1.41 2.57 4.85 1.69 1.98-.28 5.59-1.56 7.28-2.7 1.66-1.14 4.95-.22 4.66 2.25-1.54 3.77-5.5 6.14-9.28 6.61-8.69 1.09-4.93 4.33-4.93 4.33.96.65 1.64 1.29 2.13 1.86H70.4v-.02H27.42l-.02 40.48 32.09 31.54h87.57l32.09-31.49v-83.54l-27.63-28.15h-22.33c-1.78-1.19-4.18-2.52-6.36-3.94l-.02-.02c-.84-.49-1.66-1.09-2.45-1.78-.65-.52-1.24-1.07-1.74-1.63-1.93-2.01-3.37-4.11-3.76-5.08-.72-1.78-.55-4.03 1.07-3.1 4.13 2.38 10.9 2.83 13.42 2.92.29 0 2.31.23 2.8.38.5.2 1.28.57 1.28.57 1.02.44 2.87-.97.74-3.57-.55-.89-1.39-1.91-2.38-2.95h47.35V44.76"/>
        <path fill="#007C59" d="M81 50l-8 22h5l-10 24h6l-12 28h22v30h14v-30h22l-12-28h6l-10-24h5l-8-22z"/>
    </svg>
                                    <span class="font-serif font-semibold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">Stanford Online</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-rose-700 bg-rose-50 border border-rose-200/60 group-hover:bg-rose-100/70 px-1.5 py-0.5 rounded transition-colors">
            <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            <span>Video</span>
        </span>
                                </a>

                                <a href="https://hust.edu.vn/en/"  target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   title="Hanoi University of Science and Technology (HUST) — Official Website">
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <circle cx="12" cy="12" r="10" fill="#F59E0B" stroke="#D97706" stroke-width="0.8"/>
        <circle cx="12" cy="12" r="6.5" fill="#FFFFFF"/>
        <path d="M12 1v3M12 20v3M1 12h3M20 12h3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1" stroke="#D97706" stroke-width="1.6" stroke-linecap="round"/>
        <path d="M12 6L8 16h8L12 6z" fill="#CE1628"/>
        <path d="M12 9l-2 5h4l-2-5z" fill="#FFFFFF"/>
    </svg>
                                    <span class="font-semibold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">HUST Bách Khoa</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>

                                <a href="https://www.rmit.edu.vn/"  target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   title="RMIT University Vietnam — Official Website">
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M2 3h5v4h4v5h4v4h-4v-4H7V7H2V3z" fill="#E42A20"/>
        <path d="M9 3h5v4h4v5h4v4h-4v-4h-4V7H9V3z" fill="#E42A20"/>
        <path d="M2 13h5v4h4v4H7v-4H2v-4z" fill="#E42A20"/>
    </svg>
                                    <span class="font-semibold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">RMIT Vietnam</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>

                                <a href="https://www.youtube.com/@harvardonline"  target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   title="Harvard Online — Featured Video">
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M12 2C7 2 4 4 4 8.5c0 6.5 5 11.5 8 13.5 3-2 8-7 8-13.5C20 4 17 2 12 2z" fill="#A51C30"/>
        <path d="M7 6.5h3.5v3H7z" fill="#FFFFFF"/>
        <path d="M7.7 7.2h2M7.7 8.2h2" stroke="#A51C30" stroke-width="0.5"/>
        <path d="M13.5 6.5H17v3h-3.5z" fill="#FFFFFF"/>
        <path d="M14.2 7.2h2M14.2 8.2h2" stroke="#A51C30" stroke-width="0.5"/>
        <path d="M10.2 12h3.6v3h-3.6z" fill="#FFFFFF"/>
        <path d="M10.9 12.8h2.2M10.9 13.8h2.2" stroke="#A51C30" stroke-width="0.5"/>
    </svg>
                                    <span class="font-serif font-semibold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">Harvard Online</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-rose-700 bg-rose-50 border border-rose-200/60 group-hover:bg-rose-100/70 px-1.5 py-0.5 rounded transition-colors">
            <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            <span>Video</span>
        </span>
                                </a>

                                <a href="https://daihoc.fpt.edu.vn/en/"  target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   title="FPT University — Official Website">
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 34 21" fill="none" aria-hidden="true">
        <path d="M6.68 3.5C4.76 3.5 3.12 4.76 2.55 6.5l-.13.5L0 17.5h6.09c1.82 0 3.37-1.16 3.96-2.78l.23-1L12.63 3.5H6.68z" fill="#08509F"/>
        <path d="M18.17 0c-1.99 0-3.67 1.34-4.19 3.17-.04.14-.07.28-.09.42L9.88 21h5.95c2.04 0 3.75-1.4 4.22-3.3h.01L24.12 0H18.17z" fill="#F27123"/>
        <path d="M28.06 3.5c-1.86 0-3.45 1.17-4.07 2.82l-.14.43L21.37 17.5h5.95c2.04 0 3.75-1.41 4.22-3.32l2.46-10.68H28.06z" fill="#51B748"/>
    </svg>
                                    <span class="font-semibold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">FPT University</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>

                                <a href="https://mindx.edu.vn/"  target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   title="MindX Technology School — Official Website">
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M4 4l12 16" stroke="#E32028" stroke-width="3" stroke-linecap="round"/>
        <path d="M20 4l-4.5 6M8.5 14L4 20" stroke="#E32028" stroke-width="3" stroke-linecap="round"/>
        <path d="M15.5 4l-3 4M11.5 16l-3 4" stroke="#E32028" stroke-width="2.2" stroke-linecap="round"/>
    </svg>
                                    <span class="font-semibold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">MindX Technology</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>
                            </div>

                            <!-- Duplicate for seamless infinite loop -->
                            <div class="flex items-center gap-6 sm:gap-8 pr-6 sm:pr-8 shrink-0" aria-hidden="true">
                                <a href="https://ocw.mit.edu" tabindex="-1" target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   >
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 321 166" fill="none" aria-hidden="true">
        <path d="M17.5 0v166M74.5 0v113M131.5 0v166M188.5 0v33M246.5 53v113" stroke="#A31F34" stroke-width="35"/>
        <path d="M188.5 53v113" stroke="#8A8B8C" stroke-width="35"/>
        <path d="M229 16.5h92" stroke="#A31F34" stroke-width="33"/>
    </svg>
                                    <span class="font-mono font-bold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">MIT OCW</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>

                                <a href="https://vnu.edu.vn/eng/" tabindex="-1" target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   >
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <circle cx="12" cy="12" r="11" fill="#00803D" stroke="#005A2B" stroke-width="0.8"/>
        <path d="M12 16.5c-2.5-1.2-5-0.8-6.5 0v-6c1.5-0.8 4-1.2 6.5 0 2.5-1.2 5-0.8 6.5 0v6c-1.5-0.8-4-1.2-6.5 0z" fill="#FFFFFF"/>
        <path d="M12 10.5v6" stroke="#00803D" stroke-width="0.8"/>
        <path d="M5.5 6C6.8 4 9.2 3 12 3s5.2 1 6.5 3" stroke="#FFFFFF" stroke-width="1.2" stroke-linecap="round" fill="none"/>
        <circle cx="12" cy="5.8" r="1.3" fill="#FACC15"/>
    </svg>
                                    <span class="font-semibold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">VNU Vietnam</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>

                                <a href="https://www.youtube.com/@stanfordonline" tabindex="-1" target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   >
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 162 248" fill="none" aria-hidden="true">
        <path fill="#8C1515" d="M35.87 1.12 1.31 34.95v90.91l30.04 30.67h13.78c-.34.92-.78 2.35.18 3.74 1.04 1.54 2.84 1.54 3.52 1.54.81 0 1.93-.1 3.66-.34s.72.64-1.48 1.36c-1.81.64-3.39 1.69-3.91 3.5-.03.13-.05.27-.08.39H1.3l-.01 46.47 34.56 33.95h90.89l34.56-33.9v-87.08l-30.05-30.62h-25.48c-1.12-.67-2.28-1.38-3.33-2.07l-.39-.25-.07-.04c-.57-.35-1.17-.79-1.83-1.36-.57-.45-1.04-.91-1.4-1.31l-.1-.1c-.67-.7-1.26-1.38-1.71-1.97 3.98 1.14 8.31 1.43 10.39 1.5h.25c.13 0 .27.02.39.02l.18.01c.5.03.89.12 1.14.2.27.1.51.22.72.32l1.07.4c.25.07.52.08.81.08 1.56 0 3.02-.94 3.69-2.42.22-.5.55-1.51.3-2.83l45.44.03v-50.76L126.76 1.12H35.87z"/>
        <path fill="#FFFFFF" d="M157.19 36.8 125.09 5.31H37.54L5.43 36.75v87.38l27.63 28.18 17.82.02c-2 1.79-2.62 3.66-2.94 4.56-.69 2-1.41 2.57 4.85 1.69 1.98-.28 5.59-1.56 7.28-2.7 1.66-1.14 4.95-.22 4.66 2.25-1.54 3.77-5.5 6.14-9.28 6.61-8.69 1.09-4.93 4.33-4.93 4.33.96.65 1.64 1.29 2.13 1.86H70.4v-.02H27.42l-.02 40.48 32.09 31.54h87.57l32.09-31.49v-83.54l-27.63-28.15h-22.33c-1.78-1.19-4.18-2.52-6.36-3.94l-.02-.02c-.84-.49-1.66-1.09-2.45-1.78-.65-.52-1.24-1.07-1.74-1.63-1.93-2.01-3.37-4.11-3.76-5.08-.72-1.78-.55-4.03 1.07-3.1 4.13 2.38 10.9 2.83 13.42 2.92.29 0 2.31.23 2.8.38.5.2 1.28.57 1.28.57 1.02.44 2.87-.97.74-3.57-.55-.89-1.39-1.91-2.38-2.95h47.35V44.76"/>
        <path fill="#007C59" d="M81 50l-8 22h5l-10 24h6l-12 28h22v30h14v-30h22l-12-28h6l-10-24h5l-8-22z"/>
    </svg>
                                    <span class="font-serif font-semibold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">Stanford Online</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-rose-700 bg-rose-50 border border-rose-200/60 group-hover:bg-rose-100/70 px-1.5 py-0.5 rounded transition-colors">
            <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            <span>Video</span>
        </span>
                                </a>

                                <a href="https://hust.edu.vn/en/" tabindex="-1" target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   >
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <circle cx="12" cy="12" r="10" fill="#F59E0B" stroke="#D97706" stroke-width="0.8"/>
        <circle cx="12" cy="12" r="6.5" fill="#FFFFFF"/>
        <path d="M12 1v3M12 20v3M1 12h3M20 12h3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1" stroke="#D97706" stroke-width="1.6" stroke-linecap="round"/>
        <path d="M12 6L8 16h8L12 6z" fill="#CE1628"/>
        <path d="M12 9l-2 5h4l-2-5z" fill="#FFFFFF"/>
    </svg>
                                    <span class="font-semibold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">HUST Bách Khoa</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>

                                <a href="https://www.rmit.edu.vn/" tabindex="-1" target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   >
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M2 3h5v4h4v5h4v4h-4v-4H7V7H2V3z" fill="#E42A20"/>
        <path d="M9 3h5v4h4v5h4v4h-4v-4h-4V7H9V3z" fill="#E42A20"/>
        <path d="M2 13h5v4h4v4H7v-4H2v-4z" fill="#E42A20"/>
    </svg>
                                    <span class="font-semibold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">RMIT Vietnam</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>

                                <a href="https://www.youtube.com/@harvardonline" tabindex="-1" target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   >
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M12 2C7 2 4 4 4 8.5c0 6.5 5 11.5 8 13.5 3-2 8-7 8-13.5C20 4 17 2 12 2z" fill="#A51C30"/>
        <path d="M7 6.5h3.5v3H7z" fill="#FFFFFF"/>
        <path d="M7.7 7.2h2M7.7 8.2h2" stroke="#A51C30" stroke-width="0.5"/>
        <path d="M13.5 6.5H17v3h-3.5z" fill="#FFFFFF"/>
        <path d="M14.2 7.2h2M14.2 8.2h2" stroke="#A51C30" stroke-width="0.5"/>
        <path d="M10.2 12h3.6v3h-3.6z" fill="#FFFFFF"/>
        <path d="M10.9 12.8h2.2M10.9 13.8h2.2" stroke="#A51C30" stroke-width="0.5"/>
    </svg>
                                    <span class="font-serif font-semibold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">Harvard Online</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-rose-700 bg-rose-50 border border-rose-200/60 group-hover:bg-rose-100/70 px-1.5 py-0.5 rounded transition-colors">
            <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            <span>Video</span>
        </span>
                                </a>

                                <a href="https://daihoc.fpt.edu.vn/en/" tabindex="-1" target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   >
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 34 21" fill="none" aria-hidden="true">
        <path d="M6.68 3.5C4.76 3.5 3.12 4.76 2.55 6.5l-.13.5L0 17.5h6.09c1.82 0 3.37-1.16 3.96-2.78l.23-1L12.63 3.5H6.68z" fill="#08509F"/>
        <path d="M18.17 0c-1.99 0-3.67 1.34-4.19 3.17-.04.14-.07.28-.09.42L9.88 21h5.95c2.04 0 3.75-1.4 4.22-3.3h.01L24.12 0H18.17z" fill="#F27123"/>
        <path d="M28.06 3.5c-1.86 0-3.45 1.17-4.07 2.82l-.14.43L21.37 17.5h5.95c2.04 0 3.75-1.41 4.22-3.32l2.46-10.68H28.06z" fill="#51B748"/>
    </svg>
                                    <span class="font-semibold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">FPT University</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>

                                <a href="https://mindx.edu.vn/" tabindex="-1" target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   >
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M4 4l12 16" stroke="#E32028" stroke-width="3" stroke-linecap="round"/>
        <path d="M20 4l-4.5 6M8.5 14L4 20" stroke="#E32028" stroke-width="3" stroke-linecap="round"/>
        <path d="M15.5 4l-3 4M11.5 16l-3 4" stroke="#E32028" stroke-width="2.2" stroke-linecap="round"/>
    </svg>
                                    <span class="font-semibold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">MindX Technology</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Rightward infinite flow with side gradient fade transition overlays -->
                    <div class="relative overflow-hidden mt-5 group/marquee"
                         style="-webkit-mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent); mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent);">
                        <!-- Left fade transition overlay -->
                        <div class="pointer-events-none absolute inset-y-0 left-0 w-24 sm:w-44 md:w-64 bg-gradient-to-r from-[#F8FAFC] via-[#F8FAFC]/90 to-transparent z-10 transition-opacity duration-300"></div>
                        <!-- Right fade transition overlay -->
                        <div class="pointer-events-none absolute inset-y-0 right-0 w-24 sm:w-44 md:w-64 bg-gradient-to-l from-[#F8FAFC] via-[#F8FAFC]/90 to-transparent z-10 transition-opacity duration-300"></div>

                        <div class="animate-marquee-reverse py-2 items-center text-muted flex w-max shrink-0"
                             style="display: flex; width: max-content; animation: marquee-reverse 35s linear infinite; will-change: transform;">
                            <!-- Group 2 -->
                            <div class="flex items-center gap-6 sm:gap-8 pr-6 sm:pr-8 shrink-0">
                                <a href="https://github.com/edu"  target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   title="GitHub Education — Official Website">
                                    <svg class="w-4 h-4 shrink-0 text-slate-800 group-hover:text-ink transition-colors" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
    </svg>
                                    <span class="font-mono font-bold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">GitHub Education</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>

                                <a href="https://www.youtube.com/watch?v=earTjdzDC2s"  target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   title="Google Developer Student Clubs (GDSC) — Featured Video">
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M10 5L4.5 10.5a2.1 2.1 0 000 3L10 19l2.5-2.5-4-4 4-4L10 5z" fill="#4285F4"/>
        <path d="M10 5L4.5 10.5l4 4 4-4L10 5z" fill="#EA4335"/>
        <path d="M14 5l5.5 5.5a2.1 2.1 0 010 3L14 19l-2.5-2.5 4-4-4-4L14 5z" fill="#FBBC04"/>
        <path d="M14 5l5.5 5.5-4 4-4-4L14 5z" fill="#0F9D58"/>
    </svg>
                                    <span class="font-semibold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">Google DSC</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-rose-700 bg-rose-50 border border-rose-200/60 group-hover:bg-rose-100/70 px-1.5 py-0.5 rounded transition-colors">
            <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            <span>Video</span>
        </span>
                                </a>

                                <a href="https://www.youtube.com/watch?v=0kH8s3G8Z0g"  target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   title="42 Network Coding School — Featured Video">
                                    <svg class="w-4 h-4 shrink-0 text-slate-800 group-hover:text-ink transition-colors" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M19.581 16.851H24v-4.439ZM24 3.574h-4.419v4.42l-4.419 4.418v4.44h4.419v-4.44L24 7.993Zm-4.419 0h-4.419v4.42zm-6.324 8.838H4.419l8.838-8.838H8.838L0 12.412v3.595h8.838v4.419h4.419z"/>
    </svg>
                                    <span class="font-mono font-bold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">42 Network</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-rose-700 bg-rose-50 border border-rose-200/60 group-hover:bg-rose-100/70 px-1.5 py-0.5 rounded transition-colors">
            <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            <span>Video</span>
        </span>
                                </a>

                                <a href="https://vietai.org/"  target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   title="VietAI Community — Official Website">
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <circle cx="12" cy="12" r="11" fill="#FFF1F2" stroke="#FECDD3" stroke-width="0.8"/>
        <path d="M12 4l2.4 5.3 5.6.8-4.1 4 1 5.9-4.9-2.8-4.9 2.8 1-5.9-4.1-4 5.6-.8L12 4z" fill="#E11D48"/>
        <circle cx="12" cy="4" r="1.3" fill="#FBBF24"/>
        <circle cx="20" cy="10.1" r="1.3" fill="#00A99A"/>
        <circle cx="17" cy="20" r="1.3" fill="#FBBF24"/>
        <circle cx="7" cy="20" r="1.3" fill="#00A99A"/>
        <circle cx="4" cy="10.1" r="1.3" fill="#FBBF24"/>
        <circle cx="12" cy="12" r="1.5" fill="#FFFFFF"/>
    </svg>
                                    <span class="font-mono font-bold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">VietAI</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>

                                <a href="https://aws.amazon.com/developer/community/students/"  target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   title="AWS Cloud Clubs — Official Website">
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <text x="1" y="11" font-family="system-ui, -apple-system, sans-serif" font-size="9.5" font-weight="900" fill="#252F3E" letter-spacing="-0.3">aws</text>
        <path d="M2 14c4.5 3.2 11.5 3.2 16 0" stroke="#FF9900" stroke-width="1.8" stroke-linecap="round" fill="none"/>
        <path d="M17 12.8l2 2.2-2.8 1" fill="#FF9900"/>
    </svg>
                                    <span class="font-mono font-bold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">AWS Cloud Clubs</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>

                                <a href="https://www.youtube.com/@MicrosoftLearn"  target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   title="Microsoft Learn — Featured Video">
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 21 21" fill="none" aria-hidden="true">
        <rect x="1" y="1" width="9" height="9" fill="#F25022"/>
        <rect x="11" y="1" width="9" height="9" fill="#7FBA00"/>
        <rect x="1" y="11" width="9" height="9" fill="#00A4EF"/>
        <rect x="11" y="11" width="9" height="9" fill="#FFB900"/>
    </svg>
                                    <span class="font-semibold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">Microsoft Learn</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-rose-700 bg-rose-50 border border-rose-200/60 group-hover:bg-rose-100/70 px-1.5 py-0.5 rounded transition-colors">
            <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            <span>Video</span>
        </span>
                                </a>

                                <a href="https://www.coderschool.vn/en"  target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   title="CoderSchool Vietnam — Official Website">
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <circle cx="12" cy="12" r="11" fill="#D9383A"/>
        <circle cx="12" cy="12" r="9.5" stroke="#FFFFFF" stroke-width="0.7" fill="none"/>
        <path d="M14.5 7h-2c-1.5 0-2.5.8-2.5 2.5v1c0 .8-.5 1.5-1.5 1.5 1 0 1.5.7 1.5 1.5v1c0 1.7 1 2.5 2.5 2.5h2" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    </svg>
                                    <span class="font-semibold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">CoderSchool</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>

                                <a href="https://www.hackerrank.com/skills-verification"  target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   title="HackerRank Campus Skills Verification — Official Website">
                                    <svg class="w-4 h-4 shrink-0 text-[#2EC866]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M0 0v24h24V0H0zm9.95 8h1.8v7.8h-1.8v-2.9H7.9v2.9H6V8.1h1.9v2.9h2.05V8zm3 0h5.8v7.8h-5.8V8z"/>
    </svg>
                                    <span class="font-mono font-bold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">HackerRank Campus</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>
                            </div>

                            <!-- Duplicate for seamless infinite loop -->
                            <div class="flex items-center gap-6 sm:gap-8 pr-6 sm:pr-8 shrink-0" aria-hidden="true">
                                <a href="https://github.com/edu" tabindex="-1" target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   >
                                    <svg class="w-4 h-4 shrink-0 text-slate-800 group-hover:text-ink transition-colors" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
    </svg>
                                    <span class="font-mono font-bold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">GitHub Education</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>

                                <a href="https://www.youtube.com/watch?v=earTjdzDC2s" tabindex="-1" target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   >
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M10 5L4.5 10.5a2.1 2.1 0 000 3L10 19l2.5-2.5-4-4 4-4L10 5z" fill="#4285F4"/>
        <path d="M10 5L4.5 10.5l4 4 4-4L10 5z" fill="#EA4335"/>
        <path d="M14 5l5.5 5.5a2.1 2.1 0 010 3L14 19l-2.5-2.5 4-4-4-4L14 5z" fill="#FBBC04"/>
        <path d="M14 5l5.5 5.5-4 4-4-4L14 5z" fill="#0F9D58"/>
    </svg>
                                    <span class="font-semibold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">Google DSC</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-rose-700 bg-rose-50 border border-rose-200/60 group-hover:bg-rose-100/70 px-1.5 py-0.5 rounded transition-colors">
            <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            <span>Video</span>
        </span>
                                </a>

                                <a href="https://www.youtube.com/watch?v=0kH8s3G8Z0g" tabindex="-1" target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   >
                                    <svg class="w-4 h-4 shrink-0 text-slate-800 group-hover:text-ink transition-colors" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M19.581 16.851H24v-4.439ZM24 3.574h-4.419v4.42l-4.419 4.418v4.44h4.419v-4.44L24 7.993Zm-4.419 0h-4.419v4.42zm-6.324 8.838H4.419l8.838-8.838H8.838L0 12.412v3.595h8.838v4.419h4.419z"/>
    </svg>
                                    <span class="font-mono font-bold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">42 Network</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-rose-700 bg-rose-50 border border-rose-200/60 group-hover:bg-rose-100/70 px-1.5 py-0.5 rounded transition-colors">
            <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            <span>Video</span>
        </span>
                                </a>

                                <a href="https://vietai.org/" tabindex="-1" target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   >
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <circle cx="12" cy="12" r="11" fill="#FFF1F2" stroke="#FECDD3" stroke-width="0.8"/>
        <path d="M12 4l2.4 5.3 5.6.8-4.1 4 1 5.9-4.9-2.8-4.9 2.8 1-5.9-4.1-4 5.6-.8L12 4z" fill="#E11D48"/>
        <circle cx="12" cy="4" r="1.3" fill="#FBBF24"/>
        <circle cx="20" cy="10.1" r="1.3" fill="#00A99A"/>
        <circle cx="17" cy="20" r="1.3" fill="#FBBF24"/>
        <circle cx="7" cy="20" r="1.3" fill="#00A99A"/>
        <circle cx="4" cy="10.1" r="1.3" fill="#FBBF24"/>
        <circle cx="12" cy="12" r="1.5" fill="#FFFFFF"/>
    </svg>
                                    <span class="font-mono font-bold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">VietAI</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>

                                <a href="https://aws.amazon.com/developer/community/students/" tabindex="-1" target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   >
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <text x="1" y="11" font-family="system-ui, -apple-system, sans-serif" font-size="9.5" font-weight="900" fill="#252F3E" letter-spacing="-0.3">aws</text>
        <path d="M2 14c4.5 3.2 11.5 3.2 16 0" stroke="#FF9900" stroke-width="1.8" stroke-linecap="round" fill="none"/>
        <path d="M17 12.8l2 2.2-2.8 1" fill="#FF9900"/>
    </svg>
                                    <span class="font-mono font-bold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">AWS Cloud Clubs</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>

                                <a href="https://www.youtube.com/@MicrosoftLearn" tabindex="-1" target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   >
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 21 21" fill="none" aria-hidden="true">
        <rect x="1" y="1" width="9" height="9" fill="#F25022"/>
        <rect x="11" y="1" width="9" height="9" fill="#7FBA00"/>
        <rect x="1" y="11" width="9" height="9" fill="#00A4EF"/>
        <rect x="11" y="11" width="9" height="9" fill="#FFB900"/>
    </svg>
                                    <span class="font-semibold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">Microsoft Learn</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-rose-700 bg-rose-50 border border-rose-200/60 group-hover:bg-rose-100/70 px-1.5 py-0.5 rounded transition-colors">
            <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            <span>Video</span>
        </span>
                                </a>

                                <a href="https://www.coderschool.vn/en" tabindex="-1" target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   >
                                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <circle cx="12" cy="12" r="11" fill="#D9383A"/>
        <circle cx="12" cy="12" r="9.5" stroke="#FFFFFF" stroke-width="0.7" fill="none"/>
        <path d="M14.5 7h-2c-1.5 0-2.5.8-2.5 2.5v1c0 .8-.5 1.5-1.5 1.5 1 0 1.5.7 1.5 1.5v1c0 1.7 1 2.5 2.5 2.5h2" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    </svg>
                                    <span class="font-semibold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">CoderSchool</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>

                                <a href="https://www.hackerrank.com/skills-verification" tabindex="-1" target="_blank" rel="noopener noreferrer"
                                   class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-surface hover:bg-surface border border-line hover:border-brand/50 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 ease-out shrink-0 cursor-pointer"
                                   >
                                    <svg class="w-4 h-4 shrink-0 text-[#2EC866]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M0 0v24h24V0H0zm9.95 8h1.8v7.8h-1.8v-2.9H7.9v2.9H6V8.1h1.9v2.9h2.05V8zm3 0h5.8v7.8h-5.8V8z"/>
    </svg>
                                    <span class="font-mono font-bold text-xs tracking-tight text-ink group-hover:text-brand transition-colors">HackerRank Campus</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium tracking-wide text-muted bg-slate-50 border border-line/80 group-hover:bg-brand-soft group-hover:text-brand group-hover:border-teal-200/60 px-1.5 py-0.5 rounded transition-colors">
            <span>Website</span>
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
        </section>

        <!-- 2. Lifecycle Section ("Accelerating the Community Learning Lifecycle") -->
        <section id="features"
                 x-data="{
                    activePhase: 'build',
                    phases: {
                        build: {
                            name: 'Build',
                            title: 'Build structured courses fast with or without code. Own your curriculum.',
                            bullets: [
                                { verb: 'Start', text: 'with proven cohort blueprints, customize lesson structure when you need control' },
                                { verb: 'Own', text: 'your course outline, video hosting, member access, and community identity' },
                                { verb: 'Enable', text: 'students to submit verifiable deliverables and assignments naturally' }
                            ],
                            badges: ['Classrooms', 'Video Modules', 'Curriculum Outline', 'Cohort Blueprints'],
                            telemetryTitle: 'Proof-Of-Work Curriculum Builder',
                            sessionId: '#POW-BUILD-01',
                            traces: [
                                { tag: 'INIT', tagClass: 'bg-brand-soft text-brand', label: 'CURRICULUM_INIT', time: '8ms', text: 'Creator @lead published cohort blueprint: Fullstack AI Architecture.' },
                                { tag: 'OUTLINE', tagClass: 'bg-blue-50 text-blue-700', label: 'SECTION_COMPOSE', time: '14ms', text: 'Structured 6 milestone modules with video embeds & reading notes.' },
                                { tag: 'RUBRIC', tagClass: 'bg-amber-50 text-amber-700', label: 'EVAL_ATTACH', time: '22ms', text: 'Attached peer review rubric with mandatory repository verification.' },
                                { tag: 'TIER', tagClass: 'bg-emerald-50 text-emerald-700', label: 'ACCESS_RESTRICT', time: '11ms', text: 'Applied cohort membership gating: Cohort Pioneer Tier required.' },
                                { tag: 'DEPLOY', tagClass: 'bg-brand text-white', label: 'WORKSPACE_LIVE', time: '30ms', text: 'Curriculum activated. Ready for member enrollment and milestone submissions.' }
                            ]
                        },
                        test: {
                            name: 'Test',
                            title: 'Validate student submissions before awarding milestone credentials.',
                            bullets: [
                                { verb: 'Debug', text: 'assignment submissions with structured criteria and automated checks' },
                                { verb: 'Query', text: 'student progress across hundreds of cohort members in seconds' },
                                { verb: 'Turn', text: 'expert mentor reviews into standardized scoring rubrics' }
                            ],
                            badges: ['Proof of Work', 'Rubric Scoring', 'Automated Assertions', 'Mentor Queue'],
                            telemetryTitle: 'Submission Validation Suite',
                            sessionId: '#POW-TEST-02',
                            traces: [
                                { tag: 'SUBMIT', tagClass: 'bg-brand-soft text-brand', label: 'INTAKE_VERIFY', time: '12ms', text: 'Student @vietanh submitted milestone #4 repository URL.' },
                                { tag: 'STATIC', tagClass: 'bg-blue-50 text-blue-700', label: 'CODE_ANALYSIS', time: '28ms', text: 'Static lint check PASS: 0 syntax issues, correct file conventions.' },
                                { tag: 'ASSERT', tagClass: 'bg-amber-50 text-amber-700', label: 'TEST_RUNNER', time: '142ms', text: 'Automated test suite execution: 24/24 assertions passed (100% green).' },
                                { tag: 'RUBRIC', tagClass: 'bg-emerald-50 text-emerald-700', label: 'PEER_SCORE', time: '34ms', text: 'Mentor review criteria met. Score calculated: 98.5/100 points.' },
                                { tag: 'PASS', tagClass: 'bg-brand text-white', label: 'MILESTONE_ACCEPTED', time: '40ms', text: 'Submission approved. Emitted verifiable credential signature.' }
                            ]
                        },
                        deploy: {
                            name: 'Deploy',
                            title: 'Ship community spaces on infrastructure built for long-running learning cohorts.',
                            bullets: [
                                { verb: 'Build', text: 'the right cohort experience with customizable role permissions' },
                                { verb: 'Standardize', text: 'on unified membership access and gated classroom tiers' },
                                { verb: 'Run', text: 'live workshop events and private discussion channels securely' }
                            ],
                            badges: ['Role-based Tiers', 'Event Calendars', 'Direct Messages', 'Private Spaces'],
                            telemetryTitle: 'Cohort Infrastructure Gateway',
                            sessionId: '#POW-DEPLOY-03',
                            traces: [
                                { tag: 'SPACES', tagClass: 'bg-brand-soft text-brand', label: 'CHANNEL_PROVISION', time: '15ms', text: 'Allocated 4 private discussion categories and 1 pinned announcement feed.' },
                                { tag: 'ROLES', tagClass: 'bg-blue-50 text-blue-700', label: 'RBAC_ENFORCE', time: '19ms', text: 'Configured roles: Mentor, Teaching Assistant, Cohort Member, Guest.' },
                                { tag: 'CALENDAR', tagClass: 'bg-amber-50 text-amber-700', label: 'EVENT_DISPATCH', time: '24ms', text: 'Scheduled weekly live workshop event with timezone auto-conversion.' },
                                { tag: 'MEMBER', tagClass: 'bg-emerald-50 text-emerald-700', label: 'ONBOARD_QUEUE', time: '32ms', text: 'Invited 120 initial members. Welcome workflow activated.' },
                                { tag: 'LIVE', tagClass: 'bg-brand text-white', label: 'COMMUNITY_ONLINE', time: '45ms', text: 'Community active with sub-second websocket notification delivery.' }
                            ]
                        },
                        monitor: {
                            name: 'Monitor',
                            title: 'Catch student hurdles with online progress dashboards and drop-off alerts.',
                            bullets: [
                                { verb: 'Measure', text: 'completion rates across video lessons and quizzes' },
                                { verb: 'Catch', text: 'student disengagement before milestones are missed' },
                                { verb: 'Track', text: 'leaderboard points, active hours, and community interactions' }
                            ],
                            badges: ['Leaderboard Engine', 'Progress Analytics', 'Engagement Scores', 'Activity Feeds'],
                            telemetryTitle: 'Telemetry & Engagement Monitor',
                            sessionId: '#POW-MONITOR-04',
                            traces: [
                                { tag: 'METRICS', tagClass: 'bg-brand-soft text-brand', label: 'DAILY_ACTIVE', time: '10ms', text: 'Aggregate daily study time: 1,420 hours across active cohorts.' },
                                { tag: 'ALERT', tagClass: 'bg-blue-50 text-blue-700', label: 'DROP_OFF_DETECT', time: '16ms', text: '3 students flagged for inactivity on Lesson 5. Nudge email queued.' },
                                { tag: 'POINTS', tagClass: 'bg-amber-50 text-amber-700', label: 'LEADERBOARD_SYNC', time: '28ms', text: 'Updated weekly rankings. 450 total reputation points awarded.' },
                                { tag: 'HEALTH', tagClass: 'bg-emerald-50 text-emerald-700', label: 'COMMUNITY_VITALS', time: '35ms', text: 'Discussion response rate: 94% within 2 hours. Mentor ratio optimal.' },
                                { tag: 'STATUS', tagClass: 'bg-brand text-white', label: 'DASHBOARD_FRESH', time: '50ms', text: 'Creator analytics refreshed with 0 latency deviations.' }
                            ]
                        },
                        iterate: {
                            name: 'Iterate',
                            title: 'Compound community knowledge through continuous feedback loops.',
                            bullets: [
                                { verb: 'Detect', text: 'curriculum bottlenecks automatically with student feedback metrics' },
                                { verb: 'Find', text: 'root causes of concept confusion and update lessons instantly' },
                                { verb: 'Feed', text: 'every student review back into curriculum improvement cycles' }
                            ],
                            badges: ['Discussion Threads', 'Content Reports', 'Creator Notes', 'Knowledge Base'],
                            telemetryTitle: 'Knowledge Compounding Engine',
                            sessionId: '#POW-ITERATE-05',
                            traces: [
                                { tag: 'FEEDBACK', tagClass: 'bg-brand-soft text-brand', label: 'SENTIMENT_SCAN', time: '14ms', text: 'Student discussion analysis identified friction in Docker deployment task.' },
                                { tag: 'UPDATE', tagClass: 'bg-blue-50 text-blue-700', label: 'LESSON_REVISE', time: '20ms', text: 'Creator updated Lesson 3 notes with troubleshooting guide.' },
                                { tag: 'RE_EVAL', tagClass: 'bg-amber-50 text-amber-700', label: 'RUBRIC_POLISH', time: '26ms', text: 'Adjusted grading rubric assertions for Windows path compatibility.' },
                                { tag: 'SYNC', tagClass: 'bg-emerald-50 text-emerald-700', label: 'PEER_BENCHMARK', time: '38ms', text: 'Average completion time decreased by 35% after revision.' },
                                { tag: 'CYCLE', tagClass: 'bg-brand text-white', label: 'KNOWLEDGE_COMPOUNDED', time: '48ms', text: 'Knowledge base updated with resolved student FAQ items.' }
                            ]
                        },
                        govern: {
                            name: 'Govern',
                            title: 'Control membership tiers and sandbox payments with transparent records.',
                            bullets: [
                                { verb: 'Control', text: 'membership invitations, seating limits, and member approval queues' },
                                { verb: 'Require', text: 'creator approval for sensitive assignments and certification' },
                                { verb: 'Simulate', text: 'SePay sandbox IPN checkouts with idempotent audit logs' }
                            ],
                            badges: ['SePay Sandbox', 'Audit Logs', 'Admin Policies', 'Idempotent IPN'],
                            telemetryTitle: 'Governance & Payment Audit Engine',
                            sessionId: '#POW-GOVERN-06',
                            traces: [
                                { tag: 'POLICY', tagClass: 'bg-brand-soft text-brand', label: 'ACCESS_GATE', time: '11ms', text: 'Member invitation verified. Seat allocation 45/50 claimed.' },
                                { tag: 'WEBHOOK', tagClass: 'bg-blue-50 text-blue-700', label: 'SEPAY_IPN_INTAKE', time: '18ms', text: 'Received simulated sandbox IPN payload: Order #ORD-8921.' },
                                { tag: 'IDEMPOTENT', tagClass: 'bg-amber-50 text-amber-700', label: 'DUPLICATE_GUARD', time: '25ms', text: 'Transaction signature unique. Idempotency key verified.' },
                                { tag: 'UNLOCK', tagClass: 'bg-emerald-50 text-emerald-700', label: 'TIER_ACTIVATED', time: '34ms', text: 'Unlocked Premium Cohort membership. Audit log recorded.' },
                                { tag: 'SECURE', tagClass: 'bg-brand text-white', label: 'COMPLIANCE_VERIFIED', time: '44ms', text: 'All security boundaries, role permissions and ledger states verified.' }
                            ]
                        }
                    }
                 }"
                 class="py-20 md:py-28 bg-surface border-y border-line">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft text-brand border border-brand/20">
                        End-to-End Learning Architecture
                    </span>
                    <h2 class="text-3xl sm:text-5xl font-bold tracking-tight text-ink font-sans">
                        Accelerating the Community Learning Lifecycle
                    </h2>
                    <p class="text-muted text-base sm:text-lg">
                        From curriculum conception to certified mastery, S-cool provides a complete operating system for private cohort education.
                    </p>
                </div>

                <!-- Two-Column Interactive Lifecycle View -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <!-- Left: Step Selector & Description -->
                    <div class="lg:col-span-6 space-y-8">
                        <!-- Step Buttons -->
                        <div class="flex flex-wrap gap-2 p-1.5 bg-slate-100/70 rounded-xl border border-line">
                            <button type="button"
                                    @click="activePhase = 'build'"
                                    :class="activePhase === 'build' ? 'bg-brand text-white font-semibold shadow-xs' : 'text-muted hover:text-ink'"
                                    class="px-4 py-2 rounded-lg text-xs transition-all">Build</button>
                            <button type="button"
                                    @click="activePhase = 'test'"
                                    :class="activePhase === 'test' ? 'bg-brand text-white font-semibold shadow-xs' : 'text-muted hover:text-ink'"
                                    class="px-4 py-2 rounded-lg text-xs transition-all">Test</button>
                            <button type="button"
                                    @click="activePhase = 'deploy'"
                                    :class="activePhase === 'deploy' ? 'bg-brand text-white font-semibold shadow-xs' : 'text-muted hover:text-ink'"
                                    class="px-4 py-2 rounded-lg text-xs transition-all">Deploy</button>
                            <button type="button"
                                    @click="activePhase = 'monitor'"
                                    :class="activePhase === 'monitor' ? 'bg-brand text-white font-semibold shadow-xs' : 'text-muted hover:text-ink'"
                                    class="px-4 py-2 rounded-lg text-xs transition-all">Monitor</button>
                            <button type="button"
                                    @click="activePhase = 'iterate'"
                                    :class="activePhase === 'iterate' ? 'bg-brand text-white font-semibold shadow-xs' : 'text-muted hover:text-ink'"
                                    class="px-4 py-2 rounded-lg text-xs transition-all">Iterate</button>
                            <button type="button"
                                    @click="activePhase = 'govern'"
                                    :class="activePhase === 'govern' ? 'bg-brand text-white font-semibold shadow-xs' : 'text-muted hover:text-ink'"
                                    class="px-4 py-2 rounded-lg text-xs transition-all">Govern</button>
                        </div>

                        <!-- Active Step Content -->
                        <div class="space-y-6">
                            <div class="space-y-3">
                                <h3 class="text-2xl sm:text-3xl font-bold text-ink tracking-tight" x-text="phases[activePhase].name"></h3>
                                <p class="text-base text-muted leading-relaxed" x-text="phases[activePhase].title"></p>
                            </div>

                            <!-- Bullet Points with Bold Action Verbs -->
                            <ul class="space-y-3.5">
                                <template x-for="bullet in phases[activePhase].bullets" :key="bullet.verb">
                                    <li class="flex items-start gap-3">
                                        <div class="w-5 h-5 rounded-full bg-brand-soft text-brand flex items-center justify-center shrink-0 mt-0.5">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div class="text-sm text-ink leading-normal">
                                            <strong class="font-semibold text-brand" x-text="bullet.verb"></strong>
                                            <span class="text-muted" x-text="bullet.text"></span>
                                        </div>
                                    </li>
                                </template>
                            </ul>

                            <!-- Badge tags -->
                            <div class="pt-4 flex flex-wrap gap-2">
                                <template x-for="badge in phases[activePhase].badges" :key="badge">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-50 text-slate-700 border border-line">
                                        <span class="w-1.5 h-1.5 rounded-full bg-brand"></span>
                                        <span x-text="badge"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Dynamic Live Telemetry / Execution Preview Card -->
                    <div class="lg:col-span-6">
                        <div class="bg-surface rounded-xl border border-line shadow-sm overflow-hidden">
                            <!-- Telemetry Card Header -->
                            <div class="bg-slate-50/80 px-4 py-3 border-b border-line flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="text-xs font-mono font-semibold text-ink" x-text="phases[activePhase].telemetryTitle"></span>
                                </div>
                                <span class="text-[11px] font-mono text-muted bg-white px-2 py-0.5 rounded border border-line" x-text="phases[activePhase].sessionId"></span>
                            </div>

                            <!-- Execution Trace Steps -->
                            <div class="p-5 space-y-3 font-mono text-xs">
                                <template x-for="(trace, tIdx) in phases[activePhase].traces" :key="trace.label">
                                    <div class="p-3 rounded-lg bg-slate-50 border border-line/80 space-y-1 transition-all">
                                        <div class="flex items-center justify-between text-[11px]">
                                            <div class="flex items-center gap-2">
                                                <span :class="trace.tagClass" class="px-1.5 py-0.5 rounded font-bold text-[10px]" x-text="trace.tag"></span>
                                                <span class="text-slate-700 font-semibold" x-text="trace.label"></span>
                                            </div>
                                            <span class="text-muted" x-text="trace.time"></span>
                                        </div>
                                        <p class="text-slate-600 font-sans text-xs" x-text="trace.text"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. Frameworks Grid ("Build with our open source frameworks") -->
        <section class="py-20 md:py-28 bg-canvas">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end mb-14">
                    <div class="md:col-span-7 space-y-2">
                        <span class="text-xs uppercase tracking-wider font-semibold text-brand">Modular Architecture</span>
                        <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-ink font-sans">
                            Built for creators, mentors, and cohorts
                        </h2>
                    </div>
                    <div class="md:col-span-5 text-muted text-sm sm:text-base leading-relaxed">
                        Build engaging spaces fast with complete control. Choose the right workflow for the job, from zero-code templates to programmatic APIs.
                    </div>
                </div>

                <!-- Exactly 3 Framework Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Card 1: Community Engine -->
                    <div x-data="{ copied: false, code: `Community::create([\n    'name' => 'AI Pioneers',\n    'governance' => 'moderated'\n]);` }"
                         class="bg-surface rounded-xl border border-line p-6 shadow-xs hover:shadow-md hover:border-brand/40 transition-all flex flex-col justify-between space-y-6">
                        <div class="space-y-4">
                            <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-700 flex items-center justify-center border border-sky-100">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <div class="space-y-1">
                                <span class="text-xs font-mono font-semibold text-brand uppercase">community engine</span>
                                <h3 class="text-lg font-bold text-ink">Private Cohort Communities</h3>
                                <p class="text-xs text-muted leading-relaxed">
                                    For highly autonomous, long-running learning spaces with discussion categories and member roles.
                                </p>
                            </div>

                            <!-- Styled Code Widget with Copy Button -->
                            <div class="relative group/code">
                                <div class="bg-slate-50 border border-line rounded-lg p-3 text-[11px] font-mono text-ink overflow-x-auto pr-16">
                                    <span class="text-brand font-semibold">Community</span>::create([<br/>
                                    &nbsp;&nbsp;<span class="text-reward">'name'</span> =&gt; <span class="text-emerald-700">'AI Pioneers'</span>,<br/>
                                    &nbsp;&nbsp;<span class="text-reward">'governance'</span> =&gt; <span class="text-emerald-700">'moderated'</span><br/>
                                    ]);
                                </div>
                                <button type="button"
                                        @click="navigator.clipboard.writeText(code); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="absolute top-2 right-2 px-2 py-1 bg-surface border border-line rounded text-[10px] font-medium text-muted hover:text-ink shadow-xs opacity-80 hover:opacity-100 transition-opacity flex items-center gap-1"
                                        aria-label="Copy snippet">
                                    <span x-show="!copied">Copy</span>
                                    <span x-show="copied" x-cloak class="text-brand font-semibold">✓ Copied</span>
                                </button>
                            </div>
                        </div>

                        <a href="{{ route('communities.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand hover:text-brand-hover">
                            <span>Explore community engine</span>
                            <span class="text-sm">&rarr;</span>
                        </a>
                    </div>

                    <!-- Card 2: Classroom Suite -->
                    <div x-data="{ copied: false, code: `$course->addSection('Tracing')\n    ->attachLesson('Sandboxes')\n    ->requireProofOfWork();` }"
                         class="bg-surface rounded-xl border border-line p-6 shadow-xs hover:shadow-md hover:border-brand/40 transition-all flex flex-col justify-between space-y-6">
                        <div class="space-y-4">
                            <div class="w-12 h-12 rounded-xl bg-brand-soft text-brand flex items-center justify-center border border-brand/20">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                            <div class="space-y-1">
                                <span class="text-xs font-mono font-semibold text-brand uppercase">classroom suite</span>
                                <h3 class="text-lg font-bold text-ink">Interactive Classroom</h3>
                                <p class="text-xs text-muted leading-relaxed">
                                    For building courses fast with video modules, rich markdown lessons, and persistent completion state.
                                </p>
                            </div>

                            <!-- Styled Code Widget with Copy Button -->
                            <div class="relative group/code">
                                <div class="bg-slate-50 border border-line rounded-lg p-3 text-[11px] font-mono text-ink overflow-x-auto pr-16">
                                    <span class="text-brand font-semibold">$course</span>-&gt;addSection(<span class="text-emerald-700">'Tracing'</span>)<br/>
                                    &nbsp;&nbsp;-&gt;attachLesson(<span class="text-emerald-700">'Sandboxes'</span>)<br/>
                                    &nbsp;&nbsp;-&gt;requireProofOfWork();
                                </div>
                                <button type="button"
                                        @click="navigator.clipboard.writeText(code); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="absolute top-2 right-2 px-2 py-1 bg-surface border border-line rounded text-[10px] font-medium text-muted hover:text-ink shadow-xs opacity-80 hover:opacity-100 transition-opacity flex items-center gap-1"
                                        aria-label="Copy snippet">
                                    <span x-show="!copied">Copy</span>
                                    <span x-show="copied" x-cloak class="text-brand font-semibold">✓ Copied</span>
                                </button>
                            </div>
                        </div>

                        <a href="{{ route('communities.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand hover:text-brand-hover">
                            <span>Explore classroom suite</span>
                            <span class="text-sm">&rarr;</span>
                        </a>
                    </div>

                    <!-- Card 3: Proof of Work & Leaderboards -->
                    <div x-data="{ copied: false, code: `ProofOfWork::submit([\n    'student' => $user->id,\n    'url' => 'github.com/demo'\n])->evaluate();` }"
                         class="bg-surface rounded-xl border border-line p-6 shadow-xs hover:shadow-md hover:border-brand/40 transition-all flex flex-col justify-between space-y-6">
                        <div class="space-y-4">
                            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center border border-purple-100">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </div>
                            <div class="space-y-1">
                                <span class="text-xs font-mono font-semibold text-purple-700 uppercase">proof of work</span>
                                <h3 class="text-lg font-bold text-ink">Proof of Work Engine</h3>
                                <p class="text-xs text-muted leading-relaxed">
                                    For milestone reviews that require deterministic verification, peer rubrics, and earned level rewards.
                                </p>
                            </div>

                            <!-- Styled Code Widget with Copy Button -->
                            <div class="relative group/code">
                                <div class="bg-slate-50 border border-line rounded-lg p-3 text-[11px] font-mono text-ink overflow-x-auto pr-16">
                                    <span class="text-brand font-semibold">ProofOfWork</span>::submit([<br/>
                                    &nbsp;&nbsp;<span class="text-reward">'student'</span> =&gt; <span class="text-slate-700">$user</span>-&gt;id,<br/>
                                    &nbsp;&nbsp;<span class="text-reward">'url'</span> =&gt; <span class="text-emerald-700">'github.com/demo'</span><br/>
                                    ])-&gt;evaluate();
                                </div>
                                <button type="button"
                                        @click="navigator.clipboard.writeText(code); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="absolute top-2 right-2 px-2 py-1 bg-surface border border-line rounded text-[10px] font-medium text-muted hover:text-ink shadow-xs opacity-80 hover:opacity-100 transition-opacity flex items-center gap-1"
                                        aria-label="Copy snippet">
                                    <span x-show="!copied">Copy</span>
                                    <span x-show="copied" x-cloak class="text-brand font-semibold">✓ Copied</span>
                                </button>
                            </div>
                        </div>

                        <a href="{{ route('communities.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand hover:text-brand-hover">
                            <span>Explore proof of work</span>
                            <span class="text-sm">&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. Customer Stories Carousel ("Learn from teams running cohorts on S-cool") with Smooth Slide Transition -->
        <section x-data="{
                    activeStory: 0,
                    isTransitioning: true,
                    isPaused: false,
                    autoplayTimer: null,
                    stories: [
                        { company: 'Klarna', badge: 'Financial Tech', quote: 'Klarna\'s AI academy reduced student onboarding time by 80% with S-cool Classroom', metric: '80% Reduction' },
                        { company: 'Monday.com', badge: 'Work OS', quote: 'Monday Service achieved 8.7x faster milestone feedback loops with peer reviews', metric: '8.7x Faster' },
                        { company: 'Podium', badge: 'Communications', quote: 'Podium reduced mentor escalation and duplicate questions by 90% with structured discussions', metric: '90% Drop' },
                        { company: 'C.H. Robinson', badge: 'Logistics', quote: 'Automated 5,500 assignment submissions per cohort, saving 600+ hours daily with rubrics', metric: '600+ Hours' },
                        { company: 'ServiceNow', badge: 'Enterprise', quote: 'Orchestrated technical certification across 8 structured milestones with transparent leaderboards', metric: '8 Stages' }
                    ],
                    get allCards() {
                        return [...this.stories, ...this.stories.slice(0, 3)];
                    },
                    next() {
                        if (!this.isTransitioning) return;
                        this.activeStory++;
                        if (this.activeStory === this.stories.length) {
                            setTimeout(() => {
                                this.isTransitioning = false;
                                this.activeStory = 0;
                                this.$nextTick(() => {
                                    requestAnimationFrame(() => {
                                        this.isTransitioning = true;
                                    });
                                });
                            }, 500);
                        }
                    },
                    prev() {
                        if (!this.isTransitioning) return;
                        if (this.activeStory === 0) {
                            this.isTransitioning = false;
                            this.activeStory = this.stories.length;
                            this.$nextTick(() => {
                                requestAnimationFrame(() => {
                                    this.isTransitioning = true;
                                    this.activeStory = this.stories.length - 1;
                                });
                            });
                        } else {
                            this.activeStory--;
                        }
                    },
                    goTo(idx) {
                        this.isTransitioning = true;
                        this.activeStory = idx;
                    },
                    startAutoplay() {
                        this.stopAutoplay();
                        this.autoplayTimer = setInterval(() => {
                            if (!this.isPaused) this.next();
                        }, 6000);
                    },
                    stopAutoplay() {
                        if (this.autoplayTimer) clearInterval(this.autoplayTimer);
                    }
                 }"
                 x-init="startAutoplay()"
                 @mouseenter="isPaused = true"
                 @mouseleave="isPaused = false"
                 class="py-20 md:py-28 bg-surface border-t border-line">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header with Title & Action Button -->
                <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between gap-4 mb-12">
                    <div class="space-y-2">
                        <span class="text-xs font-semibold text-brand uppercase tracking-wider">Success Stories</span>
                        <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-ink font-sans">
                            Learn from teams running cohorts on S-cool
                        </h2>
                    </div>
                    <a href="{{ route('help.index') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-surface hover:bg-slate-50 text-ink border border-line text-xs font-semibold rounded-lg shadow-xs transition-colors shrink-0">
                        <span>More customer stories</span>
                        <svg class="w-3.5 h-3.5 text-muted" viewBox="0 0 16 16" fill="none"><path d="M4.61538 4V5.23077H9.90154L4 11.1323L4.86769 12L10.7692 6.09846V11.3846H12V4H4.61538Z" fill="currentColor"/></svg>
                    </a>
                </div>

                <!-- Scoped Responsive Slide Step Definition -->
                <style>
                    .stories-carousel-track {
                        --story-step: calc(100% + 1.5rem);
                    }
                    @media (min-width: 768px) {
                        .stories-carousel-track {
                            --story-step: calc((100% + 1.5rem) / 3);
                        }
                    }
                </style>

                <!-- Smooth Sliding Carousel Viewport -->
                <div class="overflow-hidden py-2 -mx-1 px-1">
                    <div class="stories-carousel-track flex gap-6 will-change-transform"
                         :class="isTransitioning ? 'transition-transform duration-500 ease-out' : 'transition-none'"
                         :style="`transform: translate3d(calc(-1 * ${activeStory} * var(--story-step)), 0, 0);`">
                        <template x-for="(story, idx) in allCards" :key="idx">
                            <div class="w-full md:w-[calc((100%-3rem)/3)] shrink-0 bg-canvas rounded-xl border border-line p-6 shadow-xs flex flex-col justify-between space-y-6 hover:border-brand/40 hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200">
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-base text-ink tracking-tight" x-text="story.company"></span>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-brand-soft text-brand border border-brand/20" x-text="story.badge"></span>
                                    </div>
                                    <p class="text-sm text-slate-700 font-medium leading-relaxed" x-text="story.quote"></p>
                                </div>

                                <div class="pt-4 border-t border-line/80 flex items-center justify-between">
                                    <span class="text-xs font-bold text-brand" x-text="story.metric"></span>
                                    <a href="{{ route('help.index') }}" class="text-xs font-semibold text-muted hover:text-brand inline-flex items-center gap-1 transition-colors">
                                        <span>Read Use Case</span>
                                        <span class="text-sm">&nearr;</span>
                                    </a>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Carousel Controls / Interactive Dots & Arrows with Smooth Transitions -->
                <div class="mt-8 flex items-center justify-between">
                    <!-- Dynamic Dots Indicator with Width Transition -->
                    <div class="flex items-center gap-2">
                        <template x-for="(story, idx) in stories" :key="idx">
                            <button type="button"
                                    @click="goTo(idx)"
                                    :class="(activeStory % stories.length) === idx ? 'w-6 bg-brand' : 'w-2 bg-slate-300 hover:bg-slate-400'"
                                    class="h-2 rounded-full transition-all duration-300 focus:outline-none cursor-pointer"
                                    :aria-label="'Go to slide ' + (idx + 1)">
                            </button>
                        </template>
                    </div>

                    <!-- Previous & Next Action Buttons with Hover Transitions -->
                    <div class="flex items-center gap-2">
                        <button type="button"
                                @click="prev()"
                                class="w-8 h-8 rounded-lg border border-line bg-surface text-muted hover:text-brand hover:border-brand/40 hover:bg-brand-soft flex items-center justify-center shadow-xs transition-all duration-150 active:scale-95 cursor-pointer"
                                aria-label="Previous story">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button type="button"
                                @click="next()"
                                class="w-8 h-8 rounded-lg border border-line bg-surface text-muted hover:text-brand hover:border-brand/40 hover:bg-brand-soft flex items-center justify-center shadow-xs transition-all duration-150 active:scale-95 cursor-pointer"
                                aria-label="Next story">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. Metrics & Scale Section ("Trusted by the largest builder community in AI") with Smooth Count-Up Animation -->
        <section x-data="{
                    rafId: null,
                    stat1: 0,
                    stat2: 0,
                    stat3: 0,
                    startCountUp() {
                        if (this.rafId) {
                            cancelAnimationFrame(this.rafId);
                        }
                        this.stat1 = 0;
                        this.stat2 = 0;
                        this.stat3 = 0;

                        const duration = 1600;
                        const startTime = performance.now();
                        const easeOutCubic = t => 1 - Math.pow(1 - t, 3);

                        const frame = (now) => {
                            const elapsed = now - startTime;
                            const progress = Math.min(elapsed / duration, 1);
                            const eased = easeOutCubic(progress);

                            this.stat1 = Math.round(eased * 350);
                            this.stat2 = Math.round(eased * 7);
                            this.stat3 = Math.round(eased * 5);

                            if (progress < 1) {
                                this.rafId = requestAnimationFrame(frame);
                            } else {
                                this.stat1 = 350;
                                this.stat2 = 7;
                                this.stat3 = 5;
                                this.rafId = null;
                            }
                        };
                        this.rafId = requestAnimationFrame(frame);
                    },
                    reset() {
                        if (this.rafId) {
                            cancelAnimationFrame(this.rafId);
                            this.rafId = null;
                        }
                        this.stat1 = 0;
                        this.stat2 = 0;
                        this.stat3 = 0;
                    },
                    init() {
                        if (typeof IntersectionObserver !== 'undefined') {
                            const observer = new IntersectionObserver((entries) => {
                                entries.forEach(entry => {
                                    if (entry.isIntersecting) {
                                        this.startCountUp();
                                    } else {
                                        this.reset();
                                    }
                                });
                            }, { threshold: 0.15 });
                            observer.observe(this.$el);
                        } else {
                            this.stat1 = 350;
                            this.stat2 = 7;
                            this.stat3 = 5;
                        }
                    }
                 }"
                 class="py-20 md:py-28 bg-canvas border-t border-line scroll-mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                    <span class="text-xs font-semibold text-brand uppercase tracking-wider">Proven Scale</span>
                    <h2 class="text-3xl sm:text-5xl font-bold tracking-tight text-ink font-sans">
                        Trusted by the largest community of builders & learners
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Metric 1: Monthly Active Study Hours -->
                    <div class="bg-surface rounded-xl border border-line p-8 text-center shadow-xs hover:shadow-md hover:border-brand/40 transition-all space-y-2">
                        <div class="text-5xl sm:text-6xl font-black text-brand tracking-tight font-sans">
                            <span x-text="stat1 + 'K+'">350K+</span>
                        </div>
                        <div class="text-sm text-muted font-medium">Monthly active study & discussion hours</div>
                    </div>

                    <!-- Metric 2: Active Communities -->
                    <div class="bg-surface rounded-xl border border-line p-8 text-center shadow-xs hover:shadow-md hover:border-brand/40 transition-all space-y-2">
                        <div class="text-5xl sm:text-6xl font-black text-brand tracking-tight font-sans">
                            <span x-text="stat2 + 'K+'">7K+</span>
                        </div>
                        <div class="text-sm text-muted font-medium">Active cohort communities & mentor workspaces</div>
                    </div>

                    <!-- Metric 3: Accelerator Adoption -->
                    <div class="bg-surface rounded-xl border border-line p-8 text-center shadow-xs hover:shadow-md hover:border-brand/40 transition-all space-y-2">
                        <div class="text-5xl sm:text-6xl font-black text-brand tracking-tight font-sans">
                            <span x-text="stat3">5</span> <span class="text-2xl text-muted font-normal">of 10</span>
                        </div>
                        <div class="text-sm text-muted font-medium">Leading tech accelerators use S-cool engines</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. Call to Action Banner ("Get started with LangSmith") -->
        <section class="py-16 md:py-24 bg-surface border-t border-line">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-gradient-to-br from-brand/5 via-brand-soft to-slate-50 border border-brand/20 rounded-2xl p-8 sm:p-14 text-center shadow-xs relative overflow-hidden">
                    <div class="relative max-w-2xl mx-auto space-y-6">
                        <h2 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-ink font-sans">
                            Get started with S-cool
                        </h2>
                        <p class="text-base sm:text-lg text-muted font-normal leading-relaxed">
                            Use S-cool, the community learning platform, to improve every step of the student journey from discussion to verified certification.
                        </p>

                        <div class="pt-4 flex flex-wrap items-center justify-center gap-3.5">
                            @auth
                                <a href="{{ route('communities.index') }}"
                                   class="inline-flex items-center justify-center gap-2 px-7 py-3.5 text-sm font-semibold text-white bg-brand hover:bg-brand-hover rounded-lg shadow-sm transition-all">
                                    <span>Go to Dashboard</span>
                                    <span>&rarr;</span>
                                </a>
                            @else
                                <a href="{{ route('register') }}"
                                   class="inline-flex items-center justify-center gap-2 px-7 py-3.5 text-sm font-semibold text-white bg-brand hover:bg-brand-hover rounded-lg shadow-sm transition-all">
                                    <span>Start building</span>
                                    <svg class="w-4 h-4" viewBox="0 0 16 16" fill="none"><path d="M11.9852 4L12 10.7704H10.9314V6.60741L10.9462 5.80741L4.74211 12L4 11.2444L10.2041 5.03704L9.47681 5.05185L5.21707 5.05185V4L11.9852 4Z" fill="currentColor"/></svg>
                                </a>
                                <a href="{{ route('communities.index') }}"
                                   class="inline-flex items-center justify-center gap-2 px-7 py-3.5 text-sm font-medium text-ink bg-surface hover:bg-slate-50 border border-line rounded-lg shadow-xs transition-all">
                                    <span>Explore communities</span>
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer x-data="{ email: '', subscribed: false }" class="bg-surface border-t border-line pt-16 pb-12 text-ink">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
            <!-- Newsletter & Brand Header Row -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start pb-12 border-b border-line">
                <div class="lg:col-span-5 space-y-3">
                    <a href="/" class="flex items-center gap-2.5">
                        <x-application-logo class="w-7 h-7 rounded-lg" />
                        <span class="font-bold text-lg text-ink">S-cool</span>
                    </a>
                    <p class="text-xs text-muted max-w-sm leading-relaxed">
                        The open platform for private cohort communities, structured curriculums, and verifiable peer feedback. Built for creators, trusted by learners.
                    </p>
                </div>

                <!-- Newsletter Subscription Form with Alpine feedback -->
                <div class="lg:col-span-7 space-y-3">
                    <div class="text-xs font-semibold text-ink">
                        Sign up for our newsletter to stay up to date
                    </div>
                    <div x-show="subscribed" x-cloak class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs">
                        ✓ Thank you! You have been subscribed to S-cool community updates.
                    </div>
                    <form x-show="!subscribed" @submit.prevent="if (email.trim()) { subscribed = true; email = ''; }" class="flex flex-col sm:flex-row gap-2 max-w-md">
                        <input type="email"
                               x-model="email"
                               placeholder="Your email"
                               required
                               class="px-3.5 py-2 rounded-lg bg-white border border-line text-xs text-ink placeholder-muted focus:outline-none focus:border-brand flex-1 transition-colors">
                        <button type="submit"
                                class="px-5 py-2 rounded-lg bg-brand hover:bg-brand-hover text-white text-xs font-semibold shadow-xs transition-colors shrink-0">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>

            <!-- Navigation Columns -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-xs">
                <!-- Products -->
                <div class="space-y-3">
                    <h4 class="font-semibold text-ink uppercase tracking-wider text-[11px]">Products</h4>
                    <ul class="space-y-2 text-muted">
                        <li><a href="{{ route('communities.index') }}" class="hover:text-ink transition-colors">Community Engine</a></li>
                        <li><a href="{{ route('communities.index') }}" class="hover:text-ink transition-colors">Interactive Classroom</a></li>
                        <li><a href="{{ route('communities.index') }}" class="hover:text-ink transition-colors">Proof of Work Engine</a></li>
                        <li><a href="{{ route('communities.index') }}" class="hover:text-ink transition-colors">Leaderboards & Levels</a></li>
                        <li><a href="{{ route('communities.index') }}" class="hover:text-ink transition-colors">SePay Sandbox Checkout</a></li>
                    </ul>
                </div>

                <!-- Resources -->
                <div class="space-y-3">
                    <h4 class="font-semibold text-ink uppercase tracking-wider text-[11px]">Resources</h4>
                    <ul class="space-y-2 text-muted">
                        <li><a href="{{ route('help.index') }}" class="hover:text-ink transition-colors">Documentation</a></li>
                        <li><a href="{{ route('help.index') }}" class="hover:text-ink transition-colors">Help Center</a></li>
                        <li><a href="{{ route('help.index') }}" class="hover:text-ink transition-colors">Cohort Blueprints</a></li>
                        <li><a href="{{ route('help.index') }}" class="hover:text-ink transition-colors">Community Guidelines</a></li>
                        <li><a href="{{ route('help.index') }}" class="hover:text-ink transition-colors">Changelog</a></li>
                    </ul>
                </div>

                <!-- Company -->
                <div class="space-y-3">
                    <h4 class="font-semibold text-ink uppercase tracking-wider text-[11px]">Company</h4>
                    <ul class="space-y-2 text-muted">
                        <li><a href="{{ route('help.index') }}" class="hover:text-ink transition-colors">About S-cool</a></li>
                        <li><a href="{{ route('help.index') }}" class="hover:text-ink transition-colors">Careers</a></li>
                        <li><a href="{{ route('help.index') }}" class="hover:text-ink transition-colors">Brand Assets</a></li>
                        <li><a href="{{ route('help.index') }}" class="hover:text-ink transition-colors">Trust & Safety</a></li>
                        <li><a href="{{ route('help.index') }}" class="hover:text-ink transition-colors">Security Disclosures</a></li>
                    </ul>
                </div>

                <!-- Social Links -->
                <div class="space-y-3">
                    <h4 class="font-semibold text-ink uppercase tracking-wider text-[11px]">Connect</h4>
                    <div class="flex items-center gap-3 text-muted">
                        <a href="https://github.com/Kerneural/S-cool" target="_blank" rel="noreferrer" class="hover:text-ink transition-colors" aria-label="GitHub">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                            </svg>
                        </a>
                        <a href="https://twitter.com" target="_blank" rel="noreferrer" class="hover:text-ink transition-colors" aria-label="Twitter">
                            <svg class="w-4 h-4" viewBox="0 0 16 16" fill="currentColor">
                                <path d="M9.54471 6.77665L15.3526 0.0253906H13.9763L8.93339 5.88726L4.90547 0.0253906H0.257812L6.34863 8.89047L0.257812 15.974H1.63415L6.95963 9.77986L11.2144 15.974H15.8621L9.54439 6.77665H9.54471ZM7.65825 8.96696L7.04128 8.08447L2.13098 1.06019H4.24495L8.20697 6.72793L8.82394 7.61042L13.9769 14.9818H11.863L7.65825 8.96728V8.96696Z"/>
                            </svg>
                        </a>
                        <a href="https://linkedin.com" target="_blank" rel="noreferrer" class="hover:text-ink transition-colors" aria-label="LinkedIn">
                            <svg class="w-4 h-4" viewBox="0 0 16 16" fill="currentColor">
                                <path d="M14.819 0H1.17657C0.526848 0 0 0.514757 0 1.14936V14.8506C0 15.4852 0.526848 16 1.17657 16H14.819C15.4687 16 16 15.4852 16 14.8506V1.14936C16 0.514757 15.4687 0 14.819 0ZM4.74316 13.6338H2.37039V5.99967H4.74316V13.6338ZM3.55677 4.95655C2.79815 4.95655 2.18376 4.34117 2.18376 3.5828C2.18376 2.82442 2.79815 2.20904 3.55677 2.20904C4.3154 2.20904 4.92978 2.82442 4.92978 3.5828C4.92978 4.34117 4.3154 4.95655 3.55677 4.95655ZM13.6296 13.6338H11.2582V9.92087C11.2582 9.03576 11.242 7.89809 10.0248 7.89809C8.78985 7.89809 8.5999 8.86178 8.5999 9.85695V13.6338H6.2285V5.99967H8.50567V7.0428H8.53752C8.85463 6.44229 9.62939 5.80877 10.7852 5.80877C13.1936 5.80877 13.6296 7.39414 13.6296 9.44755V13.6338Z"/>
                            </svg>
                        </a>
                        <a href="https://youtube.com" target="_blank" rel="noreferrer" class="hover:text-ink transition-colors" aria-label="YouTube">
                            <svg class="w-4 h-4" viewBox="0 0 23 16" fill="currentColor">
                                <path d="M22.0469 2.4777C21.7891 1.51268 21.0312 0.754854 20.0664 0.496979C18.3188 0.0244141 11.2742 0.0244141 11.2742 0.0244141C11.2742 0.0244141 4.22959 0.0244141 2.48204 0.496979C1.51722 0.754854 0.759298 1.51268 0.501485 2.4777C0.0290039 4.22562 0.0290039 7.87679 0.0290039 7.87679C0.0290039 7.87679 0.0290039 11.528 0.501485 13.2759C0.759298 14.2409 1.51722 14.9987 2.48204 15.2566C4.22959 15.7292 11.2742 15.7292 11.2742 15.7292C11.2742 15.7292 18.3188 15.7292 20.0664 15.2566C21.0312 14.9987 21.7891 14.2409 22.0469 13.2759C22.5194 11.528 22.5194 7.87679 22.5194 7.87679C22.5194 7.87679 22.5194 4.22562 22.0469 2.4777ZM8.98824 11.1969V4.55671L14.8329 7.87679L8.98824 11.1969Z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Giant Watermark Outline Text (matching LangChain interactive outline spotlight) -->
            <div x-data="{
                    mouseX: 0,
                    mouseY: 0,
                    isHover: false,
                    updatePos(e) {
                        const rect = this.$el.getBoundingClientRect();
                        this.mouseX = e.clientX - rect.left;
                        this.mouseY = e.clientY - rect.top;
                    }
                 }"
                 @mousemove="updatePos($event)"
                 @mouseenter="isHover = true; updatePos($event)"
                 @mouseleave="isHover = false"
                 class="relative pt-10 overflow-hidden text-center select-none cursor-default">
                
                <!-- Base Clearly Visible Outline Layer -->
                <div class="relative z-10">
                    <span class="text-7xl sm:text-9xl font-black font-sans tracking-tight text-transparent block"
                          style="-webkit-text-stroke: 1.75px #94A3B8;">
                        S-cool
                    </span>
                </div>

                <!-- Interactive Spotlight Glow Layer (exact overlay matching LangChain cursor glow) -->
                <div class="absolute inset-0 pt-10 z-20 flex items-center justify-center pointer-events-none transition-opacity duration-200"
                     :class="isHover ? 'opacity-100' : 'opacity-0'"
                     :style="`-webkit-mask-image: radial-gradient(260px circle at ${mouseX}px ${mouseY}px, black 30%, transparent 80%); mask-image: radial-gradient(260px circle at ${mouseX}px ${mouseY}px, black 30%, transparent 80%);`">
                    <span class="text-7xl sm:text-9xl font-black font-sans tracking-tight text-transparent block drop-shadow-[0_0_20px_rgba(15,118,110,0.4)]"
                          style="-webkit-text-stroke: 2px #0F766E;">
                        S-cool
                    </span>
                </div>
            </div>

            <!-- Bottom Sub-Footer: Status & Legal -->
            <div class="pt-6 border-t border-line flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-muted">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>All systems operational</span>
                </div>
                <div class="flex items-center gap-6">
                    <a href="{{ route('help.index') }}" class="hover:text-ink transition-colors">Privacy policy</a>
                    <a href="{{ route('help.index') }}" class="hover:text-ink transition-colors">Terms of service</a>
                    <span>&copy; {{ date('Y') }} S-cool Inc. All rights reserved.</span>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
