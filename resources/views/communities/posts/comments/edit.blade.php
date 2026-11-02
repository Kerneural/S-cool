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

                <form method="POST" enctype="multipart/form-data" action="{{ route('communities.posts.comments.update', [$community, $post, $comment]) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <x-social-editor id="edit-comment-body" :value="old('body', $comment->body)" :format="old('content_format', $comment->content_format)" :limit="2000" />

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('communities.posts.show', [$community, $post]) }}" class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-300 rounded-xl font-semibold text-xs text-slate-700 uppercase tracking-widest hover:bg-slate-50 active:scale-[0.98] transition shadow-xs">
                            {{ __('Cancel') }}
                        </a>
                        <x-primary-button>
                            {{ __('Save comment') }}
                        </x-primary-button>
                    </div>
                </form>
                @if ($comment->attachments->isNotEmpty())
                    <section class="mt-5 space-y-2 border-t border-line pt-4">
                        <h2 class="text-sm font-semibold">{{ __('Current attachments') }}</h2>
                        @foreach ($comment->attachments as $file)
                            <div class="flex items-center justify-between gap-3 rounded-lg border border-line p-3 text-sm">
                                <a href="{{ route('communities.social-media.show', [$community, $file]) }}" class="min-w-0 break-all text-brand">{{ $file->name }}</a>
                                <form method="POST" action="{{ route('communities.social-media.destroy', [$community, $file]) }}" onsubmit="return confirm(@js(__('Remove this attachment?')))">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="min-h-11 shrink-0 px-3 text-rose-700">{{ __('Remove file') }}</button>
                                </form>
                            </div>
                        @endforeach
                    </section>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
