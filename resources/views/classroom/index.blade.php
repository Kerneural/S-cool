<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center space-x-3">
                <a href="{{ route('communities.show', $community) }}" class="text-gray-500 hover:text-gray-700">
                    &larr; {{ $community->name }}
                </a>
                <span class="text-gray-300">/</span>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Classroom') }}
                </h2>
            </div>
            <div class="flex items-center space-x-2">
                @if ($isCreator)
                    <button type="button" onclick="document.getElementById('new-course-modal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        + {{ __('New Course') }}
                    </button>
                @endif
                <a href="{{ route('communities.show', $community) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 border border-transparent rounded-md font-semibold text-xs text-gray-600 uppercase tracking-widest hover:bg-gray-200 transition">
                    {{ __('Community') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
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

            <!-- Courses Grid -->
            @if ($courses->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">{{ __('No courses available') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $isCreator ? __('Get started by creating your first course.') : __('Check back later for published courses.') }}
                    </p>
                    @if ($isCreator)
                        <div class="mt-6">
                            <button type="button" onclick="document.getElementById('new-course-modal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                + {{ __('New Course') }}
                            </button>
                        </div>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($courses as $course)
                        @php
                            $lessonCount = $isCreator
                                ? $course->sections->flatMap->lessons->count()
                                : $course->sections->flatMap->lessons->filter->isPublished()->count();
                        @endphp
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex flex-col justify-between border border-gray-100 hover:shadow-md transition">
                            <div class="p-6">
                                <div class="flex items-start justify-between gap-2 mb-3">
                                    <h3 class="font-bold text-lg text-gray-900 line-clamp-2">
                                        <a href="{{ route('communities.courses.show', [$community, $course]) }}" class="hover:text-indigo-600">
                                            {{ $course->title }}
                                        </a>
                                    </h3>
                                    @if ($isCreator)
                                        <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $course->isPublished() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $course->status }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 line-clamp-3 mb-4">
                                    {{ $course->description ?: __('No course description provided.') }}
                                </p>
                            </div>
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                                <span>{{ $lessonCount }} {{ $lessonCount === 1 ? __('lesson') : __('lessons') }}</span>
                                <a href="{{ route('communities.courses.show', [$community, $course]) }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                                    {{ __('View Course') }} &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Create Course Modal -->
    @if ($isCreator)
        <div id="new-course-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('new-course-modal').classList.add('hidden')"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form method="POST" action="{{ route('communities.courses.store', $community) }}" class="p-6">
                        @csrf
                        <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Create Course') }}</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">{{ __('Course Title') }}</label>
                                <input type="text" name="title" id="title" required maxlength="255" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
                                <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="DRAFT">{{ __('Draft') }}</option>
                                    <option value="PUBLISHED">{{ __('Published') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" onclick="document.getElementById('new-course-modal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700">
                                {{ __('Save Course') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
