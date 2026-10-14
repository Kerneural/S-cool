<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <a href="{{ route('communities.posts.index', $community) }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    &larr; {{ __('Back to Feed') }}
                </a>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('communities.show', $community) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">
                    {{ __('Community Info') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 font-medium text-sm text-green-700 bg-green-50 rounded-lg border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Main Post Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 p-6">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-sm">
                            {{ strtoupper(substr($post->author->name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-1.5 font-semibold text-gray-900 text-sm">
                                <span>{{ $post->author->name ?? __('Unknown') }}</span>
                                @if ($community->isCreator($post->author))
                                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded-full text-xs font-medium">{{ __('Creator') }}</span>
                                @endif
                                @if ($post->category)
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs font-normal">
                                        {{ $post->category->name }}
                                    </span>
                                @endif
                                @if ($post->isPinned())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full text-xs font-bold border border-amber-300">
                                        <svg class="w-3 h-3 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/></svg>
                                        {{ __('Pinned') }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ $post->created_at->format('M d, Y H:i') }} ({{ $post->created_at->diffForHumans() }})
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center space-x-3">
                        @if ($community->isCreator(auth()->user()))
                            @if ($post->isPinned())
                                <form method="POST" action="{{ route('communities.posts.unpin', [$community, $post]) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-amber-700 hover:text-amber-900 underline font-medium">
                                        {{ __('Unpin') }}
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('communities.posts.pin', [$community, $post]) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-gray-500 hover:text-gray-800 underline font-medium">
                                        {{ __('Pin') }}
                                    </button>
                                </form>
                            @endif
                        @endif
                        @can('update', $post)
                            <a href="{{ route('communities.posts.edit', [$community, $post]) }}" class="text-xs text-indigo-600 hover:text-indigo-800 underline font-medium">
                                {{ __('Edit') }}
                            </a>
                        @endcan
                        @can('delete', $post)
                            <form method="POST" action="{{ route('communities.posts.destroy', [$community, $post]) }}" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-600 hover:text-red-800 underline font-medium">
                                    {{ __('Delete') }}
                                </button>
                            </form>
                        @endcan
                        @if (auth()->check() && $community->hasAccess(auth()->user()))
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" type="button" class="text-xs text-gray-400 hover:text-rose-600 font-medium">
                                    {{ __('Report') }}
                                </button>
                                <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-lg border border-gray-200 p-4 z-50 text-left" style="display: none;">
                                    <form method="POST" action="{{ route('communities.reports.store', $community) }}">
                                        @csrf
                                        <input type="hidden" name="target_type" value="POST">
                                        <input type="hidden" name="target_id" value="{{ $post->id }}">
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Report Post') }}</label>
                                        <textarea name="reason" rows="3" required minlength="10" maxlength="1000" placeholder="{{ __('Reason for report (minimum 10 characters)...') }}" class="w-full text-xs border-gray-300 rounded p-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                        <div class="mt-2 flex justify-end gap-2">
                                            <button @click="open = false" type="button" class="px-2 py-1 text-xs text-gray-500 hover:text-gray-700">{{ __('Cancel') }}</button>
                                            <button type="submit" class="px-2.5 py-1 text-xs bg-rose-600 hover:bg-rose-700 text-white font-medium rounded">{{ __('Submit Report') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <h1 class="text-2xl font-bold text-gray-900 mb-4">
                    {{ $post->title }}
                </h1>

                <div class="text-gray-800 leading-relaxed whitespace-pre-line text-base mb-6">
                    {{ $post->body }}
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                    <div class="flex items-center gap-4">
                        @php
                            $isLiked = $post->isLikedBy(auth()->user());
                            $likesCount = $post->active_likes_count ?? $post->activeLikes()->count();
                            $isBookmarked = $post->isBookmarkedBy(auth()->user());
                        @endphp
                        @if ((int) $post->user_id === (int) auth()->id())
                            <span class="inline-flex items-center text-gray-400 cursor-not-allowed" title="{{ __('You cannot like your own post') }}">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
                                {{ $likesCount }} {{ __('likes') }}
                            </span>
                        @elseif ($isLiked)
                            <form method="POST" action="{{ route('communities.posts.unlike', [$community, $post]) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center text-rose-600 hover:text-rose-700 font-semibold" title="{{ __('Unlike post') }}">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24"><path d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
                                    {{ $likesCount }} {{ __('likes') }}
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('communities.posts.like', [$community, $post]) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center text-gray-500 hover:text-rose-600 font-medium" title="{{ __('Like post') }}">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
                                    {{ $likesCount }} {{ __('likes') }}
                                </button>
                            </form>
                        @endif

                        @if ($isBookmarked)
                            <form method="POST" action="{{ route('communities.posts.unsave', [$community, $post]) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center text-amber-600 hover:text-amber-700 font-semibold" title="{{ __('Remove from saved') }}">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/></svg>
                                    {{ __('Saved') }}
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('communities.posts.save', [$community, $post]) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center text-gray-500 hover:text-amber-600 font-medium" title="{{ __('Save post for later') }}">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>
                                    {{ __('Save') }}
                                </button>
                            </form>
                        @endif
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $comments->total() }} {{ __('replies') }}
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 p-6 space-y-6">
                <h3 class="text-lg font-bold text-gray-900">
                    {{ __('Comments') }} ({{ $comments->total() }})
                </h3>

                <!-- Add Comment Form -->
                <form method="POST" action="{{ route('communities.posts.comments.store', [$community, $post]) }}" class="space-y-3">
                    @csrf
                    <div>
                        <textarea id="body" name="body" rows="3" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required placeholder="{{ __('Write a reply...') }}">{{ old('body') }}</textarea>
                        <x-input-error :messages="$errors->get('body')" class="mt-2" />
                    </div>
                    <div class="flex justify-end">
                        <x-primary-button>
                            {{ __('Comment') }}
                        </x-primary-button>
                    </div>
                </form>

                <!-- Comments List -->
                <div class="space-y-4 pt-4 border-t border-gray-100">
                    @forelse ($comments as $comment)
                        <div class="flex space-x-3 p-3 rounded-lg bg-gray-50 border border-gray-100">
                            <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-700 font-semibold flex items-center justify-center text-xs flex-shrink-0">
                                {{ strtoupper(substr($comment->author->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="flex-grow">
                                <div class="flex justify-between items-center mb-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-medium text-xs text-gray-900">{{ $comment->author->name ?? __('Unknown') }}</span>
                                        @if ($community->isCreator($comment->author))
                                            <span class="px-1.5 py-0.2 bg-indigo-50 text-indigo-600 rounded text-[10px] font-medium">{{ __('Creator') }}</span>
                                        @endif
                                        <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    @can('update', $comment)
                                        <a href="{{ route('communities.posts.comments.edit', [$community, $post, $comment]) }}" class="text-xs text-indigo-600 hover:text-indigo-800">{{ __('Edit') }}</a>
                                    @endcan
                                    @can('delete', $comment)
                                        <form method="POST" action="{{ route('communities.posts.comments.destroy', [$community, $post, $comment]) }}" onsubmit="return confirm('Delete comment?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">
                                                &times; {{ __('Delete') }}
                                            </button>
                                        </form>
                                    @endcan
                                    @if (auth()->check() && $community->hasAccess(auth()->user()))
                                        <div x-data="{ open: false }" class="relative inline-block ml-2">
                                            <button @click="open = !open" type="button" class="text-xs text-gray-400 hover:text-rose-600">
                                                {{ __('Report') }}
                                            </button>
                                            <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-lg border border-gray-200 p-4 z-50 text-left" style="display: none;">
                                                <form method="POST" action="{{ route('communities.reports.store', $community) }}">
                                                    @csrf
                                                    <input type="hidden" name="target_type" value="COMMENT">
                                                    <input type="hidden" name="target_id" value="{{ $comment->id }}">
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Report Comment') }}</label>
                                                    <textarea name="reason" rows="3" required minlength="10" maxlength="1000" placeholder="{{ __('Reason for report (minimum 10 characters)...') }}" class="w-full text-xs border-gray-300 rounded p-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                                    <div class="mt-2 flex justify-end gap-2">
                                                        <button @click="open = false" type="button" class="px-2 py-1 text-xs text-gray-500 hover:text-gray-700">{{ __('Cancel') }}</button>
                                                        <button type="submit" class="px-2.5 py-1 text-xs bg-rose-600 hover:bg-rose-700 text-white font-medium rounded">{{ __('Submit Report') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">
                                    {{ $comment->body }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-4">{{ __('No comments yet. Start the conversation!') }}</p>
                    @endforelse
                </div>
                {{ $comments->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
