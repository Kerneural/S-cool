<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="min-w-0 break-words text-xl font-semibold text-gray-800">{{ $course->title }}</h2>
            <a class="text-sm text-indigo-700" href="{{ route('communities.classroom.index', $community) }}">{{ __('Classroom') }}</a>
        </div>
    </x-slot>
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6">
        <x-classroom-feedback />
        @if ($isActiveMember && $course->isPublished())
            <section aria-label="{{ __('Your Progress') }}" class="space-y-3 rounded-lg bg-white p-6 shadow-sm">
                <h2 class="font-semibold">{{ __('Your Progress') }}</h2>
                <p class="break-words text-sm">{{ $progressSummary['completed'] }} {{ __('of') }} {{ $progressSummary['total'] }} {{ __('completed') }} ({{ $progressSummary['percentage'] }}%)</p>
                <progress class="block h-3 w-full" max="100" value="{{ $progressSummary['percentage'] }}" aria-label="{{ __('Course completion percentage') }}">{{ $progressSummary['percentage'] }}%</progress>
            </section>
        @endif
        <div class="space-y-3 rounded-lg bg-white p-6 shadow-sm">
            <h1 class="break-words text-2xl font-bold">{{ $course->title }}</h1>
            <p class="whitespace-pre-line break-words text-gray-700">{{ $course->description }}</p>
            @if ($isCreator)<p class="text-xs text-gray-500">{{ $course->status }} — {{ __('Unpublish to hide content; permanent deletion is not available.') }}</p>@endif
        </div>
        @if ($isCreator)
            <details class="rounded-lg bg-white p-6 shadow-sm" @if ($errors->any() && old('_form') === 'edit-course') open @endif>
                <summary class="cursor-pointer font-semibold text-indigo-700">{{ __('Edit Course') }}</summary>
                <form method="POST" action="{{ route('communities.courses.update', [$community, $course]) }}" class="mt-4 space-y-4">
                    @csrf @method('PUT')
                    <input type="hidden" name="_form" value="edit-course">
                    <x-classroom-course-fields form-key="edit-course" :course="$course" />
                    <button class="rounded bg-indigo-600 px-4 py-2 text-sm text-white">{{ __('Save Course') }}</button>
                </form>
            </details>
            <details class="rounded-lg bg-white p-6 shadow-sm" @if ($errors->any() && old('_form') === 'create-section') open @endif>
                <summary class="cursor-pointer font-semibold text-indigo-700">{{ __('Add Section') }}</summary>
                <form method="POST" action="{{ route('communities.courses.sections.store', [$community, $course]) }}" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="_form" value="create-section">
                    <label class="block text-sm">{{ __('Section Title') }}<input name="title" required maxlength="255" value="{{ old('_form') === 'create-section' && is_string(old('title')) ? old('title') : '' }}" class="mt-1 block w-full rounded border-gray-300"></label>
                    <button class="rounded bg-indigo-600 px-4 py-2 text-sm text-white">{{ __('Create Section') }}</button>
                </form>
            </details>
        @endif
        @forelse ($course->sections as $section)
            <section class="min-w-0 space-y-4 rounded-lg bg-white p-6 shadow-sm">
                <h3 class="break-words font-bold">{{ $section->title }}</h3>
                @if ($isCreator)
                    <x-classroom-reorder :ids="$course->sections->modelKeys()" :id="$section->id" :action="route('communities.courses.sections.reorder', [$community, $course])" />
                    @php($editKey = 'edit-section-'.$section->id)
                    <details @if ($errors->any() && old('_form') === $editKey) open @endif>
                        <summary class="cursor-pointer text-sm text-indigo-700">{{ __('Edit Section') }}</summary>
                        <form method="POST" action="{{ route('communities.courses.sections.update', [$community, $course, $section]) }}" class="mt-3 space-y-3">
                            @csrf @method('PUT')
                            <input type="hidden" name="_form" value="{{ $editKey }}">
                            <label class="block text-sm">{{ __('Section Title') }}<input name="title" required maxlength="255" value="{{ old('_form') === $editKey ? (is_string(old('title')) ? old('title') : '') : $section->title }}" class="mt-1 block w-full rounded border-gray-300"></label>
                            <button class="rounded border px-3 py-2 text-sm">{{ __('Save Section') }}</button>
                        </form>
                    </details>
                    @php($createKey = 'create-lesson-'.$section->id)
                    <details @if ($errors->any() && old('_form') === $createKey) open @endif>
                        <summary class="cursor-pointer text-sm text-indigo-700">{{ __('Add Lesson') }}</summary>
                        <form method="POST" action="{{ route('communities.lessons.store', [$community, $course, $section]) }}" class="mt-3 space-y-3">
                            @csrf
                            <input type="hidden" name="_form" value="{{ $createKey }}">
                            <x-classroom-lesson-fields :form-key="$createKey" />
                            <button class="rounded bg-indigo-600 px-4 py-2 text-sm text-white">{{ __('Create Lesson') }}</button>
                        </form>
                    </details>
                @endif
                <ul class="space-y-4">
                    @forelse ($section->lessons as $lesson)
                        <li class="space-y-2 border-t pt-3">
                            <a href="{{ route('communities.lessons.show', [$community, $course, $lesson]) }}" class="block break-words text-sm font-medium text-indigo-700">{{ $lesson->title }}</a>
                            @if ($isActiveMember && $course->isPublished() && $lesson->isPublished())
                                <span class="text-xs {{ $lesson->isCompletedBy(auth()->user()) ? 'text-green-700' : 'text-gray-500' }}">{{ $lesson->isCompletedBy(auth()->user()) ? __('Completed') : __('Incomplete') }}</span>
                            @endif
                            @if ($isCreator)
                                <span class="text-xs text-gray-500">{{ $lesson->status }}</span>
                                <x-classroom-reorder :ids="$section->lessons->modelKeys()" :id="$lesson->id" :action="route('communities.lessons.reorder', [$community, $course, $section])" />
                            @endif
                        </li>
                    @empty
                        <li class="text-sm text-gray-500">{{ __('No lessons in this section.') }}</li>
                    @endforelse
                </ul>
            </section>
        @empty
            <p class="rounded-lg bg-white p-6 text-sm text-gray-500">{{ __('No modules or sections yet.') }}</p>
        @endforelse
    </div>
</x-app-layout>
