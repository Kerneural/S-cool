<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="feed" />

            @if (session('status'))
                <div class="p-4 font-medium text-sm text-emerald-800 bg-emerald-50 rounded-xl border border-emerald-200 shadow-xs flex items-center justify-between" role="alert">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ __(session('status')) }}</span>
                    </div>
                </div>
            @endif

            <!-- Feed reading grid (design 11.10): flexible main column + 300px context rail. -->
            <div class="feed-layout">
                <section class="feed-layout__learning min-w-0" aria-label="{{ __('Learning focus') }}">
                    <x-contextual-learning-rail :community="$community" :learning-focus="$learningFocus" part="learning" />
                </section>

                <div class="feed-layout__main min-w-0 space-y-5">
                    <h1 class="sr-only">{{ __('Community Feed') }}</h1>
                    <!-- Shared composer retains server routes and keyboard dialog behavior. -->
                    @php
                        $composerValue = static fn ($value) => is_scalar($value) ? (string) $value : '';
                        $composerTitle = $composerValue(old('title', ''));
                        $composerBody = $composerValue(old('body', ''));
                        $composerCategory = $composerValue(old('category_id', ''));
                        $composerCourse = $composerValue(old('course_id', request('course_id', '')));
                        $composerLesson = $composerValue(old('lesson_id', request('lesson_id', '')));
                        $composerOpen = $errors->hasAny(['title', 'body', 'category_id', 'course_id', 'lesson_id', 'images', 'documents', 'poll_options', 'link_url', 'video_url']) || request()->hasAny(['course_id', 'lesson_id', 'compose']);
                    @endphp
                    <div x-data="{
                        title: @js($composerTitle),
                        hasFiles: false,
                        body: @js($composerBody),
                        categoryId: @js($composerCategory),
                        courseId: @js($composerCourse),
                        lessonId: @js($composerLesson),
                        confirmClose() {
                            if ((this.title.trim() || this.body.trim() || this.categoryId || this.hasFiles) && !window.confirm(@js(__('Discard your unsaved post draft?')))) return false;
                            this.title = ''; this.body = ''; this.categoryId = ''; this.hasFiles = false;
                            this.$dispatch('social-composer-discard', { id: 'modal-post-body' });
                            this.$nextTick(() => document.querySelector('[data-composer-trigger]')?.focus());
                            return true;
                        }
                    }" @social-draft-updated="body = $event.detail.body; hasFiles = $event.detail.hasFiles" class="space-y-4">
                        <a href="{{ route('communities.posts.create', [$community, 'course_id' => $currentCourseId, 'lesson_id' => request('lesson_id')]) }}"
                           @click.prevent="$dispatch('open-modal', 'post-composer')" data-composer-trigger
                           class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 sm:p-5 flex flex-wrap items-center gap-3 hover:bg-slate-50/75 transition focus:outline-none focus:ring-2 focus:ring-brand"
                           aria-haspopup="dialog">
                            <span class="w-10 h-10 rounded-full bg-brand-50 text-brand-700 font-bold flex items-center justify-center text-sm shrink-0 border border-brand-200">
                                {{ mb_strtoupper(mb_substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </span>
                            <span class="min-w-0 flex-1 basis-[calc(100%-3.25rem)] sm:basis-auto py-2.5 px-4 bg-slate-50 rounded-xl text-sm text-slate-500 border border-slate-200/80">
                                {{ __('Write a post or ask a question to the community...') }}
                            </span>
                            <span class="ml-auto px-4 py-2.5 bg-brand-600 text-white text-xs font-semibold rounded-xl shadow-xs">{{ __('Write Post') }}</span>
                        </a>

                        <x-modal name="post-composer" :show="$composerOpen" labelledby="composer-modal-title" before-close="confirmClose()" focusable>
                            <div class="p-6 sm:p-8 space-y-5">
                                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                    <h2 id="composer-modal-title" class="text-lg font-bold text-slate-900">{{ __('Create a Post') }}</h2>
                                    <button type="button" @click="$dispatch('close')" class="min-h-11 min-w-11 text-slate-500 hover:text-slate-700 p-1.5 rounded-lg text-lg leading-none transition" aria-label="{{ __('Close dialog') }}">&times;</button>
                                </div>
                                <form method="POST" enctype="multipart/form-data" action="{{ route('communities.posts.store', $community) }}" class="space-y-4">
                                    @csrf
                                    <div class="flex flex-wrap items-center gap-2 text-sm text-muted">
                                        <x-member-identity :community="$community" :user="auth()->user()" />
                                        <span>· {{ $community->name }}</span>
                                    </div>
                                    <input type="hidden" name="course_id" :value="courseId" value="{{ $composerCourse }}">
                                    <input type="hidden" name="lesson_id" :value="lessonId" value="{{ $composerLesson }}">

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
                                        <x-input-label for="modal-post-title" :value="__('Title')" />
                                        <x-text-input id="modal-post-title" x-ref="postTitle" name="title" type="text" class="mt-1 block w-full" :value="$composerTitle" x-model="title" required maxlength="255" placeholder="{{ __('Write a clear, descriptive title...') }}" />
                                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="modal-post-category" :value="__('Category (Optional)')" />
                                        <select id="modal-post-category" name="category_id" x-model="categoryId" class="mt-1 block w-full border-slate-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-slate-800 bg-white p-2.5">
                                            <option value="">{{ __('-- None / General --') }}</option>
                                            @foreach ($categories as $cat)
                                                @if (! $cat->isAnnouncements() || $community->isCreator(auth()->user()))
                                                    <option value="{{ $cat->id }}" @selected($composerCategory == $cat->id)>
                                                        {{ $cat->display_name }}{{ $cat->isAnnouncements() ? ' ('.__('Creator Only').')' : '' }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                                    </div>

                                    <x-social-editor id="modal-post-body" :value="$composerBody" :format="old('content_format', 'plain')" :poll="true" :ajax="true" />
                                    <x-social-link-fields id="modal-post" />

                                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                                        <x-secondary-button type="button" @click="$dispatch('close')">
                                            {{ __('Cancel') }}
                                        </x-secondary-button>
                                        <x-primary-button>
                                            {{ __('Publish Post') }}
                                        </x-primary-button>
                                    </div>
                                </form>
                            </div>
                        </x-modal>
                    </div>

                    <x-feed-filters :community="$community" :categories="$categories" :current-category="$currentCategory" :current-sort="$currentSort" :current-course-id="$currentCourseId" />

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

                    <!-- Posts Feed List -->
                    <div x-data class="space-y-4">
                        @forelse ($posts as $post)
                            @php
                                $latestComment = $post->latestVisibleComment;
                                $commentMetadata = $latestComment ? [
                                    'id' => $latestComment->id,
                                    'author' => $latestComment->author->name ?? __('Unknown'),
                                    'profileUrl' => \App\Support\CommunityIdentity::profile($community, $latestComment->author),
                                    'body' => \Illuminate\Support\Str::limit($latestComment->body, 140),
                                    'datetime' => $latestComment->created_at->toIso8601String(),
                                    'time' => $latestComment->created_at->diffForHumans(),
                                ] : null;
                            @endphp
                            <article x-data="feedDiscussionMetadata(@js(['communityId' => $community->id, 'postId' => $post->id, 'count' => $post->comments_count, 'latestComment' => $commentMetadata]))"
                                     @discussion-comments-updated.window="updateComments($event.detail)"
                                     class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 sm:p-6 transition hover:border-slate-300 {{ $post->isPinned() ? 'ring-2 ring-amber-400/80 bg-amber-50/15' : '' }}">
                                <div class="flex flex-wrap justify-between items-start gap-4 mb-3">
                                    <div class="min-w-0 max-w-full flex items-center space-x-3">
                                        <x-member-identity :community="$community" :user="$post->author" :name="false" />
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-1.5 font-semibold text-slate-900 text-sm">
                                                <x-member-identity :community="$community" :user="$post->author" :avatar="false" />
                                                @if ($community->isCreator($post->author))
                                                    <span class="px-2 py-0.5 bg-brand-50 text-brand-700 rounded-full text-[11px] font-semibold border border-brand-200">{{ __('Creator') }}</span>
                                                @endif
                                                @if ($post->category)
                                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full text-xs font-normal">
                                                        {{ $post->category->display_name }}
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
                                                           class="inline-flex min-w-0 max-w-full items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 hover:bg-brand-100 transition border border-brand-200" title="{{ $post->course->title }}{{ $post->lesson ? ' / ' . $post->lesson->title : '' }}">
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
                                        @if ($community->isCreator(auth()->user()) || auth()->user()->can('update', $post) || auth()->user()->can('delete', $post))
                                            <x-action-menu :label="__('Post actions').' — '.$post->title">
@if ($community->isCreator(auth()->user()))
                                            @if ($post->isPinned())
                                                <form method="POST" action="{{ route('communities.posts.unpin', [$community, $post]) }}">
                                                    @csrf
                                                    <button type="submit" class="flex min-h-11 w-full items-center rounded-lg px-3 py-2 text-left text-sm font-medium text-ink hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand">
                                                        {{ __('Unpin') }}
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('communities.posts.pin', [$community, $post]) }}">
                                                    @csrf
                                                    <button type="submit" class="flex min-h-11 w-full items-center rounded-lg px-3 py-2 text-left text-sm font-medium text-ink hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand">
                                                        {{ __('Pin') }}
                                                    </button>
                                                </form>
                                            @endif
                                        @endif

                                        @can('update', $post)
                                            <a href="{{ route('communities.posts.edit', [$community, $post]) }}" class="flex min-h-11 w-full items-center rounded-lg px-3 py-2 text-left text-sm font-medium text-ink hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand">
                                                {{ __('Edit') }}
                                            </a>
                                        @endcan

                                        @can('delete', $post)
                                            <form class="mt-1 border-t border-line pt-1" method="POST" action="{{ route('communities.posts.destroy', [$community, $post]) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this post?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex min-h-11 w-full items-center rounded-lg px-3 py-2 text-left text-sm font-semibold text-rose-700 hover:bg-rose-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-rose-600">
                                                    {{ __('Delete post') }}
                                                </button>
                                            </form>
                                        @endcan
                                            </x-action-menu>
                                        @endif
                                    </div>
                                </div>

                                <a href="{{ route('communities.posts.show', [$community, $post]) }}"
                                   data-discussion-trigger="{{ $post->id }}"
                                   @click.prevent="$dispatch('open-discussion-trigger', { postId: {{ $post->id }}, trigger: $el })"
                                   class="block group cursor-pointer">
                                    <h2 class="break-words text-lg font-bold text-slate-900 group-hover:text-brand-600 transition mb-2">
                                        {{ $post->title }}
                                    </h2>
                                </a>
                                <div class="mb-4"><x-social-post-content :community="$community" :post="$post" :preview="true" /></div>

                                    <div x-show="latestComment" @if (! $latestComment) style="display: none" @endif
                                         class="min-w-0 mb-4 rounded-xl bg-slate-50 p-3 text-xs text-slate-600" data-latest-comment="{{ $latestComment?->id }}" :data-latest-comment="latestComment?.id">
                                        <span class="font-semibold">{{ __('Latest reply') }} · <a :href="latestComment?.profileUrl || null" x-text="latestComment?.author" class="hover:text-brand hover:underline" @if($commentMetadata['profileUrl'] ?? null) href="{{ $commentMetadata['profileUrl'] }}" @endif>{{ $commentMetadata['author'] ?? '' }}</a></span>
                                        <time datetime="{{ $commentMetadata['datetime'] ?? '' }}" :datetime="latestComment?.datetime" x-text="latestComment?.time">{{ $commentMetadata['time'] ?? '' }}</time>
                                        <p class="mt-1 break-words" x-text="latestComment?.body">{{ $commentMetadata['body'] ?? '' }}</p>
                                    </div>

                                <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
                                    <div class="flex flex-wrap items-center gap-4">
                                        @livewire('post-like-button', ['community' => $community, 'post' => $post], key('post-like-'.$post->id))
                                        @livewire('post-save-button', ['community' => $community, 'post' => $post], key('post-save-'.$post->id))

                                        <a href="{{ route('communities.posts.show', [$community, $post]) }}"
                                           data-discussion-trigger="{{ $post->id }}"
                                           @click.prevent="$dispatch('open-discussion-trigger', { postId: {{ $post->id }}, trigger: $el })"
                                           class="inline-flex items-center text-brand-600 hover:text-brand-700 font-medium transition cursor-pointer">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                            </svg>
                                            <span><span x-text="commentsCount">{{ $post->comments_count }}</span> {{ __('comments') }}</span>
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
                    @if (! empty($isCreator))
                        <details class="rounded-xl border border-line bg-white p-3" @if ($errors->has('name')) open @endif><summary class="min-h-11 cursor-pointer text-sm font-medium text-brand">{{ __('Manage Categories') }}</summary>
                            <div class="mt-3 space-y-3">
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
                                                <span>{{ $cat->display_name }}</span>
                                                <form method="POST" action="{{ route('communities.categories.destroy', [$community, $cat]) }}"
                                                      onsubmit="return confirm('{{ __('Delete this category? Posts will become uncategorized.') }}');"
                                                      class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg text-muted hover:bg-rose-50 hover:text-rose-700" aria-label="{{ __('Delete category').' — '.$cat->display_name }}" title="{{ __('Delete category') }}">
                                                        <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12m-10 0 1 13h6l1-13M9 7V4h6v3"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                        </details>
                    @endif

                </div>

                <!-- Secondary community context: compact summary and collapsed disclosures. -->
                <aside class="feed-layout__context min-w-0" aria-label="{{ __('Community context') }}">
                    <x-contextual-learning-rail :community="$community" :learning-focus="$learningFocus" :mini-ranking="$miniRanking" part="context" />
                </aside>
            </div>
        </div>
    </div>

    @livewire('discussion-viewer', ['community' => $community])
</x-app-layout>
