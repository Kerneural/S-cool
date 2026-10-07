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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 font-medium text-sm text-green-700 bg-green-50 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('My Created Communities') }}</h3>

                    @if ($communities->isEmpty())
                        <div class="text-center py-8">
                            <p class="text-gray-500 mb-4">{{ __('You have not created any communities yet.') }}</p>
                            <a href="{{ route('communities.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Get Started — Create a Community') }}
                            </a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($communities as $community)
                                <div class="border rounded-lg p-5 hover:shadow-md transition">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-bold text-lg text-gray-900">
                                            @can('view', $community)
                                                <a href="{{ route('communities.show', $community) }}" class="hover:underline text-indigo-600">{{ $community->name }}</a>
                                            @else
                                                {{ $community->name }}
                                            @endcan
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
                                        <span>{{ __('Role: Creator') }}</span>
                                        @can('view', $community)
                                            <a href="{{ route('communities.show', $community) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">{{ __('Open Dashboard') }} &rarr;</a>
                                        @else
                                            <span>{{ __('Access unavailable') }}</span>
                                        @endcan
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
