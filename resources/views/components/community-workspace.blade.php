@props([
    'community',
    'activeTab' => 'feed',
])

@php
    $user = auth()->user();
    $switcherCommunities = $user
        ? \App\Models\Community::query()
            ->accessibleTo($user)
            ->active()
            ->where('id', '!=', $community->id)
            ->orderBy('name')
            ->get()
        : collect();
@endphp

<div class="space-y-4">
    @if ($community->cover_path)
        <div class="relative w-full h-40 sm:h-52 rounded-xl overflow-hidden shadow-sm border border-gray-200">
            <img src="{{ route('communities.cover.show', $community) }}" alt="{{ $community->name }}" class="w-full h-full object-cover">
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Community Info & Switcher -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center space-x-3">
                    <h2 class="font-bold text-2xl text-gray-900 tracking-tight">
                        {{ $community->name }}
                    </h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $community->isActive() ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ $community->status }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                        {{ $community->isCreator($user) ? __('Creator') : __('Member') }}
                    </span>
                </div>

                <!-- Community Switcher Dropdown -->
                <div x-data="{ open: false }" class="relative inline-block text-left ml-2">
                    <button @click="open = !open" type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-300 rounded-md text-xs font-semibold text-gray-700 tracking-wider transition">
                        <span>{{ __('Switch Community') }}</span>
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" @click.outside="open = false" x-transition class="origin-top-left absolute left-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-50" style="display: none;">
                        <div class="py-1">
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                {{ __('Your Active Communities') }}
                            </div>
                            @if ($switcherCommunities->isEmpty())
                                <div class="px-4 py-3 text-xs text-gray-500 italic">
                                    {{ __('No other active communities') }}
                                </div>
                            @else
                                @foreach ($switcherCommunities as $other)
                                    <a href="{{ route('communities.posts.index', $other) }}" class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
                                        <span class="truncate font-medium">{{ $other->name }}</span>
                                        <span class="text-xs text-gray-400 font-mono">/{{ $other->slug }}</span>
                                    </a>
                                @endforeach
                            @endif
                        </div>
                        <div class="py-1 bg-gray-50">
                            <a href="{{ route('communities.index') }}" class="block px-4 py-2 text-xs font-medium text-gray-600 hover:text-gray-900 transition">
                                &larr; {{ __('View All Communities') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workspace Action Controls -->
            <div class="flex items-center gap-2">
                <a href="{{ route('communities.index') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 border border-transparent rounded-md text-xs font-semibold text-gray-700 uppercase tracking-widest transition">
                    {{ __('Back to List') }}
                </a>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="mt-6 border-b border-gray-200">
            <nav class="-mb-px flex flex-wrap gap-2 sm:gap-6" aria-label="Tabs">
                <a href="{{ route('communities.posts.index', $community) }}"
                   class="inline-flex items-center py-2 px-1 border-b-2 font-medium text-sm transition {{ $activeTab === 'feed' ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    {{ __('Community Feed') }}
                </a>

                <a href="{{ route('communities.classroom.index', $community) }}"
                   class="inline-flex items-center py-2 px-1 border-b-2 font-medium text-sm transition {{ $activeTab === 'classroom' ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    {{ __('Classroom') }}
                </a>

                <a href="{{ route('communities.events.index', $community) }}"
                   class="inline-flex items-center py-2 px-1 border-b-2 font-medium text-sm transition {{ $activeTab === 'events' ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    {{ __('Events') }}
                </a>

                <a href="{{ route('communities.directory.index', $community) }}"
                   class="inline-flex items-center py-2 px-1 border-b-2 font-medium text-sm transition {{ $activeTab === 'directory' ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    {{ __('Members') }}
                </a>

                <a href="{{ route('communities.show', $community) }}"
                   class="inline-flex items-center py-2 px-1 border-b-2 font-medium text-sm transition {{ $activeTab === 'about' ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    {{ __('About') }}
                </a>

                @can('update', $community)
                    <a href="{{ route('communities.invitations.index', $community) }}"
                       class="inline-flex items-center py-2 px-1 border-b-2 font-medium text-sm transition {{ $activeTab === 'invitations' ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        {{ __('Invitations') }}
                    </a>

                    <a href="{{ route('communities.members.index', $community) }}"
                       class="inline-flex items-center py-2 px-1 border-b-2 font-medium text-sm transition {{ $activeTab === 'members' ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        {{ __('Manage Members') }}
                    </a>

                    <a href="{{ route('communities.edit', $community) }}"
                       class="inline-flex items-center py-2 px-1 border-b-2 font-medium text-sm transition {{ $activeTab === 'settings' ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        {{ __('Edit Settings') }}
                    </a>
                @endcan
            </nav>
        </div>
    </div>
</div>
