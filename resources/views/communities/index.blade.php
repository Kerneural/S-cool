<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Communities') }}
            </h2>
            <a href="{{ route('communities.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Create Community') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 font-medium text-sm text-green-700 bg-green-50 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Search and Filter Bar -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <!-- Filter Tabs -->
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('communities.index', array_filter(['filter' => 'all', 'q' => $searchQuery ?? ''])) }}"
                           class="px-3 py-1.5 rounded-md text-xs font-semibold uppercase tracking-wider transition {{ ($currentFilter ?? 'all') === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ __('All') }}
                        </a>
                        <a href="{{ route('communities.index', array_filter(['filter' => 'owned', 'q' => $searchQuery ?? ''])) }}"
                           class="px-3 py-1.5 rounded-md text-xs font-semibold uppercase tracking-wider transition {{ ($currentFilter ?? '') === 'owned' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ __('Owned (Creator)') }}
                        </a>
                        <a href="{{ route('communities.index', array_filter(['filter' => 'joined', 'q' => $searchQuery ?? ''])) }}"
                           class="px-3 py-1.5 rounded-md text-xs font-semibold uppercase tracking-wider transition {{ ($currentFilter ?? '') === 'joined' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ __('Joined (Member)') }}
                        </a>
                    </div>

                    <!-- Search Form -->
                    <form method="GET" action="{{ route('communities.index') }}" class="flex items-center gap-2">
                        <input type="hidden" name="filter" value="{{ $currentFilter ?? 'all' }}">
                        <div class="relative">
                            <input type="text" name="q" value="{{ $searchQuery ?? '' }}" placeholder="{{ __('Search communities...') }}"
                                   class="text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-48 sm:w-64 py-1.5 px-3">
                        </div>
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                            {{ __('Search') }}
                        </button>
                        @if (!empty($searchQuery))
                            <a href="{{ route('communities.index', ['filter' => $currentFilter ?? 'all']) }}" class="text-xs text-gray-500 hover:text-gray-700 underline">
                                {{ __('Clear') }}
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Communities Grid -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('My Communities') }}</h3>

                    @if ($communities->isEmpty())
                        <div class="text-center py-8">
                            @if (!empty($searchQuery))
                                <p class="text-gray-500 mb-4">{{ __('No communities found matching ":query".', ['query' => $searchQuery]) }}</p>
                                <a href="{{ route('communities.index', ['filter' => $currentFilter ?? 'all']) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 transition">
                                    {{ __('Clear Search') }}
                                </a>
                            @elseif (($currentFilter ?? 'all') === 'owned')
                                <p class="text-gray-500 mb-4">{{ __('You have not created any communities yet.') }}</p>
                                <a href="{{ route('communities.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 transition">
                                    {{ __('Create a Community') }}
                                </a>
                            @elseif (($currentFilter ?? 'all') === 'joined')
                                <p class="text-gray-500 mb-4">{{ __('You have not joined any communities as a member yet.') }}</p>
                            @else
                                <p class="text-gray-500 mb-4">{{ __('You have no accessible communities yet. Create one or accept an invitation sent to your email.') }}</p>
                                <a href="{{ route('communities.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 transition">
                                    {{ __('Get Started — Create a Community') }}
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($communities as $community)
                                <div class="border rounded-lg p-5 hover:shadow-md transition">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-bold text-lg text-gray-900">
                                            @if ($community->isActive())
                                                <a href="{{ route('communities.show', $community) }}" class="hover:underline text-indigo-600">{{ $community->name }}</a>
                                            @else
                                                {{ $community->name }}
                                            @endif
                                        </h4>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $community->isActive() ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $community->status }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-400 font-mono mb-2">/communities/{{ $community->slug }}</p>
                                    <p class="text-sm text-gray-600 line-clamp-2 mb-4">
                                        {{ $community->description ?: __('No description provided.') }}
                                    </p>
                                    <div class="flex justify-between items-center text-xs text-gray-500 pt-2 border-t">
                                        <span>{{ $community->isCreator(auth()->user()) ? __('Role: Creator') : __('Role: Member') }}</span>
                                        @if ($community->isActive())
                                            <a href="{{ route('communities.show', $community) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">{{ __('Open Dashboard') }} &rarr;</a>
                                        @else
                                            <span>{{ __('Access unavailable') }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-6">{{ $communities->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
