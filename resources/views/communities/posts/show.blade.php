<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="feed" />

            <!-- Back navigation & Breadcrumb -->
            <div class="flex items-center justify-between">
                <a href="{{ route('communities.posts.index', $community) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 hover:text-brand-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>{{ __('Back to Feed') }}</span>
                </a>
                <a href="{{ route('communities.show', $community) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-slate-200 rounded-xl font-semibold text-xs text-slate-700 hover:bg-slate-50 transition shadow-xs">
                    {{ __('Community Info') }}
                </a>
            </div>

            @if (session('status'))
                <div class="p-4 font-medium text-sm text-emerald-800 bg-emerald-50 rounded-2xl border border-emerald-200 shadow-xs flex items-center justify-between" role="alert">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            <!-- Main Discussion Card -->
            <article class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 sm:p-8 space-y-6">
                <!-- Post Author & Metadata Header -->
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-brand-600 to-teal-800 text-white font-bold flex items-center justify-center text-sm shadow-xs shrink-0">
                            {{ strtoupper(substr($post->author->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2 font-semibold text-ink text-sm">
                                <span>{{ $post->author->name ?? __('Unknown') }}</span>
                                @if ($community->isCreator($post->author))
                                    <span class="px-2 py-0.5 bg-brand-50 text-brand-700 rounded-full text-xs font-semibold border border-brand-200/60">
                                        {{ __('Creator') }}
                                    </span>
                                @endif
                                @if ($post->category)
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-full text-xs font-medium">
                                        {{ $post->category->name }}
                                    </span>
                                @endif
                                @if ($post->isPinned())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-amber-50 text-amber-800 rounded-full text-xs font-bold border border-amber-200">
                                        <svg class="w-3 h-3 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z" />
                                        </svg>
                                        {{ __('Pinned') }}
                                    </span>
                                @endif
                                @if ($post->hasLearningContext())
                                    @if ($post->isLearningContextAccessibleFor(auth()->user()))
                                        <a href="{{ $post->lesson ? route('communities.lessons.show', [$community, $post->course, $post->lesson]) : route('communities.courses.show', [$community, $post->course]) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 hover:bg-brand-100 transition border border-brand-200 shadow-xs" title="{{ $post->course->title }}{{ $post->lesson ? ' / ' . $post->lesson->title : '' }}">
                                            <svg class="w-3.5 h-3.5 text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                            <span class="truncate max-w-xs">{{ $post->course->title }}</span>
                                            @if ($post->lesson)
                                                <span class="text-brand-400">/</span>
                                                <span class="truncate max-w-xs">{{ $post->lesson->title }}</span>
                                            @endif
                                        </a>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500 border border-slate-200" title="{{ __('This course or lesson is not accessible to your account.') }}">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m4-6V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                            <span>{{ __('Learning context unavailable') }}</span>
                                        </span>
                                    @endif
                                @endif
                            </div>
                            <div class="text-xs text-muted mt-0.5">
                                {{ $post->created_at->format('M d, Y H:i') }} ({{ $post->created_at->diffForHumans() }})
                            </div>
                        </div>
                    </div>

                    <!-- Creator / Author Actions Toolbar -->
                    <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto sm:shrink-0">
                        @if ($community->isCreator(auth()->user()))
                            @if ($post->isPinned())
                                <form method="POST" action="{{ route('communities.posts.unpin', [$community, $post]) }}">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 text-xs text-amber-800 bg-amber-50 hover:bg-amber-100 font-semibold rounded-lg border border-amber-200 transition">
                                        {{ __('Unpin') }}
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('communities.posts.pin', [$community, $post]) }}">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 text-xs text-slate-600 bg-slate-50 hover:bg-slate-100 font-semibold rounded-lg border border-slate-200 transition">
                                        {{ __('Pin') }}
                                    </button>
                                </form>
                            @endif
                        @endif

                        @can('update', $post)
                            <a href="{{ route('communities.posts.edit', [$community, $post]) }}" class="px-2.5 py-1 text-xs text-brand-700 bg-brand-50 hover:bg-brand-100 font-semibold rounded-lg border border-brand-200/60 transition">
                                {{ __('Edit') }}
                            </a>
                        @endcan

                        @can('delete', $post)
                            <form method="POST" action="{{ route('communities.posts.destroy', [$community, $post]) }}" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2.5 py-1 text-xs text-rose-700 bg-rose-50 hover:bg-rose-100 font-semibold rounded-lg border border-rose-200 transition">
                                    {{ __('Delete') }}
                                </button>
                            </form>
                        @endcan

                        @if (auth()->check() && $community->hasAccess(auth()->user()))
                            @php
                                $postReportHasErrors = $errors->has('reason')
                                    && old('target_type') === 'POST'
                                    && (int) old('target_id') === (int) $post->id;
                            @endphp
                            <div x-data="{ open: @js($postReportHasErrors) }" @keydown.escape.window="if (open) { open = false; $nextTick(() => $refs.reportTrigger.focus()) }" class="relative">
                                <button x-ref="reportTrigger" @click="open = !open" :aria-expanded="open.toString()" aria-controls="post-report-form-{{ $post->id }}" type="button" class="px-2 py-1 text-xs text-slate-500 hover:text-rose-700 font-medium rounded-lg hover:bg-slate-50 transition" title="{{ __('Report Post') }}">
                                    {{ __('Report') }}
                                </button>
                                <div id="post-report-form-{{ $post->id }}" x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-white rounded-2xl shadow-xl border border-slate-200 p-4 z-50 text-left">
                                    <form method="POST" action="{{ route('communities.reports.store', $community) }}" class="space-y-3">
                                        @csrf
                                        <input type="hidden" name="target_type" value="POST">
                                        <input type="hidden" name="target_id" value="{{ $post->id }}">
                                        <div>
                                            <label for="post-report-reason-{{ $post->id }}" class="block text-xs font-bold text-ink mb-1">{{ __('Report Post') }}</label>
                                            <p class="text-[11px] text-muted mb-2">{{ __('Notify community creators of inappropriate or infringing content.') }}</p>
                                            <textarea id="post-report-reason-{{ $post->id }}" name="reason" rows="3" required minlength="10" maxlength="1000" placeholder="{{ __('Reason for report (minimum 10 characters)...') }}" class="w-full text-xs border-slate-300 rounded-xl p-2.5 focus:ring-brand-500 focus:border-brand-500">{{ $postReportHasErrors && is_string(old('reason')) ? old('reason') : '' }}</textarea>
                                            @if ($postReportHasErrors)
                                                <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                                            @endif
                                        </div>
                                        <div class="flex justify-end gap-2 pt-1 border-t border-slate-100">
                                            <button @click="open = false" type="button" class="px-3 py-1.5 text-xs text-slate-600 hover:text-slate-800 font-semibold">{{ __('Cancel') }}</button>
                                            <button type="submit" class="px-3 py-1.5 text-xs bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl shadow-xs transition">{{ __('Submit Report') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Post Title Heading (Single canonical h1) -->
                <h1 class="break-words text-2xl sm:text-3xl font-extrabold text-ink tracking-tight leading-tight">
                    {{ $post->title }}
                </h1>

                <!-- Post Body Content -->
                <div class="break-words text-slate-700 leading-relaxed whitespace-pre-line text-base font-normal">
                    {{ $post->body }}
                </div>

                <!-- Reaction & Engagement Bar -->
                <div class="pt-5 border-t border-slate-100 flex items-center justify-between text-xs text-muted">
                    <div class="flex items-center gap-4">
                        @livewire('post-like-button', ['community' => $community, 'post' => $post], key('show-like-'.$post->id))
                        @livewire('post-save-button', ['community' => $community, 'post' => $post], key('show-save-'.$post->id))
                    </div>

                    <div class="text-xs font-semibold text-slate-500">
                        {{ $comments->total() }} {{ __('replies') }}
                    </div>
                </div>
            </article>

            <!-- Comments & Discussion Replies Thread -->
            <section class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 sm:p-8 space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-ink">
                        {{ __('Comments') }} ({{ $comments->total() }})
                    </h2>
                </div>

                <!-- Add Comment Composer Form -->
                <form method="POST" action="{{ route('communities.posts.comments.store', [$community, $post]) }}" class="space-y-3">
                    @csrf
                    <div>
                        <textarea id="body" name="body" rows="3" required placeholder="{{ __('Write a reply...') }}" class="block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink placeholder-slate-400 p-3.5">{{ old('body') }}</textarea>
                        <x-input-error :messages="$errors->get('body')" class="mt-2" />
                    </div>
                    <div class="flex justify-end">
                        <x-primary-button>
                            {{ __('Comment') }}
                        </x-primary-button>
                    </div>
                </form>

                <!-- Comments List -->
                <div class="space-y-4 pt-4 border-t border-slate-100">
                    @forelse ($comments as $comment)
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60 flex items-start gap-3.5">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 text-white font-bold flex items-center justify-center text-xs shrink-0 shadow-xs">
                                {{ strtoupper(substr($comment->author->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-xs text-ink">{{ $comment->author->name ?? __('Unknown') }}</span>
                                        @if ($community->isCreator($comment->author))
                                            <span class="px-1.5 py-0.5 bg-brand-50 text-brand-700 rounded text-[10px] font-semibold border border-brand-200/60">
                                                {{ __('Creator') }}
                                            </span>
                                        @endif
                                        <span class="text-[11px] text-muted">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        @can('update', $comment)
                                            <a href="{{ route('communities.posts.comments.edit', [$community, $post, $comment]) }}" class="text-xs font-semibold text-brand-700 hover:text-brand-800 transition">
                                                {{ __('Edit') }}
                                            </a>
                                        @endcan

                                        @can('delete', $comment)
                                            <form method="POST" action="{{ route('communities.posts.comments.destroy', [$community, $post, $comment]) }}" onsubmit="return confirm('Delete comment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-semibold transition">
                                                    &times; {{ __('Delete') }}
                                                </button>
                                            </form>
                                        @endcan

                                        @if (auth()->check() && $community->hasAccess(auth()->user()))
                                            @php
                                                $commentReportHasErrors = $errors->has('reason')
                                                    && old('target_type') === 'COMMENT'
                                                    && (int) old('target_id') === (int) $comment->id;
                                            @endphp
                                            <div x-data="{ open: @js($commentReportHasErrors) }" @keydown.escape.window="if (open) { open = false; $nextTick(() => $refs.reportTrigger.focus()) }" class="relative inline-block">
                                                <button x-ref="reportTrigger" @click="open = !open" :aria-expanded="open.toString()" aria-controls="comment-report-form-{{ $comment->id }}" type="button" class="text-xs text-slate-500 hover:text-rose-700 font-medium">
                                                    {{ __('Report') }}
                                                </button>
                                                <div id="comment-report-form-{{ $comment->id }}" x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-72 max-w-[calc(100vw-2rem)] bg-white rounded-2xl shadow-xl border border-slate-200 p-4 z-50 text-left">
                                                    <form method="POST" action="{{ route('communities.reports.store', $community) }}" class="space-y-3">
                                                        @csrf
                                                        <input type="hidden" name="target_type" value="COMMENT">
                                                        <input type="hidden" name="target_id" value="{{ $comment->id }}">
                                                        <div>
                                                            <label for="comment-report-reason-{{ $comment->id }}" class="block text-xs font-bold text-ink mb-1">{{ __('Report Comment') }}</label>
                                                            <textarea id="comment-report-reason-{{ $comment->id }}" name="reason" rows="3" required minlength="10" maxlength="1000" placeholder="{{ __('Reason for report (minimum 10 characters)...') }}" class="w-full text-xs border-slate-300 rounded-xl p-2.5 focus:ring-brand-500 focus:border-brand-500">{{ $commentReportHasErrors && is_string(old('reason')) ? old('reason') : '' }}</textarea>
                                                            @if ($commentReportHasErrors)
                                                                <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                                                            @endif
                                                        </div>
                                                        <div class="flex justify-end gap-2 pt-1 border-t border-slate-100">
                                                            <button @click="open = false" type="button" class="px-2.5 py-1 text-xs text-slate-600 hover:text-slate-800 font-semibold">{{ __('Cancel') }}</button>
                                                            <button type="submit" class="px-3 py-1 text-xs bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl shadow-xs transition">{{ __('Submit Report') }}</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="break-words text-sm text-slate-700 whitespace-pre-line leading-relaxed">
                                    {{ $comment->body }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center space-y-2">
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                            <p class="text-sm text-muted font-medium">{{ __('No comments yet. Start the conversation!') }}</p>
                        </div>
                    @endforelse
                </div>

                @if ($comments->hasPages())
                    <div class="pt-4 border-t border-slate-100">
                        {{ $comments->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
