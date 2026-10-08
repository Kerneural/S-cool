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
                            <div class="font-semibold text-gray-900 text-sm">
                                {{ $post->author->name ?? __('Unknown') }}
                                @if ($community->isCreator($post->author))
                                    <span class="ml-1 px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded-full text-xs font-medium">{{ __('Creator') }}</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ $post->created_at->format('M d, Y H:i') }} ({{ $post->created_at->diffForHumans() }})
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center space-x-3">
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
                    </div>
                </div>

                <h1 class="text-2xl font-bold text-gray-900 mb-4">
                    {{ $post->title }}
                </h1>

                <div class="text-gray-800 leading-relaxed whitespace-pre-line text-base mb-6">
                    {{ $post->body }}
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
