<x-app-layout>
    <div class="py-10">
        <div class="min-w-0 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 break-words [overflow-wrap:anywhere]">
            <x-community-workspace :community="$community" active-tab="feed" />

            @if (session('status'))
                <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm shadow-xs">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="font-medium text-sm text-green-700">{{ session('status') }}</span>
                </div>
            @endif

            <!-- Saved Posts Header -->
            <div class="flex flex-wrap items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-xs border border-slate-200/80">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/></svg>
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-slate-900 tracking-tight">{{ __('Your Saved Posts') }}</h1>
                        <p class="text-xs text-slate-500">{{ __('Personal bookmarks in this community. Only visible to you.') }}</p>
                    </div>
                </div>

                <a href="{{ route('communities.posts.index', $community) }}" class="inline-flex min-h-11 items-center gap-1.5 text-xs font-bold text-teal-600 hover:text-teal-700">
                    &larr; {{ __('Back to Community Feed') }}
                </a>
            </div>

            <!-- Posts List -->
            <div x-data class="space-y-4">
                @forelse ($posts as $post)
                    <div x-data="{ visible: true }"
                         x-show="visible"
                         x-transition.opacity.duration.150ms
                         @post-bookmark-updated.window="if ($event.detail.postId === {{ $post->id }} && !$event.detail.saved) visible = false"
                         class="min-w-0 bg-white rounded-2xl p-4 sm:p-6 border border-slate-200/80 shadow-xs hover:border-slate-300 transition duration-150 motion-reduce:transition-none">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="w-9 h-9 shrink-0 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white font-bold flex items-center justify-center text-xs shadow-2xs">
                                    {{ strtoupper(substr($post->author->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 font-bold text-slate-900 text-sm">
                                        <span>{{ $post->author->name ?? __('Unknown') }}</span>
                                        @if ($community->isCreator($post->author))
                                            <span class="px-2 py-0.5 bg-teal-50 text-teal-700 rounded-full text-[11px] font-bold border border-teal-200/60">{{ __('Creator') }}</span>
                                        @endif
                                        @if ($post->category)
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded-md text-[11px] font-medium">
                                                {{ $post->category->name }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400 font-medium">
                                        {{ $post->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>

                        </div>

                        <a href="{{ route('communities.posts.show', [$community, $post]) }}"
                           data-discussion-trigger="{{ $post->id }}"
                           @click.prevent="$dispatch('open-discussion-trigger', { postId: {{ $post->id }}, trigger: $el })"
                           class="block group cursor-pointer">
                            <h4 class="text-base font-bold text-slate-900 group-hover:text-teal-600 transition mb-2">
                                {{ $post->title }}
                            </h4>
                            <p class="text-slate-600 text-sm whitespace-pre-line line-clamp-3 leading-relaxed mb-4">
                                {{ $post->body }}
                            </p>
                        </a>

                        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
                            <div class="flex min-w-0 flex-wrap items-center gap-4">
                                @livewire('post-like-button', ['community' => $community, 'post' => $post], key('saved-like-'.$post->id))
                                @livewire('post-save-button', ['community' => $community, 'post' => $post], key('saved-save-'.$post->id))

                                <a href="{{ route('communities.posts.show', [$community, $post]) }}"
                                   data-discussion-trigger="{{ $post->id }}"
                                   @click.prevent="$dispatch('open-discussion-trigger', { postId: {{ $post->id }}, trigger: $el })"
                                   class="inline-flex min-h-11 items-center text-teal-600 hover:text-teal-700 font-semibold cursor-pointer">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                    {{ $post->comments_count }} {{ __('comments') }}
                                </a>
                            </div>

                            <a href="{{ route('communities.posts.show', [$community, $post]) }}"
                               data-discussion-trigger="{{ $post->id }}"
                               @click.prevent="$dispatch('open-discussion-trigger', { postId: {{ $post->id }}, trigger: $el })"
                               class="inline-flex min-h-11 items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-brand-700 font-semibold text-xs rounded-xl transition border border-slate-200/80 cursor-pointer"
                               aria-label="{{ __('Open discussion for :title', ['title' => $post->title]) }}">
                                <svg class="w-3.5 h-3.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <span>{{ __('Discussion') }}</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl p-6 sm:p-16 text-center border border-slate-200/80 shadow-xs">
                        <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>
                        <p class="text-base font-bold text-slate-900">{{ __('No saved posts yet') }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ __('Posts you save will appear here privately.') }}</p>
                        <div class="mt-4">
                            <a href="{{ route('communities.posts.index', $community) }}" class="inline-flex items-center px-4 py-2 bg-brand text-white rounded-xl text-xs font-bold hover:bg-brand-hover shadow-xs">
                                {{ __('Browse Community Feed') }}
                            </a>
                        </div>
                    </div>
                @endforelse

                <div class="mt-6">
                    {{ $posts->links() }}
                </div>
            </div>
        </div>
    </div>

    @livewire('discussion-viewer', ['community' => $community])
</x-app-layout>
