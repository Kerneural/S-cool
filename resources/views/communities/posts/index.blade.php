<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="feed" />

            @if (session('status'))
                <div class="p-4 font-medium text-sm text-green-700 bg-green-50 rounded-lg border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Category Filter and Sorting Controls -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <!-- Categories Pills -->
                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                    <span class="font-semibold text-gray-500 mr-1">{{ __('Filter:') }}</span>
                    <a href="{{ route('communities.posts.index', [$community, 'sort' => $currentSort]) }}"
                       class="px-3 py-1 rounded-full font-medium transition {{ empty($currentCategory) || $currentCategory === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ __('All') }}
                    </a>
                    @foreach ($categories as $cat)
                        <a href="{{ route('communities.posts.index', [$community, 'category' => $cat->slug, 'sort' => $currentSort]) }}"
                           class="px-3 py-1 rounded-full font-medium transition {{ $currentCategory === $cat->slug ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                    <a href="{{ route('communities.posts.index', [$community, 'category' => 'uncategorized', 'sort' => $currentSort]) }}"
                       class="px-3 py-1 rounded-full font-medium transition {{ $currentCategory === 'uncategorized' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ __('Uncategorized') }}
                    </a>
                </div>

                <!-- Sort Toggle -->
                <div class="flex items-center gap-2 text-xs">
                    <span class="font-semibold text-gray-500">{{ __('Sort:') }}</span>
                    <div class="inline-flex rounded-md shadow-sm">
                        <a href="{{ route('communities.posts.index', [$community, 'category' => $currentCategory, 'sort' => 'newest']) }}"
                           class="px-2.5 py-1 text-xs font-medium rounded-l-md border border-gray-300 {{ ($currentSort ?? 'newest') === 'newest' ? 'bg-indigo-50 text-indigo-700 font-semibold border-indigo-300' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                            {{ __('Newest') }}
                        </a>
                        <a href="{{ route('communities.posts.index', [$community, 'category' => $currentCategory, 'sort' => 'most_commented']) }}"
                           class="px-2.5 py-1 text-xs font-medium rounded-r-md border border-l-0 border-gray-300 {{ ($currentSort ?? '') === 'most_commented' ? 'bg-indigo-50 text-indigo-700 font-semibold border-indigo-300' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                            {{ __('Most Commented') }}
                        </a>
                    </div>
                </div>
            </div>

            @if (! empty($isCreator))
                <div x-data="{ openManage: false }" class="bg-white p-3 rounded-xl shadow-sm border border-gray-100 text-xs">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5 text-gray-700 font-semibold">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                            <span>{{ __('Manage Categories') }}</span>
                        </div>
                        <button type="button" @click="openManage = !openManage" class="text-indigo-600 hover:text-indigo-800 font-medium">
                            <span x-show="!openManage">{{ __('Open +') }}</span>
                            <span x-show="openManage">{{ __('Close') }}</span>
                        </button>
                    </div>

                    <div x-show="openManage" x-cloak class="mt-3 pt-3 border-t border-gray-100 space-y-3">
                        <form method="POST" action="{{ route('communities.categories.store', $community) }}" class="flex items-center gap-2">
                            @csrf
                            <input type="text" name="name" required maxlength="60" placeholder="{{ __('New category name...') }}"
                                   class="text-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 flex-1 py-1.5 px-2.5" />
                            <button type="submit" class="px-3 py-1.5 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-medium whitespace-nowrap shadow-sm">
                                {{ __('Add Category') }}
                            </button>
                        </form>

                        @if ($categories->isNotEmpty())
                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                @foreach ($categories as $cat)
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-50 border border-gray-200 rounded-lg text-gray-700">
                                        <span>{{ $cat->name }}</span>
                                        <form method="POST" action="{{ route('communities.categories.destroy', [$community, $cat]) }}"
                                              onsubmit="return confirm('{{ __('Delete this category? Posts will become uncategorized.') }}');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-gray-400 hover:text-red-600 font-bold ml-1 leading-none" title="{{ __('Delete category') }}">
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
                            <x-input-label for="category_id" :value="__('Category')" />
                            <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
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
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 p-6 {{ $post->isPinned() ? 'ring-2 ring-amber-400 bg-amber-50/20' : '' }}">
                        <div class="flex justify-between items-start mb-3">
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
                                        {{ $post->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center space-x-2">
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
                                            <button type="submit" class="text-xs text-gray-500 hover:text-gray-800 underline">
                                                {{ __('Pin') }}
                                            </button>
                                        </form>
                                    @endif
                                @endif

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
                        <p class="text-sm text-gray-500 mt-1">{{ __('No posts in this category or matching the current filter.') }}</p>
                    </div>
                @endforelse

                <div class="mt-6">
                    {{ $posts->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
