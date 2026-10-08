<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="min-w-0 break-words text-xl font-semibold text-gray-800">{{ $lesson->title }}</h2>
            <a class="text-sm text-indigo-700" href="{{ route('communities.courses.show', [$community, $course]) }}">{{ __('Outline') }}</a>
        </div>
    </x-slot>
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6">
        <x-classroom-feedback />
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
            <article class="min-w-0 space-y-6 rounded-lg bg-white p-6 shadow-sm lg:col-span-3">
                <h1 class="break-words text-2xl font-bold">{{ $lesson->title }}</h1>
                @if ($isCreator)<span class="text-xs text-gray-500">{{ $lesson->status }}</span>@endif
                @if ($lesson->embed_url)
                    <div x-data="{ showVideo: true }" class="space-y-3">
                        <div x-show="showVideo" class="aspect-video overflow-hidden rounded bg-black">
                            <iframe src="{{ $lesson->embed_url }}" title="{{ $lesson->title }}" class="h-full w-full border-0" allow="fullscreen; picture-in-picture" allowfullscreen></iframe>
                        </div>
                        <p class="text-sm text-gray-600">{{ __('Video unavailable?') }} <a class="break-all text-indigo-700 underline" href="{{ $lesson->provider_url }}" target="_blank" rel="noopener noreferrer">{{ __('Open on provider') }}</a></p>
                        <button type="button" @click="showVideo = !showVideo" class="text-sm text-indigo-700">{{ __('Show / hide video') }}</button>
                    </div>
                @endif
                <p class="whitespace-pre-line break-words text-gray-800">{{ $lesson->content ?? __('No text notes for this lesson.') }}</p>
                <nav aria-label="Lesson navigation" class="flex flex-wrap justify-between gap-3 border-t pt-4">
                    @if ($prevLesson)<a class="break-words text-sm text-indigo-700" href="{{ route('communities.lessons.show', [$community, $course, $prevLesson]) }}">&larr; {{ __('Previous: ') }} {{ $prevLesson->title }}</a>@endif
                    @if ($nextLesson)<a class="break-words text-sm text-indigo-700" href="{{ route('communities.lessons.show', [$community, $course, $nextLesson]) }}">{{ __('Next: ') }} {{ $nextLesson->title }} &rarr;</a>@endif
                </nav>
            </article>
            <aside class="min-w-0 space-y-4 rounded-lg bg-white p-4 shadow-sm">
                <h2 class="font-bold">{{ __('Course Content') }}</h2>
                @foreach ($course->sections as $section)
                    <h3 class="break-words text-sm font-semibold">{{ $section->title }}</h3>
                    <ul class="space-y-2">
                        @foreach ($section->lessons as $item)
                            <li><a href="{{ route('communities.lessons.show', [$community, $course, $item]) }}" class="block break-words text-sm {{ $item->id === $lesson->id ? 'font-bold text-indigo-700' : 'text-gray-600' }}">{{ $item->title }} @if ($isCreator && $item->isDraft()) ({{ __('draft') }}) @endif</a></li>
                        @endforeach
                    </ul>
                @endforeach
            </aside>
        </div>
        @if ($isCreator)
            <details class="rounded-lg bg-white p-6 shadow-sm" @if ($errors->any() && old('_form') === 'edit-lesson') open @endif>
                <summary class="cursor-pointer font-semibold text-indigo-700">{{ __('Edit Lesson') }}</summary>
                <form method="POST" action="{{ route('communities.lessons.update', [$community, $course, $lesson->section, $lesson]) }}" class="mt-4 space-y-4">
                    @csrf @method('PUT')
                    <input type="hidden" name="_form" value="edit-lesson">
                    <x-classroom-lesson-fields form-key="edit-lesson" :lesson="$lesson" />
                    <button class="rounded bg-indigo-600 px-4 py-2 text-sm text-white">{{ __('Save Lesson') }}</button>
                </form>
            </details>
        @endif
    </div>
</x-app-layout>
