<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="feed" />

            <h1 class="text-2xl font-bold tracking-tight text-ink">{{ __('Community Feed') }}</h1>

            <x-learning-focus :community="$community" :learning-focus="$learningFocus" />

            @if (session('status'))
                <div class="p-4 font-medium text-sm text-emerald-800 bg-emerald-50 rounded-xl border border-emerald-200 shadow-xs flex items-center justify-between" role="alert">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            <!-- Main Feed 2-Column Grid (Main 680-760px, Context 280-300px) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Main Feed Column (8 cols on lg) -->
                <div class="lg:col-span-8 space-y-5">
                    <!-- Category Filter & Sort Toolbar -->
                    <div class="bg-white p-3.5 sm:p-4 rounded-2xl shadow-xs border border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <!-- Horizontal Category Filter Pills -->
                        <div class="flex items-center gap-1.5 text-xs overflow-x-auto scrollbar-none pb-1 sm:pb-0">
                            <span class="font-semibold text-slate-400 shrink-0 mr-1">{{ __('Filter:') }}</span>
                            <a href="{{ route('communities.posts.index', [$community, 'sort' => $currentSort]) }}"
                               class="px-3 py-1.5 rounded-full font-medium transition shrink-0 active:scale-[0.98] {{ empty($currentCategory) || $currentCategory === 'all' ? 'bg-brand-600 text-white shadow-xs font-semibold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                                {{ __('All') }}
                            </a>
                            @foreach ($categories as $cat)
                                <a href="{{ route('communities.posts.index', [$community, 'category' => $cat->slug, 'sort' => $currentSort]) }}"
                                   class="px-3 py-1.5 rounded-full font-medium transition shrink-0 active:scale-[0.98] {{ $currentCategory === $cat->slug ? 'bg-brand-600 text-white shadow-xs font-semibold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                                    {{ $cat->name }}
                                </a>
                            @endforeach
                            <a href="{{ route('communities.posts.index', [$community, 'category' => 'uncategorized', 'sort' => $currentSort]) }}"
                               class="px-3 py-1.5 rounded-full font-medium transition shrink-0 active:scale-[0.98] {{ $currentCategory === 'uncategorized' ? 'bg-brand-600 text-white shadow-xs font-semibold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                                {{ __('Uncategorized') }}
                            </a>
                            <a href="{{ route('communities.saved.index', $community) }}"
                               class="px-3 py-1.5 rounded-full font-medium transition shrink-0 active:scale-[0.98] inline-flex items-center gap-1 bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200">
                                <svg class="w-3.5 h-3.5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z" />
                                </svg>
                                <span>{{ __('Saved Posts') }}</span>
                            </a>
                        </div>

                        <!-- Sort Toggle Controls -->
                        <div class="flex items-center gap-2 text-xs shrink-0 self-end sm:self-center">
                            <span class="font-semibold text-slate-400">{{ __('Sort:') }}</span>
                            <div class="inline-flex rounded-lg shadow-xs border border-slate-200 bg-slate-50 p-0.5">
                                <a href="{{ route('communities.posts.index', [$community, 'category' => $currentCategory, 'sort' => 'newest']) }}"
                                   class="px-2.5 py-1 text-xs font-medium rounded-md transition {{ ($currentSort ?? 'newest') === 'newest' ? 'bg-white text-brand-700 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                                    {{ __('Newest') }}
                                </a>
                                <a href="{{ route('communities.posts.index', [$community, 'category' => $currentCategory, 'sort' => 'most_commented']) }}"
                                   class="px-2.5 py-1 text-xs font-medium rounded-md transition {{ ($currentSort ?? '') === 'most_commented' ? 'bg-white text-brand-700 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                                    {{ __('Most Commented') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    @if (! empty($isCreator))
                        {{-- Creator Category Management --}}
                        <div x-data="{ openManage: {{ $errors->has('name') ? 'true' : 'false' }} }" class="bg-white p-3.5 rounded-2xl shadow-xs border border-slate-200/80 text-xs">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-slate-700 font-semibold">
                                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    <span>{{ __('Manage Categories') }}</span>
                                </div>
                                <button type="button" @click="openManage = !openManage" class="text-brand-600 hover:text-brand-700 font-semibold transition">
                                    <span x-show="!openManage">{{ __('Manage +') }}</span>
                                    <span x-show="openManage" x-cloak>{{ __('Close &times;') }}</span>
                                </button>
                            </div>

                            <div x-show="openManage" x-cloak x-transition class="mt-3 pt-3 border-t border-slate-100 space-y-3">
                                <form method="POST" action="{{ route('communities.categories.store', $community) }}" class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-start">
                                    @csrf
                                    <div class="min-w-0 flex-1">
                                        <label for="new-category-name" class="sr-only">{{ __('New category name') }}</label>
                                        <input id="new-category-name" type="text" name="name" required maxlength="60" value="{{ $errors->has('name') && is_string(old('name')) ? old('name') : '' }}" placeholder="{{ __('New category name...') }}"
                                               class="w-full text-xs rounded-xl border-slate-300 shadow-xs focus:border-brand-500 focus:ring-1 focus:ring-brand-500 py-2.5 px-3 bg-white" />
                                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                    </div>
                                    <button type="submit" class="px-3.5 py-1.5 bg-brand-600 text-white rounded-xl hover:bg-brand-500 font-semibold whitespace-nowrap shadow-xs transition active:scale-[0.99]">
                                        {{ __('Add Category') }}
                                    </button>
                                </form>

                                @if ($categories->isNotEmpty())
                                    <div class="flex flex-wrap items-center gap-2 pt-1">
                                        @foreach ($categories as $cat)
                                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 border border-slate-200 rounded-lg text-slate-700">
                                                <span>{{ $cat->name }}</span>
                                                <form method="POST" action="{{ route('communities.categories.destroy', [$community, $cat]) }}"
                                                      onsubmit="return confirm('{{ __('Delete this category? Posts will become uncategorized.') }}');"
                                                      class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-slate-400 hover:text-rose-600 font-bold ml-1 leading-none" title="{{ __('Delete category') }}">
                                                        &times;
                                                    </button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if (! empty($currentCourseId))
                        <div class="flex items-center justify-between p-3.5 rounded-2xl bg-brand-50 border border-brand-200 text-xs">
                            <div class="flex items-center gap-2 text-brand-900 font-semibold">
                                <svg class="w-4 h-4 text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <span>{{ __('Filtering discussions by Course #') }}{{ $currentCourseId }}</span>
                            </div>
                            <a href="{{ route('communities.posts.index', [$community, 'category' => $currentCategory, 'sort' => $currentSort]) }}" class="text-brand-700 hover:text-brand-900 font-bold">
                                &times; {{ __('Clear Filter') }}
                            </a>
                        </div>
                    @endif

                    <!-- Create Post Collapsible Card / Composer -->
                    <div x-data="{
                        openPostForm: {{ ($errors->hasAny(['title', 'body', 'category_id', 'course_id', 'lesson_id']) || request()->has('course_id') || request()->has('lesson_id')) ? 'true' : 'false' }},
                        courseId: @js(old('course_id', request('course_id', ''))),
                        lessonId: @js(old('lesson_id', request('lesson_id', '')))
                    }" class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden transition">
                        <!-- Collapsed prompt trigger -->
                        <div x-show="!openPostForm" @click="openPostForm = true" class="p-4 sm:p-5 flex items-center gap-3 cursor-pointer hover:bg-slate-50/75 transition">
                            <div class="w-10 h-10 rounded-full bg-brand-50 text-brand-700 font-bold flex items-center justify-center text-sm shrink-0 border border-brand-200">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="flex-1 py-2 px-4 bg-slate-50 rounded-xl text-sm text-slate-500 border border-slate-200/80 hover:border-slate-300 transition">
                                {{ __('Write a post or ask a question to the community...') }}
                            </div>
                            <button type="button" class="px-4 py-2 bg-brand-600 text-white text-xs font-semibold rounded-xl shadow-xs hover:bg-brand-500 transition active:scale-[0.99] shrink-0">
                                {{ __('Write Post') }}
                            </button>
                        </div>

                        <!-- Expanded Composer Form -->
                        <div x-show="openPostForm" x-cloak class="p-5 sm:p-6 space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <h3 class="text-base font-bold text-slate-900">{{ __('Create a Post') }}</h3>
                                <button type="button" @click="openPostForm = false" class="text-xs text-slate-500 hover:text-slate-900 font-semibold transition">
                                    {{ __('Cancel') }}
                                </button>
                            </div>

                            <form method="POST" action="{{ route('communities.posts.store', $community) }}" class="space-y-4">
                                @csrf
                                <input type="hidden" name="course_id" value="{{ is_scalar(old('course_id', request('course_id'))) ? old('course_id', request('course_id')) : '' }}" :value="courseId">
                                <input type="hidden" name="lesson_id" value="{{ is_scalar(old('lesson_id', request('lesson_id'))) ? old('lesson_id', request('lesson_id')) : '' }}" :value="lessonId">

                                <template x-if="courseId">
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-brand-50 border border-brand-200 text-xs">
                                        <div class="flex items-center gap-1.5 text-brand-800 font-medium">
                                            <svg class="w-4 h-4 text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                            <span>{{ __('Attached Learning Context: Course #') }}<span x-text="courseId"></span><span x-show="lessonId"> ({{ __('Lesson #') }}<span x-text="lessonId"></span>)</span></span>
                                        </div>
                                        <button type="button" @click="courseId = ''; lessonId = ''" class="text-slate-400 hover:text-slate-700 font-bold ml-2">
                                            &times; {{ __('Remove') }}
                                        </button>
                                    </div>
                                </template>
                                <x-input-error :messages="$errors->get('course_id')" class="mt-1" />
                                <x-input-error :messages="$errors->get('lesson_id')" class="mt-1" />

                                <div>
                                    <x-input-label for="title" :value="__('Title')" />
                                    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required placeholder="{{ __('Write a clear, descriptive title...') }}" />
                                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="category_id" :value="__('Category')" />
                                    <select id="category_id" name="category_id" class="mt-1 block w-full border-slate-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-slate-800 bg-white">
                                        <option value="">{{ __('-- None / General --') }}</option>
                                        @foreach ($categories as $cat)
                                            @if (! $cat->isAnnouncements() || $community->isCreator(auth()->user()))
                                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                                    {{ $cat->name }}{{ $cat->isAnnouncements() ? ' (Creator Only)' : '' }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="body" :value="__('Content')" />
                                    <textarea id="body" name="body" rows="4" class="mt-1 block w-full border-slate-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-slate-800 bg-white leading-relaxed" required placeholder="{{ __('Share your thoughts, insights, or questions with the community...') }}">{{ old('body') }}</textarea>
                                    <x-input-error :messages="$errors->get('body')" class="mt-2" />
                                </div>

                                <div class="flex items-center justify-end gap-3 pt-2">
                                    <x-secondary-button type="button" @click="openPostForm = false">
                                        {{ __('Cancel') }}
                                    </x-secondary-button>
                                    <x-primary-button>
                                        {{ __('Publish Post') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Posts Feed List -->
                    <div x-data class="space-y-4">
                        @forelse ($posts as $post)
                            <article class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 sm:p-6 transition hover:border-slate-300 {{ $post->isPinned() ? 'ring-2 ring-amber-400/80 bg-amber-50/15' : '' }}">
                                <div class="flex justify-between items-start gap-4 mb-3">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-full bg-brand-50 text-brand-700 font-bold flex items-center justify-center text-sm shrink-0 border border-brand-200">
                                            {{ strtoupper(substr($post->author->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="flex flex-wrap items-center gap-1.5 font-semibold text-slate-900 text-sm">
                                                <span>{{ $post->author->name ?? __('Unknown') }}</span>
                                                @if ($community->isCreator($post->author))
                                                    <span class="px-2 py-0.5 bg-brand-50 text-brand-700 rounded-full text-[11px] font-semibold border border-brand-200">{{ __('Creator') }}</span>
                                                @endif
                                                @if ($post->category)
                                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full text-xs font-normal">
                                                        {{ $post->category->name }}
                                                    </span>
                                                @endif
                                                @if ($post->isPinned())
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full text-xs font-bold border border-amber-300">
                                                        <svg class="w-3 h-3 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z" />
                                                        </svg>
                                                        <span>{{ __('Pinned') }}</span>
                                                    </span>
                                                @endif
                                                @if ($post->hasLearningContext())
                                                    @if ($post->isLearningContextAccessibleFor(auth()->user()))
                                                        <a href="{{ $post->lesson ? route('communities.lessons.show', [$community, $post->course, $post->lesson]) : route('communities.courses.show', [$community, $post->course]) }}"
                                                           class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 hover:bg-brand-100 transition border border-brand-200" title="{{ $post->course->title }}{{ $post->lesson ? ' / ' . $post->lesson->title : '' }}">
                                                            <svg class="w-3 h-3 text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                            </svg>
                                                            <span class="truncate max-w-[180px]">{{ $post->course->title }}</span>
                                                            @if ($post->lesson)
                                                                <span class="text-brand-400">/</span>
                                                                <span class="truncate max-w-[140px]">{{ $post->lesson->title }}</span>
                                                            @endif
                                                        </a>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500 border border-slate-200" title="{{ __('This course or lesson is not accessible to your account.') }}">
                                                            <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m4-6V7a4 4 0 00-8 0v4h8z" />
                                                            </svg>
                                                            <span>{{ __('Learning context unavailable') }}</span>
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                            <time class="text-xs text-slate-400 block mt-0.5">
                                                {{ $post->created_at->diffForHumans() }}
                                            </time>
                                        </div>
                                    </div>

                                    <!-- Moderator / Creator Actions Dropdown or Row -->
                                    <div class="flex items-center space-x-2 shrink-0">
                                        @if ($community->isCreator(auth()->user()))
                                            @if ($post->isPinned())
                                                <form method="POST" action="{{ route('communities.posts.unpin', [$community, $post]) }}">
                                                    @csrf
                                                    <button type="submit" class="text-xs text-amber-700 hover:text-amber-900 font-medium transition underline">
                                                        {{ __('Unpin') }}
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('communities.posts.pin', [$community, $post]) }}">
                                                    @csrf
                                                    <button type="submit" class="text-xs text-slate-500 hover:text-slate-900 font-medium transition underline">
                                                        {{ __('Pin') }}
                                                    </button>
                                                </form>
                                            @endif
                                        @endif

                                        @can('update', $post)
                                            <a href="{{ route('communities.posts.edit', [$community, $post]) }}" class="text-xs text-brand-600 hover:text-brand-700 font-medium underline transition">
                                                {{ __('Edit') }}
                                            </a>
                                        @endcan

                                        @can('delete', $post)
                                            <form method="POST" action="{{ route('communities.posts.destroy', [$community, $post]) }}" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-medium underline transition">
                                                    {{ __('Delete') }}
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>

                                <a href="{{ route('communities.posts.show', [$community, $post]) }}"
                                   data-discussion-trigger="{{ $post->id }}"
                                   @click.prevent="$dispatch('open-discussion-trigger', { postId: {{ $post->id }}, trigger: $el })"
                                   class="block group cursor-pointer">
                                    <h4 class="text-lg font-bold text-slate-900 group-hover:text-brand-600 transition mb-2">
                                        {{ $post->title }}
                                    </h4>
                                    <p class="text-slate-700 text-sm whitespace-pre-line line-clamp-3 leading-relaxed mb-4">
                                        {{ $post->body }}
                                    </p>
                                </a>

                                <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
                                    <div class="flex items-center gap-4">
                                        @livewire('post-like-button', ['community' => $community, 'post' => $post], key('post-like-'.$post->id))
                                        @livewire('post-save-button', ['community' => $community, 'post' => $post], key('post-save-'.$post->id))

                                        <a href="{{ route('communities.posts.show', [$community, $post]) }}"
                                           data-discussion-trigger="{{ $post->id }}"
                                           @click.prevent="$dispatch('open-discussion-trigger', { postId: {{ $post->id }}, trigger: $el })"
                                           class="inline-flex items-center text-brand-600 hover:text-brand-700 font-medium transition cursor-pointer">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                            </svg>
                                            <span>{{ $post->comments_count }} {{ __('comments') }}</span>
                                        </a>
                                    </div>

                                    <a href="{{ route('communities.posts.show', [$community, $post]) }}"
                                       data-discussion-trigger="{{ $post->id }}"
                                       @click.prevent="$dispatch('open-discussion-trigger', { postId: {{ $post->id }}, trigger: $el })"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-brand-700 font-semibold text-xs rounded-xl transition border border-slate-200/80 cursor-pointer"
                                       aria-label="{{ __('Open discussion for :title', ['title' => $post->title]) }}">
                                        <svg class="w-3.5 h-3.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                        <span>{{ __('Discussion') }}</span>
                                    </a>
                                </div>
                            </article>
                        @empty
                            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-12 text-center text-slate-500">
                                <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <p class="text-base font-semibold text-slate-900">{{ __('No posts yet') }}</p>
                                <p class="text-sm text-slate-500 mt-1">{{ __('No posts in this category or matching the current filter.') }}</p>
                            </div>
                        @endforelse

                        <div class="mt-6">
                            {{ $posts->links() }}
                        </div>
                    </div>
                </div>

                <!-- Context Sidebar Column (4 cols on lg, 280-300px target) -->
                <aside class="lg:col-span-4 space-y-6">
                    <!-- Onboarding Checklist (when applicable) -->
                    @include('communities.partials.onboarding-checklist', ['community' => $community])

                    <!-- About Community Card -->
                    <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200/80 space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-brand-600 text-white font-bold flex items-center justify-center text-base shadow-xs">
                                {{ strtoupper(substr($community->name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-base text-slate-900">{{ $community->name }}</h3>
                                <p class="text-xs text-slate-400">
                                    {{ __('Created ') }} {{ $community->created_at->format('M Y') }}
                                </p>
                            </div>
                        </div>

                        <p class="text-xs text-slate-600 leading-relaxed">
                            {{ $community->description ?? __('A dedicated private learning and discussion community.') }}
                        </p>

                        <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                            <a href="{{ route('communities.classroom.index', $community) }}"
                               class="inline-flex items-center justify-between px-3 py-2 bg-slate-50 hover:bg-brand-50 hover:text-brand-700 border border-slate-200/80 rounded-xl text-xs font-semibold text-slate-700 transition">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    {{ __('Browse Classroom Courses') }}
                                </span>
                                <span>&rarr;</span>
                            </a>
                            <a href="{{ route('communities.directory.index', $community) }}"
                               class="inline-flex items-center justify-between px-3 py-2 bg-slate-50 hover:bg-brand-50 hover:text-brand-700 border border-slate-200/80 rounded-xl text-xs font-semibold text-slate-700 transition">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    {{ __('View Member Directory') }}
                                </span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>

    @livewire('discussion-viewer', ['community' => $community])
</x-app-layout>
