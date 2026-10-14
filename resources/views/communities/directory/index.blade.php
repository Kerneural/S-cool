<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="directory" />

            <!-- Directory Toolbar -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-lg text-gray-900">{{ __('Community Members') }}</h3>
                        <p class="text-xs text-gray-500">{{ __('Discover fellow members and creators in this community.') }}</p>
                    </div>

                    <!-- Search Members -->
                    <form method="GET" action="{{ route('communities.directory.index', $community) }}" class="flex items-center gap-2">
                        <input type="text" name="q" value="{{ $searchQuery ?? '' }}" placeholder="{{ __('Search members by name...') }}"
                               class="text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-56 sm:w-64 py-1.5 px-3">
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-gray-800 hover:bg-gray-700 border border-transparent rounded-md text-xs font-semibold text-white uppercase tracking-widest transition">
                            {{ __('Search') }}
                        </button>
                        @if (!empty($searchQuery))
                            <a href="{{ route('communities.directory.index', $community) }}" class="text-xs text-gray-500 hover:text-gray-700 underline">
                                {{ __('Clear') }}
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Members Grid -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                @if ($members->isEmpty())
                    <div class="text-center py-10">
                        @if (!empty($searchQuery))
                            <p class="text-gray-500 text-sm mb-3">{{ __('No community members match ":query".', ['query' => $searchQuery]) }}</p>
                            <a href="{{ route('communities.directory.index', $community) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md text-xs font-semibold text-gray-700 uppercase tracking-wider transition">
                                {{ __('Clear Search') }}
                            </a>
                        @else
                            <p class="text-gray-500 text-sm">{{ __('No active members found in this community.') }}</p>
                        @endif
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                        @foreach ($members as $member)
                            @php
                                $isCreator = $member->id === $community->creator_id;
                            @endphp
                            <div class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition bg-white flex flex-col justify-between">
                                <div class="flex items-start space-x-4">
                                    <img src="{{ route('communities.directory.avatar', [$community, $member]) }}"
                                         alt="{{ $member->name }}"
                                         class="w-14 h-14 rounded-full object-cover border border-gray-200 shadow-xs shrink-0 bg-gray-50">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <a href="{{ route('communities.directory.show', [$community, $member]) }}" class="font-bold text-gray-900 hover:text-indigo-600 hover:underline truncate">
                                                {{ $member->name }}
                                            </a>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $isCreator ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-700' }}">
                                                {{ $isCreator ? __('Creator') : __('Member') }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-600 mt-2 line-clamp-2">
                                            {{ $member->bio ?: __('No bio provided yet.') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                                    <span>{{ __('Community Member') }}</span>
                                    <a href="{{ route('communities.directory.show', [$community, $member]) }}" class="font-medium text-indigo-600 hover:text-indigo-800">
                                        {{ __('View Profile') }} &rarr;
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $members->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
