<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <nav class="flex items-center space-x-2 text-xs sm:text-sm text-slate-500">
                <a class="hover:text-brand-600 transition" href="{{ route('communities.posts.index', $community) }}">
                    {{ __('Community Feed') }}
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-900 font-semibold">{{ __('New Post') }}</span>
            </nav>
            <a class="text-xs sm:text-sm font-semibold text-brand-600 hover:text-brand-700 transition" href="{{ route('communities.posts.index', $community) }}">
                &larr; {{ __('Back to Feed') }}
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6">
        <x-classroom-feedback />

        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-ink">{{ __('Create a Post') }}</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    {{ __('Share insights, ask questions, or start a discussion in :community.', ['community' => $community->name]) }}
                </p>
            </div>

            <form method="POST" action="{{ route('communities.posts.store', $community) }}" class="space-y-5">
                @csrf
                @php
                    $courseId = is_scalar(old('course_id', $courseId)) ? old('course_id', $courseId) : null;
                    $lessonId = is_scalar(old('lesson_id', $lessonId)) ? old('lesson_id', $lessonId) : null;
                @endphp

                @if ($courseId)
                    <input type="hidden" name="course_id" value="{{ $courseId }}">
                    <div class="flex items-center justify-between p-3.5 rounded-xl bg-brand-50 border border-brand-200 text-xs">
                        <div class="flex items-center gap-2 text-brand-900 font-semibold">
                            <svg class="w-4 h-4 text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span>{{ __('Attached Learning Context: Course #') }}{{ $courseId }} @if ($lessonId) ({{ __('Lesson #') }}{{ $lessonId }}) @endif</span>
                        </div>
                    </div>
                @endif

                @if ($lessonId)
                    <input type="hidden" name="lesson_id" value="{{ $lessonId }}">
                @endif
                <x-input-error :messages="$errors->get('course_id')" />
                <x-input-error :messages="$errors->get('lesson_id')" />

                <div>
                    <x-input-label for="post-title" :value="__('Title')" />
                    <x-text-input id="post-title" name="title" type="text" class="mt-1 block w-full" :value="is_string(old('title')) ? old('title') : ''" required maxlength="255" placeholder="{{ __('Write a clear, descriptive title...') }}" />
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="post-category" :value="__('Category (Optional)')" />
                    <select id="post-category" name="category_id" class="mt-1 block w-full border-slate-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-slate-800 bg-white p-2.5">
                        <option value="">{{ __('-- None / General --') }}</option>
                        @foreach ($categories as $cat)
                            @if (! $cat->isAnnouncements() || $community->isCreator(auth()->user()))
                                <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>
                                    {{ $cat->name }}{{ $cat->isAnnouncements() ? ' (Creator Only)' : '' }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="post-body" :value="__('Content')" />
                    <textarea id="post-body" name="body" rows="6" maxlength="10000" class="mt-1 block w-full border-slate-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-slate-800 bg-white leading-relaxed p-3" required placeholder="{{ __('Share your thoughts, insights, or questions with the community...') }}">{{ is_string(old('body')) ? old('body') : '' }}</textarea>
                    <x-input-error :messages="$errors->get('body')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <a href="{{ route('communities.posts.index', $community) }}" class="px-4 py-2 bg-white border border-slate-300 rounded-xl text-slate-700 text-xs font-semibold hover:bg-slate-50 transition">
                        {{ __('Cancel') }}
                    </a>
                    <x-primary-button>
                        {{ __('Publish Post') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
