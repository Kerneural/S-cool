@props(['formKey', 'lesson' => null])
@php
    $retry = old('_form') === $formKey;
    $value = fn ($key, $default = '') => $retry ? (is_string(old($key)) ? old($key) : '') : $default;
@endphp
<label class="block text-sm">{{ __('Lesson Title') }}<input name="title" required maxlength="255" value="{{ $value('title', $lesson?->title) }}" class="mt-1 block w-full rounded border-gray-300"></label>
<label class="block text-sm">{{ __('Video URL (YouTube or Vimeo HTTPS only)') }}<input type="url" name="video_url" maxlength="500" value="{{ $value('video_url', $lesson?->video_url) }}" class="mt-1 block w-full rounded border-gray-300"></label>
<label class="block text-sm">{{ __('Lesson Text Content') }}<textarea name="content" maxlength="50000" rows="4" class="mt-1 block w-full rounded border-gray-300">{{ $value('content', $lesson?->content) }}</textarea></label>
<p class="text-xs text-gray-500">{{ __('Provide text or a supported video URL. No HTML iframe input.') }}</p>
<label class="block text-sm">{{ __('Status') }}
    <select name="status" class="mt-1 rounded border-gray-300">
        @foreach (['DRAFT', 'PUBLISHED'] as $status)<option value="{{ $status }}" @selected(($retry ? old('status') : ($lesson?->status ?? 'DRAFT')) === $status)>{{ $status }}</option>@endforeach
    </select>
</label>
