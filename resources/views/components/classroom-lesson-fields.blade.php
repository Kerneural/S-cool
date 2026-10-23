@props(['formKey', 'lesson' => null])
@php
    $retry = old('_form') === $formKey;
    $value = fn ($key, $default = '') => $retry ? (is_string(old($key)) ? old($key) : '') : $default;
    $practical = (bool) ($retry ? old('is_practical') : ($lesson?->is_practical ?? false));
@endphp
<div class="space-y-4">
    <div>
        <label for="{{ $formKey }}-title" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">{{ __('Lesson Title') }}</label>
        <input id="{{ $formKey }}-title" type="text" name="title" required maxlength="255" value="{{ $value('title', $lesson?->title) }}" placeholder="{{ __('e.g., Understanding TCP Handshake') }}" class="block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink placeholder-slate-400 p-2.5">
    </div>

    <div>
        <label for="{{ $formKey }}-video-url" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">{{ __('Video URL (YouTube, Vimeo or Loom HTTPS only)') }}</label>
        <input id="{{ $formKey }}-video-url" type="url" name="video_url" maxlength="500" value="{{ $value('video_url', $lesson?->video_url) }}" placeholder="https://www.youtube.com/watch?v=..." class="block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink placeholder-slate-400 p-2.5">
    </div>

    <div>
        <label for="{{ $formKey }}-content" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">{{ __('Lesson Text Content') }}</label>
        <textarea id="{{ $formKey }}-content" name="content" maxlength="50000" rows="5" placeholder="{{ __('Lesson guide, key takeaways, and practice exercises...') }}" class="block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink placeholder-slate-400 p-2.5">{{ $value('content', $lesson?->content) }}</textarea>
        <p class="text-xs text-muted mt-1.5">{{ __('Provide text or a supported video URL. No HTML iframe input.') }}</p>
    </div>

    <div x-data="{ isPractical: @js($practical) }" class="space-y-3 pt-2 border-t border-slate-100">
        <div class="flex items-center gap-2">
            <input type="hidden" name="is_practical" value="0">
            <input id="{{ $formKey }}-is-practical" type="checkbox" name="is_practical" value="1" @checked($practical) x-model="isPractical" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            <label for="{{ $formKey }}-is-practical" class="text-xs font-bold text-ink uppercase tracking-wider cursor-pointer">{{ __('Practical Lesson (Requires Proof of Work)') }}</label>
        </div>

        <div x-show="isPractical" class="space-y-1.5 pl-6">
            <label for="{{ $formKey }}-skill-label" class="block text-xs font-bold text-ink uppercase tracking-wider">{{ __('Demonstrated Skill Label') }} <span class="text-rose-500">*</span></label>
            <input id="{{ $formKey }}-skill-label" type="text" name="skill_label" maxlength="80" value="{{ $value('skill_label', $lesson?->skill_label) }}" placeholder="{{ __('e.g., Docker Containerization, REST API Design') }}" class="block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink placeholder-slate-400 p-2.5">
            <p class="text-xs text-muted">{{ __('Explicit nonempty skill label (max 80 chars) snapshotted upon verification.') }}</p>
        </div>
    </div>

    <div>
        <label for="{{ $formKey }}-status" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">{{ __('Status') }}</label>
        <select id="{{ $formKey }}-status" name="status" class="block w-full sm:w-48 border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink p-2.5">
            @foreach (['DRAFT', 'PUBLISHED'] as $status)
                <option value="{{ $status }}" @selected(($retry ? old('status') : ($lesson?->status ?? 'DRAFT')) === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>
</div>
