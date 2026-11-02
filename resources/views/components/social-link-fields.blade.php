@props(['post' => null, 'id' => 'social'])
<details class="rounded-xl border border-line bg-slate-50 p-3">
    <summary class="min-h-11 cursor-pointer text-sm font-medium text-brand">{{ __('Share a link or video') }}</summary>
    <div class="space-y-3 pt-3">
        <div><label for="{{ $id }}-link" class="mb-1 block text-sm">{{ __('Link URL') }}</label>
            <input id="{{ $id }}-link" type="url" name="link_url" value="{{ is_string(old('link_url', $post?->link_url)) ? old('link_url', $post?->link_url) : '' }}" maxlength="2048" placeholder="https://" class="min-h-11 w-full rounded-lg border-slate-300 text-sm">
            <x-input-error :messages="$errors->get('link_url')" />
        </div>
        <div><label for="{{ $id }}-link-title" class="mb-1 block text-sm">{{ __('Link title (optional)') }}</label>
            <input id="{{ $id }}-link-title" name="link_title" value="{{ is_string(old('link_title', $post?->link_title)) ? old('link_title', $post?->link_title) : '' }}" maxlength="255" class="min-h-11 w-full rounded-lg border-slate-300 text-sm">
        </div>
        <div><label for="{{ $id }}-video" class="mb-1 block text-sm">{{ __('Video URL') }}</label>
            <input id="{{ $id }}-video" type="url" name="video_url" value="{{ is_string(old('video_url', $post?->video_url)) ? old('video_url', $post?->video_url) : '' }}" maxlength="2048" placeholder="YouTube / Vimeo / Loom" class="min-h-11 w-full rounded-lg border-slate-300 text-sm">
            <x-input-error :messages="$errors->get('video_url')" />
        </div>
    </div>
</details>
