@props(['formKey', 'course' => null])
@php
    $retry = old('_form') === $formKey;
    $value = fn ($key, $default = '') => $retry ? (is_string(old($key)) ? old($key) : '') : $default;
@endphp
<label class="block text-sm">{{ __('Course Title') }}<input name="title" required maxlength="255" value="{{ $value('title', $course?->title) }}" class="mt-1 block w-full rounded border-gray-300"></label>
<label class="block text-sm">{{ __('Description') }}<textarea name="description" maxlength="2000" rows="3" class="mt-1 block w-full rounded border-gray-300">{{ $value('description', $course?->description) }}</textarea></label>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <label class="block text-sm">{{ __('Access Mode') }}
        <select name="access_mode" class="mt-1 block w-full rounded border-gray-300">
            <option value="INCLUDED" @selected(($retry ? old('access_mode') : ($course?->access_mode ?? 'INCLUDED')) === 'INCLUDED')>{{ __('Included with Community') }}</option>
            <option value="ONE_TIME" @selected(($retry ? old('access_mode') : ($course?->access_mode ?? 'INCLUDED')) === 'ONE_TIME')>{{ __('One-time Purchase') }}</option>
        </select>
    </label>
    <label class="block text-sm">{{ __('Price (VND)') }}
        <input type="number" name="price" min="1" step="1" value="{{ $value('price', $course?->price) }}" placeholder="e.g. 500000" class="mt-1 block w-full rounded border-gray-300">
        <span class="text-xs text-gray-500">{{ __('Required for One-time Purchase. Ignored if Included.') }}</span>
    </label>
</div>
<label class="block text-sm">{{ __('Status') }}
    <select name="status" class="mt-1 rounded border-gray-300">
        @foreach (['DRAFT', 'PUBLISHED'] as $status)<option value="{{ $status }}" @selected(($retry ? old('status') : ($course?->status ?? 'DRAFT')) === $status)>{{ $status }}</option>@endforeach
    </select>
</label>
