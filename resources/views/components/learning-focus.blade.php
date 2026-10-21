@props(['community', 'learningFocus'])

@if (isset($learningFocus))
    <section aria-label="{{ __('Learning Focus') }}" class="bg-gradient-to-br from-white via-slate-50/50 to-brand-50/30 rounded-3xl p-6 sm:p-7 border border-slate-200/90 shadow-xs space-y-6">
        <!-- Learning State -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            @if ($learningFocus->isContinue())
                <div class="min-w-0 space-y-2 max-w-xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-600"></span>
                        <span>{{ __('Next Learning Step') }}</span>
                    </div>
                    <h2 class="break-words text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                        {{ $learningFocus->nextLesson?->title ?? $learningFocus->nextCourse?->title }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-600 line-clamp-2">
                        <span class="font-semibold text-slate-800">{{ $learningFocus->nextCourse?->title }}</span>
                        @if ($learningFocus->nextLesson && $learningFocus->nextLesson->section)
                            &bull; <span class="text-slate-500">{{ $learningFocus->nextLesson->section->title }}</span>
                        @endif
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 shrink-0">
                    <div class="bg-white px-4 py-3 rounded-2xl border border-slate-200/80 shadow-xs min-w-[140px]">
                        <span class="block text-[11px] uppercase tracking-wider font-semibold text-slate-600">{{ __('Overall Progress') }}</span>
                        <div class="flex items-baseline gap-2 mt-0.5">
                            <span class="text-xl font-black text-slate-900">{{ $learningFocus->percentage }}%</span>
                            <span class="text-xs font-medium text-slate-500">({{ $learningFocus->completedLessons }}/{{ $learningFocus->totalLessons }})</span>
                        </div>
                    </div>

                    @if ($learningFocus->nextCourse && $learningFocus->nextLesson)
                        <a href="{{ route('communities.lessons.show', [$community, $learningFocus->nextCourse, $learningFocus->nextLesson]) }}"
                           class="inline-flex items-center justify-center px-5 py-3.5 bg-brand-600 hover:bg-brand-500 text-white rounded-2xl text-xs sm:text-sm font-semibold shadow-xs transition active:scale-[0.99] whitespace-nowrap">
                            <span>{{ $learningFocus->hasStarted ? __('Resume Lesson') : __('Start Lesson') }}</span>
                            <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    @elseif ($learningFocus->nextCourse)
                        <a href="{{ route('communities.courses.show', [$community, $learningFocus->nextCourse]) }}"
                           class="inline-flex items-center justify-center px-5 py-3.5 bg-brand-600 hover:bg-brand-500 text-white rounded-2xl text-xs sm:text-sm font-semibold shadow-xs transition active:scale-[0.99] whitespace-nowrap">
                            <span>{{ __('Open Course') }}</span>
                            <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    @endif
                </div>
            @elseif ($learningFocus->isComplete())
                <div class="min-w-0 space-y-2 max-w-xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span>🎉</span>
                        <span>{{ __('All Caught Up!') }}</span>
                    </div>
                    <h2 class="break-words text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                        {{ __('You completed all accessible lessons!') }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-600">
                        {{ __('Great work! You have finished all lessons currently accessible to you. Review a course or join the community discussions.') }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-4">
                    <div class="bg-white px-5 py-3.5 rounded-2xl border border-slate-200/80 shadow-xs min-w-[140px] text-center">
                        <span class="block text-[11px] uppercase tracking-wider font-semibold text-emerald-600">{{ __('Completed') }}</span>
                        <span class="text-2xl font-black text-slate-900">100%</span>
                    </div>
                    <a href="{{ route('communities.posts.index', $community) }}"
                       class="inline-flex items-center justify-center px-5 py-3.5 bg-slate-900 hover:bg-slate-800 text-white rounded-2xl text-xs sm:text-sm font-semibold shadow-xs transition active:scale-[0.99] whitespace-nowrap">
                        <span>{{ __('Community Feed') }}</span>
                        <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            @elseif ($learningFocus->isLocked())
                <div class="min-w-0 space-y-2 max-w-xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                        <span>🔒</span>
                        <span>{{ __('Unlock Learning Access') }}</span>
                    </div>
                    <h2 class="break-words text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                        {{ __('Course access required') }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        {{ __('The courses in this classroom require separate enrollment or active membership. Unlock a course below to begin learning.') }}
                    </p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ request()->routeIs('communities.classroom.index') ? '#courses-list' : route('communities.classroom.index', $community).'#courses-list' }}" class="inline-flex items-center justify-center px-5 py-3 bg-brand-600 hover:bg-brand-500 text-white rounded-2xl text-xs sm:text-sm font-semibold shadow-xs transition active:scale-[0.99]">
                        <span>{{ __('Browse Courses') }}</span>
                        <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </a>
                </div>
            @elseif ($learningFocus->isEmpty())
                <div class="min-w-0 space-y-2">
                    <h2 class="text-xl font-bold text-slate-900">{{ __('No learning content yet') }}</h2>
                    <p class="text-sm text-slate-600">{{ __('Published lessons will appear here when available. You can still join community discussions and events.') }}</p>
                </div>
            @endif
        </div>

        <!-- Upcoming Event Section (if scheduled non-cancelled event exists) -->
        @if ($learningFocus->upcomingEvent)
            <div class="pt-4 border-t border-slate-200/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white/70 rounded-2xl p-4 border border-slate-100">
                <div class="flex items-start sm:items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 border border-indigo-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">{{ __('Upcoming Event') }}</span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">{{ $learningFocus->upcomingEvent['title'] }}</h4>
                        <p class="text-xs text-slate-500">{{ $learningFocus->upcomingEvent['formatted_time'] }}</p>
                    </div>
                </div>

                @if ($learningFocus->upcomingEvent['meeting_url'])
                    <a href="{{ $learningFocus->upcomingEvent['meeting_url'] }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-[0.99] whitespace-nowrap self-start sm:self-auto">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span>{{ __('Join Meeting') }}</span>
                    </a>
                @endif
            </div>
        @endif
    </section>
@endif
