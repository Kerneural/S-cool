@props(['formKey', 'course' => null])
@php
    $retry = old('_form') === $formKey;
    $value = fn ($key, $default = '') => $retry ? (is_string(old($key)) ? old($key) : '') : $default;
@endphp
<div class="space-y-4">
    <div>
        <label for="{{ $formKey }}-title" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">{{ __('Course Title') }}</label>
        <input id="{{ $formKey }}-title" type="text" name="title" required maxlength="255" value="{{ $value('title', $course?->title) }}" placeholder="{{ __('e.g., Network Fundamentals') }}" class="block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink placeholder-slate-400 p-2.5">
    </div>

    <div>
        <label for="{{ $formKey }}-description" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">{{ __('Description') }}</label>
        <textarea id="{{ $formKey }}-description" name="description" maxlength="2000" rows="3" placeholder="{{ __('Course overview and learning goals...') }}" class="block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink placeholder-slate-400 p-2.5">{{ $value('description', $course?->description) }}</textarea>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="{{ $formKey }}-access-mode" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">{{ __('Access Mode') }}</label>
            <select id="{{ $formKey }}-access-mode" name="access_mode" class="block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink p-2.5">
                <option value="INCLUDED" @selected(($retry ? old('access_mode') : ($course?->access_mode ?? 'INCLUDED')) === 'INCLUDED')>{{ __('Included with Community') }}</option>
                <option value="ONE_TIME" @selected(($retry ? old('access_mode') : ($course?->access_mode ?? 'INCLUDED')) === 'ONE_TIME')>{{ __('One-time Purchase') }}</option>
            </select>
        </div>

        <div>
            <label for="{{ $formKey }}-price" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">{{ __('Price (VND)') }}</label>
            <input id="{{ $formKey }}-price" type="number" name="price" min="1" step="1" value="{{ $value('price', $course?->price) }}" placeholder="e.g. 500000" class="block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink placeholder-slate-400 p-2.5">
            <span class="block text-xs text-muted mt-1">{{ __('Required for One-time Purchase. Ignored if Included.') }}</span>
        </div>
    </div>

    <div>
        <label for="{{ $formKey }}-status" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">{{ __('Status') }}</label>
        <select id="{{ $formKey }}-status" name="status" class="block w-full sm:w-48 border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink p-2.5">
            @foreach (['DRAFT', 'PUBLISHED'] as $status)
                <option value="{{ $status }}" @selected(($retry ? old('status') : ($course?->status ?? 'DRAFT')) === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>
</div>
