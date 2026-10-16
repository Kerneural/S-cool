<x-app-layout>
    <x-slot name="header">
        <nav class="flex items-center space-x-2 text-xs sm:text-sm text-slate-500">
            <a href="{{ route('communities.classroom.index', $community) }}" class="hover:text-brand-600 transition">
                {{ __('Classroom') }}
            </a>
            <span class="text-slate-300">/</span>
            <a href="{{ route('communities.courses.show', [$community, $course]) }}" class="hover:text-brand-600 transition truncate max-w-[200px]">
                {{ $course->title }}
            </a>
            <span class="text-slate-300">/</span>
            <span class="text-slate-900 font-semibold truncate max-w-[280px]">{{ $lesson->title }}</span>
        </nav>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <x-classroom-feedback />

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 items-start">
            <!-- Main Content Area (8 cols on lg) -->
            <article class="min-w-0 space-y-6 rounded-2xl bg-white p-6 sm:p-8 shadow-xs border border-slate-200/80 lg:col-span-8">
                <!-- Lesson Header Info -->
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($lesson->section)
                            <span class="text-xs font-medium text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-full">
                                {{ $lesson->section->title }}
                            </span>
                        @endif
                        @if ($isCreator)
                            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $lesson->isPublished() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                {{ $lesson->status }}
                            </span>
                        @endif
                    </div>

                    <h1 class="break-words text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ $lesson->title }}
                    </h1>
                </div>

                <!-- Responsive 16:9 Video Embed Player (if video URL provided) -->
                @if ($lesson->embed_url)
                    <div x-data="{ showVideo: true }" class="space-y-3">
                        <div x-show="showVideo" class="aspect-video overflow-hidden rounded-2xl bg-slate-950 shadow-md border border-slate-800">
                            <iframe src="{{ $lesson->embed_url }}" title="{{ $lesson->title }}" class="h-full w-full border-0" allow="fullscreen; picture-in-picture" allowfullscreen></iframe>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500 pt-1">
                            <p>
                                {{ __('Video unavailable?') }}
                                <a class="break-all font-semibold text-brand-600 hover:underline" href="{{ $lesson->provider_url }}" target="_blank" rel="noopener noreferrer">
                                    {{ __('Open on provider') }}
                                </a>
                            </p>
                            <button type="button" @click="showVideo = !showVideo" class="font-semibold text-slate-600 hover:text-slate-900 transition">
                                <span x-show="showVideo">{{ __('Hide video') }}</span>
                                <span x-show="!showVideo" x-cloak>{{ __('Show video') }}</span>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Reading Content Note (capped to comfortable measure) -->
                <div class="max-w-none prose prose-slate">
                    <p class="whitespace-pre-line break-words text-slate-800 leading-relaxed text-base">{{ $lesson->content ?? __('No text notes for this lesson.') }}</p>
                </div>

                <!-- Proof of Work Section (if practical lesson) -->
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

                <!-- Progress Action & Continue CTA in Natural Reading Flow (if eligible member) -->
                @if ($isActiveMember && $course->isPublished() && $lesson->isPublished())
                    <div class="pt-6 border-t border-slate-100 space-y-4">
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <x-lesson-progress :community="$community" :course="$course" :lesson="$lesson" />

                            @if ($nextLesson)
                                <a href="{{ route('communities.lessons.show', [$community, $course, $nextLesson]) }}"
                                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-hover text-white font-semibold text-xs sm:text-sm shadow-xs transition active:scale-[0.99] shrink-0">
                                    <span>{{ __('Continue to Next Lesson') }}</span>
                                    <span>&rarr;</span>
                                </a>
                            @else
                                <a href="{{ route('communities.courses.show', [$community, $course]) }}"
                                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs sm:text-sm shadow-2xs transition shrink-0">
                                    <span>{{ __('Return to Course') }}</span>
                                    <span>&rarr;</span>
                                </a>
                            @endif
                        </div>
                        <div class="flex items-center justify-end">
                            <a href="{{ route('communities.posts.index', [$community, 'course_id' => $course->id, 'lesson_id' => $lesson->id]) }}"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 hover:text-brand-800 bg-brand-50/80 hover:bg-brand-100/80 px-3.5 py-2 rounded-xl border border-brand-200/80 transition shadow-2xs active:scale-[0.99]">
                                <svg class="w-3.5 h-3.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <span>{{ __('Ask a Question about this Lesson') }}</span>
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Previous / Next Lesson Navigation Cards -->
                <nav aria-label="Lesson navigation" class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-slate-100 pt-6">
                    <div>
                        @if ($prevLesson)
                            <a class="flex flex-col p-4 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/20 transition group active:scale-[0.99]" href="{{ route('communities.lessons.show', [$community, $course, $prevLesson]) }}">
                                <span class="text-xs font-semibold text-slate-500 group-hover:text-brand-600 transition flex items-center gap-1">
                                    <span>&larr;</span> {{ __('Previous Lesson') }}
                                </span>
                                <span class="break-words text-sm font-bold text-slate-900 mt-1 group-hover:text-brand-600 transition">
                                    {{ $prevLesson->title }}
                                </span>
                            </a>
                        @endif
                    </div>
                    <div>
                        @if ($nextLesson)
                            <a class="flex flex-col p-4 rounded-xl border border-teal-200 bg-brand-50/30 hover:bg-brand-50 hover:border-brand-500 transition group active:scale-[0.99] sm:text-right" href="{{ route('communities.lessons.show', [$community, $course, $nextLesson]) }}">
                                <span class="text-xs font-semibold text-brand-600 flex items-center justify-start sm:justify-end gap-1">
                                    {{ __('Next Lesson') }} <span>&rarr;</span>
                                </span>
                                <span class="break-words text-sm font-bold text-slate-900 mt-1 group-hover:text-brand-600 transition">
                                    {{ $nextLesson->title }}
                                </span>
                            </a>
                        @endif
                    </div>
                </nav>
            </article>

            <!-- Sticky Course Content Outline Sidebar (4 cols on lg, 280-300px) -->
            @php
                $publishedOutlineLessons = $course->sections->flatMap->lessons->filter(fn ($item) => $item->isPublished());
                $outlineTotal = $publishedOutlineLessons->count();
                $outlineCompleted = $publishedOutlineLessons->filter(fn ($item) => $item->isCompletedBy(auth()->user()))->count();
                $outlinePercentage = $outlineTotal ? (int) round($outlineCompleted / $outlineTotal * 100) : 0;
            @endphp
            <aside x-data="{
                outlineOpen: false,
                completedCount: @js($outlineCompleted),
                totalCount: @js($outlineTotal),
                percentage: @js($outlinePercentage)
            }" @lesson-progress-updated.window="if ($event.detail?.summary && $event.detail.lessonId === @js($lesson->id)) {
                completedCount = $event.detail.summary.course_completed_lessons_count;
                totalCount = $event.detail.summary.course_published_lessons_count;
                percentage = $event.detail.summary.course_progress_percentage;
            }" class="min-w-0 space-y-4 rounded-2xl bg-white p-5 sm:p-6 shadow-xs border border-slate-200/80 lg:col-span-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="font-bold text-base text-slate-900">{{ __('Course Content') }}</h2>
                    <a href="{{ route('communities.courses.show', [$community, $course]) }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">
                        {{ __('View Outline') }}
                    </a>
                </div>

                @if ($isActiveMember && $course->isPublished())
                    <p role="status" class="text-xs text-slate-600" x-text="completedCount + ' / ' + totalCount + ' · ' + percentage + '%'">{{ $outlineCompleted }} / {{ $outlineTotal }} · {{ $outlinePercentage }}%</p>
                    <progress class="w-full accent-brand" max="100" value="{{ $outlinePercentage }}" :value="percentage" aria-label="{{ __('Course completion percentage') }}">{{ $outlinePercentage }}%</progress>
                @endif

                <button hidden x-init="$el.hidden = false" type="button" class="lg:hidden min-h-11 w-full rounded-lg border border-line px-3 py-2 text-sm font-semibold text-brand" @click="outlineOpen = !outlineOpen" :aria-expanded="outlineOpen.toString()" aria-controls="lesson-outline">
                    <span x-show="!outlineOpen">{{ __('Show course outline') }}</span>
                    <span x-show="outlineOpen" x-cloak>{{ __('Hide course outline') }}</span>
                </button>
                <div id="lesson-outline" :class="{ 'hidden': !outlineOpen }" class="lg:block space-y-4">
                    @foreach ($course->sections as $section)
                        <div class="space-y-2">
                            <h3 class="break-words text-xs font-bold text-slate-400 uppercase tracking-wider">
                                {{ $section->title }}
                            </h3>
                            <ul class="space-y-1">
                                @foreach ($section->lessons as $item)
                                    @php($isCurrent = $item->id === $lesson->id)
                                    @php($isCompleted = $isActiveMember && $course->isPublished() && $item->isPublished() && $item->isCompletedBy(auth()->user()))
                                    <li x-data="{ completed: @js((bool) $isCompleted) }" @lesson-progress-updated.window="if ($event.detail?.lessonId === @js($item->id)) completed = $event.detail.completed">
                                        <a href="{{ route('communities.lessons.show', [$community, $course, $item]) }}"
                                           @if ($isCurrent) aria-current="page" @endif
                                           class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl text-xs sm:text-sm transition {{ $isCurrent ? 'bg-brand-50 text-brand-700 font-bold border-l-4 border-brand-600' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                            <span class="min-w-0 break-words flex items-center gap-1.5">
                                                <span>{{ $item->title }}</span>
                                                @if ($item->isPractical())
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-100 text-teal-800 shrink-0">
                                                        {{ __('Practical') }}
                                                    </span>
                                                @endif
                                                @if ($isCreator && $item->isDraft())
                                                    <span class="text-[11px] font-normal text-slate-400 italic">({{ __('draft') }})</span>
                                                @endif
                                            </span>
                                            @if ($isActiveMember && $course->isPublished() && $item->isPublished())
                                                <span class="text-xs shrink-0 font-medium" :class="completed ? 'text-emerald-700 font-semibold' : 'text-slate-500'" x-text="completed ? '{{ __('Completed') }}' : '{{ __('Incomplete') }}'">
                                                    {{ $isCompleted ? __('Completed') : __('Incomplete') }}
                                                </span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </aside>
        </div>

        <!-- Creator Edit Lesson Drawer / Details -->
        @if ($isCreator)
            <details class="rounded-2xl bg-white p-6 shadow-xs border border-slate-200/80" @if ($errors->any() && old('_form') === 'edit-lesson') open @endif>
                <summary class="cursor-pointer font-bold text-sm text-brand-600 hover:text-brand-700 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>{{ __('Edit Lesson Details') }}</span>
                    </span>
                    <span class="text-xs text-slate-400 font-normal">{{ __('Click to expand') }}</span>
                </summary>
                <form method="POST" action="{{ route('communities.lessons.update', [$community, $course, $lesson->section, $lesson]) }}" class="mt-5 space-y-4 pt-4 border-t border-slate-100">
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
