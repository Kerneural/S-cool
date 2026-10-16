<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <nav class="flex items-center space-x-2 text-xs sm:text-sm text-slate-500">
                <a class="hover:text-brand-600 transition" href="{{ route('communities.classroom.index', $community) }}">
                    {{ __('Classroom') }}
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-900 font-semibold truncate max-w-sm">{{ $course->title }}</span>
            </nav>
            <a class="text-xs sm:text-sm font-semibold text-brand-600 hover:text-brand-700 transition" href="{{ route('communities.classroom.index', $community) }}">
                &larr; {{ __('Back to Classroom') }}
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6">
        <x-classroom-feedback />

        @if ($isActiveMember && $course->isPublished() && $isEntitled)
            <section aria-label="{{ __('Your Progress') }}" class="space-y-3 rounded-2xl bg-white p-6 shadow-xs border border-slate-200/80">
                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-base text-slate-900">{{ __('Your Progress') }}</h2>
                    <span class="text-xs font-bold text-brand-600 bg-brand-50 px-2.5 py-1 rounded-full border border-brand-200">
                        {{ $progressSummary['percentage'] }}%
                    </span>
                </div>
                <p class="break-words text-sm text-slate-600">
                    {{ $progressSummary['completed'] }} {{ __('of') }} {{ $progressSummary['total'] }} {{ __('completed') }} ({{ $progressSummary['percentage'] }}%)
                </p>
                <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-brand-600 rounded-full transition-all duration-300" style="width: {{ $progressSummary['percentage'] }}%"></div>
                </div>
                <progress class="sr-only" max="100" value="{{ $progressSummary['percentage'] }}" aria-label="{{ __('Course completion percentage') }}">{{ $progressSummary['percentage'] }}%</progress>
            </section>
        @endif

        @if ($isActiveMember && $course->isPublished() && ! $isEntitled)
            <section aria-label="{{ __('Course Purchase') }}" class="rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50/70 via-white to-amber-50/40 p-6 sm:p-8 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-1.5 rounded-full bg-amber-100/80 px-3 py-1 text-xs font-semibold text-amber-900 border border-amber-300">
                            <span>🔒</span> {{ __('Paid Course') }}
                        </div>
                        <h2 class="text-xl sm:text-2xl font-bold text-slate-900">{{ __('Unlock full access to this course') }}</h2>
                        <p class="text-sm text-slate-600 max-w-xl leading-relaxed">
                            {{ __('This course is available as a one-time purchase. Complete payment via SePay Test Mode to unlock all lessons.') }}
                        </p>
                        <p class="text-sm font-semibold text-slate-900 pt-1">
                            {{ __('Price') }}: <span class="text-brand-600 text-lg font-bold">{{ $course->formattedPrice() }}</span>
                        </p>
                    </div>
                    <div class="shrink-0">
                        <form method="POST" action="{{ route('communities.courses.checkout', [$community, $course]) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition active:scale-[0.99]">
                                {{ __('Buy Course') }} — {{ $course->formattedPrice() }}
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        @endif

        <!-- Course Header Card -->
        <div class="space-y-4 rounded-2xl bg-white p-6 sm:p-8 shadow-xs border border-slate-200/80">
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="break-words text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $course->title }}</h1>
                @if ($course->isOneTime())
                    <span class="rounded-full bg-amber-50 text-amber-800 border border-amber-200 px-3 py-0.5 text-xs font-semibold">
                        {{ __('One-time Purchase') }} — {{ $course->formattedPrice() }}
                    </span>
                @else
                    <span class="rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-0.5 text-xs font-semibold">
                        {{ __('Included with Community') }}
                    </span>
                @endif
            </div>

            @if ($isEntitled)
                <p class="whitespace-pre-line break-words text-slate-600 text-sm sm:text-base leading-relaxed">
                    {{ $course->description }}
                </p>
            @endif

            @if ($isCreator)
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full font-semibold bg-slate-100 text-slate-700">
                        {{ $course->status }}
                    </span>
                    <span>{{ __('Unpublish to hide content; permanent deletion is not available.') }}</span>
                </div>
            @endif
        </div>

        @if (($isEntitled || $isCreator) && $course->isPublished())
            <div class="flex items-center justify-start">
                <a href="{{ route('communities.posts.index', [$community, 'course_id' => $course->id]) }}"
                   class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-brand-700 hover:text-brand-800 bg-white hover:bg-brand-50/80 px-4 py-2.5 rounded-2xl border border-brand-200 shadow-xs transition active:scale-[0.99]">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <span>{{ __('Ask a Question / Discuss Course') }}</span>
                </a>
            </div>
        @endif

        @if ($isCreator)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <details class="rounded-2xl bg-white p-5 shadow-xs border border-slate-200/80" @if ($errors->any() && old('_form') === 'edit-course') open @endif>
                    <summary class="cursor-pointer font-bold text-sm text-brand-600 hover:text-brand-700 flex items-center justify-between">
                        <span>{{ __('Edit Course') }}</span>
                        <span class="text-xs text-slate-400 font-normal">{{ __('Click to expand') }}</span>
                    </summary>
                    <form method="POST" action="{{ route('communities.courses.update', [$community, $course]) }}" class="mt-4 space-y-4 pt-3 border-t border-slate-100">
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

                <details class="rounded-2xl bg-white p-5 shadow-xs border border-slate-200/80" @if ($errors->any() && old('_form') === 'create-section') open @endif>
                    <summary class="cursor-pointer font-bold text-sm text-brand-600 hover:text-brand-700 flex items-center justify-between">
                        <span>{{ __('Add Section') }}</span>
                        <span class="text-xs text-slate-400 font-normal">{{ __('Click to expand') }}</span>
                    </summary>
                    <form method="POST" action="{{ route('communities.courses.sections.store', [$community, $course]) }}" class="mt-4 space-y-4 pt-3 border-t border-slate-100">
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
        @endif

        @if ($isEntitled)
            @forelse ($course->sections as $section)
                <section class="min-w-0 space-y-4 rounded-2xl bg-white p-6 shadow-xs border border-slate-200/80">
                    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100">
                        <h3 class="break-words font-bold text-base sm:text-lg text-slate-900">{{ $section->title }}</h3>
                        @if ($isCreator)
                            <div class="flex items-center gap-2">
                                <x-classroom-reorder :ids="$course->sections->modelKeys()" :id="$section->id" :action="route('communities.courses.sections.reorder', [$community, $course])" />
                            </div>
                        @endif
                    </div>

                    @if ($isCreator)
                        @php($editKey = 'edit-section-'.$section->id)
                        @php($createKey = 'create-lesson-'.$section->id)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 py-1">
                            <details class="bg-slate-50 p-3 rounded-xl border border-slate-200/70" @if ($errors->any() && old('_form') === $editKey) open @endif>
                                <summary class="cursor-pointer text-xs font-semibold text-brand-600 hover:text-brand-700">{{ __('Edit Section') }}</summary>
                                <form method="POST" action="{{ route('communities.courses.sections.update', [$community, $course, $section]) }}" class="mt-3 space-y-3 pt-2 border-t border-slate-200/60">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="_form" value="{{ $editKey }}">
                                    <label class="block text-xs font-medium text-slate-700">
                                        {{ __('Section Title') }}
                                        <input name="title" required maxlength="255" value="{{ old('_form') === $editKey ? (is_string(old('title')) ? old('title') : '') : $section->title }}" class="mt-1 block w-full rounded-xl border-slate-300 text-xs shadow-xs focus:border-brand-500 focus:ring-brand-500">
                                    </label>
                                    <button class="px-3 py-1.5 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition">{{ __('Save Section') }}</button>
                                </form>
                            </details>

                            <details class="bg-slate-50 p-3 rounded-xl border border-slate-200/70" @if ($errors->any() && old('_form') === $createKey) open @endif>
                                <summary class="cursor-pointer text-xs font-semibold text-brand-600 hover:text-brand-700">{{ __('Add Lesson') }}</summary>
                                <form method="POST" action="{{ route('communities.lessons.store', [$community, $course, $section]) }}" class="mt-3 space-y-3 pt-2 border-t border-slate-200/60">
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
                    @endif

                    <ul class="space-y-3">
                        @forelse ($section->lessons as $lesson)
                            <li class="p-3.5 rounded-xl border border-slate-100 hover:border-slate-200 hover:bg-slate-50/50 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    @if ($isActiveMember && $course->isPublished() && $lesson->isPublished())
                                        @if ($lesson->isCompletedBy(auth()->user()))
                                            <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold shrink-0" title="{{ __('Completed') }}">✓</span>
                                        @else
                                            <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xs shrink-0">&bull;</span>
                                        @endif
                                    @endif

                                    <div class="min-w-0">
                                        <a href="{{ route('communities.lessons.show', [$community, $course, $lesson]) }}" class="block break-words text-sm font-semibold text-slate-900 hover:text-brand-600 transition">
                                            {{ $lesson->title }}
                                        </a>
                                        @if ($isActiveMember && $course->isPublished() && $lesson->isPublished())
                                            <span class="text-xs {{ $lesson->isCompletedBy(auth()->user()) ? 'text-emerald-700 font-medium' : 'text-slate-400' }}">
                                                {{ $lesson->isCompletedBy(auth()->user()) ? __('Completed') : __('Incomplete') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if ($isCreator)
                                    <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $lesson->isPublished() ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                            {{ $lesson->status }}
                                        </span>
                                        <x-classroom-reorder :ids="$section->lessons->modelKeys()" :id="$lesson->id" :action="route('communities.lessons.reorder', [$community, $course, $section])" />
                                    </div>
                                @endif
                            </li>
                        @empty
                            <li class="text-sm text-slate-400 italic py-2">{{ __('No lessons in this section.') }}</li>
                        @endforelse
                    </ul>
                </section>
            @empty
                <div class="rounded-2xl bg-white p-12 text-center text-slate-500 border border-slate-200/80 shadow-xs">
                    <p class="text-sm">{{ __('No modules or sections yet.') }}</p>
                </div>
            @endforelse
        @endif
    </div>
</x-app-layout>
