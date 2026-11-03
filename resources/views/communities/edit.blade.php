<x-app-layout>
    <div class="py-8 sm:py-10 bg-canvas">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="settings" />

            @if (session('status'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center gap-3 shadow-xs">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="text-emerald-700 font-medium">{{ session('status') }}</p>
                </div>
            @endif

            @php
                $initialTab = request('tab', 'general');
                if ($errors->hasAny(['access_mode', 'price_vnd', 'billing_period', 'referral_commission_rate'])) {
                    $initialTab = 'pricing';
                } elseif ($errors->hasAny(['onboarding_enabled', 'introduction_category_id'])) {
                    $initialTab = 'onboarding';
                } elseif ($errors->has('cover')) {
                    $initialTab = 'cover';
                }
            @endphp

            <!-- Settings 2-Column Sidebar Layout -->
            <div x-data="{ currentTab: @js($initialTab) }" class="grid grid-cols-1 lg:grid-cols-[260px_minmax(0,1fr)] gap-6 items-start">
                <!-- Left Sidebar: Category Navigation -->
                <aside class="space-y-4">
                    <div class="bg-surface rounded-2xl border border-line shadow-xs p-4 sm:p-5 space-y-3">
                        <div class="px-1">
                            <h2 class="text-xs font-bold uppercase tracking-wider text-muted">{{ __('Settings Sections') }}</h2>
                            <p class="text-[11px] text-muted mt-0.5">{{ __('Select a category to customize.') }}</p>
                        </div>
                        <nav class="grid grid-cols-2 lg:grid-cols-1 gap-1.5" aria-label="{{ __('Settings Sections') }}">
                            <button type="button" @click="currentTab = 'general'"
                                :class="currentTab === 'general' ? 'bg-brand text-white shadow-xs font-bold' : 'text-ink hover:bg-slate-100/80 font-medium'"
                                class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs sm:text-sm transition text-left cursor-pointer min-h-[44px]">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ __('General Information') }}</span>
                            </button>

                            <button type="button" @click="currentTab = 'cover'"
                                :class="currentTab === 'cover' ? 'bg-brand text-white shadow-xs font-bold' : 'text-ink hover:bg-slate-100/80 font-medium'"
                                class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs sm:text-sm transition text-left cursor-pointer min-h-[44px]">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>{{ __('Ảnh bìa cộng đồng') }}</span>
                            </button>

                            <button type="button" @click="currentTab = 'onboarding'"
                                :class="currentTab === 'onboarding' ? 'bg-brand text-white shadow-xs font-bold' : 'text-ink hover:bg-slate-100/80 font-medium'"
                                class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs sm:text-sm transition text-left cursor-pointer min-h-[44px]">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ __('Chào mừng thành viên') }}</span>
                            </button>

                            <button type="button" @click="currentTab = 'pricing'"
                                :class="currentTab === 'pricing' ? 'bg-brand text-white shadow-xs font-bold' : 'text-ink hover:bg-slate-100/80 font-medium'"
                                class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs sm:text-sm transition text-left cursor-pointer min-h-[44px]">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ __('Access & Pricing') }}</span>
                            </button>
                        </nav>

                        <div class="pt-3 border-t border-line space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted px-1">{{ __('Quick Operations') }}</span>
                            <a href="{{ route('communities.members.index', $community) }}" class="flex items-center justify-between px-2.5 py-2 text-xs text-muted hover:text-ink hover:bg-slate-100/80 rounded-lg transition font-medium min-h-[36px]">
                                <span>{{ __('Manage Members') }}</span>
                                <span class="text-slate-400">→</span>
                            </a>
                            <a href="{{ route('communities.manage', $community) }}" class="flex items-center justify-between px-2.5 py-2 text-xs text-muted hover:text-ink hover:bg-slate-100/80 rounded-lg transition font-medium min-h-[36px]">
                                <span>{{ __('Overview') }}</span>
                                <span class="text-slate-400">→</span>
                            </a>
                        </div>
                    </div>
                </aside>

                <!-- Right Main Column: Content & Forms -->
                <div class="min-w-0 flex-1 bg-surface rounded-2xl border border-line shadow-xs overflow-hidden">
                    <div class="p-6 sm:p-8 border-b border-line bg-slate-50/50">
                        <h1 class="text-xl sm:text-2xl font-bold text-ink tracking-tight">
                            {{ __('Community settings') }}: {{ $community->name }}
                        </h1>
                        <p class="text-xs sm:text-sm text-muted mt-1">
                            {{ __('Manage core settings, visual branding, guidelines, and access pricing.') }}
                        </p>
                    </div>

                    <!-- Unified Form for Community Settings (Tabs 1, 3, 4 submit together on final tab) -->
                    <form id="community-settings-form" method="POST" action="{{ route('communities.update', $community) }}">
                        @csrf
                        @method('PUT')

                        <!-- Tab 1: General Info Form -->
                        <div x-show="currentTab === 'general'" class="p-6 sm:p-8 space-y-6">
                        <!-- Community Name -->
                        <div>
                            <x-input-label for="name" :value="__('Community Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1.5 block w-full rounded-xl border-slate-200 py-2.5 shadow-2xs focus:border-brand focus:ring-brand" :value="old('name', $community->name)" required autofocus />
                            <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
                        </div>

                        <!-- Read-only Slug -->
                        <div>
                            <x-input-label for="slug" :value="__('Slug (Read-only)')" />
                            <div class="mt-1.5 flex rounded-xl shadow-2xs border border-slate-200 bg-slate-50 overflow-hidden">
                                <span class="inline-flex items-center px-4 text-xs font-mono text-slate-500 bg-slate-100 border-r border-slate-200">/communities/</span>
                                <input id="slug" type="text" readonly disabled value="{{ $community->slug }}" class="block w-full border-0 bg-transparent py-2.5 px-3 text-xs font-mono text-slate-600 focus:ring-0">
                            </div>
                            <p class="mt-1.5 text-xs text-muted">{{ __('Community slug cannot be changed once established to preserve URL integrity.') }}</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="3" class="mt-1.5 block w-full rounded-xl border-slate-200 shadow-2xs text-sm focus:border-brand focus:ring-brand" placeholder="{{ __('What is this community about?') }}">{{ old('description', $community->description) }}</textarea>
                            <x-input-error class="mt-1.5" :messages="$errors->get('description')" />
                        </div>

                        <!-- Introduction Video URL -->
                        <div>
                            <x-input-label for="intro_video_url" :value="__('Introduction Video URL (YouTube, Vimeo or Loom)')" />
                            <x-text-input id="intro_video_url" name="intro_video_url" type="url" class="mt-1.5 block w-full rounded-xl border-slate-200 py-2.5 shadow-2xs focus:border-brand focus:ring-brand" :value="old('intro_video_url', $community->intro_video_url)" placeholder="https://www.youtube.com/watch?v=..." />
                            <x-input-error class="mt-1.5" :messages="$errors->get('intro_video_url')" />
                            <p class="mt-1.5 text-xs text-muted">{{ __('Optional video displayed on the community About page.') }}</p>
                        </div>

                        <!-- Rules -->
                        <div>
                            <x-input-label for="rules" :value="__('Community Rules (Max 5,000 characters)')" />
                            <textarea id="rules" name="rules" rows="4" maxlength="5000" class="mt-1.5 block w-full rounded-xl border-slate-200 shadow-2xs text-sm focus:border-brand focus:ring-brand" placeholder="{{ __('Guidelines and expectations for members...') }}">{{ old('rules', $community->rules) }}</textarea>
                            <x-input-error class="mt-1.5" :messages="$errors->get('rules')" />
                            <p class="mt-1.5 text-xs text-muted">{{ __('Plain-text guidelines and rules visible to all community members.') }}</p>
                        </div>

                        <!-- Footer Actions for General Tab: [Quay về] on left, [Tiếp tục] on right -->
                        <div class="flex items-center justify-between pt-6 border-t border-line">
                            <a href="{{ route('communities.show', $community) }}" class="inline-flex items-center px-4 py-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl font-bold text-xs text-slate-700 uppercase tracking-widest transition">
                                {{ __('Quay về') }}
                            </a>
                            <button type="button" @click="currentTab = 'cover'" class="inline-flex items-center gap-1.5 px-6 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-xl font-bold text-xs tracking-wider transition active:scale-[0.98]">
                                <span>{{ __('Tiếp tục') }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Tab 3: Onboarding Checklist -->
                    <div x-show="currentTab === 'onboarding'" class="p-6 sm:p-8 space-y-6">
                        <div>
                            <h2 class="text-base font-bold text-ink">{{ __('Member Onboarding Checklist') }}</h2>
                            <p class="text-xs text-muted mt-0.5">{{ __('Guide new members through introduction tasks upon admission.') }}</p>
                        </div>

                        <div class="flex items-start gap-3.5 p-4 bg-slate-50 rounded-2xl border border-line">
                            <div class="flex items-center h-5 mt-0.5">
                                <input id="onboarding_enabled" name="onboarding_enabled" type="checkbox" value="1"
                                    class="focus:ring-brand h-4 w-4 text-brand border-slate-300 rounded"
                                    {{ old('onboarding_enabled', $community->onboarding_enabled) ? 'checked' : '' }}>
                            </div>
                            <div class="text-sm">
                                <label for="onboarding_enabled" class="font-bold text-slate-800 cursor-pointer">{{ __('Enable Welcome Checklist') }}</label>
                                <p class="text-xs text-muted mt-0.5">{{ __('Display a persistent welcome checklist to new members on the community feed.') }}</p>
                            </div>
                        </div>

                        @if ($community->categories->isNotEmpty())
                            <div>
                                <x-input-label for="introduction_category_id" :value="__('Designated Introductions Category')" />
                                <select id="introduction_category_id" name="introduction_category_id" class="mt-1.5 block w-full rounded-xl border-slate-200 text-sm focus:border-brand focus:ring-brand py-2.5 shadow-2xs">
                                    <option value="">{{ __('-- Default (Category with slug "introductions") --') }}</option>
                                    @foreach ($community->categories as $category)
                                        <option value="{{ $category->id }}" {{ (int) old('introduction_category_id', $community->introduction_category_id) === $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-1.5" :messages="$errors->get('introduction_category_id')" />
                                <p class="mt-1.5 text-xs text-muted">{{ __('Members will be guided to post in this category to complete their introduction task.') }}</p>
                            </div>
                        @endif

                        <!-- Footer Actions for Onboarding Tab: [Quay về] on left, [Tiếp tục] on right -->
                        <div class="flex items-center justify-between pt-6 border-t border-line">
                            <button type="button" @click="currentTab = 'cover'" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl font-bold text-xs text-slate-700 uppercase tracking-widest transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                <span>{{ __('Quay về') }}</span>
                            </button>
                            <button type="button" @click="currentTab = 'pricing'" class="inline-flex items-center gap-1.5 px-6 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-xl font-bold text-xs tracking-wider transition active:scale-[0.98]">
                                <span>{{ __('Tiếp tục') }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Tab 4: Access & Pricing (Final tab: Hủy & Lưu thay đổi) -->
                    <div x-show="currentTab === 'pricing'" class="p-6 sm:p-8 space-y-6">
                        @include('communities.partials.access-pricing', ['community' => $community])

                        <!-- Footer Actions for Pricing Tab: [Hủy] on left, [Lưu thay đổi] on right -->
                        <div class="flex items-center justify-between pt-6 border-t border-line">
                            <a href="{{ route('communities.show', $community) }}" class="inline-flex items-center px-4 py-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl font-bold text-xs text-slate-700 uppercase tracking-widest transition">
                                {{ __('Hủy') }}
                            </a>
                            <x-primary-button class="px-6 py-2.5 rounded-xl font-bold text-xs tracking-wider">
                                {{ __('Save Changes') }}
                            </x-primary-button>
                        </div>
                    </div>
                </form>

                <!-- Tab 2: Cover Image Section (Standalone upload form) -->
                <div x-show="currentTab === 'cover'" class="p-6 sm:p-8 space-y-6">
                    <div>
                        <h2 class="text-base font-bold text-ink">{{ __('Ảnh bìa cộng đồng') }}</h2>
                        <p class="text-xs text-muted mt-0.5">{{ __('Visible on the community discovery catalog and header.') }}</p>
                    </div>

                    @if ($community->cover_path)
                        <div class="relative rounded-2xl overflow-hidden border border-line bg-slate-900/5 group shadow-2xs flex items-center justify-center p-2 sm:p-3">
                            <img src="{{ route('communities.cover.show', $community) }}" alt="{{ __('Community cover') }}" class="w-full h-auto max-h-[480px] object-contain rounded-xl">
                            <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-semibold rounded-2xl pointer-events-none">
                                {{ __('Current cover preview') }}
                            </div>
                        </div>
                    @endif

                    <form method="POST" enctype="multipart/form-data" action="{{ route('communities.cover.store', $community) }}" class="space-y-4 p-5 bg-slate-50 rounded-2xl border border-line">
                        @csrf
                        <x-input-label for="cover" :value="__('Ảnh bìa cộng đồng')" class="font-bold text-ink text-sm" />
                        <input id="cover" name="cover" type="file" accept="image/jpeg,image/png,image/webp" required
                            class="block w-full text-xs text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-soft file:text-brand hover:file:bg-teal-100 cursor-pointer">
                        <p class="text-xs text-muted">
                            <span class="font-semibold text-ink">{{ __('Khuyến nghị:') }}</span> {{ __('Tỉ lệ 16:9 (ví dụ 1200 × 675 px) để hiển thị đẹp nhất.') }} {{ __('JPEG, PNG hoặc WebP. Tối đa 2 MB và 4096 pixel mỗi cạnh.') }}
                        </p>
                        <x-input-error :messages="$errors->get('cover')" class="mt-1" />
                        <div>
                            <x-primary-button class="text-xs px-5 py-2.5 rounded-xl font-semibold">
                                {{ __('Tải ảnh bìa lên') }}
                            </x-primary-button>
                        </div>
                    </form>

                    <!-- Footer Actions for Cover Tab: [Quay về] on left, [Tiếp tục] on right -->
                    <div class="flex items-center justify-between pt-6 border-t border-line">
                        <button type="button" @click="currentTab = 'general'" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl font-bold text-xs text-slate-700 uppercase tracking-widest transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>{{ __('Quay về') }}</span>
                        </button>
                        <button type="button" @click="currentTab = 'onboarding'" class="inline-flex items-center gap-1.5 px-6 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-xl font-bold text-xs tracking-wider transition active:scale-[0.98]">
                            <span>{{ __('Tiếp tục') }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
