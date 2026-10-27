@props(['formKey', 'lesson' => null])
@php
    $retry = old('_form') === $formKey;
    $value = fn ($key, $default = '') => $retry ? (is_string(old($key)) ? old($key) : '') : $default;
    $practical = (bool) ($retry ? old('is_practical') : ($lesson?->is_practical ?? false));
@endphp
<div class="space-y-4">
    <div>
        <label for="{{ $formKey }}-title" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">{{ __('Tiêu đề bài học') }}</label>
        <input id="{{ $formKey }}-title" type="text" name="title" required maxlength="255" value="{{ $value('title', $lesson?->title) }}" placeholder="{{ __('Ví dụ: Hiểu về bắt tay 3 bước TCP') }}" class="block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink placeholder-slate-400 p-2.5">
    </div>

    <div>
        <label for="{{ $formKey }}-video-url" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">{{ __('Đường dẫn video (Chỉ hỗ trợ HTTPS từ YouTube, Vimeo hoặc Loom)') }}</label>
        <input id="{{ $formKey }}-video-url" type="url" name="video_url" maxlength="500" value="{{ $value('video_url', $lesson?->video_url) }}" placeholder="https://www.youtube.com/watch?v=..." class="block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink placeholder-slate-400 p-2.5">
    </div>

    <div>
        <label for="{{ $formKey }}-content" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">{{ __('Nội dung bài học') }}</label>
        <textarea id="{{ $formKey }}-content" name="content" maxlength="50000" rows="5" placeholder="{{ __('Nội dung bài học, kiến thức cốt lõi và bài tập thực hành...') }}" class="block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink placeholder-slate-400 p-2.5">{{ $value('content', $lesson?->content) }}</textarea>
        <p class="text-xs text-muted mt-1.5">{{ __('Cung cấp nội dung văn bản hoặc đường dẫn video hợp lệ. Không hỗ trợ thẻ iframe HTML.') }}</p>
    </div>

    <div x-data="{ isPractical: @js($practical) }" class="space-y-3 pt-2 border-t border-slate-100">
        <div class="flex items-center gap-2">
            <input type="hidden" name="is_practical" value="0">
            <input id="{{ $formKey }}-is-practical" type="checkbox" name="is_practical" value="1" @checked($practical) x-model="isPractical" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            <label for="{{ $formKey }}-is-practical" class="text-xs font-bold text-ink uppercase tracking-wider cursor-pointer">{{ __('Bài học kỹ năng thực hành (Yêu cầu minh chứng)') }}</label>
        </div>

        <div x-show="isPractical" class="space-y-1.5 pl-6">
            <label for="{{ $formKey }}-skill-label" class="block text-xs font-bold text-ink uppercase tracking-wider">{{ __('Tên kỹ năng thực hành') }} <span class="text-rose-500">*</span></label>
            <input id="{{ $formKey }}-skill-label" type="text" name="skill_label" maxlength="80" value="{{ $value('skill_label', $lesson?->skill_label) }}" placeholder="{{ __('Ví dụ: Đóng gói Docker, Thiết kế REST API') }}" class="block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink placeholder-slate-400 p-2.5">
            <p class="text-xs text-muted">{{ __('Tên kỹ năng cụ thể không để trống (tối đa 80 ký tự) được ghi nhận khi xác thực.') }}</p>
        </div>
    </div>

    <div>
        <label for="{{ $formKey }}-status" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">{{ __('Trạng thái') }}</label>
        <select id="{{ $formKey }}-status" name="status" class="block w-full sm:w-48 border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink p-2.5">
            @foreach (['DRAFT' => 'Bản nháp', 'PUBLISHED' => 'Đã xuất bản'] as $status => $statusLabel)
                <option value="{{ $status }}" @selected(($retry ? old('status') : ($lesson?->status ?? 'DRAFT')) === $status)>{{ __($statusLabel) }}</option>
            @endforeach
        </select>
    </div>
</div>
