<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $community->name }}
                </h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $community->isActive() ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                    {{ $community->status }}
                </span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('communities.posts.index', $community) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                    {{ __('Community Feed') }}
                </a>
                <a href="{{ route('communities.events.index', $community) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">
                    {{ __('Events') }}
                </a>
                <a href="{{ route('communities.classroom.index', $community) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition ease-in-out duration-150">
                    {{ __('Classroom') }}
                </a>
                @can('update', $community)
                    <a class="underline text-sm" href="{{ route('communities.invitations.index', $community) }}">Invitations</a>
                    <a class="underline text-sm" href="{{ route('communities.members.index', $community) }}">Members</a>
                    <a href="{{ route('communities.edit', $community) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Edit Settings') }}
                    </a>
                @endcan
                <a href="{{ route('communities.index') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 border border-transparent rounded-md font-semibold text-xs text-gray-600 uppercase tracking-widest hover:bg-gray-200 transition ease-in-out duration-150">
                    {{ __('Back to List') }}
                </a>
            </div>

        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($community->cover_path)<img src="{{ route('communities.cover.show', $community) }}" alt="Community cover" class="w-full max-h-64 object-cover rounded-lg">@endif
            @if (session('status'))
                <div class="p-4 font-medium text-sm text-green-700 bg-green-50 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Community Overview Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('About this Community') }}</h3>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                {{ $community->description ?: __('No description provided yet.') }}
                            </p>

                            <div class="flex flex-wrap gap-4 text-sm text-gray-500 pt-4 border-t">
                                <div>
                                    <span class="font-medium text-gray-700">{{ __('Tenant Slug:') }}</span>
                                    <code class="ml-1 px-2 py-0.5 bg-gray-100 rounded text-xs text-indigo-600">{{ $community->slug }}</code>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">{{ __('Creator:') }}</span>
                                    <span class="ml-1">{{ $community->creator->name ?? 'Unknown' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">{{ __('Created:') }}</span>
                                    <span class="ml-1">{{ $community->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Private Workspace Info -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 flex flex-col justify-between">
                            <div>
                                <h4 class="font-semibold text-gray-800 text-sm mb-2">{{ __('Private Community Status') }}</h4>
                                <p class="text-xs text-gray-500 leading-normal mb-3">
                                    {{ __('This is an invite-only private community. Non-members cannot discover or view internal content.') }}
                                </p>
                                <div class="space-y-1.5 text-xs text-gray-600">
                                    <div class="flex items-center">
                                        <span class="h-2 w-2 rounded-full bg-green-400 mr-2"></span>
                                        <span>{{ __('Tenant Root Active') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="h-2 w-2 rounded-full bg-indigo-400 mr-2"></span>
                                        <span>{{ $community->isCreator(auth()->user()) ? __('Creator access') : __('Active member access') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
