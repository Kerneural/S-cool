<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center space-x-2 text-sm text-gray-500 overflow-hidden">
                <a href="{{ route('communities.classroom.index', $community) }}" class="hover:text-gray-700 whitespace-nowrap">
                    {{ __('Classroom') }}
                </a>
                <span>/</span>
                <a href="{{ route('communities.courses.show', [$community, $course]) }}" class="hover:text-gray-700 truncate max-w-xs">
                    {{ $course->title }}
                </a>
                <span>/</span>
                <span class="font-semibold text-gray-800 truncate max-w-xs">
                    {{ $lesson->title }}
                </span>
                @if ($isCreator)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $lesson->isPublished() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                        {{ $lesson->status }}
                    </span>
                @endif
            </div>
            <div class="flex items-center space-x-2">
                @if ($isCreator)
                    <button type="button" onclick="document.getElementById('edit-lesson-modal').classList.remove('hidden')" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                        {{ __('Edit Lesson') }}
                    </button>
                @endif
                <a href="{{ route('communities.courses.show', [$community, $course]) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 border border-transparent rounded-md font-semibold text-xs text-gray-600 uppercase tracking-widest hover:bg-gray-200">
                    {{ __('Outline') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 p-4 font-medium text-sm text-green-700 bg-green-50 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 font-medium text-sm text-red-700 bg-red-50 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Main Lesson View (3 cols) -->
                <div class="lg:col-span-3 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 p-6">
                        <!-- Video Player Embed -->
                        @if ($lesson->embed_url)
                            <div class="relative w-full overflow-hidden rounded-xl bg-black mb-6 shadow" style="padding-top: 56.25%;">
                                <iframe class="absolute top-0 left-0 w-full h-full border-0"
                                        src="{{ $lesson->embed_url }}"
                                        title="{{ $lesson->title }}"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen>
                                </iframe>
                            </div>
                        @endif

                        <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ $lesson->title }}</h1>

                        @if ($lesson->content)
                            <div class="prose max-w-none text-gray-800 leading-relaxed whitespace-pre-line border-t border-gray-100 pt-4">
                                {!! nl2br(e($lesson->content)) !!}
                            </div>
                        @else
                            <div class="text-sm text-gray-400 italic border-t border-gray-100 pt-4">
                                {{ __('No text notes for this lesson.') }}
                            </div>
                        @endif

                        <!-- Bottom Navigation: Prev / Next Lesson -->
                        <div class="mt-8 pt-6 border-t border-gray-100 flex items-center justify-between">
                            @if ($prevLesson)
                                <a href="{{ route('communities.lessons.show', [$community, $course, $prevLesson]) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    &larr; {{ __('Previous: ') }} {{ \Illuminate\Support\Str::limit($prevLesson->title, 25) }}
                                </a>
                            @else
                                <div></div>
                            @endif

                            @if ($nextLesson)
                                <a href="{{ route('communities.lessons.show', [$community, $course, $nextLesson]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700">
                                    {{ __('Next: ') }} {{ \Illuminate\Support\Str::limit($nextLesson->title, 25) }} &rarr;
                                </a>
                            @else
                                <div></div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Course Curriculum Sidebar (1 col) -->
                <div class="lg:col-span-1 space-y-4">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 p-4">
                        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">
                            {{ __('Course Content') }}
                        </h2>

                        <div class="space-y-4">
                            @foreach ($course->sections as $section)
                                <div>
                                    <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">{{ $section->title }}</h3>
                                    <ul class="space-y-1">
                                        @foreach ($section->lessons as $secLesson)
                                            <li>
                                                <a href="{{ route('communities.lessons.show', [$community, $course, $secLesson]) }}"
                                                   class="block px-2.5 py-1.5 rounded-md text-xs font-medium transition {{ $secLesson->id === $lesson->id ? 'bg-indigo-50 text-indigo-700 font-semibold border-l-2 border-indigo-600' : 'text-gray-700 hover:bg-gray-50' }}">
                                                    {{ $secLesson->title }}
                                                    @if ($isCreator && $secLesson->isDraft())
                                                        <span class="text-[10px] text-gray-400">({{ __('draft') }})</span>
                                                    @endif
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Lesson Modal (Creator only) -->
    @if ($isCreator)
        <div id="edit-lesson-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
            <div class="flex items-center justify-center min-h-screen px-4 p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('edit-lesson-modal').classList.add('hidden')"></div>
                <div class="inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full p-6 z-10">
                    <form method="POST" action="{{ route('communities.lessons.update', [$community, $course, $lesson->section, $lesson]) }}">
                        @csrf
                        @method('PUT')
                        <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Edit Lesson') }}</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Lesson Title') }}</label>
                                <input type="text" name="title" value="{{ old('title', $lesson->title) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Video URL (YouTube or Vimeo HTTPS only)') }}</label>
                                <input type="url" name="video_url" value="{{ old('video_url', $lesson->video_url) }}" placeholder="https://www.youtube.com/watch?v=..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Lesson Text Content') }}</label>
                                <textarea name="content" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('content', $lesson->content) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                                <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="DRAFT" {{ $lesson->status === 'DRAFT' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                                    <option value="PUBLISHED" {{ $lesson->status === 'PUBLISHED' ? 'selected' : '' }}>{{ __('Published') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-between items-center">
                            <button type="button" onclick="if(confirm('{{ __('Delete this lesson?') }}')) { document.getElementById('delete-lesson-form').submit(); }" class="text-sm text-red-600 hover:text-red-800">
                                {{ __('Delete Lesson') }}
                            </button>
                            <div class="flex space-x-3">
                                <button type="button" onclick="document.getElementById('edit-lesson-modal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                                    {{ __('Cancel') }}
                                </button>
                                <button type="submit" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm text-white hover:bg-indigo-700">
                                    {{ __('Save') }}
                                </button>
                            </div>
                        </div>
                    </form>
                    <form id="delete-lesson-form" method="POST" action="{{ route('communities.lessons.destroy', [$community, $course, $lesson->section, $lesson]) }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
