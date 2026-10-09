<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="min-w-0 break-words text-xl font-semibold text-gray-800">{{ $community->name }} / {{ __('Classroom') }}</h2>
            <a class="text-sm text-indigo-700" href="{{ route('communities.show', $community) }}">{{ __('Community') }}</a>
        </div>
    </x-slot>
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6">
        <x-classroom-feedback />
        @if ($isCreator)
            <details class="rounded-lg bg-white p-6 shadow-sm" @if ($errors->any() && old('_form') === 'create-course') open @endif>
                <summary class="cursor-pointer font-semibold text-indigo-700">{{ __('New Course') }}</summary>
                <form method="POST" action="{{ route('communities.courses.store', $community) }}" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="_form" value="create-course">
                    <x-classroom-course-fields form-key="create-course" />
                    <button class="rounded bg-indigo-600 px-4 py-2 text-sm text-white">{{ __('Save Course') }}</button>
                </form>
            </details>
        @endif
        @if ($courses->isEmpty())
            <div class="rounded-lg bg-white p-8 text-center text-gray-600">{{ __('No courses available') }}</div>
        @else
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($courses as $course)
                    <article class="min-w-0 space-y-4 rounded-lg border border-gray-100 bg-white p-6 shadow-sm">
                        <a href="{{ route('communities.courses.show', [$community, $course]) }}" class="block break-words text-lg font-bold text-indigo-700">{{ $course->title }}</a>
                        @if ($isCreator)<span class="rounded bg-gray-100 px-2 py-1 text-xs">{{ $course->status }}</span>@endif
                        <p class="break-words text-sm text-gray-600">{{ $course->description ?? __('No course description provided.') }}</p>
                        <p class="text-xs text-gray-500">{{ $course->visible_lessons_count }} {{ $course->visible_lessons_count === 1 ? __('lesson') : __('lessons') }}</p>
                        @if ($isActiveMember && $course->isPublished())
                            @php($percent = $course->progress_total ? (int) round(100 * $course->progress_completed / $course->progress_total) : 0)
                            <p class="text-sm">{{ __('Your Progress') }}: {{ $percent }}%</p>
                            <progress class="block h-3 w-full" max="100" value="{{ $percent }}" aria-label="{{ __('Course completion percentage') }}">{{ $percent }}%</progress>
                        @endif
                        @if ($isCreator)
                            <x-classroom-reorder :ids="$courseOrder" :id="$course->id" :action="route('communities.courses.reorder', $community)" />
                        @endif
                    </article>
                @endforeach
            </div>
            {{ $courses->links() }}
        @endif
    </div>
</x-app-layout>
