<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
            <div class="flex items-center space-x-3">
                <a href="{{ route('communities.events.index', $community) }}" class="text-sm text-brand-600 hover:text-brand-700 font-semibold inline-flex items-center transition">
                    &larr; {{ __('Back to Calendar') }}
                </a>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @can('update', $event)
                    @if (! $event->isCancelled())
                        <a href="{{ route('communities.events.edit', [$community, $event]) }}" class="inline-flex items-center px-3.5 py-1.5 bg-white border border-slate-300 rounded-xl font-semibold text-xs text-slate-700 uppercase tracking-wider shadow-xs hover:bg-slate-50 transition">
                            {{ __('Edit Event') }}
                        </a>
                    @endif
                @endcan

                @can('cancel', $event)
                    @if (! $event->isCancelled())
                        <form method="POST" action="{{ route('communities.events.cancel', [$community, $event]) }}" onsubmit="return confirm('Are you sure you want to cancel this event?');">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-3.5 py-1.5 bg-rose-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-wider hover:bg-rose-700 transition shadow-xs">
                                {{ __('Cancel Event') }}
                            </button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 font-medium text-sm text-emerald-800 bg-emerald-50 rounded-xl border border-emerald-200 shadow-xs flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            <!-- Event Details Card -->
            <div class="bg-white rounded-2xl shadow-xs border {{ $event->isCancelled() ? 'border-rose-200' : 'border-slate-200/80' }} p-6 sm:p-8 space-y-6">
                <!-- Status Banner -->
                @if ($event->isCancelled())
                    <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl flex items-center space-x-3">
                        <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h4 class="text-sm font-bold text-rose-800">{{ __('This event has been cancelled') }}</h4>
                            <p class="text-xs text-rose-600">{{ __('The organizer cancelled this session. Meeting links are no longer available.') }}</p>
                        </div>
                    </div>
                @endif

                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        @if ($event->isCancelled())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                {{ __('CANCELLED') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ __('SCHEDULED') }}
                            </span>
                        @endif
                        <span class="text-xs font-semibold text-brand-700 bg-brand-50 px-2.5 py-0.5 rounded-full border border-brand-200">
                            {{ __('Timezone:') }} {{ $event->timezone }}
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight {{ $event->isCancelled() ? 'line-through text-slate-400' : '' }}">
                        {{ $event->title }}
                    </h1>
                </div>

                <!-- Date & Time Box -->
                <div class="bg-slate-50 rounded-xl p-5 border border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Date & Time') }}</div>
                        <div class="text-base font-bold text-slate-900 mt-1">
                            {{ $event->localStartsAt()->format('l, M d, Y') }}
                        </div>
                        <div class="text-sm text-slate-600 mt-0.5">
                            {{ $event->localStartsAt()->format('H:i') }} &ndash; {{ $event->localEndsAt()->format('H:i') }}
                            <span class="font-medium text-brand-600">({{ $event->timezone }})</span>
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Organizer') }}</div>
                        <div class="text-base font-bold text-slate-900 mt-1">
                            {{ $event->creator->name ?? __('Creator') }}
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            {{ __('Community Creator') }}
                        </div>
                    </div>
                </div>

                <!-- Meeting Link (only when not cancelled) -->
                @if (! $event->isCancelled())
                    @if ($event->meeting_url)
                        <div class="p-4 bg-brand-50/60 border border-brand-200/80 rounded-xl flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h4 class="text-sm font-semibold text-brand-900">{{ __('Online Meeting Link') }}</h4>
                                <p class="text-xs text-brand-700 mt-0.5">{{ __('Click below to join the call when the session begins.') }}</p>
                            </div>
                            <a href="{{ $event->meeting_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold uppercase tracking-wider shadow-sm transition active:scale-[0.99]">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                {{ __('Join Call') }}
                            </a>
                        </div>
                    @else
                        <p class="text-xs text-slate-400 italic">{{ __('No online meeting link provided for this event.') }}</p>
                    @endif
                @endif

                <!-- Description -->
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('About This Event') }}</h3>
                    <div class="text-slate-700 text-sm whitespace-pre-line leading-relaxed">
                        {{ $event->description ?: __('No detailed description provided.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
