<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="settings" />
            <div x-data="communitySettings(@js([
                    'discardSettings' => __('Uploading a cover reloads this page. Discard unsaved community settings?'),
                    'discardCover' => __('The selected cover has not been uploaded. Save settings and discard that file selection?'),
                ]))" class="space-y-6">
                <header>
                    <h1 class="text-2xl font-bold text-ink">{{ __('Community settings') }}</h1>
                    <p class="mt-1 text-sm text-muted">{{ __('Update information, appearance and access for this community.') }}</p>
                </header>
                    @if (session('status'))
                        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center gap-3 shadow-xs">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <p class="text-green-700 font-medium">{{ session('status') }}</p>
                        </div>
                    @endif


                @if ($errors->any())
                    <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                        <p class="font-semibold">{{ __('Check the highlighted fields. Your entered settings are retained below.') }}</p>
                        <x-input-error :messages="$errors->all()" class="mt-2" />
                    </div>
                @endif
                <div class="grid grid-cols-1 min-w-0 items-start gap-6 lg:grid-cols-[200px_minmax(0,1fr)]">
                    <nav aria-label="{{ __('Settings sections') }}" class="min-w-0 max-w-full rounded-xl border border-line bg-surface p-3 lg:sticky lg:top-20">
                        <p class="px-3 py-2 text-xs font-semibold text-muted">{{ __('On this page') }}</p>
                        <div class="flex min-w-0 gap-1 overflow-x-auto lg:flex-col lg:overflow-visible">
                            @foreach (['community-appearance' => 'Images', 'community-information' => 'General information', 'community-guidelines' => 'Rules and introduction', 'community-welcome' => 'Member welcome', 'community-access' => 'Access and pricing'] as $anchor => $label)
                                <a href="#{{ $anchor }}" class="inline-flex min-h-11 shrink-0 items-center whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium text-ink hover:bg-brand-soft lg:whitespace-normal hover:text-brand focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand">{{ __($label) }}</a>
                            @endforeach
                        </div>
                    </nav>
                    <div class="min-w-0 space-y-6">
                        <section id="community-appearance" class="scroll-mt-24 rounded-2xl border border-line bg-surface p-5 sm:p-6">
                            <h2 class="text-lg font-bold text-ink">{{ __('Community cover') }}</h2>
                            <p id="cover-save-help" class="mt-1 text-sm text-muted">{{ __('The cover is saved separately using Upload cover. Save changes applies to the settings below.') }}</p>
                            <div class="mt-5 grid min-w-0 gap-5 sm:grid-cols-[240px_minmax(0,1fr)]">
                                @if ($community->cover_path)
                                    <x-media-frame :src="route('communities.cover.show', $community)" :alt="__('Community cover')" class="aspect-video w-full rounded-xl" />
                                @else
                                    <div class="flex aspect-video items-center justify-center rounded-xl bg-brand-soft text-sm text-brand">{{ __('No cover image yet') }}</div>
                                @endif
                        <form method="POST" enctype="multipart/form-data" action="{{ route('communities.cover.store', $community) }}" x-ref="coverForm" @change="coverDirty = Boolean($event.target.files?.length)" @submit="submitCover($event)" class="min-w-0 space-y-3">
                            @csrf
                            <label class="inline-flex min-h-11 cursor-pointer items-center rounded-lg border border-line bg-surface px-3 py-2 text-sm font-semibold text-ink hover:bg-slate-50 focus-within:ring-2 focus-within:ring-brand">
                                {{ __('Choose cover image') }}
                                <input aria-describedby="cover-save-help" id="cover" name="cover" type="file" accept="image/jpeg,image/png,image/webp" required
                                    @change="coverName = $event.target.files[0]?.name || ''" class="sr-only">
                            </label>
                            <p x-show="coverName" x-cloak x-text="coverName" class="break-all text-sm text-muted"></p>
                            <p class="text-xs text-muted">{{ __('JPEG, PNG or WebP. Maximum 2 MB and 4096 pixels per side. Visible only to authorized community users.') }}</p>
                            <x-input-error :messages="$errors->get('cover')" class="mt-1" />
                            <div class="pt-1">
                                <x-primary-button x-bind:disabled="coverSubmitting" class="min-h-11">
                                    <span x-show="!coverSubmitting">{{ __('Upload cover') }}</span><span x-show="coverSubmitting" x-cloak>{{ __('Saving…') }}</span>
                                </x-primary-button>
                            </div>
                        </form>
                            </div>
                        </section>
                        <form id="community-settings-form" x-ref="settingsForm" method="POST" action="{{ route('communities.update', $community) }}"
                              @input="settingsChanged()" @change="settingsChanged()" @submit="submitSettings($event)" class="space-y-6">
                            @csrf
                            @method('PUT')
                            <section id="community-information" class="scroll-mt-24 rounded-2xl border border-line bg-surface p-5 sm:p-6 space-y-5">
                                <h2 class="text-lg font-bold text-ink">{{ __('General information') }}</h2>
                        <!-- Community Name -->
                        <div>
                            <x-input-label for="name" :value="__('Community Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1.5 block w-full rounded-xl border-slate-200 py-2.5" :value="old('name', $community->name)" required />
                            <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
                        </div>

                        <!-- Slug (Readonly) -->
                        <div>
                            <x-input-label for="slug" :value="__('URL Slug (Read-only)')" />
                            <div class="mt-1.5 flex rounded-xl shadow-2xs overflow-hidden border border-slate-200 bg-slate-50">
                                <span class="inline-flex items-center px-4 border-r border-slate-200 bg-slate-100/80 text-slate-500 font-mono text-xs">
                                    /communities/
                                </span>
                                <input id="slug" type="text" class="block min-w-0 w-full border-0 bg-transparent text-slate-500 cursor-not-allowed text-xs font-mono py-2.5 px-3" value="{{ $community->slug }}" readonly disabled />
                            </div>
                            <p class="mt-1 text-xs text-muted">
                                {{ __('Community slug is immutable to maintain URL and tenant integrity.') }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="4" class="mt-1.5 block w-full border-slate-200 focus:border-teal-500 focus:ring-teal-500 rounded-xl shadow-2xs text-sm py-2.5">{{ old('description', $community->description) }}</textarea>
                            <x-input-error class="mt-1.5" :messages="$errors->get('description')" />
                        </div>


                            </section>
                            <section id="community-guidelines" class="scroll-mt-24 rounded-2xl border border-line bg-surface p-5 sm:p-6 space-y-5">
                                <h2 class="text-lg font-bold text-ink">{{ __('Rules and introduction') }}</h2>
                        <!-- Community Rules -->
                        <div>
                            <x-input-label for="rules" :value="__('Community Rules (max 5,000 characters)')" />
                            <textarea id="rules" name="rules" rows="6" maxlength="5000" class="mt-1.5 block w-full border-slate-200 focus:border-teal-500 focus:ring-teal-500 rounded-xl shadow-2xs text-sm py-2.5">{{ old('rules', $community->rules) }}</textarea>
                            <x-input-error class="mt-1.5" :messages="$errors->get('rules')" />
                            <p class="mt-1 text-xs text-muted">{{ __('Plain-text guidelines and rules visible to all community members.') }}</p>
                        </div>

                        <!-- Introduction Video URL -->
                        <div>
                            <x-input-label for="intro_video_url" :value="__('Introduction Video URL (YouTube, Vimeo or Loom)')" />
                            <x-text-input id="intro_video_url" name="intro_video_url" type="url" class="mt-1.5 block w-full rounded-xl border-slate-200 py-2.5" :value="old('intro_video_url', $community->intro_video_url)" placeholder="https://www.youtube.com/watch?v=..." />
                            <x-input-error class="mt-1.5" :messages="$errors->get('intro_video_url')" />
                            <p class="mt-1 text-xs text-muted">{{ __('Optional video displayed on the community About page.') }}</p>
                        </div>


                            </section>
                            <section id="community-welcome" class="scroll-mt-24 rounded-2xl border border-line bg-surface p-5 sm:p-6">
                        <!-- Onboarding Checklist Settings -->
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 tracking-tight">{{ __('Member Onboarding Checklist') }}</h3>
                                <p class="text-xs text-slate-500 mt-0.5">{{ __('Guide new members through introduction tasks upon admission.') }}</p>
                            </div>

                            <div class="flex items-start gap-3 p-4 bg-slate-50 rounded-xl border border-slate-200/70">
                                <div class="flex items-center h-5 mt-0.5">
                                    <input id="onboarding_enabled" name="onboarding_enabled" type="checkbox" value="1"
                                        class="focus:ring-teal-500 h-4 w-4 text-teal-600 border-slate-300 rounded"
                                        {{ old('onboarding_enabled', $community->onboarding_enabled) ? 'checked' : '' }}>
                                </div>
                                <div class="text-sm">
                                    <label for="onboarding_enabled" class="font-bold text-slate-800">{{ __('Enable Welcome Checklist') }}</label>
                                    <p class="text-xs text-muted">{{ __('Display a persistent welcome checklist to new members on the community feed.') }}</p>
                                </div>
                            </div>

                            @if ($community->categories->isNotEmpty())
                                <div>
                                    <x-input-label for="introduction_category_id" :value="__('Designated Introductions Category')" />
                                    <select id="introduction_category_id" name="introduction_category_id" class="mt-1.5 block w-full rounded-xl border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500 py-2.5">
                                        <option value="">{{ __('-- Default (Category with slug "introductions") --') }}</option>
                                        @foreach ($community->categories as $category)
                                            <option value="{{ $category->id }}" {{ (int) old('introduction_category_id', $community->introduction_category_id) === $category->id ? 'selected' : '' }}>
                                                {{ $category->display_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-1.5" :messages="$errors->get('introduction_category_id')" />
                                    <p class="mt-1 text-xs text-muted">{{ __('Members will be guided to post in this category to complete their introduction task.') }}</p>
                                </div>
                            @endif
                        </div>


                            </section>
                            <section id="community-access" class="scroll-mt-24 rounded-2xl border border-line bg-surface p-5 sm:p-6">
                                @include('communities.partials.access-pricing', ['community' => $community])
                            </section>
                            <div class="sticky bottom-3 z-20 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-line bg-white/95 p-4 shadow-lg backdrop-blur-sm">
                                <p role="status" class="text-sm text-muted">
                                    <span x-show="settingsDirty" x-cloak class="font-semibold text-amber-800">{{ __('Unsaved settings') }}</span>
                                    <span x-show="!settingsDirty">{{ __('Changes are applied after you save.') }}</span>
                                </p>
                                <div class="flex flex-wrap items-center gap-3">
                                    <a href="{{ route('communities.manage', $community) }}" class="inline-flex min-h-11 items-center rounded-lg border border-line px-4 py-2 text-sm font-semibold text-ink hover:bg-slate-50">{{ __('Back to overview') }}</a>
                                    <x-primary-button x-bind:disabled="settingsSubmitting" class="min-h-11">
                                        <span x-show="!settingsSubmitting">{{ __('Save Changes') }}</span>
                                        <span x-show="settingsSubmitting" x-cloak>{{ __('Saving…') }}</span>
                                    </x-primary-button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
