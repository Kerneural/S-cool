@props([
    'formKey',
    'lesson' => null,
    'course' => null,
    'community' => null,
    'previewUrl' => null,
])
@php
    $retry = old('_form') === $formKey;
    $value = fn ($key, $default = '') => $retry ? (is_string(old($key)) ? old($key) : '') : $default;
    $practical = (bool) ($retry ? old('is_practical') : ($lesson?->is_practical ?? false));

    $resolvedPreviewUrl = $previewUrl;
    if (! $resolvedPreviewUrl) {
        $resolvedCourse = $course
            ?? $lesson?->section?->course
            ?? $lesson?->courseSection?->course
            ?? request()->route('course')
            ?? null;

        $resolvedCommunity = $community
            ?? $resolvedCourse?->community
            ?? request()->route('community')
            ?? null;

        if ($resolvedCommunity && $resolvedCourse) {
            $resolvedPreviewUrl = route('communities.courses.lessons.preview', [$resolvedCommunity, $resolvedCourse]);
        }
    }
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

    <div
        x-data="lessonAuthoring({
            previewUrl: @js($resolvedPreviewUrl),
            uploadUrl: @js($resolvedPreviewUrl ? preg_replace('~/lessons/preview$~', '/lesson-images', $resolvedPreviewUrl) : ''),
            csrfToken: @js(csrf_token()),
            initialTab: 'edit',
            saveLabel: @js($retry ? 'Lưu chưa thành công — nội dung được giữ lại' : ($lesson ? 'Nội dung đã lưu' : 'Bài học mới chưa lưu')),
            emptyPlaceholder: @js(__('Chưa có nội dung bài học để xem trước.')),
            loadingMessage: @js(__('Đang tải bản xem trước...')),
            errorMessage: @js(__('Không thể tải bản xem trước. Vui lòng thử lại.'))
        })"
        class="space-y-1.5 lesson-authoring"
        :class="expanded ? 'lesson-authoring--expanded' : ''"
    >
        <div class="flex items-center justify-between gap-2">
            <label for="{{ $formKey }}-content" class="block text-xs font-bold text-ink uppercase tracking-wider">{{ __('Nội dung bài học') }}</label>
            <div class="inline-flex rounded-lg border border-slate-200 bg-slate-50 p-0.5 text-xs font-medium" role="tablist">
                <button
                    type="button"
                    role="tab"
                    :aria-selected="tab === 'edit'"
                    @click="setTab('edit')"
                    class="px-2.5 py-1 rounded-md transition-colors"
                    :class="tab === 'edit' ? 'bg-white text-brand-600 font-semibold shadow-xs' : 'text-slate-600 hover:text-ink'"
                >
                    {{ __('Soạn thảo') }}
                </button>
                <button
                    type="button"
                    role="tab"
                    :aria-selected="tab === 'preview'"
                    @click="setTab('preview')"
                    class="px-2.5 py-1 rounded-md transition-colors flex items-center gap-1.5"
                    :class="tab === 'preview' ? 'bg-white text-brand-600 font-semibold shadow-xs' : 'text-slate-600 hover:text-ink'"
                >
                    <span>{{ __('Xem trước') }}</span>
                    <span x-show="loading" class="inline-block animate-spin w-3 h-3 border-2 border-brand-500 border-t-transparent rounded-full" aria-hidden="true"></span>
                </button>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 py-2 text-sm">
            <div class="flex gap-2" x-show="tab === 'edit'">
                <button type="button" @click="chooseMode('visual')" :disabled="!visualReady" :aria-pressed="mode === 'visual'" class="rounded-lg border border-line px-3 py-2">Trực quan</button>
                <button type="button" @click="chooseMode('source')" :aria-pressed="mode === 'source'" class="rounded-lg border border-line px-3 py-2">Markdown</button>
            </div>
            <button type="button" @click="expanded = !expanded" :aria-expanded="expanded" class="rounded-lg border border-line px-3 py-2" x-text="expanded ? 'Thu gọn khung viết' : 'Mở rộng khung viết'">Mở rộng khung viết</button>
            <button type="submit" x-show="expanded" :disabled="uploading" @click="expanded = false" class="rounded-lg bg-brand px-4 py-2 font-semibold text-white disabled:opacity-50">Lưu bài học</button>
            <span role="status" class="text-muted" x-text="submitting ? 'Đang lưu...' : (dirty ? 'Có thay đổi chưa lưu' : saveLabel)"></span>
        </div>
        <div class="border border-slate-300 rounded-xl overflow-clip focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500 shadow-xs bg-white">
            <div x-show="tab === 'edit'" class="flex flex-wrap items-center gap-1 p-2 bg-slate-50 lesson-editor-toolbar border-b border-slate-200 text-slate-700">
                <button
                    type="button"
                    @click="applyFormat('h2')"
                    title="{{ __('Tiêu đề (H2)') }}"
                    aria-label="{{ __('Tiêu đề (H2)') }}"
                    class="p-1 rounded-md text-slate-600 hover:text-brand-600 hover:bg-slate-200/60 font-bold text-xs leading-none flex items-center justify-center min-w-[36px] h-9"
                >
                    H2
                </button>
                <button
                    type="button"
                    @click="applyFormat('bold')"
                    title="{{ __('In đậm') }}"
                    aria-label="{{ __('In đậm') }}"
                    class="p-1 rounded-md text-slate-600 hover:text-brand-600 hover:bg-slate-200/60 font-black text-xs leading-none flex items-center justify-center min-w-[36px] h-9"
                >
                    B
                </button>
                <button
                    type="button"
                    @click="applyFormat('italic')"
                    title="{{ __('In nghiêng') }}"
                    aria-label="{{ __('In nghiêng') }}"
                    class="p-1 rounded-md text-slate-600 hover:text-brand-600 hover:bg-slate-200/60 italic font-serif text-xs leading-none flex items-center justify-center min-w-[36px] h-9"
                >
                    I
                </button>
                <span class="w-px h-4 bg-slate-200 mx-0.5" aria-hidden="true"></span>
                <button
                    type="button"
                    @click="applyFormat('bullet')"
                    title="{{ __('Danh sách chấm') }}"
                    aria-label="{{ __('Danh sách chấm') }}"
                    class="p-1 rounded-md text-slate-600 hover:text-brand-600 hover:bg-slate-200/60 flex items-center justify-center min-w-[36px] h-9"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16M2 6h.01M2 12h.01M2 18h.01"/></svg>
                </button>
                <button
                    type="button"
                    @click="applyFormat('number')"
                    title="{{ __('Danh sách số') }}"
                    aria-label="{{ __('Danh sách số') }}"
                    class="p-1 rounded-md text-slate-600 hover:text-brand-600 hover:bg-slate-200/60 flex items-center justify-center min-w-[36px] h-9"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 6h13M7 12h13M7 18h13M3 5v3m0 0h1.5M3 12h2m-2 4h2v2H3"/></svg>
                </button>
                <button
                    type="button"
                    @click="applyFormat('quote')"
                    title="{{ __('Trích dẫn') }}"
                    aria-label="{{ __('Trích dẫn') }}"
                    class="p-1 rounded-md text-slate-600 hover:text-brand-600 hover:bg-slate-200/60 flex items-center justify-center min-w-[36px] h-9"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </button>
                <button
                    type="button"
                    @click="applyFormat('link')"
                    title="{{ __('Liên kết') }}"
                    aria-label="{{ __('Liên kết') }}"
                    class="p-1 rounded-md text-slate-600 hover:text-brand-600 hover:bg-slate-200/60 flex items-center justify-center min-w-[36px] h-9"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                </button>
                <button type="button" @click="$refs.imageInput.click()" :disabled="uploading" class="rounded-lg border border-line px-3 py-2 text-sm" aria-label="Chèn ảnh">Ảnh</button>
                <button type="button" @click="applyFormat('code')" x-show="visualReady && mode === 'visual'" class="rounded-lg px-3 py-2 text-sm">Mã</button>
                <button type="button" @click="applyFormat('table')" x-show="visualReady && mode === 'visual'" class="rounded-lg px-3 py-2 text-sm">Bảng</button>
                <button type="button" @click="applyFormat('undo')" x-show="visualReady && mode === 'visual'" class="rounded-lg px-3 py-2 text-sm" aria-label="Hoàn tác">↶</button>
                <button type="button" @click="applyFormat('redo')" x-show="visualReady && mode === 'visual'" class="rounded-lg px-3 py-2 text-sm" aria-label="Làm lại">↷</button>
                <input type="file" x-ref="imageInput" accept="image/jpeg,image/png,image/webp" class="hidden" @change="uploadImage($event.target.files[0])">
            </div>
            <div x-show="uploading" role="status" class="p-3 text-sm text-brand">Đang tải ảnh: <span x-text="uploadProgress"></span>%</div>
            <p x-show="uploadError" x-text="uploadError" role="alert" class="p-3 text-sm text-rose-700"></p>
            <div x-show="tab === 'edit' && visualReady && mode === 'visual'" x-ref="rich" class="lesson-rich-editor"></div>
            <div x-show="tab === 'edit' && (!visualReady || mode === 'source')">
                <textarea
                    id="{{ $formKey }}-content"
                    x-ref="textarea"
                    name="content"
                    maxlength="50000"
                    rows="18"
                    placeholder="{{ __('Nội dung bài học, kiến thức cốt lõi và bài tập thực hành...') }}"
                    class="block w-full border-0 focus:ring-0 text-base text-ink placeholder-slate-400 p-5 bg-white resize-y min-h-[420px] leading-relaxed"
                >{{ $value('content', $lesson?->content) }}</textarea>
            </div>

            <div
                x-show="tab === 'preview'"
                x-cloak
                class="w-full min-h-[420px] overflow-y-auto p-4 bg-white"
            >
                <div x-show="loading" class="flex items-center justify-center py-8 text-sm text-slate-500 gap-2">
                    <span class="inline-block animate-spin w-4 h-4 border-2 border-brand-500 border-t-transparent rounded-full" aria-hidden="true"></span>
                    <span>{{ __('Đang tải bản xem trước...') }}</span>
                </div>

                <div x-show="errorMessage && !loading" class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-700">
                    <span x-text="errorMessage"></span>
                </div>

                <article
                    x-show="!loading && !errorMessage"
                    class="lesson-prose"
                    x-html="previewHtml"
                ></article>
            </div>
        </div>

        <p class="text-xs text-slate-500 mt-1.5 flex items-start gap-1.5">
            <svg class="w-3.5 h-3.5 text-brand-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ __('Để đính kèm tài liệu tham khảo hoặc liên kết bài học khác, hãy chèn cú pháp: [Tên tài nguyên](URL).') }}</span>
        </p>
        <p class="text-xs text-muted mt-1">{{ __('Cung cấp nội dung văn bản hoặc đường dẫn video hợp lệ. Không hỗ trợ thẻ iframe HTML.') }}</p>
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
