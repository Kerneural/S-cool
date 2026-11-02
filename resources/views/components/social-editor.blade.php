@props(['id', 'value' => '', 'format' => 'plain', 'limit' => 10000, 'files' => true, 'poll' => false, 'wireBody' => null, 'wireFormat' => null, 'ajax' => false])
@php
$compact = $limit === 2000;
$options = [
    'body' => is_string($value) ? $value : '', 'format' => $format, 'allowFiles' => $files, 'ajax' => $ajax,
    'wireBody' => $wireBody, 'wireFormat' => $wireFormat, 'label' => __('Content'),
    'placeholder' => $compact ? __('Write a comment...') : '',
    'linkPrompt' => __('Enter an HTTPS link'), 'badLink' => __('Enter a valid HTTPS link.'),
    'failed' => __('Unable to publish. Your draft is retained. Try again.'),
    'empty' => __('Write some content before publishing.'),
    'filesMessage' => ['count' => __('Use at most four images and three files.'), 'type' => __('Use JPG, PNG, WebP (5 MB) or PDF, DOCX, XLSX, PPTX (10 MB).'), 'total' => __('Attachments must total at most 20 MB.')],
    'pollEnabled' => (bool) old('poll_enabled', false),
    'choices' => is_array(old('poll_options')) ? old('poll_options') : [],
];
@endphp
<div x-data="socialComposer(@js($options))" class="space-y-3 {{ $limit === 2000 ? 'social-editor--comment' : '' }}" data-social-composer
     @social-composer-discard.window="if ($event.detail?.id === @js($id)) resetDraft()" @social-comment-cleared.window="if (@js($wireBody === 'newCommentBody')) resetDraft()">
    <label for="{{ $id }}" class="{{ $compact ? 'sr-only' : 'block text-sm font-medium text-ink' }}">{{ __('Content') }}</label>
    <div class="rounded-xl border border-slate-300 bg-white focus-within:ring-2 focus-within:ring-brand">
        <div x-show="ready && (!@js($compact) || formattingOpen)" x-cloak class="flex flex-wrap items-center gap-1 border-b border-line p-1.5" role="toolbar" aria-label="{{ __('Formatting') }}">
            @foreach (['bold' => __('Bold'), 'italic' => __('Italic'), 'list' => __('List'), 'quote' => __('Quote'), 'link' => __('Link')] as $command => $label)
                <button type="button" @click="command(@js($command))" class="social-tool" aria-label="{{ $label }}" title="{{ $label }}">
                    @switch($command)
                        @case('bold') <span aria-hidden="true" class="text-base font-bold">B</span> @break
                        @case('italic') <span aria-hidden="true" class="font-serif text-base italic">I</span> @break
                        @case('list') <span aria-hidden="true" class="text-xl">☷</span> @break
                        @case('quote') <span aria-hidden="true" class="font-serif text-xl">❞</span> @break
                        @case('link') <x-social-tool-icon name="link" /> @break
                    @endswitch
                </button>
            @endforeach
        </div>
        <div class="social-editor-writing" wire:ignore>
            <div x-ref="editor" x-show="ready" x-cloak></div>
        </div>
        <textarea id="{{ $id }}" x-ref="source" name="body" x-model="body" @input="sync()" x-show="!ready" class="social-editor-source block w-full rounded-xl border-0 p-3 text-base" rows="{{ $compact ? 1 : 7 }}" placeholder="{{ $compact ? __('Write a comment...') : '' }}" maxlength="{{ $limit }}" required>{{ is_string($value) ? $value : '' }}</textarea>
        <input type="hidden" name="content_format" :value="format" value="{{ $format }}">
        <div class="social-editor-tools flex flex-wrap items-center gap-1 {{ $compact ? 'px-1' : 'border-t border-line p-2' }}">
            @if ($compact)
                <button type="button" @click="formattingOpen = !formattingOpen" :aria-expanded="formattingOpen" class="social-tool" aria-label="{{ __('Text formatting') }}" title="{{ __('Text formatting') }}"><span aria-hidden="true" class="font-serif font-bold underline">Aa</span></button>
            @endif
            @if ($files)
                <label class="inline-flex min-h-11 cursor-pointer items-center rounded-lg px-3 text-sm hover:bg-slate-100 focus-within:ring-2 focus-within:ring-brand">
                    <x-social-tool-icon name="photo" /><span class="{{ $compact ? 'sr-only' : '' }}">{{ __('Photo') }}</span><input x-ref="images" type="file" name="images[]" multiple accept=".jpg,.jpeg,.png,.webp" class="sr-only" @change="addFiles([...$event.target.files])">
                </label>
                <label class="inline-flex min-h-11 cursor-pointer items-center rounded-lg px-3 text-sm hover:bg-slate-100 focus-within:ring-2 focus-within:ring-brand">
                    <x-social-tool-icon name="attachment" /><span class="{{ $compact ? 'sr-only' : '' }}">{{ __('Attach file') }}</span><input x-ref="documents" type="file" name="documents[]" multiple accept=".pdf,.docx,.xlsx,.pptx" class="sr-only" @change="addFiles([...$event.target.files])">
                </label>
            @endif
            <div class="relative" @click.outside="emojiOpen = false" @keydown.escape.stop="emojiOpen = false">
                <button type="button" @click="emojiOpen = !emojiOpen" :aria-expanded="emojiOpen" class="social-tool" aria-label="{{ __('Emoji') }}" title="{{ __('Emoji') }}"><x-social-tool-icon name="emoji" /></button>
                <div x-show="emojiOpen" x-cloak class="social-emoji-picker absolute bottom-full left-0 z-20 grid w-64 grid-cols-6 rounded-xl border border-line bg-white p-2 shadow-lg" aria-label="{{ __('Choose an emoji') }}">
                    <template x-for="item in emojis"><button type="button" @click="emoji(item)" class="h-10 w-10 rounded-lg text-xl hover:bg-slate-100" x-text="item" :aria-label="item"></button></template>
                </div>
            </div>
            {{ $tools ?? '' }}
        </div>
    </div>
    <p @if($compact) x-show="body.length > 1800" x-cloak @endif class="text-xs text-muted"><span x-text="body.length">{{ mb_strlen(is_string($value) ? $value : '') }}</span> / {{ $limit }}</p>
    <p x-show="error" x-cloak x-text="error" class="text-sm text-rose-700" role="alert"></p>
    <x-input-error :messages="$errors->get('body')" />
    @if ($files)
        <p class="text-xs text-muted">{{ __('Up to four images (5 MB each), three files (10 MB each), 20 MB total.') }}</p>
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4" x-show="images.length" x-cloak>
            <template x-for="(entry, index) in images" :key="entry.preview">
                <div class="relative overflow-hidden rounded-lg border border-line">
                    <img :src="entry.preview" :alt="entry.name" class="aspect-square w-full object-cover">
                    <button type="button" @click="removeFile('images', index)" class="absolute right-1 top-1 flex h-10 w-10 items-center justify-center rounded-full bg-white text-rose-700 shadow" :aria-label="@js(__('Remove image')) + ': ' + entry.name">×</button>
                </div>
            </template>
        </div>
        <ul class="space-y-2" x-show="documents.length" x-cloak>
            <template x-for="(entry, index) in documents" :key="index">
                <li class="flex items-center justify-between gap-2 rounded-lg border border-line p-2 text-sm">
                    <span class="min-w-0 break-all" x-text="entry.name"></span>
                    <button type="button" @click="removeFile('documents', index)" class="min-h-11 shrink-0 px-3 text-rose-700">{{ __('Remove file') }}</button>
                </li>
            </template>
        </ul>
        @foreach (['images', 'documents'] as $field)
            @foreach ($errors->get($field.'.*') as $messages)<x-input-error :messages="$messages" />@endforeach
            <x-input-error :messages="$errors->get($field)" />
        @endforeach
    @endif
    @if ($poll)
        <label class="flex min-h-11 items-center gap-2 text-sm font-medium">
            <input type="checkbox" name="poll_enabled" value="1" x-model="pollEnabled" @checked(old('poll_enabled')) class="rounded border-slate-300 text-brand">
            {{ __('Add a poll') }}
        </label>
        <fieldset x-show="pollEnabled" x-cloak :disabled="!pollEnabled" class="space-y-2 rounded-xl border border-line bg-slate-50 p-4">
            <legend class="text-sm font-medium">{{ __('Poll choices') }}</legend>
            <p class="text-xs text-muted">{{ __('One choice per member. Votes can change until the poll closes.') }}</p>
            @for ($i = 0; $i < 2; $i++)
                <label class="block text-sm">{{ __('Choice') }} {{ $i + 1 }}
                    <input name="poll_options[]" x-model="choices[{{ $i }}]" value="{{ is_string(old('poll_options.'.$i)) ? old('poll_options.'.$i) : '' }}" maxlength="120" :required="pollEnabled" class="mt-1 min-h-11 w-full rounded-lg border-slate-300 text-sm">
                </label>
            @endfor
            <template x-for="(choice, offset) in choices.slice(2)" :key="offset">
                <div class="flex items-center gap-2">
                    <label class="min-w-0 flex-1 text-sm"><span x-text="@js(__('Choice')) + ' ' + (offset + 3)"></span>
                        <input name="poll_options[]" x-model="choices[offset + 2]" maxlength="120" :required="pollEnabled" class="mt-1 min-h-11 w-full rounded-lg border-slate-300 text-sm">
                    </label>
                    <button type="button" @click="choices.splice(offset + 2, 1)" class="min-h-11 px-3 text-sm text-rose-700">{{ __('Remove') }}</button>
                </div>
            </template>
            <button type="button" @click="choices.push('')" :disabled="choices.length >= 6" class="min-h-11 rounded-lg border border-line bg-white px-3 text-sm disabled:opacity-40">+ {{ __('Add choice') }}</button>
        </fieldset>
        <x-input-error :messages="$errors->get('poll_options')" />
    @endif
    <div x-show="uploading" x-cloak class="space-y-1" role="status">
        <p class="text-sm text-brand"><span>{{ __('Uploading') }}</span> <span x-text="progress + '%'"></span></p>
        <progress :value="progress" max="100" class="h-2 w-full accent-teal-700" aria-label="{{ __('Upload progress') }}"></progress>
    </div>
</div>
