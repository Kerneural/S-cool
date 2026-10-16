<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="feed" />

            <!-- Back link -->
            <div class="flex items-center justify-between">
                <a href="{{ route('communities.posts.show', [$community, $post]) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 hover:text-brand-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>{{ __('Back to Discussion') }}</span>
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 sm:p-8 space-y-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">
                        {{ __('Edit Post') }}
                    </h1>
                    <p class="text-xs text-muted mt-1">
                        {{ $community->name }}
                    </p>
                </div>

                <form method="POST" action="{{ route('communities.posts.update', [$community, $post]) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="title" :value="__('Title')" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $post->title)" required />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="category_id" :value="__('Category')" />
                        <select id="category_id" name="category_id" class="mt-1 block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink p-2.5">
                            <option value="">{{ __('-- None / General --') }}</option>
                            @foreach ($categories as $cat)
                                @if (! $cat->isAnnouncements() || $community->isCreator(auth()->user()))
                                    <option value="{{ $cat->id }}" {{ old('category_id', $post->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}{{ $cat->isAnnouncements() ? ' (Creator Only)' : '' }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="body" :value="__('Content')" />
                        <textarea id="body" name="body" rows="8" class="mt-1 block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink p-3.5" required>{{ old('body', $post->body) }}</textarea>
                        <x-input-error :messages="$errors->get('body')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('communities.posts.show', [$community, $post]) }}" class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-300 rounded-xl font-semibold text-xs text-slate-700 uppercase tracking-widest hover:bg-slate-50 active:scale-[0.98] transition shadow-xs">
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
</x-app-layout>
