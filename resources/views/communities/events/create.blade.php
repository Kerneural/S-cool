<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="events" />

            <!-- Back link -->
            <div class="flex items-center justify-between">
                <a href="{{ route('communities.events.index', $community) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 hover:text-brand-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>{{ __('Back to Events') }}</span>
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 sm:p-8 space-y-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">
                        {{ __('Schedule Event') }}
                    </h1>
                    <p class="text-xs text-muted mt-1">
                        {{ $community->name }}
                    </p>
                </div>

                <form method="POST" action="{{ route('communities.events.store', $community) }}" class="space-y-5">
                    @csrf

                    <div>
                        <x-input-label for="title" :value="__('Event Title')" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required placeholder="{{ __('e.g., Weekly Community Q&A') }}" />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="timezone" :value="__('Event Timezone (IANA)')" />
                        <select id="timezone" name="timezone" class="mt-1 block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink p-2.5" required>
                            @foreach ($timezones as $tz)
                                <option value="{{ $tz }}" {{ old('timezone', 'Asia/Ho_Chi_Minh') === $tz ? 'selected' : '' }}>
                                    {{ $tz }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-muted mt-1">{{ __('The event will be scheduled and displayed according to this timezone.') }}</p>
                        <x-input-error :messages="$errors->get('timezone')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="starts_at" :value="__('Starts At (Local Time)')" />
                            <x-text-input id="starts_at" name="starts_at" type="datetime-local" class="mt-1 block w-full" :value="old('starts_at')" required />
                            <x-input-error :messages="$errors->get('starts_at')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="ends_at" :value="__('Ends At (Local Time)')" />
                            <x-text-input id="ends_at" name="ends_at" type="datetime-local" class="mt-1 block w-full" :value="old('ends_at')" required />
                            <x-input-error :messages="$errors->get('ends_at')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="meeting_url" :value="__('External Meeting Link (Google Meet, Zoom, etc.)')" />
                        <x-text-input id="meeting_url" name="meeting_url" type="url" class="mt-1 block w-full" :value="old('meeting_url')" placeholder="https://meet.google.com/xyz-abc-def" />
                        <p class="text-xs text-muted mt-1">{{ __('Only HTTP/HTTPS URLs are allowed. Link is hidden if the event is cancelled.') }}</p>
                        <x-input-error :messages="$errors->get('meeting_url')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Event Description')" />
                        <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink p-3.5" placeholder="{{ __('What is this event about? Any preparation needed?') }}">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('communities.events.index', $community) }}" class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-300 rounded-xl font-semibold text-xs text-slate-700 uppercase tracking-widest hover:bg-slate-50 active:scale-[0.98] transition shadow-xs">
                            {{ __('Cancel') }}
                        </a>
                        <x-primary-button>
                            {{ __('Schedule Event') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
