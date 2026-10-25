<x-app-layout>
    @php
        $viewer = auth()->user();
        $tracksProgress = $isActiveMember && $course->isPublished() && $isEntitled;
        $orderedLessons = $course->sections->flatMap->lessons;
        $entryLessons = $isCreator ? $orderedLessons : $orderedLessons->filter(fn ($item) => $item->isPublished());
        $firstLesson = $entryLessons->first();
        $nextIncomplete = $tracksProgress ? $entryLessons->first(fn ($item) => $item->isPublished() && ! $item->isCompletedBy($viewer)) : null;
        $entryLesson = null;
        $entryLabel = null;
        if ($isEntitled && $firstLesson) {
            if (! $tracksProgress) {
                [$entryLesson, $entryLabel] = [$firstLesson, $isCreator ? __('Preview first lesson') : __('Start course')];
            } elseif ($progressSummary['completed'] === 0) {
                [$entryLesson, $entryLabel] = [$firstLesson, __('Start course')];
            } elseif ($nextIncomplete) {
                [$entryLesson, $entryLabel] = [$nextIncomplete, __('Continue course')];
            } else {
                [$entryLesson, $entryLabel] = [$firstLesson, __('Review course')];
            }
        }
        $openSectionId = ($nextIncomplete ?? $firstLesson)?->course_section_id ?? $course->sections->first()?->id;
        $lessonCount = $isCreator ? $orderedLessons->count() : $entryLessons->count();
    @endphp

    <div class="mx-auto max-w-5xl space-y-8 px-4 py-6 sm:px-6 lg:py-8">
        <x-classroom-feedback />

        <!-- Course identity and the one learning action come before the curriculum -->
        <section class="rounded-xl border border-line bg-surface p-5 sm:p-6">
            <nav aria-label="{{ __('Breadcrumb') }}" class="mb-4 text-sm text-muted">
                <a class="underline-offset-4 transition-colors duration-150 hover:text-brand hover:underline" href="{{ route('communities.classroom.index', $community) }}">
                    <span aria-hidden="true">&larr;</span> {{ __('Classroom') }}
                </a>
            </nav>
            <div class="flex flex-col gap-6 md:flex-row md:items-start">
                <div class="min-w-0 flex-1 space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($course->isOneTime())
                            <span class="rounded-md border border-amber-200 bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-800">
                                {{ __('One-time Purchase') }} — {{ $course->formattedPrice() }}
                            </span>
                        @else
                            <span class="rounded-md border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                {{ __('Included with Community') }}
                            </span>
                        @endif
                        @if ($isCreator)
                            <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-ink">{{ $course->status }}</span>
                        @endif
                        @if ($isEntitled)
                            <span class="text-xs text-muted">
                                {{ trans_choice(':count section|:count sections', $course->sections->count(), ['count' => $course->sections->count()]) }}
                                · {{ trans_choice(':count lesson|:count lessons', $lessonCount, ['count' => $lessonCount]) }}
                            </span>
                        @endif
                    </div>

                    <h1 class="break-words text-2xl font-bold leading-tight tracking-tight text-ink sm:text-3xl">{{ $course->title }}</h1>

                    @if ($isEntitled && filled($course->description))
                        <p class="max-w-prose whitespace-pre-line break-words text-base leading-relaxed text-muted">{{ $course->description }}</p>
                    @endif

                    @if ($tracksProgress)
                        <div aria-label="{{ __('Your Progress') }}" role="group" class="max-w-md space-y-1.5">
                            <div class="flex items-baseline justify-between gap-3 text-sm">
                                <span class="font-semibold text-ink">{{ __('Your Progress') }}</span>
                                <span class="tabular-nums text-muted">{{ $progressSummary['completed'] }} {{ __('of') }} {{ $progressSummary['total'] }} {{ __('completed') }} ({{ $progressSummary['percentage'] }}%)</span>
                            </div>
                            <progress class="lesson-rail__progress" max="100" value="{{ $progressSummary['percentage'] }}" aria-label="{{ __('Course completion percentage') }}">{{ $progressSummary['percentage'] }}%</progress>
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-3 pt-1">
                        @if ($entryLesson)
                            <a href="{{ route('communities.lessons.show', [$community, $course, $entryLesson]) }}"
                               class="inline-flex min-h-11 items-center gap-2 rounded-lg bg-brand px-5 text-sm font-semibold text-white shadow-xs transition-colors duration-150 hover:bg-brand-hover">
                                <span>{{ $entryLabel }}</span>
                                <span aria-hidden="true">&rarr;</span>
                            </a>
                            @if ($entryLesson->isNot($firstLesson))
                                <span class="min-w-0 break-words text-sm text-muted">{{ __('Next:') }} <span class="font-medium text-ink">{{ $entryLesson->title }}</span></span>
                            @endif
                        @elseif ($isEntitled)
                            <p class="text-sm text-muted">{{ __('No lessons are available in this course yet.') }}</p>
                        @endif
                        @if (($isEntitled || $isCreator) && $course->isPublished())
                            <a href="{{ route('communities.posts.index', [$community, 'course_id' => $course->id]) }}"
                               class="inline-flex min-h-11 items-center gap-2 rounded-lg border border-line px-4 text-sm font-semibold text-ink transition-colors duration-150 hover:border-slate-300 hover:bg-slate-50">
                                <svg class="h-4 w-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <span>{{ __('Discuss this course') }}</span>
                            </a>
                        @endif
                    </div>
                </div>

                @if ($course->cover_path)
                    <div class="aspect-video w-full shrink-0 overflow-hidden rounded-lg border border-line bg-slate-100 md:w-72">
                        <img src="{{ route('communities.courses.cover', [$community, $course]) }}" alt="" width="640" height="360" class="h-full w-full object-cover">
                    </div>
                @endif
            </div>
        </section>

        @if ($isActiveMember && $course->isPublished() && ! $isEntitled)
            <section aria-label="{{ __('Course Purchase') }}" class="rounded-xl border border-amber-200 bg-amber-50/60 p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-1.5 rounded-md bg-amber-500/15 px-2.5 py-0.5 text-xs font-semibold text-amber-900 border border-amber-500/30">
                            <span aria-hidden="true">🔒</span> {{ __('Paid Course') }}
                        </div>
                        <h2 class="text-xl sm:text-2xl font-bold text-ink tracking-tight">{{ __('Unlock full access to this course') }}</h2>
                        <p class="text-sm text-muted max-w-xl leading-relaxed">
                            {{ __('This course is available as a one-time purchase. Complete payment via SePay Test Mode to unlock all lessons.') }}
                        </p>
                        <p class="text-sm font-semibold text-ink pt-1">
                            {{ __('Price') }}: <span class="text-brand text-lg font-bold">{{ $course->formattedPrice() }}</span>
                        </p>
                    </div>
                    <div class="shrink-0">
                        <form method="POST" action="{{ route('communities.courses.checkout', [$community, $course]) }}">
                            @csrf
                            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-brand px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 transition-colors cursor-pointer">
                                {{ __('Buy Course') }} — {{ $course->formattedPrice() }}
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        @endif

        @if ($isCreator)
            <section aria-labelledby="course-management-heading" class="space-y-3">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <h2 id="course-management-heading" class="text-sm font-semibold uppercase tracking-wide text-muted">{{ __('Manage course') }}</h2>
                    <p class="text-xs text-muted">{{ __('Unpublish to hide content; permanent deletion is not available.') }}</p>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <details class="rounded-xl bg-surface p-5 shadow-2xs border border-line" @if ($errors->has('cover')) open @endif>
                        <summary class="cursor-pointer font-semibold text-sm text-brand hover:text-brand-hover flex items-center justify-between">
                            <span>{{ __('Course Cover') }}</span>
                            <span class="text-xs text-muted font-normal">{{ __('Manage') }}</span>
                        </summary>
                        <div class="mt-4 space-y-4 pt-3 border-t border-line/60">
                            @if ($course->cover_path)
                                <div class="space-y-2">
                                    <span class="text-xs font-semibold text-muted block">{{ __('Current Cover:') }}</span>
                                    <div class="aspect-video max-w-xs overflow-hidden rounded-lg border border-line bg-slate-900">
                                        <img src="{{ route('communities.courses.cover', [$community, $course]) }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                                    </div>
                                    <form method="POST" action="{{ route('communities.courses.cover.destroy', [$community, $course]) }}" x-data="{ confirmMsg: @js(__('Remove this course cover?')) }" @submit="if (! confirm(confirmMsg)) $event.preventDefault()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-700 underline cursor-pointer">
                                            {{ __('Remove Cover') }}
                                        </button>
                                    </form>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('communities.courses.cover.store', [$community, $course]) }}" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <div>
                                    <label for="course-cover-input" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">{{ __('Upload New Cover') }}</label>
                                    <input id="course-cover-input" type="file" name="cover" accept=".jpg,.jpeg,.png,.webp" required class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-soft file:text-brand hover:file:bg-teal-100 cursor-pointer">
                                    <span class="block text-xs text-muted mt-1">{{ __('JPEG, PNG, or WebP. Max 5 MiB, max 4096px.') }}</span>
                                    <x-input-error :messages="$errors->get('cover')" class="mt-1" />
                                </div>
                                <div class="flex justify-end">
                                    <x-primary-button>
                                        {{ __('Save Cover') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>
                    </details>

                    <details class="rounded-xl bg-surface p-5 shadow-2xs border border-line" @if ($errors->any() && old('_form') === 'edit-course') open @endif>
                        <summary class="cursor-pointer font-semibold text-sm text-brand hover:text-brand-hover flex items-center justify-between">
                            <span>{{ __('Edit Course') }}</span>
                            <span class="text-xs text-muted font-normal">{{ __('Settings') }}</span>
                        </summary>
                        <form method="POST" action="{{ route('communities.courses.update', [$community, $course]) }}" class="mt-4 space-y-4 pt-3 border-t border-line/60">
                            @csrf @method('PUT')
                            <input type="hidden" name="_form" value="edit-course">
                            <x-classroom-course-fields form-key="edit-course" :course="$course" />
                            <div class="flex justify-end pt-2">
                                <x-primary-button>
                                    {{ __('Save Course') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </details>

                    <details class="rounded-xl bg-surface p-5 shadow-2xs border border-line" @if ($errors->any() && old('_form') === 'create-section') open @endif>
                        <summary class="cursor-pointer font-semibold text-sm text-brand hover:text-brand-hover flex items-center justify-between">
                            <span>{{ __('Add Section') }}</span>
                            <span class="text-xs text-muted font-normal">{{ __('Click to expand') }}</span>
                        </summary>
                        <form method="POST" action="{{ route('communities.courses.sections.store', [$community, $course]) }}" class="mt-4 space-y-4 pt-3 border-t border-line/60">
                            @csrf
                            <input type="hidden" name="_form" value="create-section">
                            <div>
                                <x-input-label for="new_section_title" :value="__('Section Title')" />
                                <x-text-input id="new_section_title" name="title" required maxlength="255" value="{{ old('_form') === 'create-section' && is_string(old('title')) ? old('title') : '' }}" class="mt-1 block w-full" placeholder="{{ __('e.g., Module 1: Foundations') }}" />
                            </div>
                            <div class="flex justify-end pt-2">
                                <x-primary-button>
                                    {{ __('Create Section') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </details>
                </div>
            </section>
        @endif

        @if ($isEntitled)
            <section aria-labelledby="curriculum-heading" class="space-y-3">
                <h2 id="curriculum-heading" class="text-lg font-semibold text-ink">{{ __('Course content') }}</h2>
                @forelse ($course->sections as $section)
                    @php
                        $editKey = 'edit-section-'.$section->id;
                        $createKey = 'create-lesson-'.$section->id;
                        $sectionHasErrors = $errors->any() && in_array(old('_form'), [$editKey, $createKey], true);
                        $sectionPublished = $section->lessons->filter(fn ($item) => $item->isPublished());
                        $sectionDone = $tracksProgress ? $sectionPublished->filter(fn ($item) => $item->isCompletedBy($viewer))->count() : 0;
                    @endphp
                    <details class="curriculum-section min-w-0" @if ($section->id === $openSectionId || $sectionHasErrors) open @endif>
                        <summary>
                            <svg class="lesson-chevron outline-section__chevron h-4 w-4 shrink-0 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            <h3 class="min-w-0 flex-1 break-words text-base font-semibold text-ink">{{ $section->title }}</h3>
                            <span class="shrink-0 text-sm tabular-nums text-muted">
                                @if ($tracksProgress)
                                    {{ $sectionDone }}/{{ $sectionPublished->count() }} <span class="sr-only">{{ __('completed') }}</span>
                                @else
                                    {{ trans_choice(':count lesson|:count lessons', $section->lessons->count(), ['count' => $section->lessons->count()]) }}
                                @endif
                            </span>
                        </summary>

                        @if ($isCreator)
                            <div class="space-y-3 border-b border-line bg-slate-50/60 px-5 py-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-muted">{{ __('Section tools') }}</span>
                                    <x-classroom-reorder :ids="$course->sections->modelKeys()" :id="$section->id" :action="route('communities.courses.sections.reorder', [$community, $course])" />
                                </div>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <details class="bg-surface p-3 rounded-lg border border-line" @if ($errors->any() && old('_form') === $editKey) open @endif>
                                        <summary class="cursor-pointer text-xs font-semibold text-brand hover:text-brand-hover">{{ __('Edit Section') }}</summary>
                                        <form method="POST" action="{{ route('communities.courses.sections.update', [$community, $course, $section]) }}" class="mt-3 space-y-3 pt-2 border-t border-line/60">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="_form" value="{{ $editKey }}">
                                            <label class="block text-xs font-medium text-slate-700">
                                                {{ __('Section Title') }}
                                                <input name="title" required maxlength="255" value="{{ old('_form') === $editKey ? (is_string(old('title')) ? old('title') : '') : $section->title }}" class="mt-1 block w-full rounded-lg border-line text-xs shadow-2xs focus:border-brand focus:ring-brand">
                                            </label>
                                            <button class="px-3 py-1.5 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition cursor-pointer">{{ __('Save Section') }}</button>
                                        </form>
                                    </details>

                                    <details class="bg-surface p-3 rounded-lg border border-line" @if ($errors->any() && old('_form') === $createKey) open @endif>
                                        <summary class="cursor-pointer text-xs font-semibold text-brand hover:text-brand-hover">{{ __('Add Lesson') }}</summary>
                                        <form method="POST" action="{{ route('communities.lessons.store', [$community, $course, $section]) }}" class="mt-3 space-y-3 pt-2 border-t border-line/60">
                                            @csrf
                                            <input type="hidden" name="_form" value="{{ $createKey }}">
                                            <x-classroom-lesson-fields :form-key="$createKey" />
                                            <div class="flex justify-end pt-1">
                                                <x-primary-button>
                                                    {{ __('Create Lesson') }}
                                                </x-primary-button>
                                            </div>
                                        </form>
                                    </details>
                                </div>
                            </div>
                        @endif

                        <ul>
                            @forelse ($section->lessons as $lesson)
                                @php
                                    $tracksItem = $tracksProgress && $lesson->isPublished();
                                    $isDone = $tracksItem && $lesson->isCompletedBy($viewer);
                                @endphp
                                <li class="flex flex-col sm:flex-row sm:items-center">
                                    <a href="{{ route('communities.lessons.show', [$community, $course, $lesson]) }}" class="outline-row min-w-0 flex-1 {{ $entryLesson?->is($lesson) && $tracksProgress ? 'outline-row--current' : '' }}">
                                        @if ($tracksItem)
                                            <span @class(['outline-row__state', 'outline-row__state--done' => $isDone]) aria-hidden="true">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            </span>
                                            <span class="sr-only">{{ $isDone ? __('Completed:') : __('Not completed:') }}</span>
                                        @else
                                            <span class="outline-row__state outline-row__state--none" aria-hidden="true"></span>
                                        @endif
                                        <span class="min-w-0 flex-1 break-words">{{ $lesson->title }}</span>
                                        @if ($lesson->video_url)
                                            <span class="inline-flex shrink-0 items-center gap-1 text-xs text-muted">
                                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M6.3 2.84A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.27l9.34-5.89a1.5 1.5 0 000-2.54L6.3 2.84z"/></svg>
                                                {{ __('Video') }}
                                            </span>
                                        @endif
                                        @if ($lesson->isPractical())
                                            <span class="shrink-0 rounded bg-teal-100 px-1.5 py-0.5 text-[11px] font-semibold text-teal-800">{{ __('Practical') }}</span>
                                        @endif
                                    </a>

                                    @if ($isCreator)
                                        <div class="flex shrink-0 items-center gap-2 px-5 pb-3 sm:pb-0 sm:pl-0">
                                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-md {{ $lesson->isPublished() ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-800' }}">
                                                {{ $lesson->status }}
                                            </span>
                                            <x-classroom-reorder :ids="$section->lessons->modelKeys()" :id="$lesson->id" :action="route('communities.lessons.reorder', [$community, $course, $section])" />
                                        </div>
                                    @endif
                                </li>
                            @empty
                                <li class="px-5 py-4 text-sm text-muted">{{ __('No lessons in this section.') }}</li>
                            @endforelse
                        </ul>
                    </details>
                @empty
                    <div class="rounded-xl bg-surface p-12 text-center text-muted border border-line">
                        <p class="text-sm">{{ __('No modules or sections yet.') }}</p>
                    </div>
                @endforelse
            </section>
        @endif
    </div>
</x-app-layout>
