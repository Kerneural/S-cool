<div x-data="{
        isOpen: $wire.entangle('isOpen'),
        opening: false,
        historyNavigation: false,
        openError: null,
        triggerEl: null,
        inertNodes: [],
        previousBodyOverflow: '',
        init() {
            window.addEventListener('discussion-opened', (e) => {
                this.opening = false;
                this.setBackgroundInert(true);

                if (!this.historyNavigation && window.history.pushState && e.detail.url) {
                    window.history.pushState({ scoolDiscussionPostId: e.detail.postId }, '', e.detail.url);
                }
                this.historyNavigation = false;

                this.$nextTick(() => {
                    this.$refs.closeButton?.focus();
                });
            });

            window.addEventListener('discussion-closed', () => {
                this.opening = false;
                this.historyNavigation = false;
                this.setBackgroundInert(false);

                if (this.triggerEl) {
                    this.triggerEl.focus();
                    this.triggerEl = null;
                }
            });

            window.addEventListener('popstate', (e) => {
                if (e.state && e.state.scoolDiscussionPostId) {
                    this.openDiscussion(e.state.scoolDiscussionPostId, null, true);
                } else if (this.isOpen) {
                    this.historyNavigation = true;
                    $wire.close();
                }
            });

            window.addEventListener('open-discussion-trigger', (e) => {
                this.openDiscussion(e.detail.postId, e.detail.trigger, false);
            });
        },
        async openDiscussion(postId, trigger, fromHistory) {
            if (this.opening) return;

            if (!trigger) {
                trigger = document.querySelector(`[data-discussion-trigger='${postId}']`);
            }
            if (trigger) this.triggerEl = trigger;
            this.opening = true;
            this.openError = null;
            this.historyNavigation = fromHistory;
            this.setBackgroundInert(true);

            try {
                await $wire.openPost(postId);
            } catch (error) {
                this.opening = false;
                this.historyNavigation = false;
                this.setBackgroundInert(false);
                this.openError = @js(__('Unable to open this discussion. Refresh the page or check your access.'));
                this.triggerEl?.focus();
            }
        },
        requestClose() {
            if (window.history.state?.scoolDiscussionPostId) {
                window.history.back();
                return;
            }

            this.historyNavigation = true;
            $wire.close();
        },
        setBackgroundInert(enabled) {
            if (enabled) {
                if (this.inertNodes.length > 0) return;

                let branch = this.$root;
                while (branch?.parentElement && branch.parentElement !== document.documentElement) {
                    for (const sibling of branch.parentElement.children) {
                        if (sibling !== branch && !sibling.matches('script, style, link')) {
                            if (!sibling.inert) {
                                sibling.inert = true;
                                this.inertNodes.push(sibling);
                            }
                        }
                    }
                    branch = branch.parentElement;
                }

                this.previousBodyOverflow = document.body.style.overflow;
                document.body.style.overflow = 'hidden';
                return;
            }

            for (const node of this.inertNodes) node.inert = false;
            this.inertNodes = [];
            document.body.style.overflow = this.previousBodyOverflow;
        },
        handleKeyDown(e) {
            if (e.key === 'Escape') {
                this.requestClose();
                return;
            }
            if (e.key === 'Tab') {
                const focusables = this.$refs.modalDialog?.querySelectorAll('button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex=\'-1\'])');
                if (!focusables || focusables.length === 0) return;
                const first = focusables[0];
                const last = focusables[focusables.length - 1];
                if (!this.$refs.modalDialog.contains(document.activeElement)) {
                    first.focus();
                    e.preventDefault();
                } else if (e.shiftKey && document.activeElement === first) {
                    last.focus();
                    e.preventDefault();
                } else if (!e.shiftKey && document.activeElement === last) {
                    first.focus();
                    e.preventDefault();
                }
            }
        }
     }"
     @keydown.window="isOpen && handleKeyDown($event)"
     class="relative">

    <p x-show="openError"
       x-cloak
       x-text="openError"
       class="fixed bottom-5 left-1/2 z-50 -translate-x-1/2 rounded-xl border border-rose-200 bg-white px-4 py-3 text-sm font-medium text-rose-700 shadow-xl"
       role="alert"></p>

    <!-- Modal Backdrop & Container -->
    <div x-show="isOpen || opening"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center overflow-hidden"
         role="dialog"
         aria-modal="true"
         :aria-label="opening ? @js(__('Loading discussion')) : null"
         :aria-labelledby="opening ? null : 'discussion-modal-title'">

        <!-- Dim Backdrop -->
        <div x-show="isOpen || opening"
             x-transition:enter="transition-opacity ease-out duration-200 motion-reduce:duration-0"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150 motion-reduce:duration-0"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="if (isOpen) requestClose()"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"></div>

        <!-- Modal Dialog (Full screen on mobile, large centered dialog on desktop) -->
        <div x-show="isOpen || opening"
             x-ref="modalDialog"
             :aria-busy="opening.toString()"
             x-transition:enter="transition ease-out duration-200 motion-reduce:duration-0"
             x-transition:enter-start="opacity-0 scale-95 sm:translate-y-0 translate-y-full"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150 motion-reduce:duration-0"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 sm:translate-y-0 translate-y-full"
             class="relative w-full h-full sm:h-auto sm:max-h-[90vh] sm:max-w-3xl bg-white sm:rounded-3xl shadow-2xl border border-slate-200/80 flex flex-col z-10 overflow-hidden">

            <div x-show="opening && !isOpen"
                 class="absolute inset-0 z-20 flex flex-col items-center justify-center gap-3 bg-white"
                 role="status">
                <span class="h-8 w-8 animate-spin motion-reduce:animate-pulse rounded-full border-2 border-slate-200 border-t-brand-600" aria-hidden="true"></span>
                <span class="text-sm font-semibold text-slate-600">{{ __('Loading discussion...') }}</span>
            </div>

            <!-- Modal Header -->
            <div x-show="isOpen" class="px-5 py-4 sm:px-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/75 shrink-0">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-brand-500 shrink-0"></span>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">
                        {{ $categoryName ?? __('Discussion') }}
                    </span>
                    @if ($isPinned)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full text-[10px] font-bold border border-amber-300 shrink-0">
                            {{ __('Pinned') }}
                        </span>
                    @endif
                    @if ($hasLearningContext)
                        @if ($isLearningContextAccessible)
                            <a href="{{ $lessonUrl ?: $courseUrl }}" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-brand-50 text-brand-700 hover:bg-brand-100 transition border border-brand-200 shrink-0 max-w-[240px] truncate" title="{{ $courseTitle }}{{ $lessonTitle ? ' / ' . $lessonTitle : '' }}">
                                <svg class="w-3 h-3 text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <span class="truncate">{{ $courseTitle }}</span>
                                @if ($lessonTitle)
                                    <span class="text-brand-400">/</span>
                                    <span class="truncate">{{ $lessonTitle }}</span>
                                @endif
                            </a>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-500 border border-slate-200 shrink-0" title="{{ __('This course or lesson is not accessible to your account.') }}">
                                <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m4-6V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span>{{ __('Learning context unavailable') }}</span>
                            </span>
                        @endif
                    @endif
                </div>

                <button type="button"
                        x-ref="closeButton"
                        @click="requestClose()"
                        class="p-2 -mr-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition focus:outline-none focus:ring-2 focus:ring-brand-500"
                        title="{{ __('Close discussion (Esc)') }}"
                        aria-label="{{ __('Close discussion') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Scrollable Content: Post + Comments -->
            <div x-show="isOpen" class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-6 overscroll-contain">
                <!-- Post Main Body -->
                <article class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-brand-50 text-brand-700 font-bold flex items-center justify-center text-sm shrink-0 border border-brand-200">
                            {{ $authorInitial }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 text-sm">{{ $authorName }}</span>
                                @if ($isAuthorCreator)
                                    <span class="px-2 py-0.5 bg-brand-50 text-brand-700 rounded-full text-[10px] font-semibold border border-brand-200">
                                        {{ __('Creator') }}
                                    </span>
                                @endif
                            </div>
                            <time class="text-xs text-slate-400 block mt-0.5">{{ $postCreatedAt }}</time>
                        </div>
                    </div>

                    <h2 id="discussion-modal-title" class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight leading-snug">
                        {{ $postTitle ?: __('Discussion') }}
                    </h2>

                    <div class="prose prose-slate max-w-none text-slate-700 text-sm sm:text-base leading-relaxed whitespace-pre-line">
                        {{ $postBody }}
                    </div>

                    @if ($postId)
                        <div class="pt-3 border-t border-slate-100 flex items-center gap-4 text-xs text-slate-500">
                            @livewire('post-like-button', ['community' => App\Models\Community::find($communityId), 'post' => App\Models\Post::find($postId)], key('modal-like-'.$postId))
                            @livewire('post-save-button', ['community' => App\Models\Community::find($communityId), 'post' => App\Models\Post::find($postId)], key('modal-save-'.$postId))
                            <span class="inline-flex items-center text-slate-500 font-medium">
                                <svg class="w-4 h-4 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <span>{{ $commentsCount }} {{ __('comments') }}</span>
                            </span>
                        </div>
                    @endif
                </article>

                <!-- Feedback Notices -->
                @if ($errorMessage)
                    <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs flex items-center justify-between" role="alert">
                        <span>{{ $errorMessage }}</span>
                        <button type="button" wire:click="$set('errorMessage', null)" class="text-rose-600 hover:text-rose-900 font-bold ml-2">&times;</button>
                    </div>
                @endif
                @if ($successMessage)
                    <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-center justify-between" role="status">
                        <span>{{ $successMessage }}</span>
                        <button type="button" wire:click="$set('successMessage', null)" class="text-emerald-600 hover:text-emerald-900 font-bold ml-2">&times;</button>
                    </div>
                @endif

                <!-- Comments Thread Section -->
                <section class="space-y-4 pt-4 border-t border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span>{{ __('Comments') }}</span>
                        <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">{{ $commentsCount }}</span>
                    </h3>

                    @if ($hasEarlierComments)
                        <button type="button"
                                wire:click="loadEarlierComments"
                                wire:loading.attr="disabled"
                                wire:target="loadEarlierComments"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-brand-700 transition hover:border-brand-200 hover:bg-brand-50 disabled:cursor-wait disabled:opacity-60 motion-reduce:transition-none">
                            <span wire:loading.remove wire:target="loadEarlierComments">{{ __('Load earlier comments') }}</span>
                            <span wire:loading wire:target="loadEarlierComments">{{ __('Loading comments...') }}</span>
                        </button>
                    @endif

                    @if (empty($comments))
                        <div class="py-8 text-center text-slate-400 text-xs sm:text-sm">
                            <p>{{ __('No comments yet. Start the conversation!') }}</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($comments as $comment)
                                <div class="p-4 rounded-2xl {{ $comment['is_helpful'] ? 'bg-emerald-50/70 border-2 border-emerald-300 shadow-xs' : 'bg-slate-50/80 border border-slate-100' }} space-y-2 group" wire:key="comment-{{ $comment['id'] }}">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div class="flex min-w-0 flex-1 items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-700 font-bold flex items-center justify-center text-xs shrink-0">
                                                {{ $comment['author_initial'] }}
                                            </div>
                                            <div class="min-w-0 break-words [overflow-wrap:anywhere]">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="font-bold text-slate-900 text-xs">{{ $comment['author_name'] }}</span>
                                                    @if ($comment['is_creator'])
                                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-brand-50 text-brand-700">
                                                            {{ __('Creator') }}
                                                        </span>
                                                    @endif
                                                    @if ($comment['is_helpful'])
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                            <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            {{ __('Accepted Answer') }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <span class="text-[11px] text-slate-400 block">{{ $comment['created_at'] }}</span>
                                            </div>
                                        </div>

                                        <div class="flex max-w-full flex-wrap items-center gap-2 text-xs">
                                            @if ($comment['can_accept'])
                                                <button type="button"
                                                        wire:click="acceptHelpfulAnswer({{ $comment['id'] }})"
                                                        wire:loading.attr="disabled" wire:target="acceptHelpfulAnswer,revokeHelpfulAnswer"
                                                        class="inline-flex min-h-11 items-center gap-1 px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-lg border border-emerald-200 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand disabled:opacity-50">
                                                    {{ __('Accept Answer') }}
                                                </button>
                                            @endif
                                            @if ($comment['can_unaccept'])
                                                <button type="button"
                                                        wire:click="revokeHelpfulAnswer({{ $comment['id'] }})"
                                                        wire:loading.attr="disabled" wire:target="acceptHelpfulAnswer,revokeHelpfulAnswer"
                                                        class="min-h-11 px-2 text-xs text-slate-500 hover:text-slate-700 font-medium underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand disabled:opacity-50">
                                                    {{ __('Revoke') }}
                                                </button>
                                            @endif
                                            @if ($comment['can_edit'] && $editingCommentId !== $comment['id'])
                                                <button type="button"
                                                        wire:click="editComment({{ $comment['id'] }})"
                                                        class="text-brand-600 hover:text-brand-700 font-medium">
                                                    {{ __('Edit') }}
                                                </button>
                                            @endif
                                            @if ($comment['can_delete'] && $editingCommentId !== $comment['id'])
                                                <button type="button"
                                                        wire:click="deleteComment({{ $comment['id'] }})"
                                                        wire:confirm="{{ __('Delete this comment?') }}"
                                                        class="text-rose-600 hover:text-rose-800 font-medium">
                                                    {{ __('Delete') }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($editingCommentId === $comment['id'])
                                        <div class="space-y-2 pt-2">
                                            <textarea wire:model="editingCommentBody"
                                                      rows="3"
                                                      maxlength="2000"
                                                      class="w-full text-xs sm:text-sm rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 p-2.5 bg-white"></textarea>
                                            @error('editingCommentBody')
                                                <p class="text-[11px] text-rose-600 font-medium">{{ $message }}</p>
                                            @enderror
                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button"
                                                        wire:click="cancelEditComment"
                                                        class="px-3 py-1.5 bg-slate-200 text-slate-700 hover:bg-slate-300 text-xs font-semibold rounded-lg transition">
                                                    {{ __('Cancel') }}
                                                </button>
                                                <button type="button"
                                                        wire:click="updateComment"
                                                        wire:loading.attr="disabled"
                                                        class="px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold rounded-lg transition shadow-xs">
                                                    {{ __('Save') }}
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-xs sm:text-sm text-slate-700 whitespace-pre-line leading-relaxed pl-9">
                                            {{ $comment['body'] }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>

            <!-- Sticky Comment Composer (Bottom) -->
            <div x-show="isOpen" class="p-3 sm:p-4 bg-slate-50 border-t border-slate-200 shrink-0">
                <form wire:submit="addComment" class="space-y-2">
                    <div class="flex gap-2">
                        <textarea wire:model="newCommentBody"
                                  rows="2"
                                  maxlength="2000"
                                  placeholder="{{ __('Write a constructive reply...') }}"
                                  class="flex-1 text-xs sm:text-sm rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 p-2.5 bg-white shadow-xs resize-none"></textarea>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="px-4 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold rounded-xl shadow-xs transition active:scale-[0.98] self-stretch flex items-center justify-center disabled:opacity-60">
                            <span wire:loading.remove wire:target="addComment">{{ __('Reply') }}</span>
                            <span wire:loading wire:target="addComment" class="animate-pulse">{{ __('Posting...') }}</span>
                        </button>
                    </div>
                    @error('newCommentBody')
                        <p class="text-[11px] text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </form>
            </div>
        </div>
    </div>
</div>
