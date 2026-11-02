@props(['community', 'attachments', 'compact' => false])
@php
$images = $attachments->where('is_image', true)->values()->map(fn ($item) => ['url' => route('communities.social-media.show', [$community, $item]), 'name' => $item->name])->all();
@endphp
@if ($attachments->isNotEmpty())
<div class="{{ $compact ? 'social-attachments--comment mt-2' : 'mt-4' }} space-y-3" x-data="{ items: @js($images), index: 0, openImage(i) { this.index = i; this.$refs.viewer.showModal() } }">
    @if ($images !== [])
        <div class="social-gallery {{ count($images) === 1 ? 'social-gallery--single' : '' }}">
            @foreach ($images as $index => $image)
                <a href="{{ $image['url'] }}" target="_blank" rel="noopener" @click.prevent="openImage({{ $index }})" class="block overflow-hidden rounded-lg bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand" aria-label="{{ __('Open image') }}: {{ $image['name'] }}">
                    <x-media-frame :src="$image['url']" :alt="$image['name']" loading="lazy" class="aspect-video w-full" />
                </a>
            @endforeach
        </div>
        <dialog x-ref="viewer" @click="if ($event.target === $el) $el.close()" @keydown.tab.stop @keydown.escape.stop @cancel.stop class="social-lightbox rounded-xl bg-white p-3 shadow-xl" aria-label="{{ __('Image viewer') }}">
            <div class="flex items-center justify-between gap-3 pb-2">
                <p class="min-w-0 break-all text-sm" x-text="items[index]?.name"></p>
                <button type="button" @click="$refs.viewer.close()" class="min-h-11 min-w-11 rounded-lg border border-line text-xl" aria-label="{{ __('Close dialog') }}">×</button>
            </div>
            <img :src="items[index]?.url" :alt="items[index]?.name" class="max-h-[70vh] max-w-full object-contain">
            <div x-show="items.length > 1" class="mt-2 flex justify-between gap-3">
                <button type="button" @click="index = (index - 1 + items.length) % items.length" class="min-h-11 rounded-lg border border-line px-4">{{ __('Previous') }}</button>
                <span class="self-center text-sm" x-text="(index + 1) + ' / ' + items.length"></span>
                <button type="button" @click="index = (index + 1) % items.length" class="min-h-11 rounded-lg border border-line px-4">{{ __('Next') }}</button>
            </div>
        </dialog>
    @endif
    @foreach ($attachments->where('is_image', false) as $file)
        <a href="{{ route('communities.social-media.show', [$community, $file]) }}" class="flex min-h-14 items-center justify-between gap-3 rounded-lg border border-line bg-slate-50 p-3 text-sm hover:border-brand">
            <span class="min-w-0 break-all font-medium">{{ $file->name }}</span>
            <span class="shrink-0 text-xs text-muted">{{ number_format($file->size / 1024) }} KB · {{ __('Download') }}</span>
        </a>
    @endforeach
</div>
@endif
