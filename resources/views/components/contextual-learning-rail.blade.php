@props(['community', 'learningFocus', 'miniRanking' => null, 'part' => 'learning'])

{{-- Feed rail (design 11.10): 'learning' is the first block, 'context' holds secondary disclosures. --}}
<div class="space-y-3">
@if ($part === 'learning')
    {{-- One Clear Learning Action Card --}}
    @if (isset($learningFocus))
        @if ($learningFocus->isContinue())
            <div class="bg-white p-4 rounded-2xl shadow-xs border border-slate-200/80 space-y-3.5">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1">
                        <span class="whitespace-nowrap text-xs font-bold text-slate-600 uppercase tracking-wider">{{ __('Learning Focus') }}</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-brand-50 text-brand-700 border border-brand-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-600" aria-hidden="true"></span>
                            <span>{{ __('Next Learning Step') }}</span>
                        </span>
                    </div>
                    <span class="text-xs font-bold text-slate-900">{{ $learningFocus->percentage }}%</span>
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
                    <div class="flex justify-between text-[11px] text-slate-600 font-medium">
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
            <div class="bg-white p-4 rounded-2xl shadow-xs border border-emerald-200 space-y-3">
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
            <div class="bg-white p-4 rounded-2xl shadow-xs border border-slate-200/80 space-y-2">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                    {{ __('Learning Focus') }}
                </span>
                <h3 class="font-bold text-sm text-slate-900">{{ __('No learning content yet') }}</h3>
                <p class="text-xs text-slate-500 leading-relaxed">{{ __('Published lessons will appear here when available. You can still join community discussions and events.') }}</p>
            </div>
        @else
            <div class="bg-white p-4 rounded-2xl shadow-xs border border-slate-200/80 space-y-3">
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
            <div class="bg-white p-4 rounded-2xl shadow-xs border border-brand-100 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                        <svg class="w-3.5 h-3.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>{{ __('Upcoming Live Event') }}</span>
                    </span>
                </div>
                <h4 class="font-bold text-sm text-slate-900 line-clamp-2">{{ $learningFocus->upcomingEvent['title'] }}</h4>
                <div class="text-xs text-slate-500 space-y-0.5">
                    <p class="font-medium text-slate-700">{{ $learningFocus->upcomingEvent['formatted_time'] }}</p>
                    <p class="text-[11px] text-slate-600">{{ $learningFocus->upcomingEvent['timezone'] }}</p>
                </div>
                @if (! empty($learningFocus->upcomingEvent['meeting_url']))
                    <a href="{{ $learningFocus->upcomingEvent['meeting_url'] }}" target="_blank" rel="noopener noreferrer"
                       class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-semibold text-xs transition shadow-2xs">
                        <span>{{ __('Join Live Session') }}</span> &rarr;
                    </a>
                @endif
            </div>
        @endif
    @endif

@else
    @include('communities.partials.onboarding-checklist', ['community' => $community])

    <details class="rail-card rail-disclosure">
        <summary>
            <svg class="rail-disclosure__chevron h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
            <span>{{ __('Community details') }}</span>
        </summary>
        <div class="rail-disclosure__body">
            <x-skool-community-card :community="$community" variant="compact" />
        </div>
    </details>

    @if ($miniRanking !== null)
        <details class="rail-card rail-disclosure">
            <summary>
                <svg class="rail-disclosure__chevron h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                <span>{{ __('Contributions') }}</span>
            </summary>
            <div class="rail-disclosure__body">
                <x-contribution-rail :community="$community" :entries="$miniRanking" :embedded="true" />
            </div>
        </details>
    @endif
@endif
</div>
