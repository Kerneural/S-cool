<x-app-layout>
    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="settings" />

            <div class="bg-white overflow-hidden shadow-xs rounded-2xl border border-slate-200/80">
                <div class="p-6 sm:p-8 text-slate-900">
                    <div class="border-b border-slate-100 pb-5 mb-8">
                        <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Edit Community: ') }} {{ $community->name }}</h1>
                        <p class="text-xs text-slate-500 mt-1">Manage core settings, visual branding, guidelines, and access pricing.</p>
                    </div>

                    @if (session('status'))
                        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center gap-3 shadow-xs">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <p class="text-green-700 font-medium">{{ session('status') }}</p>
                        </div>
                    @endif

                    <!-- Community Cover Image Section -->
                    <div class="mb-8 p-6 bg-slate-50/70 border border-slate-200/70 rounded-2xl space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <x-input-label for="cover" value="Private community cover" class="font-bold text-slate-900 text-sm" />
                                <p class="text-xs text-slate-500">Visible on the community discovery catalog and header.</p>
                            </div>
                        </div>

                        @if ($community->cover_path)
                            <div class="relative rounded-xl overflow-hidden border border-slate-200 max-h-52 group">
                                <img src="{{ route('communities.cover.show', $community) }}" alt="Community cover" class="w-full h-48 object-cover">
                                <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-semibold">
                                    Current cover preview
                                </div>
                            </div>
                        @endif

                        <form method="POST" enctype="multipart/form-data" action="{{ route('communities.cover.store', $community) }}" class="space-y-3 pt-2">
                            @csrf
                            <input id="cover" name="cover" type="file" accept="image/jpeg,image/png,image/webp" required
                                class="block w-full text-xs text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer">
                            <p class="text-xs text-gray-500">JPEG, PNG or WebP. Maximum 2 MB and 4096 pixels per side. Visible only to authorized community users.</p>
                            <x-input-error :messages="$errors->get('cover')" class="mt-1" />
                            <div class="pt-1">
                                <x-primary-button class="text-xs px-4 py-2 rounded-xl">
                                    Upload cover
                                </x-primary-button>
                            </div>
                        </form>
                    </div>

                    <!-- Main Settings Form -->
                    <form method="POST" action="{{ route('communities.update', $community) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Community Name -->
                        <div>
                            <x-input-label for="name" :value="__('Community Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1.5 block w-full rounded-xl border-slate-200 py-2.5" :value="old('name', $community->name)" required autofocus />
                            <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
                        </div>

                        <!-- Slug (Readonly) -->
                        <div>
                            <x-input-label for="slug" :value="__('URL Slug (Read-only)')" />
                            <div class="mt-1.5 flex rounded-xl shadow-2xs overflow-hidden border border-slate-200 bg-slate-50">
                                <span class="inline-flex items-center px-4 border-r border-slate-200 bg-slate-100/80 text-slate-500 font-mono text-xs">
                                    /communities/
                                </span>
                                <input id="slug" type="text" class="block w-full border-0 bg-transparent text-slate-500 cursor-not-allowed text-xs font-mono py-2.5 px-3" value="{{ $community->slug }}" readonly disabled />
                            </div>
                            <p class="mt-1 text-xs text-gray-400">
                                {{ __('Community slug is immutable to maintain URL and tenant integrity.') }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="4" class="mt-1.5 block w-full border-slate-200 focus:border-teal-500 focus:ring-teal-500 rounded-xl shadow-2xs text-sm py-2.5">{{ old('description', $community->description) }}</textarea>
                            <x-input-error class="mt-1.5" :messages="$errors->get('description')" />
                        </div>

                        <!-- Community Rules -->
                        <div>
                            <x-input-label for="rules" :value="__('Community Rules (max 5,000 characters)')" />
                            <textarea id="rules" name="rules" rows="6" maxlength="5000" class="mt-1.5 block w-full border-slate-200 focus:border-teal-500 focus:ring-teal-500 rounded-xl shadow-2xs text-sm py-2.5">{{ old('rules', $community->rules) }}</textarea>
                            <x-input-error class="mt-1.5" :messages="$errors->get('rules')" />
                            <p class="mt-1 text-xs text-gray-500">{{ __('Plain-text guidelines and rules visible to all community members.') }}</p>
                        </div>

                        <!-- Introduction Video URL -->
                        <div>
                            <x-input-label for="intro_video_url" :value="__('Introduction Video URL (YouTube, Vimeo or Loom)')" />
                            <x-text-input id="intro_video_url" name="intro_video_url" type="url" class="mt-1.5 block w-full rounded-xl border-slate-200 py-2.5" :value="old('intro_video_url', $community->intro_video_url)" placeholder="https://www.youtube.com/watch?v=..." />
                            <x-input-error class="mt-1.5" :messages="$errors->get('intro_video_url')" />
                            <p class="mt-1 text-xs text-gray-500">{{ __('Optional video displayed on the community About page.') }}</p>
                        </div>

                        <!-- Onboarding Checklist Settings -->
                        <div class="border-t border-slate-100 pt-6 space-y-4">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 tracking-tight">{{ __('Member Onboarding Checklist') }}</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Guide new members through introduction tasks upon admission.</p>
                            </div>

                            <div class="flex items-start gap-3 p-4 bg-slate-50 rounded-xl border border-slate-200/70">
                                <div class="flex items-center h-5 mt-0.5">
                                    <input id="onboarding_enabled" name="onboarding_enabled" type="checkbox" value="1"
                                        class="focus:ring-teal-500 h-4 w-4 text-teal-600 border-slate-300 rounded"
                                        {{ old('onboarding_enabled', $community->onboarding_enabled) ? 'checked' : '' }}>
                                </div>
                                <div class="text-sm">
                                    <label for="onboarding_enabled" class="font-bold text-slate-800">{{ __('Enable Welcome Checklist') }}</label>
                                    <p class="text-xs text-gray-500">{{ __('Display a persistent welcome checklist to new members on the community feed.') }}</p>
                                </div>
                            </div>

                            @if ($community->categories->isNotEmpty())
                                <div>
                                    <x-input-label for="introduction_category_id" :value="__('Designated Introductions Category')" />
                                    <select id="introduction_category_id" name="introduction_category_id" class="mt-1.5 block w-full rounded-xl border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500 py-2.5">
                                        <option value="">{{ __('-- Default (Category with slug "introductions") --') }}</option>
                                        @foreach ($community->categories as $category)
                                            <option value="{{ $category->id }}" {{ (int) old('introduction_category_id', $community->introduction_category_id) === $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-1.5" :messages="$errors->get('introduction_category_id')" />
                                    <p class="mt-1 text-xs text-gray-500">{{ __('Members will be guided to post in this category to complete their introduction task.') }}</p>
                                </div>
                            @endif
                        </div>

                        @include('communities.partials.access-pricing', ['community' => $community])

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <a href="{{ route('communities.show', $community) }}" class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-200 rounded-xl font-bold text-xs text-slate-700 uppercase tracking-widest shadow-2xs hover:bg-slate-50 transition duration-150">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button class="px-6 py-2.5 rounded-xl font-bold text-xs tracking-wider">
                                {{ __('Save Changes') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
