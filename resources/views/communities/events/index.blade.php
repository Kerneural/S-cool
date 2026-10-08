<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
            <div class="flex flex-wrap items-center gap-3 min-w-0">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $community->name }} — {{ __('Events Calendar') }}
                </h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $community->isActive() ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                    {{ $community->status }}
                </span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('communities.posts.index', $community) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">
                    {{ __('Community Feed') }}
                </a>
                <a href="{{ route('communities.show', $community) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">
                    {{ __('About') }}
                </a>
                @can('create', [App\Models\Event::class, $community])
                    <a href="{{ route('communities.events.create', $community) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                        + {{ __('Create Event') }}
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

            <!-- Events List -->
            <div class="space-y-4">
                @forelse ($events as $event)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border {{ $event->isCancelled() ? 'border-red-200 bg-red-50/20' : 'border-gray-100' }} p-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-start mb-3">
                            <div>
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    @if ($event->isCancelled())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800">
                                            {{ __('CANCELLED') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            {{ __('SCHEDULED') }}
                                        </span>
                                    @endif
                                    <span class="text-xs font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">
                                        {{ $event->timezone }}
                                    </span>
                                </div>
                                <a href="{{ route('communities.events.show', [$community, $event]) }}" class="group">
                                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition">
                                        {{ $event->title }}
                                    </h3>
                                </a>
                            </div>

                            <div class="text-left sm:text-right">
                                <div class="text-sm font-bold text-gray-900">
                                    {{ $event->localStartsAt()->format('M d, Y') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $event->localStartsAt()->format('H:i') }} - {{ $event->localEndsAt()->format('H:i') }} ({{ $event->timezone }})
                                </div>
                            </div>
                        </div>

                        @if ($event->description)
                            <p class="text-gray-700 text-sm whitespace-pre-line line-clamp-3 mb-4 leading-relaxed">
                                {{ $event->description }}
                            </p>
                        @endif

                        <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center pt-3 border-t border-gray-100 text-xs text-gray-500">
                            <div>
                                {{ __('Organized by:') }} <span class="font-medium text-gray-700">{{ $event->creator->name ?? __('Creator') }}</span>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <a href="{{ route('communities.events.show', [$community, $event]) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                    {{ __('View Details &rarr;') }}
                                </a>

                                @if (! $event->isCancelled() && $event->meeting_url)
                                    <a href="{{ $event->meeting_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-3 py-1 bg-green-600 text-white rounded text-xs font-medium hover:bg-green-700 transition">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                        {{ __('Join Meeting') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <p class="text-base font-medium text-gray-900">{{ __('No events scheduled yet') }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ __('Stay tuned! Community events, workshops and coaching sessions will appear here.') }}</p>
                    </div>
                @endforelse

                <div class="mt-6">
                    {{ $events->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
