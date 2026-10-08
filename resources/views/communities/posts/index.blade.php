<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
            <div class="flex flex-wrap items-center gap-3 min-w-0">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $community->name }} — {{ __('Community Feed') }}
                </h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $community->isActive() ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                    {{ $community->status }}
                </span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('communities.events.index', $community) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">
                    {{ __('Events') }}
                </a>
                <a href="{{ route('communities.show', $community) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">
                    {{ __('About') }}
                </a>
                @can('update', $community)
                    <a class="underline text-sm px-2" href="{{ route('communities.invitations.index', $community) }}">Invitations</a>
                    <a href="{{ route('communities.edit', $community) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">
                        {{ __('Edit Settings') }}
                    </a>
                @endcan
                <a href="{{ route('communities.index') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 border border-transparent rounded-md font-semibold text-xs text-gray-600 uppercase tracking-widest hover:bg-gray-200 transition">
                    {{ __('Back to List') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($community->cover_path)
                <img src="{{ route('communities.cover.show', $community) }}" alt="Community cover" class="w-full max-h-48 object-cover rounded-lg shadow-sm">
            @endif

            @if (session('status'))
                <div class="p-4 font-medium text-sm text-green-700 bg-green-50 rounded-lg border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Create Post Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <h3 class="text-md font-bold text-gray-900 mb-3">{{ __('Create a Post') }}</h3>
                    <form method="POST" action="{{ route('communities.posts.store', $community) }}" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required placeholder="{{ __('Write a catchy title...') }}" />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="body" :value="__('Content')" />
                            <textarea id="body" name="body" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required placeholder="{{ __('Share your thoughts or questions with the community...') }}">{{ old('body') }}</textarea>
                            <x-input-error :messages="$errors->get('body')" class="mt-2" />
                        </div>

                        <div class="flex justify-end">
                            <x-primary-button>
                                {{ __('Publish Post') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Posts Feed -->
            <div class="space-y-4">
                @forelse ($posts as $post)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 p-6">
                        <div class="flex justify-between items-start mb-3">
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
                                        {{ $post->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center space-x-2">
                                @can('update', $post)
                                    <a href="{{ route('communities.posts.edit', [$community, $post]) }}" class="text-xs text-indigo-600 hover:text-indigo-800 underline">
                                        {{ __('Edit') }}
                                    </a>
                                @endcan
                                @can('delete', $post)
                                    <form method="POST" action="{{ route('communities.posts.destroy', [$community, $post]) }}" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:text-red-800 underline">
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                @endcan
                            </div>
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
                            <a href="{{ route('communities.posts.show', [$community, $post]) }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                {{ $post->comments_count }} {{ __('comments') }}
                            </a>
                            <a href="{{ route('communities.posts.show', [$community, $post]) }}" class="text-gray-400 hover:text-gray-600">
                                {{ __('View full discussion &rarr;') }}
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <p class="text-base font-medium text-gray-900">{{ __('No posts yet') }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ __('Be the first to start a conversation in this community!') }}</p>
                    </div>
                @endforelse

                <div class="mt-6">
                    {{ $posts->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
