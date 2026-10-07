<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Community Event') }} — {{ $community->name }}
            </h2>
            <a href="{{ route('communities.events.index', $community) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 border border-transparent rounded-md font-semibold text-xs text-gray-600 uppercase tracking-widest hover:bg-gray-200 transition">
                {{ __('Cancel') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 p-6">
                <form method="POST" action="{{ route('communities.events.store', $community) }}" class="space-y-5">
                    @csrf

                    <div>
                        <x-input-label for="title" :value="__('Event Title')" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required placeholder="{{ __('e.g., Weekly Community Q&A') }}" />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="timezone" :value="__('Event Timezone (IANA)')" />
                        <select id="timezone" name="timezone" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                            @foreach ($timezones as $tz)
                                <option value="{{ $tz }}" {{ old('timezone', 'Asia/Ho_Chi_Minh') === $tz ? 'selected' : '' }}>
                                    {{ $tz }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">{{ __('The event will be scheduled and displayed according to this timezone.') }}</p>
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
                        <p class="text-xs text-gray-500 mt-1">{{ __('Only HTTP/HTTPS URLs are allowed. Link is hidden if the event is cancelled.') }}</p>
                        <x-input-error :messages="$errors->get('meeting_url')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Event Description')" />
                        <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" placeholder="{{ __('What is this event about? Any preparation needed?') }}">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <a href="{{ route('communities.events.index', $community) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">
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
