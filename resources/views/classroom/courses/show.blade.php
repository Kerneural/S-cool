<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center space-x-3">
                <a href="{{ route('communities.classroom.index', $community) }}" class="text-gray-500 hover:text-gray-700">
                    &larr; {{ __('Classroom') }}
                </a>
                <span class="text-gray-300">/</span>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $course->title }}
                </h2>
                @if ($isCreator)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $course->isPublished() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                        {{ $course->status }}
                    </span>
                @endif
            </div>
            <div class="flex items-center space-x-2">
                @if ($isCreator)
                    <button type="button" onclick="document.getElementById('edit-course-modal').classList.remove('hidden')" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                        {{ __('Edit Course') }}
                    </button>
                    <button type="button" onclick="document.getElementById('new-section-modal').classList.remove('hidden')" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        + {{ __('Add Section') }}
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 font-medium text-sm text-green-700 bg-green-50 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 font-medium text-sm text-red-700 bg-red-50 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Course Header Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $course->title }}</h1>
                @if ($course->description)
                    <p class="text-gray-700 whitespace-pre-line leading-relaxed">{{ $course->description }}</p>
                @endif
            </div>

            <!-- Sections & Lessons List -->
            @if ($course->sections->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 text-center border border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('No modules or sections yet.') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $isCreator ? __('Add sections to start organizing lessons.') : __('Check back later when content is published.') }}
                    </p>
                    @if ($isCreator)
                        <div class="mt-6">
                            <button type="button" onclick="document.getElementById('new-section-modal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                + {{ __('Add Section') }}
                            </button>
                        </div>
                    @endif
                </div>
            @else
                <div class="space-y-6">
                    @foreach ($course->sections as $section)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                            <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="font-bold text-base text-gray-900">{{ $section->title }}</h3>
                                @if ($isCreator)
                                    <div class="flex items-center space-x-2">
                                        <button type="button" onclick="openNewLessonModal({{ $section->id }}, '{{ addslashes($section->title) }}')" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                            + {{ __('Add Lesson') }}
                                        </button>
                                        <span class="text-gray-300">|</span>
                                        <form method="POST" action="{{ route('communities.courses.sections.destroy', [$community, $course, $section]) }}" onsubmit="return confirm('{{ __('Delete section and all its lessons?') }}')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium">
                                                {{ __('Delete') }}
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            @if ($section->lessons->isEmpty())
                                <div class="p-6 text-center text-sm text-gray-500">
                                    {{ __('No lessons in this section.') }}
                                </div>
                            @else
                                <ul class="divide-y divide-gray-100">
                                    @foreach ($section->lessons as $lesson)
                                        <li class="p-4 hover:bg-gray-50 flex items-center justify-between transition">
                                            <div class="flex items-center space-x-3">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    @if ($lesson->video_url)
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    @endif
                                                </svg>
                                                <a href="{{ route('communities.lessons.show', [$community, $course, $lesson]) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600">
                                                    {{ $lesson->title }}
                                                </a>
                                                @if ($isCreator)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $lesson->isPublished() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                                        {{ $lesson->status }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex items-center space-x-3 text-xs">
                                                <a href="{{ route('communities.lessons.show', [$community, $course, $lesson]) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                                    {{ __('Open') }} &rarr;
                                                </a>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Creator Modals -->
    @if ($isCreator)
        <!-- Edit Course Modal -->
        <div id="edit-course-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
            <div class="flex items-center justify-center min-h-screen px-4 p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('edit-course-modal').classList.add('hidden')"></div>
                <div class="inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full p-6 z-10">
                    <form method="POST" action="{{ route('communities.courses.update', [$community, $course]) }}">
                        @csrf
                        @method('PUT')
                        <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Edit Course') }}</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Course Title') }}</label>
                                <input type="text" name="title" value="{{ old('title', $course->title) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
                                <textarea name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $course->description) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                                <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="DRAFT" {{ $course->status === 'DRAFT' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                                    <option value="PUBLISHED" {{ $course->status === 'PUBLISHED' ? 'selected' : '' }}>{{ __('Published') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-between items-center">
                            <button type="button" onclick="if(confirm('{{ __('Delete this course?') }}')) { document.getElementById('delete-course-form').submit(); }" class="text-sm text-red-600 hover:text-red-800">
                                {{ __('Delete Course') }}
                            </button>
                            <div class="flex space-x-3">
                                <button type="button" onclick="document.getElementById('edit-course-modal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                                    {{ __('Cancel') }}
                                </button>
                                <button type="submit" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm text-white hover:bg-indigo-700">
                                    {{ __('Save') }}
                                </button>
                            </div>
                        </div>
                    </form>
                    <form id="delete-course-form" method="POST" action="{{ route('communities.courses.destroy', [$community, $course]) }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Section Modal -->
        <div id="new-section-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
            <div class="flex items-center justify-center min-h-screen px-4 p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('new-section-modal').classList.add('hidden')"></div>
                <div class="inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-md sm:w-full p-6 z-10">
                    <form method="POST" action="{{ route('communities.courses.sections.store', [$community, $course]) }}">
                        @csrf
                        <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Add New Section') }}</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Section Title') }}</label>
                            <input type="text" name="title" required placeholder="e.g. Module 1: Introduction" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" onclick="document.getElementById('new-section-modal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm text-white hover:bg-indigo-700">
                                {{ __('Add Section') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Lesson Modal -->
        <div id="new-lesson-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
            <div class="flex items-center justify-center min-h-screen px-4 p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('new-lesson-modal').classList.add('hidden')"></div>
                <div class="inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full p-6 z-10">
                    <form id="new-lesson-form" method="POST" action="">
                        @csrf
                        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('Add Lesson') }}</h3>
                        <p id="new-lesson-section-name" class="text-xs text-gray-500 mb-4"></p>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Lesson Title') }}</label>
                                <input type="text" name="title" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Video URL (YouTube or Vimeo HTTPS only)') }}</label>
                                <input type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <span class="text-xs text-gray-500">{{ __('Supported: https://youtube.com/watch?v=..., https://youtu.be/..., https://vimeo.com/... (optional)') }}</span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Lesson Text Content') }}</label>
                                <textarea name="content" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                                <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="DRAFT">{{ __('Draft') }}</option>
                                    <option value="PUBLISHED">{{ __('Published') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" onclick="document.getElementById('new-lesson-modal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm text-white hover:bg-indigo-700">
                                {{ __('Create Lesson') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function openNewLessonModal(sectionId, sectionTitle) {
                const form = document.getElementById('new-lesson-form');
                form.action = "{{ url('/communities/' . $community->slug . '/courses/' . $course->id . '/sections') }}/" + sectionId + "/lessons";
                document.getElementById('new-lesson-section-name').textContent = "{{ __('Adding to: ') }}" + sectionTitle;
                document.getElementById('new-lesson-modal').classList.remove('hidden');
            }
        </script>
    @endif
</x-app-layout>
