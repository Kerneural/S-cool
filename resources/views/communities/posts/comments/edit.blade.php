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
                        {{ __('Edit comment') }}
                    </h1>
                    <p class="text-xs text-muted mt-1">
                        {{ __('Editing reply on:') }} {{ $post->title }}
                    </p>
                </div>

                <form method="POST" action="{{ route('communities.posts.comments.update', [$community, $post, $comment]) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="body" :value="__('Comment')" />
                        <textarea id="body" name="body" rows="6" maxlength="2000" required class="block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink p-3.5">{{ old('body', $comment->body) }}</textarea>
                        <x-input-error :messages="$errors->get('body')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('communities.posts.show', [$community, $post]) }}" class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-300 rounded-xl font-semibold text-xs text-slate-700 uppercase tracking-widest hover:bg-slate-50 active:scale-[0.98] transition shadow-xs">
                            {{ __('Cancel') }}
                        </a>
                        <x-primary-button>
                            {{ __('Save comment') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
