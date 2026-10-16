<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <x-community-workspace :community="$community" active-tab="classroom" />
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight text-ink">{{ __('Classroom Courses') }}</h1>
        </div>
        <x-classroom-feedback />

        @if ($isActiveMember && $courses->isNotEmpty())
            @php
                $accessibleCourses = $courses->getCollection()->filter(
                    fn ($course) => $isCreator || $course->hasMemberAccess(auth()->user())
                );
                $totalCompleted = $accessibleCourses->sum('progress_completed');
                $totalLessons = $accessibleCourses->sum('progress_total');
                $overallPercent = $totalLessons > 0 ? (int) round(100 * $totalCompleted / $totalLessons) : 0;
                $nextUpCourse = $accessibleCourses->first(function ($c) {
                    return $c->progress_total > 0 && $c->progress_completed < $c->progress_total;
                }) ?? $accessibleCourses->first();
            @endphp
            @if ($nextUpCourse)
                <div class="bg-gradient-to-br from-white via-slate-50/50 to-brand-50/30 rounded-2xl p-5 sm:p-6 border border-slate-200/90 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2 max-w-xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-600"></span>
                        <span>{{ __('Next Learning Step') }}</span>
                    </div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">
                        {{ $nextUpCourse->title }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-600 line-clamp-2">
                        {{ $nextUpCourse->description ?? __('Continue your learning journey in this course.') }}
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 shrink-0">
                    <div class="bg-white px-4 py-3 rounded-xl border border-slate-200/80 shadow-xs min-w-[140px]">
                        <span class="block text-[11px] uppercase tracking-wider font-semibold text-slate-400">{{ __('Overall Progress') }}</span>
                        <div class="flex items-baseline gap-2 mt-0.5">
                            <span class="text-xl font-black text-slate-900">{{ $overallPercent }}%</span>
                            <span class="text-xs font-medium text-slate-500">({{ $totalCompleted }}/{{ $totalLessons }})</span>
                        </div>
                    </div>

                    <a href="{{ route('communities.courses.show', [$community, $nextUpCourse]) }}"
                       class="inline-flex items-center justify-center px-5 py-3 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs sm:text-sm font-semibold shadow-xs transition active:scale-[0.99] whitespace-nowrap">
                        <span>{{ $nextUpCourse->progress_completed > 0 ? __('Resume Learning') : __('Start Learning') }}</span>
                        <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
                </div>
            @endif
        @endif

        @if ($isCreator)
            <details class="rounded-2xl bg-white p-6 shadow-xs border border-slate-200/80" @if ($errors->any() && old('_form') === 'create-course') open @endif>
                <summary class="cursor-pointer font-bold text-sm text-brand-600 hover:text-brand-700 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>{{ __('New Course') }}</span>
                    </span>
                    <span class="text-xs text-slate-400 font-normal">{{ __('Click to expand') }}</span>
                </summary>
                <form method="POST" action="{{ route('communities.courses.store', $community) }}" class="mt-4 space-y-4 pt-4 border-t border-slate-100">
                    @csrf
                    <input type="hidden" name="_form" value="create-course">
                    <x-classroom-course-fields form-key="create-course" />
                    <div class="flex justify-end pt-2">
                        <x-primary-button>
                            {{ __('Save Course') }}
                        </x-primary-button>
                    </div>
                </form>
            </details>
        @endif

        @if ($courses->isEmpty())
            <div class="rounded-2xl bg-white p-12 text-center border border-slate-200/80 shadow-xs">
                <div class="mx-auto w-12 h-12 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-600 mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-900 mb-1">{{ __('No courses available') }}</h3>
                <p class="text-sm text-slate-500 max-w-sm mx-auto">{{ __('There are no published courses in this community classroom yet.') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($courses as $course)
                    <article class="min-w-0 flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs hover:border-brand-500/30 hover:shadow-lg transition-all group">
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $course->isOneTime() ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                    @if ($course->isOneTime())
                                        <span>🔒</span> {{ $course->formattedPrice() }}
                                    @else
                                        <span>{{ __('Included') }}</span>
                                    @endif
                                </span>
                                @if ($isCreator)
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">
                                        {{ $course->status }}
                                    </span>
                                @endif
                            </div>

                            <a href="{{ route('communities.courses.show', [$community, $course]) }}" class="block break-words text-lg font-bold text-slate-900 group-hover:text-brand-600 transition">
                                {{ $course->title }}
                            </a>

                            @if ($isCreator || $course->hasMemberAccess(auth()->user()))
                                <p class="break-words text-sm text-slate-600 line-clamp-2 leading-relaxed">
                                    {{ $course->description ?? __('No course description provided.') }}
                                </p>
                                <div class="flex items-center gap-1.5 text-xs text-slate-400 font-medium">
                                    <svg class="w-3.5 h-3.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    <span>{{ $course->visible_lessons_count }} {{ $course->visible_lessons_count === 1 ? __('lesson') : __('lessons') }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 pt-4 border-t border-slate-100 space-y-3">
                            @if ($isActiveMember && $course->isPublished())
                                @if ($course->isOneTime() && ! $course->isEntitledFor(auth()->user()))
                                    <div class="rounded-xl bg-amber-50 border border-amber-200/80 p-2.5 text-xs font-medium text-amber-800 flex items-center justify-between">
                                        <span>🔒 {{ __('One-time purchase required') }}</span>
                                        <span class="font-bold">{{ $course->formattedPrice() }}</span>
                                    </div>
                                @else
                                    @php($percent = $course->progress_total ? (int) round(100 * $course->progress_completed / $course->progress_total) : 0)
                                    <div class="space-y-1.5">
                                        <div class="flex items-center justify-between text-xs font-semibold text-slate-600">
                                            <span>{{ __('Your Progress') }}: {{ $percent }}%</span>
                                            <span class="text-brand-600">{{ $course->progress_completed }}/{{ $course->progress_total }}</span>
                                        </div>
                                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-brand-600 rounded-full transition-all duration-300" style="width: {{ $percent }}%"></div>
                                        </div>
                                        <progress class="sr-only" max="100" value="{{ $percent }}" aria-label="{{ __('Course completion percentage') }}">{{ $percent }}%</progress>
                                    </div>
                                @endif
                            @endif

                            <div class="flex items-center justify-between">
                                <a href="{{ route('communities.courses.show', [$community, $course]) }}" class="inline-flex items-center text-xs font-semibold text-brand-600 hover:text-brand-700 group-hover:translate-x-0.5 transition-transform">
                                    <span>{{ __('View Course') }}</span>
                                    <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </a>

                                @if ($isCreator)
                                    <x-classroom-reorder :ids="$courseOrder" :id="$course->id" :action="route('communities.courses.reorder', $community)" />
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-6">
                {{ $courses->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
