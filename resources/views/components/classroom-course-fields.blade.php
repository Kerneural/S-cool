@props(['formKey', 'course' => null])
@php
    $retry = old('_form') === $formKey;
    $value = fn ($key, $default = '') => $retry ? (is_string(old($key)) ? old($key) : '') : $default;
@endphp
<label class="block text-sm">{{ __('Course Title') }}<input name="title" required maxlength="255" value="{{ $value('title', $course?->title) }}" class="mt-1 block w-full rounded border-gray-300"></label>
<label class="block text-sm">{{ __('Description') }}<textarea name="description" maxlength="2000" rows="3" class="mt-1 block w-full rounded border-gray-300">{{ $value('description', $course?->description) }}</textarea></label>
<label class="block text-sm">{{ __('Status') }}
    <select name="status" class="mt-1 rounded border-gray-300">
        @foreach (['DRAFT', 'PUBLISHED'] as $status)<option value="{{ $status }}" @selected(($retry ? old('status') : ($course?->status ?? 'DRAFT')) === $status)>{{ $status }}</option>@endforeach
    </select>
</label>
