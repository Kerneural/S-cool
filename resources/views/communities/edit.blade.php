<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="settings" />
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Edit Community: ') }} {{ $community->name }}</h3>
                    @if (session('status'))<p class="mb-4 text-green-700">{{ session('status') }}</p>@endif
                    <form method="POST" enctype="multipart/form-data" action="{{ route('communities.cover.store', $community) }}" class="mb-8 space-y-4 border-b pb-6">
                        @csrf
                        <x-input-label for="cover" value="Private community cover" />
                        @if ($community->cover_path)<img src="{{ route('communities.cover.show', $community) }}" alt="Community cover" class="w-full max-h-48 object-cover rounded-lg">@endif
                        <input id="cover" name="cover" type="file" accept="image/jpeg,image/png,image/webp" required class="block w-full text-sm">
                        <p class="text-xs text-gray-500">JPEG, PNG or WebP. Maximum 2 MB and 4096 pixels per side. Visible only to authorized community users.</p>
                        <x-input-error :messages="$errors->get('cover')" />
                        <x-primary-button>Upload cover</x-primary-button>
                    </form>
                    <form method="POST" action="{{ route('communities.update', $community) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Community Name -->
                        <div>
                            <x-input-label for="name" :value="__('Community Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $community->name)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <!-- Slug (Readonly) -->
                        <div>
                            <x-input-label for="slug" :value="__('URL Slug (Read-only)')" />
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    /communities/
                                </span>
                                <input id="slug" type="text" class="block w-full rounded-none rounded-r-md border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed shadow-sm text-sm" value="{{ $community->slug }}" readonly disabled />
                            </div>
                            <p class="mt-1 text-xs text-gray-400">
                                {{ __('Community slug is immutable to maintain URL and tenant integrity.') }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $community->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <!-- Community Rules -->
                        <div>
                            <x-input-label for="rules" :value="__('Community Rules (max 5,000 characters)')" />
                            <textarea id="rules" name="rules" rows="6" maxlength="5000" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('rules', $community->rules) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('rules')" />
                            <p class="mt-1 text-xs text-gray-500">{{ __('Plain-text guidelines and rules visible to all community members.') }}</p>
                        </div>

                        <!-- Introduction Video URL -->
                        <div>
                            <x-input-label for="intro_video_url" :value="__('Introduction Video URL (YouTube or Vimeo)')" />
                            <x-text-input id="intro_video_url" name="intro_video_url" type="url" class="mt-1 block w-full" :value="old('intro_video_url', $community->intro_video_url)" placeholder="https://www.youtube.com/watch?v=..." />
                            <x-input-error class="mt-2" :messages="$errors->get('intro_video_url')" />
                            <p class="mt-1 text-xs text-gray-500">{{ __('Optional video displayed on the community About page.') }}</p>
                        </div>

                        @include('communities.partials.access-pricing', ['community' => $community])

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('communities.show', $community) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Save Changes') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
