<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="feed" />

            @if (session('status'))
                <div class="p-4 font-medium text-sm text-green-700 bg-green-50 rounded-lg border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Saved Posts Header -->
            <div class="flex items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="p-2 bg-amber-50 rounded-lg text-amber-600">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900">{{ __('Your Saved Posts') }}</h2>
                        <p class="text-xs text-gray-500">{{ __('Personal bookmarks in this community. Only visible to you.') }}</p>
                    </div>
                </div>

                <a href="{{ route('communities.posts.index', $community) }}" class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                    &larr; {{ __('Back to Community Feed') }}
                </a>
            </div>

            <!-- Posts List -->
            <div class="space-y-4">
                @forelse ($posts as $post)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100 hover:border-gray-200 transition">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs">
                                    {{ strtoupper(substr($post->author->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 font-semibold text-gray-900 text-sm">
                                        <span>{{ $post->author->name ?? __('Unknown') }}</span>
                                        @if ($community->isCreator($post->author))
                                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded-full text-xs font-medium">{{ __('Creator') }}</span>
                                        @endif
                                        @if ($post->category)
                                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs font-normal">
                                                {{ $post->category->name }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ $post->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('communities.posts.unsave', [$community, $post]) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-amber-700 hover:text-amber-900 underline font-medium" title="{{ __('Remove from saved') }}">
                                    {{ __('Remove from saved') }}
                                </button>
                            </form>
                        </div>

                        <a href="{{ route('communities.posts.show', [$community, $post]) }}" class="block group">
                            <h4 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition mb-2">
                                {{ $post->title }}
                            </h4>
                            <p class="text-gray-700 text-sm whitespace-pre-line line-clamp-4 leading-relaxed mb-4">
                                {{ $post->body }}
                            </p>
                        </a>

                        <div class="flex items-center justify-between pt-3 border-t text-xs text-gray-500">
                            <div class="flex items-center gap-4">
                                @php
                                    $isLiked = $post->isLikedBy(auth()->user());
                                    $likesCount = $post->active_likes_count ?? $post->activeLikes()->count();
                                @endphp
                                @if ((int) $post->user_id === (int) auth()->id())
                                    <span class="inline-flex items-center text-gray-400 cursor-not-allowed">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
                                        {{ $likesCount }} {{ __('likes') }}
                                    </span>
                                @elseif ($isLiked)
                                    <form method="POST" action="{{ route('communities.posts.unlike', [$community, $post]) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center text-rose-600 hover:text-rose-700 font-semibold">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24"><path d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
                                            {{ $likesCount }} {{ __('likes') }}
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('communities.posts.like', [$community, $post]) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center text-gray-500 hover:text-rose-600 font-medium">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
                                            {{ $likesCount }} {{ __('likes') }}
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('communities.posts.show', [$community, $post]) }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                    {{ $post->comments_count }} {{ __('comments') }}
                                </a>
                            </div>

                            <a href="{{ route('communities.posts.show', [$community, $post]) }}" class="text-gray-400 hover:text-gray-600">
                                {{ __('View full discussion &rarr;') }}
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 text-center text-gray-500 border border-gray-100">
                        <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>
                        <p class="text-base font-medium text-gray-900">{{ __('No saved posts yet') }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ __('Posts you save will appear here privately.') }}</p>
                        <div class="mt-4">
                            <a href="{{ route('communities.posts.index', $community) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700">
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
</x-app-layout>
