<x-app-layout>
    <div data-live-search-surface class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <x-community-workspace :community="$community" active-tab="classroom" />
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-2xl font-bold tracking-tight text-ink">{{ __('Classroom Courses') }}</h1>

            <!-- Classroom Course Search Form with Alpine Debounce -->
            <form method="GET" action="{{ route('communities.classroom.index', $community) }}" class="flex flex-wrap items-center gap-2 min-w-0" x-data="liveListSearch" @submit.prevent="search()">
                <div class="relative w-full sm:w-72">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-muted">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="q" aria-label="Search courses in classroom" maxlength="100" value="{{ $searchQuery ?? '' }}" placeholder="{{ __('Search courses in classroom...') }}"
                           @input="liveSearch"
                           autocomplete="off"
                           class="w-full text-sm rounded-xl border-line pl-9 pr-3 py-2 bg-surface focus:bg-white focus:border-brand focus:ring-brand transition-colors text-ink shadow-2xs">
                </div>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand hover:bg-brand-hover text-white font-medium text-xs rounded-xl transition shadow-2xs">
                    {{ __('Search') }}
                </button>
                    <a href="{{ route('communities.classroom.index', $community) }}" class="text-xs text-muted hover:text-ink underline ml-1">
                        {{ __('Clear') }}
                    </a>

                        <p x-show="loading" x-cloak role="status" class="basis-full text-xs text-muted">{{ __('Searching…') }}</p>
                        <div x-show="error" x-cloak role="alert" class="basis-full text-xs text-rose-700">
                            <span x-text="error"></span>
                            <button type="button" @click="search()" class="ml-2 underline font-semibold">{{ __('Retry') }}</button>
                        </div>
