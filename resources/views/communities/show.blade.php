<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="about" />

            @if (session('status'))
                <div class="p-4 font-medium text-sm text-emerald-800 bg-emerald-50 rounded-xl border border-emerald-200 shadow-xs flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            <!-- Editorial 2-Column Grid (Main Editorial Measure + Identity & Destinations Rail) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Left: Editorial Content (8 cols on lg, comfortable reading measure) -->
                <div class="min-w-0 lg:col-span-8 space-y-6">
                    <!-- Community Story & Mission -->
                    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 sm:p-8 space-y-4">
                        <div class="border-b border-slate-100 pb-4">
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">{{ __('About this Community') }} &mdash; {{ $community->name }}</h1>
                        </div>

                        <div class="text-slate-700 leading-relaxed text-sm sm:text-base max-w-prose space-y-4">
                            <p class="whitespace-pre-line">
                                {{ $community->description ?: __('No description provided yet.') }}
                            </p>
                        </div>
                    </div>

                    <!-- Introduction Video (if present) -->
                    @if ($community->intro_embed_url || $community->intro_video_url)
                        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 sm:p-8 space-y-4">
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ __('Introduction Video') }}</span>
                            </h2>
                            <div>
                                <x-secure-video-player :url="$community->intro_video_url" :title="$community->name" />
                            </div>
                        </div>
                    @endif

                    <!-- Community Rules & Guidelines -->
                    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 sm:p-8 space-y-4">
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>{{ __('Community Guidelines & Rules') }}</span>
                        </h2>
                        @if ($community->rules)
                            <div class="p-4 sm:p-5 bg-slate-50 rounded-xl border border-slate-100 text-slate-700 text-xs sm:text-sm whitespace-pre-line leading-relaxed max-w-prose">
                                {{ $community->rules }}
                            </div>
                        @else
                            <p class="text-xs sm:text-sm text-slate-400 italic">
                                {{ __('No community rules posted yet.') }}
                            </p>
                        @endif
                    </div>
                    <section class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6" aria-labelledby="about-learning-roadmap">
                        <h2 id="about-learning-roadmap" class="text-lg font-bold text-slate-900">{{ __('Your learning roadmap') }}</h2>
                        <p class="mt-1 text-sm text-slate-600">{{ __('Published courses available with your current access. Open Classroom for the complete catalog.') }}</p>
                        <ol class="mt-4 space-y-2">
                            @forelse ($roadmapCourses as $roadmapCourse)
                                <li>
                                    <a href="{{ route('communities.courses.show', [$community, $roadmapCourse]) }}" class="flex min-h-11 items-center gap-3 rounded-xl bg-slate-50 px-3 py-2 text-sm font-semibold text-brand hover:bg-brand-soft">
                                        <span class="shrink-0 text-xs text-muted">{{ $loop->iteration }}</span>
                                        <span class="min-w-0 break-words">{{ $roadmapCourse->title }}</span>
                                    </a>
                                </li>
                            @empty
                                <li class="text-sm text-muted">{{ __('No published courses are available with your current access yet.') }}</li>
                            @endforelse
                        </ol>
                        <a href="{{ route('communities.classroom.index', $community) }}" class="mt-4 inline-flex min-h-11 items-center text-sm font-semibold text-brand underline">{{ __('Open Classroom') }}</a>
                    </section>
                </div>

                <!-- Right: Identity, Useful Destinations & Private Status (4 cols on lg) -->
                <aside class="min-w-0 lg:col-span-4 space-y-6">
                    <x-skool-community-card :community="$community" />

                    <!-- Community Identity Card -->
                    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 space-y-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ route('communities.directory.avatar', [$community, $community->creator]) }}"
                                 alt="{{ $community->creator->name }}"
                                 class="w-12 h-12 rounded-full object-cover border border-slate-200 bg-slate-100 shadow-xs shrink-0">
                            <div class="min-w-0">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Creator & Host') }}</span>
                                <h3 class="text-sm font-bold text-slate-900 truncate">{{ $community->creator->name }}</h3>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 space-y-2 text-xs text-slate-600">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">{{ __('Tenant Slug') }}</span>
                                <code class="px-2 py-0.5 bg-slate-100 rounded text-[11px] font-mono text-brand-700 font-semibold">{{ $community->slug }}</code>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">{{ __('Active Members:') }}</span>
                                <span class="font-bold text-slate-900 tabular-nums">{{ $activeMemberCount ?? $community->memberships()->effective()->count() }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">{{ __('Founded') }}</span>
                                <span class="font-medium text-slate-700">{{ $community->created_at->format('M Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Useful Destinations Navigation Card -->
                    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Useful Destinations') }}</h4>
                        <div class="space-y-1.5 text-xs font-semibold">
                            <a href="{{ route('communities.posts.index', $community) }}"
                               class="flex items-center justify-between px-3 py-2 bg-slate-50 hover:bg-brand-50 hover:text-brand-700 rounded-xl transition text-slate-700">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    {{ __('Community Feed') }}
                                </span>
                                <span>&rarr;</span>
                            </a>
                            <a href="{{ route('communities.classroom.index', $community) }}"
                               class="flex items-center justify-between px-3 py-2 bg-slate-50 hover:bg-brand-50 hover:text-brand-700 rounded-xl transition text-slate-700">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    {{ __('Classroom Courses') }}
                                </span>
                                <span>&rarr;</span>
                            </a>
                            <a href="{{ route('communities.events.index', $community) }}"
                               class="flex items-center justify-between px-3 py-2 bg-slate-50 hover:bg-brand-50 hover:text-brand-700 rounded-xl transition text-slate-700">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ __('Events & Sessions') }}
                                </span>
                                <span>&rarr;</span>
                            </a>
                            <a href="{{ route('communities.directory.index', $community) }}"
                               class="flex items-center justify-between px-3 py-2 bg-slate-50 hover:bg-brand-50 hover:text-brand-700 rounded-xl transition text-slate-700">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    {{ __('Member Directory') }}
                                </span>
                                <span>&rarr;</span>
                            </a>
                            <a href="{{ route('communities.leaderboard', $community) }}"
                               class="flex items-center justify-between px-3 py-2 bg-slate-50 hover:bg-brand-50 hover:text-brand-700 rounded-xl transition text-slate-700">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    {{ __('Leaderboard & Points') }}
                                </span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>

                    <!-- Private Workspace Status Card -->
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200/80 space-y-3">
                        <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">{{ __('Private Community') }}</h4>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            {{ __('This is an invite-only private community. Non-members cannot discover or view internal content.') }}
                        </p>
                        <div class="space-y-1.5 text-xs text-slate-600 pt-1">
                            <div class="flex items-center">
                                <span class="h-2 w-2 rounded-full bg-emerald-500 mr-2 shrink-0"></span>
                                <span>{{ __('Tenant Active') }}</span>
                            </div>
                            <div class="flex items-center">
                                <span class="h-2 w-2 rounded-full bg-brand-500 mr-2 shrink-0"></span>
                                <span>{{ $community->isCreator(auth()->user()) ? __('Creator host access') : __('Active member access') }}</span>
                            </div>
                        </div>
                        @can('update', $community)
                            <div class="pt-2">
                                <a href="{{ route('communities.edit', $community) }}" class="inline-flex min-h-11 items-center justify-center w-full rounded-xl border border-brand-200 bg-white px-4 py-2 text-xs font-bold text-brand-700 shadow-xs transition hover:bg-brand-50">
                                    {{ __('Edit Settings') }}
                                </a>
                            </div>
                        @endcan
                    </div>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
