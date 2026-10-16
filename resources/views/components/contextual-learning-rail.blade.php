@props(['community', 'learningFocus'])

<div class="space-y-5">
    {{-- One Clear Learning Action Card --}}
    @if (isset($learningFocus))
        @if ($learningFocus->isContinue())
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200/80 space-y-3.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">{{ __('Learning Focus') }}</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-brand-50 text-brand-700 border border-brand-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-600" aria-hidden="true"></span>
                            <span>{{ __('Next Learning Step') }}</span>
                        </span>
                    </div>
                    <span class="text-xs font-black text-slate-900">{{ $learningFocus->percentage }}%</span>
                </div>

                <div class="space-y-1">
                    <h3 class="font-extrabold text-sm sm:text-base text-slate-900 line-clamp-2 leading-snug">
                        {{ $learningFocus->nextLesson?->title ?? $learningFocus->nextCourse?->title }}
                    </h3>
                    <p class="text-xs text-slate-500 line-clamp-1">
                        {{ $learningFocus->nextCourse?->title }}
                        @if ($learningFocus->nextLesson?->section)
                            &bull; {{ $learningFocus->nextLesson->section->title }}
                        @endif
                    </p>
                </div>

                <div class="space-y-1">
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-brand-600 rounded-full transition-all duration-300" style="width: {{ $learningFocus->percentage }}%"></div>
                    </div>
                    <div class="flex justify-between text-[11px] text-slate-400 font-medium">
                        <span>{{ __('Accessible lesson progress') }}</span>
                        <span>{{ $learningFocus->completedLessons }}/{{ $learningFocus->totalLessons }} {{ __('lessons') }}</span>
                    </div>
                </div>

                @if ($learningFocus->nextCourse && $learningFocus->nextLesson)
                    <a href="{{ route('communities.lessons.show', [$community, $learningFocus->nextCourse, $learningFocus->nextLesson]) }}"
                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-xs transition active:scale-[0.99]">
                        <span>{{ $learningFocus->hasStarted ? __('Resume Lesson') : __('Start Lesson') }}</span>
                        <span>&rarr;</span>
                    </a>
                @elseif ($learningFocus->nextCourse)
                    <a href="{{ route('communities.courses.show', [$community, $learningFocus->nextCourse]) }}"
                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-xs transition active:scale-[0.99]">
                        <span>{{ __('Open Course') }}</span>
                        <span>&rarr;</span>
                    </a>
                @endif
            </div>
        @elseif ($learningFocus->isComplete())
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-emerald-200 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">{{ __('Learning Focus') }}</span>
                    <div class="flex items-center gap-1.5">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            {{ __('Completed') }}
                        </span>
                        <span class="text-xs font-bold text-emerald-700">100%</span>
                    </div>
                </div>
                <h3 class="font-extrabold text-sm text-slate-900">{{ __('All Accessible Lessons Finished!') }}</h3>
                <p class="text-xs text-slate-600 leading-relaxed">{{ __('You have completed all lessons currently available in this community.') }}</p>
                <a href="{{ route('communities.classroom.index', $community) }}"
                   class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition">
                    <span>{{ __('Browse Classroom') }}</span> &rarr;
                </a>
            </div>
        @elseif ($learningFocus->isEmpty())
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200/80 space-y-2">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                    {{ __('Learning Focus') }}
                </span>
                <h3 class="font-bold text-sm text-slate-900">{{ __('No learning content yet') }}</h3>
                <p class="text-xs text-slate-500 leading-relaxed">{{ __('Published lessons will appear here when available. You can still join community discussions and events.') }}</p>
            </div>
        @else
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200/80 space-y-3">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                    {{ __('Learning Focus') }}
                </span>
                <h3 class="font-bold text-sm text-slate-900">{{ __('Explore Courses') }}</h3>
                <p class="text-xs text-slate-600 leading-relaxed">{{ __('Unlock new learning paths and start practicing.') }}</p>
                <a href="{{ route('communities.classroom.index', $community) }}"
                   class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-semibold text-xs transition">
                    <span>{{ __('Go to Classroom') }}</span> &rarr;
                </a>
            </div>
        @endif

        {{-- Upcoming Event Secondary Context --}}
        @if ($learningFocus->upcomingEvent)
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-indigo-100 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>{{ __('Upcoming Live Event') }}</span>
                    </span>
                </div>
                <h4 class="font-bold text-sm text-slate-900 line-clamp-2">{{ $learningFocus->upcomingEvent['title'] }}</h4>
                <div class="text-xs text-slate-500 space-y-0.5">
                    <p class="font-medium text-slate-700">{{ $learningFocus->upcomingEvent['formatted_time'] }}</p>
                    <p class="text-[11px] text-slate-400">{{ $learningFocus->upcomingEvent['timezone'] }}</p>
                </div>
                @if (! empty($learningFocus->upcomingEvent['meeting_url']))
                    <a href="{{ $learningFocus->upcomingEvent['meeting_url'] }}" target="_blank" rel="noopener noreferrer"
                       class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs transition shadow-2xs">
                        <span>{{ __('Join Live Session') }}</span> &rarr;
                    </a>
                @endif
            </div>
        @endif
    @endif

    {{-- Onboarding Checklist (when applicable) --}}
    @include('communities.partials.onboarding-checklist', ['community' => $community])

    {{-- About Community Card --}}
    <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200/80 space-y-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-brand-600 text-white font-bold flex items-center justify-center text-base shadow-xs">
                {{ strtoupper(substr($community->name, 0, 1)) }}
            </div>
            <div>
                <h3 class="font-bold text-base text-slate-900">{{ $community->name }}</h3>
                <p class="text-xs text-slate-400">
                    {{ __('Created ') }} {{ $community->created_at->format('M Y') }}
                </p>
            </div>
        </div>

        <p class="text-xs text-slate-600 leading-relaxed">
            {{ $community->description ?? __('A dedicated private learning and discussion community.') }}
        </p>

        <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
            <a href="{{ route('communities.classroom.index', $community) }}"
               class="inline-flex items-center justify-between px-3 py-2 bg-slate-50 hover:bg-brand-50 hover:text-brand-700 border border-slate-200/80 rounded-xl text-xs font-semibold text-slate-700 transition">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    {{ __('Browse Classroom Courses') }}
                </span>
                <span>&rarr;</span>
            </a>
            <a href="{{ route('communities.directory.index', $community) }}"
               class="inline-flex items-center justify-between px-3 py-2 bg-slate-50 hover:bg-brand-50 hover:text-brand-700 border border-slate-200/80 rounded-xl text-xs font-semibold text-slate-700 transition">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    {{ __('View Member Directory') }}
                </span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>
</div>
