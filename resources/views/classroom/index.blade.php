<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <x-community-workspace :community="$community" active-tab="classroom" />
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight text-ink">{{ __('Classroom Courses') }}</h1>
        </div>
        <x-classroom-feedback />

        <x-learning-focus :community="$community" :learning-focus="$learningFocus" />

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
            <div id="courses-list" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 scroll-mt-6">
                @foreach ($courses as $course)
                    @php($hasAccess = $isCreator || ($isActiveMember && ($course->isIncluded() || in_array($course->id, $entitledCourseIds, true))))
                    <article class="min-w-0 flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white overflow-hidden shadow-xs hover:border-brand-500/30 hover:shadow-lg transition-all group">
                        <div class="aspect-video w-full overflow-hidden bg-slate-900 relative">
                            @if ($course->cover_path)
                                <img src="{{ route('communities.courses.cover', [$community, $course]) }}" alt="{{ $course->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-brand-700 via-teal-800 to-slate-900 flex flex-col items-center justify-center p-4 text-center group-hover:scale-105 transition-transform duration-300">
                                    <div class="w-12 h-12 rounded-xl bg-white/10 backdrop-blur-xs flex items-center justify-center text-white mb-2 shadow-xs border border-white/10">
                                        <svg class="w-6 h-6 text-teal-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <span class="text-white/90 text-xs font-bold tracking-wider uppercase line-clamp-1 max-w-[200px]">{{ $course->title }}</span>
                                </div>
                            @endif

                            <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold backdrop-blur-md shadow-xs {{ $course->isOneTime() ? 'bg-amber-500/95 text-white' : 'bg-emerald-600/95 text-white' }}">
                                    @if ($course->isOneTime())
                                        <span>🔒</span> {{ $course->formattedPrice() }}
                                    @else
                                        <span>{{ __('Included') }}</span>
                                    @endif
                                </span>
                            </div>

                            @if ($isCreator && $course->isDraft())
                                <div class="absolute top-3 right-3">
                                    <span class="rounded-full bg-slate-900/80 backdrop-blur-md px-2.5 py-0.5 text-xs font-semibold text-amber-300 border border-amber-400/30">
                                        {{ __('Draft') }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                            <div class="space-y-2">
                                <a href="{{ route('communities.courses.show', [$community, $course]) }}" class="block break-words text-lg font-bold text-slate-900 group-hover:text-brand-600 transition">
                                    {{ $course->title }}
                                </a>

                                @if ($hasAccess)
                                    <p class="break-words text-sm text-slate-600 line-clamp-2 leading-relaxed">
                                        {{ $course->description ?? __('No course description provided.') }}
                                    </p>
                                    <div class="flex items-center gap-1.5 text-xs text-slate-400 font-medium pt-1">
                                        <svg class="w-3.5 h-3.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        <span>{{ $course->visible_lessons_count }} {{ $course->visible_lessons_count === 1 ? __('lesson') : __('lessons') }}</span>
                                    </div>
                                @else
                                    <div class="rounded-xl bg-amber-50 border border-amber-200/80 p-3 text-xs text-amber-900 space-y-1">
                                        <div class="font-bold flex items-center gap-1.5">
                                            <span>🔒</span> {{ __('Locked Course') }}
                                        </div>
                                        <p class="text-amber-800">{{ __('Purchase required to unlock lessons and description.') }}</p>
                                    </div>
                                @endif
                            </div>

                            <div class="pt-3 border-t border-slate-100 space-y-3">
                                @if ($hasAccess && $isActiveMember && $course->isPublished())
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

                                <div class="flex items-center justify-between">
                                    <a href="{{ route('communities.courses.show', [$community, $course]) }}" class="inline-flex items-center text-xs font-semibold text-brand-600 hover:text-brand-700 group-hover:translate-x-0.5 transition-transform">
                                        <span>{{ $hasAccess ? __('Open Course') : __('Unlock Course') }}</span>
                                        <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>

                                    @if ($isCreator)
                                        <x-classroom-reorder :ids="$courseOrder" :id="$course->id" :action="route('communities.courses.reorder', $community)" />
                                    @endif
                                </div>
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
