<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <a href="{{ route('communities.events.index', $community) }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    &larr; {{ __('Back to Calendar') }}
                </a>
            </div>
            <div class="flex items-center space-x-2">
                @can('update', $event)
                    @if (! $event->isCancelled())
                        <a href="{{ route('communities.events.edit', [$community, $event]) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">
                            {{ __('Edit Event') }}
                        </a>
                    @endif
                @endcan

                @can('cancel', $event)
                    @if (! $event->isCancelled())
                        <form method="POST" action="{{ route('communities.events.cancel', [$community, $event]) }}" onsubmit="return confirm('Are you sure you want to cancel this event?');">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                                {{ __('Cancel Event') }}
                            </button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 font-medium text-sm text-green-700 bg-green-50 rounded-lg border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Event Details Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border {{ $event->isCancelled() ? 'border-red-200' : 'border-gray-100' }} p-6 space-y-6">
                <!-- Status Banner -->
                @if ($event->isCancelled())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg flex items-center space-x-3">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                        <div>
                            <h4 class="text-sm font-bold text-red-800">{{ __('This event has been cancelled') }}</h4>
                            <p class="text-xs text-red-600">{{ __('The organizer cancelled this session. Meeting links are no longer available.') }}</p>
                        </div>
                    </div>
                @endif

                <div>
                    <div class="flex items-center space-x-2 mb-2">
                        @if ($event->isCancelled())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800">
                                {{ __('CANCELLED') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                {{ __('SCHEDULED') }}
                            </span>
                        @endif
                        <span class="text-xs font-semibold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded">
                            {{ __('Timezone:') }} {{ $event->timezone }}
                        </span>
                    </div>

                    <h1 class="text-2xl font-bold text-gray-900 {{ $event->isCancelled() ? 'line-through text-gray-500' : '' }}">
                        {{ $event->title }}
                    </h1>
                </div>

                <!-- Date & Time Box -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date & Time') }}</div>
                        <div class="text-base font-bold text-gray-900 mt-1">
                            {{ $event->localStartsAt()->format('l, M d, Y') }}
                        </div>
                        <div class="text-sm text-gray-600 mt-0.5">
                            {{ $event->localStartsAt()->format('H:i') }} &ndash; {{ $event->localEndsAt()->format('H:i') }}
                            <span class="font-medium text-indigo-600">({{ $event->timezone }})</span>
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Organizer') }}</div>
                        <div class="text-base font-bold text-gray-900 mt-1">
                            {{ $event->creator->name ?? __('Creator') }}
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5">
                            {{ __('Community Creator') }}
                        </div>
                    </div>
                </div>

                <!-- Meeting Link (only when not cancelled) -->
                @if (! $event->isCancelled())
                    @if ($event->meeting_url)
                        <div class="p-4 bg-indigo-50 border border-indigo-100 rounded-lg flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-semibold text-indigo-900">{{ __('Online Meeting Link') }}</h4>
                                <p class="text-xs text-indigo-700 mt-0.5">{{ __('Click below to join the call when the session begins.') }}</p>
                            </div>
                            <a href="{{ $event->meeting_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-xs font-bold uppercase tracking-wider shadow-sm transition">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                {{ __('Join Call') }}
                            </a>
                        </div>
                    @else
                        <p class="text-xs text-gray-400 italic">{{ __('No online meeting link provided for this event.') }}</p>
                    @endif
                @endif

                <!-- Description -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-2">{{ __('About This Event') }}</h3>
                    <div class="text-gray-700 text-sm whitespace-pre-line leading-relaxed">
                        {{ $event->description ?: __('No detailed description provided.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
