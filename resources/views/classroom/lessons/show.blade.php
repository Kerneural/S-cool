<x-app-layout>
    @php
        $viewer = auth()->user();
        $tracksProgress = $isActiveMember && $course->isPublished();
        $publishedOutlineLessons = $course->sections->flatMap->lessons->filter(fn ($item) => $item->isPublished());
        $outlineTotal = $publishedOutlineLessons->count();
        $outlineCompleted = $tracksProgress ? $publishedOutlineLessons->filter(fn ($item) => $item->isCompletedBy($viewer))->count() : 0;
        $outlinePercentage = $outlineTotal ? (int) round($outlineCompleted / $outlineTotal * 100) : 0;
        $currentSectionId = $lesson->course_section_id;
        $outlineSections = $course->sections->map(fn ($section) => [
            'id' => $section->id,
            'lessons' => $section->lessons->map(fn ($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'completed' => $tracksProgress && $item->isPublished() && $item->isCompletedBy($viewer),
            ])->values(),
        ])->values();
        $lessonHtml = \App\Support\LessonMarkdown::toHtml($lesson->content);
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        <x-classroom-feedback />

        <div class="lesson-layout">
            <!-- Lesson heading: compact context, then the one page h1 -->
            <header class="lesson-layout__head min-w-0 space-y-3">
                <nav aria-label="{{ __('Breadcrumb') }}" class="flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1 text-sm text-muted">
                    <a href="{{ route('communities.classroom.index', $community) }}" class="underline-offset-4 transition-colors duration-150 hover:text-brand hover:underline">{{ __('Classroom') }}</a>
                    <span aria-hidden="true" class="text-slate-300">/</span>
                    <a href="{{ route('communities.courses.show', [$community, $course]) }}" class="min-w-0 break-words underline-offset-4 transition-colors duration-150 hover:text-brand hover:underline">{{ $course->title }}</a>
                </nav>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($lesson->section)
                        <span class="text-xs font-semibold uppercase tracking-wide text-brand">{{ $lesson->section->title }}</span>
                    @endif
                    @if ($lesson->isPractical())
                        <span class="rounded-md bg-teal-100 px-2 py-0.5 text-[11px] font-semibold text-teal-800">{{ __('Practical') }}</span>
                    @endif
                    @if ($isCreator)
                        <span class="rounded-md px-2 py-0.5 text-[11px] font-semibold {{ $lesson->isPublished() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">{{ $lesson->status }}</span>
                    @endif
                </div>
                <h1 class="break-words text-2xl font-bold leading-tight tracking-tight text-ink sm:text-3xl">{{ $lesson->title }}</h1>
            </header>

            <!-- Course content rail: sticky on wide screens, a disclosure before the body on narrow ones -->
            <aside x-data="courseOutline({ sections: @js($outlineSections), currentSection: @js($currentSectionId) })"
                   @lesson-progress-updated.window="progressUpdated($event.detail)"
                   aria-labelledby="lesson-outline-heading"
                   class="lesson-layout__rail">
                <div x-data="{
                        completedCount: @js($outlineCompleted),
                        totalCount: @js($outlineTotal),
                        percentage: @js($outlinePercentage)
                     }"
                     @lesson-progress-updated.window="if ($event.detail?.summary && $event.detail.lessonId === @js($lesson->id)) {
                        completedCount = $event.detail.summary.course_completed_lessons_count;
                        totalCount = $event.detail.summary.course_published_lessons_count;
                        percentage = $event.detail.summary.course_progress_percentage;
                     }"
                     class="lesson-rail">
                    <div class="space-y-3 border-b border-line p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h2 id="lesson-outline-heading" class="text-sm font-semibold text-ink">{{ __('Course content') }}</h2>
                            <a href="{{ route('communities.courses.show', [$community, $course]) }}" class="text-xs font-semibold text-brand underline-offset-4 hover:underline">{{ __('Course overview') }}</a>
                        </div>
                        @if ($tracksProgress)
                            <div class="space-y-1.5">
                                <p role="status" class="text-xs tabular-nums text-muted">
                                    <span x-text="completedCount + ' / ' + totalCount + ' · ' + percentage + '%'">{{ $outlineCompleted }} / {{ $outlineTotal }} · {{ $outlinePercentage }}%</span>
                                    <span class="sr-only">{{ __('lessons completed') }}</span>
                                </p>
                                <progress class="lesson-rail__progress" max="100" value="{{ $outlinePercentage }}" :value="percentage" aria-label="{{ __('Course completion percentage') }}">{{ $outlinePercentage }}%</progress>
                            </div>
                        @endif
                        <button type="button" hidden x-init="$el.hidden = false"
                                class="lg:hidden flex min-h-11 w-full items-center justify-between rounded-lg border border-control-line/40 px-3 text-sm font-semibold text-ink transition-colors duration-150 hover:bg-slate-50"
                                @click="panelOpen = !panelOpen" :aria-expanded="panelOpen.toString()" aria-controls="lesson-outline-panel">
                            <span x-text="panelOpen ? @js(__('Hide course content')) : @js(__('Show course content'))">{{ __('Show course content') }}</span>
                            <svg class="lesson-chevron h-4 w-4 text-muted" :class="{ 'rotate-180': panelOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>

                    <div id="lesson-outline-panel" :class="{ 'max-lg:hidden': !panelOpen }" class="lesson-rail__panel">
                        <div class="border-b border-line p-3">
                            <label for="lesson-outline-search" class="sr-only">{{ __('Search lesson titles') }}</label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                                <input id="lesson-outline-search" type="search" autocomplete="off" placeholder="{{ __('Search lessons') }}"
                                       :value="query" @input="setQuery($event.target.value); $refs.list.scrollTop = 0" @keydown.escape="clear()"
                                       class="block min-h-10 w-full rounded-lg border-control-line/40 py-2 pl-8 pr-16 text-sm shadow-none focus:border-brand focus:ring-brand">
                                <button type="button" x-show="searching" x-cloak @click="clear(); $el.previousElementSibling.focus()"
                                        class="absolute right-1.5 top-1/2 -translate-y-1/2 rounded-md px-2 py-1 text-xs font-semibold text-brand hover:bg-brand-soft">{{ __('Clear') }}</button>
                            </div>
                            <p x-show="searching" x-cloak role="status" class="mt-2 text-xs text-muted"
                               x-text="matchCount === 0 ? @js(__('No lessons match this title.')) : matchCount + ' ' + (matchCount === 1 ? @js(__('matching lesson')) : @js(__('matching lessons')))"></p>
                        </div>

                        <div x-ref="list" class="lesson-rail__list"
                             x-init="queueCurrent($el); $watch('panelOpen', value => { if (value) queueCurrent($el); }); $watch('searching', value => { if (!value) queueCurrent($el); })">
                            @foreach ($course->sections as $section)
                                @php
                                    $sectionLessons = $section->lessons;
                                    $sectionPublished = $sectionLessons->filter(fn ($item) => $item->isPublished());
                                    $sectionDone = $tracksProgress ? $sectionPublished->filter(fn ($item) => $item->isCompletedBy($viewer))->count() : 0;
                                @endphp
                                <details class="outline-section" x-show="!searching || sectionMatches(@js($section->id)) > 0"
                                         @if ($section->id === $currentSectionId) open @endif
                                         :open="isOpen(@js($section->id))" @toggle="onToggle(@js($section->id), $el.open)">
                                    <summary class="outline-section__summary">
                                        <svg class="lesson-chevron outline-section__chevron h-4 w-4 shrink-0 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        <span class="min-w-0 flex-1 break-words">{{ $section->title }}</span>
                                        <span class="shrink-0 text-xs font-medium tabular-nums text-muted">
                                            @if ($tracksProgress)
                                                <span x-text="sectionCompleted(@js($section->id))">{{ $sectionDone }}</span>/{{ $sectionPublished->count() }}
                                                <span class="sr-only">{{ __('completed') }}</span>
                                            @else
                                                {{ $sectionLessons->count() }}
                                            @endif
                                        </span>
                                    </summary>
                                    <ul class="pb-2">
                                        @foreach ($sectionLessons as $item)
                                            @php
                                                $isCurrent = $item->id === $lesson->id;
                                                $tracksItem = $tracksProgress && $item->isPublished();
                                                $isCompleted = $tracksItem && $item->isCompletedBy($viewer);
                                            @endphp
                                            <li x-show="matches(@js($item->id))">
                                                <a href="{{ route('communities.lessons.show', [$community, $course, $item]) }}"
                                                   @if ($isCurrent) aria-current="page" @endif
                                                   class="outline-row {{ $isCurrent ? 'outline-row--current' : '' }}">
                                                    @if ($tracksItem)
                                                        <span :class="{ 'outline-row__state--done': completed[@js($item->id)] }" @class(['outline-row__state', 'outline-row__state--done' => $isCompleted]) aria-hidden="true">
                                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                        </span>
                                                        <span class="sr-only" x-text="completed[@js($item->id)] ? @js(__('Completed:')) : @js(__('Not completed:'))">{{ $isCompleted ? __('Completed:') : __('Not completed:') }}</span>
                                                    @else
                                                        <span class="outline-row__state outline-row__state--none" aria-hidden="true"></span>
                                                    @endif
                                                    <span class="min-w-0 flex-1 break-words">{{ $item->title }}</span>
                                                    @if ($item->video_url)
                                                        <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-muted" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M6.3 2.84A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.27l9.34-5.89a1.5 1.5 0 000-2.54L6.3 2.84z"/></svg>
                                                        <span class="sr-only">{{ __('(video)') }}</span>
                                                    @endif
                                                    @if ($isCreator && $item->isDraft())
                                                        <span class="shrink-0 text-[11px] italic text-muted">{{ __('draft') }}</span>
                                                    @endif
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endforeach
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Reading column: media, article, practical work, then one action region -->
            <div class="lesson-layout__main min-w-0 space-y-8">
                @if ($lesson->embed_url || $lesson->video_url)
                    <div x-data="{ showVideo: true }" class="space-y-2">
                        <div x-show="showVideo">
                            <x-secure-video-player :url="$lesson->video_url" :title="$lesson->title" :community="$community" :lesson="$lesson" />
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-muted">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="flex items-center gap-1.5">
                                    <svg class="inline h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <span>{{ __('Protected stream — Direct downloads disabled') }}</span>
                                </p>
                                @if ($lesson->provider_url)
                                    <span aria-hidden="true" class="text-slate-300">•</span>
                                    <p>
                                        {{ __('Video unavailable?') }}
                                        <a class="break-all font-semibold text-brand underline-offset-4 hover:underline" href="{{ $lesson->provider_url }}" target="_blank" rel="noopener noreferrer">
                                            {{ __('Open on provider') }}
                                        </a>
                                    </p>
                                @endif
                            </div>
                            <button type="button" @click="showVideo = !showVideo" class="min-h-8 font-semibold text-muted transition-colors duration-150 hover:text-ink">
                                <span x-show="showVideo">{{ __('Hide video') }}</span>
                                <span x-show="!showVideo" x-cloak>{{ __('Show video') }}</span>
                            </button>
                        </div>
                    </div>
                @endif

                @if ($lessonHtml !== '')
                    {{-- Trusted output: produced only by the configured safe renderer (raw HTML stripped, unsafe links removed). --}}
                    <article class="lesson-prose">{!! $lessonHtml !!}</article>
                @elseif (! ($lesson->embed_url || $lesson->video_url))
                    <p class="text-sm text-muted">{{ __('No text notes for this lesson.') }}</p>
                @endif

                @if ($lesson->isPractical())
                    <x-lesson-proof-of-work
                        :community="$community"
                        :course="$course"
                        :lesson="$lesson"
                        :current-proof-of-work="$currentProofOfWork"
                        :proof-of-work-history="$proofOfWorkHistory"
                        :is-creator="$isCreator"
                        :is-active-member="$isActiveMember"
                    />
                @endif

                <section aria-label="{{ __('Lesson actions') }}" class="space-y-4 border-t border-line pt-6">
                    @if ($isActiveMember && $course->isPublished() && $lesson->isPublished())
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <x-lesson-progress :community="$community" :course="$course" :lesson="$lesson" />
                            <a href="{{ route('communities.posts.index', [$community, 'course_id' => $course->id, 'lesson_id' => $lesson->id]) }}"
                               class="inline-flex min-h-11 items-center gap-2 self-start rounded-lg px-3 text-sm font-semibold text-brand transition-colors duration-150 hover:bg-brand-soft sm:self-auto">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <span>{{ __('Ask a question about this lesson') }}</span>
                            </a>
                        </div>
                    @endif

                    <nav aria-label="{{ __('Lesson navigation') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            @if ($prevLesson)
                                <a class="lesson-step" href="{{ route('communities.lessons.show', [$community, $course, $prevLesson]) }}">
                                    <span class="text-xs font-semibold text-muted"><span aria-hidden="true">&larr;</span> {{ __('Previous') }}</span>
                                    <span class="mt-1 break-words text-sm font-semibold text-ink">{{ $prevLesson->title }}</span>
                                </a>
                            @endif
                        </div>
                        <div>
                            @if ($nextLesson)
                                <a class="lesson-step lesson-step--next sm:text-right" href="{{ route('communities.lessons.show', [$community, $course, $nextLesson]) }}">
                                    <span class="text-xs font-semibold text-brand">{{ __('Next') }} <span aria-hidden="true">&rarr;</span></span>
                                    <span class="mt-1 break-words text-sm font-semibold text-ink">{{ $nextLesson->title }}</span>
                                </a>
                            @else
                                <a class="lesson-step lesson-step--next sm:text-right" href="{{ route('communities.courses.show', [$community, $course]) }}">
                                    <span class="text-xs font-semibold text-brand">{{ __('Last lesson') }}</span>
                                    <span class="mt-1 text-sm font-semibold text-ink">{{ __('Return to course') }} <span aria-hidden="true">&rarr;</span></span>
                                </a>
                            @endif
                        </div>
                    </nav>
                </section>
            </div>
        </div>

        <!-- Creator Edit Lesson Drawer / Details -->
        @if ($isCreator)
            <details class="mt-8 rounded-xl bg-surface p-6 shadow-2xs border border-line" @if ($errors->any() && old('_form') === 'edit-lesson') open @endif>
                <summary class="cursor-pointer font-semibold text-sm text-brand hover:text-brand-hover flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>{{ __('Edit Lesson Details') }}</span>
                    </span>
                    <span class="text-xs text-muted font-normal">{{ __('Click to expand') }}</span>
                </summary>
                <form method="POST" action="{{ route('communities.lessons.update', [$community, $course, $lesson->section, $lesson]) }}" class="mt-5 space-y-4 pt-4 border-t border-line/60">
                    @csrf @method('PUT')
                    <input type="hidden" name="_form" value="edit-lesson">
                    <x-classroom-lesson-fields form-key="edit-lesson" :lesson="$lesson" />
                    <div class="flex justify-end pt-2">
                        <x-primary-button>
                            {{ __('Save Lesson') }}
                        </x-primary-button>
                    </div>
                </form>
            </details>
        @endif
    </div>
</x-app-layout>