</form>
        </div>
        <x-classroom-feedback />

        <x-learning-focus :community="$community" :learning-focus="$learningFocus" />

        @if ($isCreator)
            <details class="rounded-xl bg-surface p-6 shadow-2xs border border-line" @if ($errors->any() && old('_form') === 'create-course') open @endif>
                <summary class="cursor-pointer font-semibold text-sm text-brand hover:text-brand-hover flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>{{ __('New Course') }}</span>
                    </span>
                    <span class="text-xs text-muted font-normal">{{ __('Click to expand') }}</span>
                </summary>
                <form method="POST" action="{{ route('communities.courses.store', $community) }}" class="mt-4 space-y-4 pt-4 border-t border-line">
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

        <div data-live-search-results>
        @if ($courses->isEmpty())
            <div class="rounded-xl bg-surface p-12 text-center border border-line shadow-2xs">
                <div class="mx-auto w-12 h-12 rounded-xl bg-brand-soft border border-brand/20 flex items-center justify-center text-brand mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-ink mb-1">{{ !empty($searchQuery) ? __('No matching courses') : __('No courses available') }}</h3>
                <p class="text-sm text-muted max-w-sm mx-auto">
                    {{ !empty($searchQuery) ? __('No courses matched your query ":query". Try different keywords or clear the search.', ['query' => $searchQuery]) : __('There are no published courses in this community classroom yet.') }}
                </p>
                @if (!empty($searchQuery))
                    <div class="mt-4">
                        <a href="{{ route('communities.classroom.index', $community) }}" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-ink text-xs font-semibold rounded-xl transition">
                            {{ __('Clear search') }}
                        </a>
                    </div>
                @endif
            </div>
        @else
            <div id="courses-list" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 scroll-mt-6">
                @foreach ($courses as $course)
                    @php($hasAccess = $isCreator || ($isActiveMember && ($course->isIncluded() || in_array($course->id, $entitledCourseIds, true))))
                    <article class="min-w-0 flex flex-col justify-between rounded-xl border border-line bg-surface overflow-hidden shadow-2xs hover:border-brand/40 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 group">
                        <div class="aspect-video w-full overflow-hidden bg-slate-900 relative">
                            @if ($course->cover_path)
                                <x-media-frame :src="route('communities.courses.cover', [$community, $course])" :alt="$course->title" loading="lazy" class="h-full w-full" img-class="group-hover:scale-105 [transition:opacity_180ms_ease-out,transform_500ms_ease-out]" />
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-brand-700 via-teal-800 to-slate-900 flex flex-col items-center justify-center p-4 text-center group-hover:scale-105 transition-transform duration-500">
                                    <div class="w-12 h-12 rounded-xl bg-white/10 backdrop-blur-xs flex items-center justify-center text-white mb-2 shadow-xs border border-white/10">
                                        <svg class="w-6 h-6 text-teal-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <span class="text-white/90 text-xs font-bold tracking-wider uppercase line-clamp-1 max-w-[200px]">{{ $course->title }}</span>
                                </div>
                            @endif

                            <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-semibold backdrop-blur-md shadow-xs {{ $course->isOneTime() ? 'bg-amber-500/95 text-white' : 'bg-emerald-600/95 text-white' }}">
                                    @if ($course->isOneTime())
                                        <span>🔒</span> {{ $course->price > 0 ? $course->formattedPrice() : __('Separate access required') }}
                                    @else
                                        <span>{{ __('Included') }}</span>
                                    @endif
                                </span>
                            </div>

                            @if ($isCreator && $course->isDraft())
                                <div class="absolute top-3 right-3">
                                    <span class="rounded-md bg-slate-900/80 backdrop-blur-md px-2.5 py-0.5 text-xs font-semibold text-amber-300 border border-amber-400/30">
                                        {{ __('Draft') }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                            <div class="space-y-2">
                                <a href="{{ route('communities.courses.show', [$community, $course]) }}" class="block break-words text-base font-semibold text-ink group-hover:text-brand transition-colors">
                                    {{ $course->title }}
                                </a>

                                @if ($hasAccess)
                                    <p class="break-words text-sm text-muted line-clamp-2 leading-relaxed">
                                        {{ $course->description ?? __('No course description provided.') }}
                                    </p>
                                    <div class="flex items-center gap-1.5 text-xs text-muted font-medium pt-1">
                                        <svg class="w-3.5 h-3.5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        <span>{{ $course->visible_lessons_count }} {{ $course->visible_lessons_count === 1 ? __('lesson') : __('lessons') }}</span>
                                    </div>
                                @else
                                    <div class="rounded-xl bg-amber-500/10 border border-amber-500/20 p-3 text-xs text-amber-900 space-y-1">
                                        <div class="font-bold flex items-center gap-1.5">
                                            <span>🔒</span> {{ __('Locked Course') }}
                                        </div>
                                        <p class="text-amber-800">{{ __('Purchase required to unlock lessons and description.') }}</p>
                                    </div>
                                @endif
                            </div>

                            <div class="pt-3 border-t border-line/60 space-y-3">
                                @if ($hasAccess && $isActiveMember && $course->isPublished())
                                    @php($percent = $course->progress_total ? (int) round(100 * $course->progress_completed / $course->progress_total) : 0)
                                    <div class="space-y-1.5">
                                        <div class="flex items-center justify-between text-xs font-semibold text-muted">
                                            <span>{{ __('Your Progress') }}: {{ $percent }}%</span>
                                            <span class="text-brand">{{ $course->progress_completed }}/{{ $course->progress_total }}</span>
                                        </div>
                                        <div x-data="{ width: '{{ (int) $percent }}%' }" class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-brand rounded-full transition-all duration-300" :style="{ width: width }"></div>
                                        </div>
                                        <progress class="sr-only" max="100" value="{{ $percent }}" aria-label="{{ __('Course completion percentage') }}">{{ $percent }}%</progress>
                                    </div>
                                @endif

                                <div class="flex items-center justify-between text-xs">
                                    <a href="{{ route('communities.courses.show', [$community, $course]) }}" class="inline-flex items-center font-semibold text-brand hover:text-brand-hover group-hover:translate-x-0.5 transition-transform">
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
    </div>
</x-app-layout>
