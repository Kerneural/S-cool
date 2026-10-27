<x-app-layout>
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:py-8 space-y-6">
        <x-classroom-feedback />

        {{-- Top Navigation & Header Bar --}}
        <div class="rounded-xl border border-line bg-surface p-5 sm:p-6 shadow-2xs">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0 space-y-2">
                    <nav aria-label="{{ __('Breadcrumb') }}" class="flex items-center gap-2 text-xs font-medium text-muted">
                        <a href="{{ route('communities.classroom.index', $community) }}" class="hover:text-brand transition-colors">
                            {{ __('Lớp Học') }}
                        </a>
                        <span aria-hidden="true">&rsaquo;</span>
                        <a href="{{ route('communities.courses.show', [$community, $course]) }}" class="hover:text-brand transition-colors">
                            {{ $course->title }}
                        </a>
                        <span aria-hidden="true">&rsaquo;</span>
                        <span class="text-ink font-semibold">{{ __('Quản lý khóa học') }}</span>
                    </nav>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-ink break-words">
                            {{ $course->title }}
                        </h1>
                        @if ($course->isPublished())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ __('Đã xuất bản') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                {{ __('Bản nháp') }}
                            </span>
                        @endif
                        @if ($course->isOneTime())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                {{ __('Mua một lần') }} — {{ $course->formattedPrice() }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ __('Bao gồm trong cộng đồng') }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    <a href="{{ route('communities.manage.courses.show', [$community, $course, 'tab' => 'settings']) }}"
                       class="inline-flex min-h-10 items-center justify-center rounded-lg border border-line px-3.5 py-2 text-xs font-semibold text-ink shadow-2xs hover:bg-slate-50 transition-colors cursor-pointer {{ $selectedMode === 'settings' ? 'bg-brand-soft border-brand text-brand' : 'bg-surface' }}">
                        <svg class="w-4 h-4 mr-1.5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>{{ __('Cài đặt khóa học') }}</span>
                    </a>
                    <a href="{{ route('communities.courses.show', [$community, $course]) }}"
                       class="inline-flex min-h-10 items-center justify-center rounded-lg border border-line bg-surface px-3.5 py-2 text-xs font-semibold text-ink shadow-2xs hover:bg-slate-50 transition-colors cursor-pointer">
                        <svg class="w-4 h-4 mr-1.5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <span>{{ __('Xem khóa học') }}</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Two-Column Workspace Layout --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">

            {{-- Left Column: Curriculum Tree (4 cols on desktop) --}}
            <aside aria-label="{{ __('Chương trình học') }}" class="md:col-span-5 lg:col-span-4 space-y-4">
                <div class="rounded-xl border border-line bg-surface p-4 sm:p-5 shadow-2xs">
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-line">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-ink">
                            {{ __('Chương trình học') }}
                        </h2>
                        <a href="{{ route('communities.manage.courses.show', [$community, $course, 'action' => 'create-section']) }}"
                           class="inline-flex items-center gap-1 text-xs font-semibold text-brand hover:text-brand-hover bg-brand-soft hover:bg-teal-100 px-2.5 py-1.5 rounded-lg transition-colors cursor-pointer"
                           title="{{ __('Thêm chương mới') }}">
                            <span aria-hidden="true">+</span>
                            <span>{{ __('Thêm chương') }}</span>
                        </a>
                    </div>

                    @if ($course->sections->isEmpty())
                        <div class="py-8 text-center text-xs text-muted">
                            <p>{{ __('Chưa có chương nào trong khóa học này.') }}</p>
                            <a href="{{ route('communities.manage.courses.show', [$community, $course, 'action' => 'create-section']) }}"
                               class="mt-2 inline-block text-brand font-semibold hover:underline">
                                {{ __('+ Bắt đầu thêm chương đầu tiên') }}
                            </a>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($course->sections as $sectionIndex => $section)
                                @php
                                    $isSectionSelected = $selectedMode === 'section' && $selectedSection?->id === $section->id;
                                    $isAddingLessonHere = $selectedMode === 'create-lesson' && $selectedSection?->id === $section->id;
                                @endphp
                                <div class="rounded-lg border {{ $isSectionSelected ? 'border-brand bg-brand-soft/30' : 'border-line/80 bg-slate-50/50' }} p-2.5 space-y-2 transition-colors">
                                    {{-- Section Header --}}
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                            <span class="text-xs font-bold text-muted shrink-0">{{ $sectionIndex + 1 }}.</span>
                                            <a href="{{ route('communities.manage.courses.show', [$community, $course, 'section' => $section->id]) }}"
                                               class="text-xs sm:text-sm font-semibold truncate hover:text-brand transition-colors {{ $isSectionSelected ? 'text-brand font-bold' : 'text-ink' }}"
                                               title="{{ $section->title }}">
                                                {{ $section->title }}
                                            </a>
                                        </div>
                                        <div class="flex items-center gap-1 shrink-0">
                                            <x-classroom-reorder :ids="$course->sections->modelKeys()" :id="$section->id" :action="route('communities.courses.sections.reorder', [$community, $course])" :up-label="__('Di chuyển lên')" :down-label="__('Di chuyển xuống')" :compact="true" />
                                            <a href="{{ route('communities.manage.courses.show', [$community, $course, 'create_lesson' => $section->id]) }}"
                                               class="p-1 rounded text-muted hover:text-brand hover:bg-white transition-colors"
                                               title="{{ __('Thêm bài học vào chương này') }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>

                                    {{-- Lessons in Section --}}
                                    @if ($section->lessons->isEmpty())
                                        <div class="pl-4 pr-1 py-1.5 text-xs text-muted/70 italic flex items-center justify-between">
                                            <span>{{ __('Chưa có bài học') }}</span>
                                            <a href="{{ route('communities.manage.courses.show', [$community, $course, 'create_lesson' => $section->id]) }}" class="text-brand font-medium hover:underline not-italic">
                                                + {{ __('Thêm') }}
                                            </a>
                                        </div>
                                    @else
                                        <ul class="space-y-1 pl-2" role="list">
                                            @foreach ($section->lessons as $lessonIndex => $lesson)
                                                @php
                                                    $isLessonSelected = $selectedMode === 'lesson' && $selectedLesson?->id === $lesson->id;
                                                @endphp
                                                <li class="flex items-center justify-between gap-1.5 rounded-md px-2 py-1.5 text-xs transition-colors {{ $isLessonSelected ? 'bg-brand text-white font-semibold shadow-xs' : 'bg-surface hover:bg-slate-100/80 text-ink' }}">
                                                    <a href="{{ route('communities.manage.courses.show', [$community, $course, 'lesson' => $lesson->id]) }}"
                                                       class="flex items-center gap-1.5 min-w-0 flex-1 truncate {{ $isLessonSelected ? 'text-white' : 'text-ink hover:text-brand' }}">
                                                        <span class="text-xs opacity-75 shrink-0">{{ $sectionIndex + 1 }}.{{ $lessonIndex + 1 }}</span>
                                                        <span class="truncate">{{ $lesson->title }}</span>
                                                    </a>
                                                    <div class="flex items-center gap-1 shrink-0">
                                                        @if ($lesson->isPublished())
                                                            <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-semibold {{ $isLessonSelected ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-700' }}">
                                                                {{ __('Đã xuất bản') }}
                                                            </span>
                                                        @else
                                                            <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-semibold {{ $isLessonSelected ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-800' }}">
                                                                {{ __('Bản nháp') }}
                                                            </span>
                                                        @endif
                                                        <x-classroom-reorder :ids="$section->lessons->modelKeys()" :id="$lesson->id" :action="route('communities.lessons.reorder', [$community, $course, $section])" :up-label="__('Di chuyển lên')" :down-label="__('Di chuyển xuống')" :compact="true" />
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    @if ($isAddingLessonHere)
                                        <div class="pl-2 pt-1">
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-brand bg-white px-2 py-0.5 rounded border border-brand/40">
                                                &rarr; {{ __('Đang thêm bài học mới...') }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </aside>

            {{-- Right Column: Active Editor Workspace (8 cols on desktop) --}}
            <main aria-label="{{ __('Khung soạn thảo') }}" class="md:col-span-7 lg:col-span-8">
                <div class="rounded-xl border border-line bg-surface p-5 sm:p-6 shadow-2xs">

                    {{-- Mode 1: Edit Lesson --}}
                    @if ($selectedMode === 'lesson' && $selectedLesson)
                        @php
                            $formKey = 'edit-lesson-'.$selectedLesson->id;
                        @endphp
                        <div class="space-y-5">
                            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-line">
                                <div>
                                    <h2 class="text-lg sm:text-xl font-bold text-ink">
                                        {{ __('Chỉnh sửa bài học') }}: {{ $selectedLesson->title }}
                                    </h2>
                                </div>
                                <span class="text-xs text-muted">
                                    {{ __('ID') }}: #{{ $selectedLesson->id }}
                                </span>
                            </div>

                            <form method="POST" action="{{ route('communities.lessons.update', [$community, $course, $selectedLesson->section, $selectedLesson]) }}?lesson={{ $selectedLesson->id }}" class="space-y-4">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="_form" value="{{ $formKey }}">

                                <x-classroom-lesson-fields :form-key="$formKey" :lesson="$selectedLesson" />

                                <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-line">
                                    <p class="text-xs text-muted">
                                        {{ __('Hủy xuất bản để ẩn nội dung; không hỗ trợ xóa vĩnh viễn.') }}
                                    </p>
                                    <x-primary-button>
                                        {{ __('Lưu bài học') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>

                    {{-- Mode 2: Create Lesson --}}
                    @elseif ($selectedMode === 'create-lesson' && $selectedSection)
                        @php
                            $formKey = 'create-lesson-'.$selectedSection->id;
                        @endphp
                        <div class="space-y-5">
                            <div class="pb-4 border-b border-line">
                                <span class="text-xs font-bold text-brand uppercase tracking-wider block">
                                    {{ __('Thuộc chương') }}: {{ $selectedSection->title }}
                                </span>
                                <h2 class="text-lg sm:text-xl font-bold text-ink">
                                    {{ __('Thêm bài học mới') }}
                                </h2>
                            </div>

                            <form method="POST" action="{{ route('communities.lessons.store', [$community, $course, $selectedSection]) }}?create_lesson={{ $selectedSection->id }}" class="space-y-4">
                                @csrf
                                <input type="hidden" name="_form" value="{{ $formKey }}">

                                <x-classroom-lesson-fields :form-key="$formKey" />

                                <div class="flex justify-end pt-4 border-t border-line">
                                    <x-primary-button>
                                        {{ __('Tạo bài học') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>

                    {{-- Mode 3: Edit Section --}}
                    @elseif ($selectedMode === 'section' && $selectedSection)
                        @php
                            $formKey = 'edit-section-'.$selectedSection->id;
                            $retry = old('_form') === $formKey;
                            $sectionTitle = $retry ? (is_string(old('title')) ? old('title') : '') : $selectedSection->title;
                        @endphp
                        <div class="space-y-5">
                            <div class="pb-4 border-b border-line">
                                <span class="text-xs font-bold text-muted uppercase tracking-wider block">
                                    {{ __('Chương') }}
                                </span>
                                <h2 class="text-lg sm:text-xl font-bold text-ink">
                                    {{ __('Chỉnh sửa chương') }}: {{ $selectedSection->title }}
                                </h2>
                            </div>

                            <form method="POST" action="{{ route('communities.courses.sections.update', [$community, $course, $selectedSection]) }}?section={{ $selectedSection->id }}" class="space-y-4">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="_form" value="{{ $formKey }}">

                                <div>
                                    <label for="{{ $formKey }}-title" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">{{ __('Tiêu đề chương') }}</label>
                                    <input id="{{ $formKey }}-title" type="text" name="title" required maxlength="255" value="{{ $sectionTitle }}" placeholder="{{ __('e.g., Module 1: Foundations') }}" class="block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink placeholder-slate-400 p-2.5">
                                    <x-input-error :messages="$errors->get('title')" class="mt-1" />
                                </div>

                                <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-line">
                                    <a href="{{ route('communities.manage.courses.show', [$community, $course, 'create_lesson' => $selectedSection->id]) }}"
                                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand hover:underline">
                                        <span>+ {{ __('Thêm bài học vào chương này') }}</span>
                                    </a>
                                    <x-primary-button>
                                        {{ __('Lưu chương') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>

                    {{-- Mode 4: Create Section --}}
                    @elseif ($selectedMode === 'create-section')
                        @php
                            $formKey = 'create-section';
                            $retry = old('_form') === $formKey;
                            $newSectionTitle = $retry && is_string(old('title')) ? old('title') : '';
                        @endphp
                        <div class="space-y-5">
                            <div class="pb-4 border-b border-line">
                                <span class="text-xs font-bold text-brand uppercase tracking-wider block">
                                    {{ __('Chương mới') }}
                                </span>
                                <h2 class="text-lg sm:text-xl font-bold text-ink">
                                    {{ __('Thêm chương mới') }}
                                </h2>
                            </div>

                            <form method="POST" action="{{ route('communities.courses.sections.store', [$community, $course]) }}?action=create-section" class="space-y-4">
                                @csrf
                                <input type="hidden" name="_form" value="{{ $formKey }}">

                                <div>
                                    <label for="{{ $formKey }}-title" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">{{ __('Tiêu đề chương') }}</label>
                                    <input id="{{ $formKey }}-title" type="text" name="title" required maxlength="255" value="{{ $newSectionTitle }}" placeholder="{{ __('e.g., Module 1: Foundations') }}" class="block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink placeholder-slate-400 p-2.5">
                                    <x-input-error :messages="$errors->get('title')" class="mt-1" />
                                </div>

                                <div class="flex justify-end pt-4 border-t border-line">
                                    <x-primary-button>
                                        {{ __('Tạo chương') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>

                    {{-- Mode 5: Settings and Cover --}}
                    @elseif ($selectedMode === 'settings')
                        <div class="space-y-8">
                            {{-- Course Settings Form --}}
                            <div class="space-y-4">
                                <div class="pb-3 border-b border-line">
                                    <h2 class="text-lg sm:text-xl font-bold text-ink">
                                        {{ __('Cài đặt khóa học') }}
                                    </h2>
                                    <p class="text-xs text-muted mt-0.5">
                                        {{ __('Tiêu đề, mô tả, quyền truy cập và xuất bản khóa học.') }}
                                    </p>
                                </div>

                                <form method="POST" action="{{ route('communities.courses.update', [$community, $course]) }}?tab=settings" class="space-y-4">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="_form" value="edit-course">

                                    <x-classroom-course-fields form-key="edit-course" :course="$course" />

                                    <div class="flex justify-end pt-2">
                                        <x-primary-button>
                                            {{ __('Lưu khóa học') }}
                                        </x-primary-button>
                                    </div>
                                </form>
                            </div>

                            {{-- Course Cover Management --}}
                            <div class="space-y-4 pt-6 border-t border-line">
                                <div class="pb-2 border-b border-line/60">
                                    <h3 class="text-base font-bold text-ink">
                                        {{ __('Ảnh bìa khóa học') }}
                                    </h3>
                                    <p class="text-xs text-muted mt-0.5">
                                        {{ __('Ảnh bìa hiển thị trên trang danh sách lớp học và trang chi tiết khóa học.') }}
                                    </p>
                                </div>

                                @if ($course->cover_path)
                                    <div class="space-y-2">
                                        <span class="text-xs font-semibold text-muted block">{{ __('Ảnh bìa hiện tại:') }}</span>
                                        <div class="aspect-video max-w-sm overflow-hidden rounded-lg border border-line bg-slate-900">
                                            <img src="{{ route('communities.courses.cover', [$community, $course]) }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                                        </div>
                                        <form method="POST" action="{{ route('communities.courses.cover.destroy', [$community, $course]) }}" x-data="{ confirmMsg: @js(__('Xóa ảnh bìa này?')) }" @submit="if (! confirm(confirmMsg)) $event.preventDefault()">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-700 underline cursor-pointer">
                                                {{ __('Xóa ảnh bìa') }}
                                            </button>
                                        </form>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('communities.courses.cover.store', [$community, $course]) }}" enctype="multipart/form-data" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label for="workspace-course-cover-input" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">{{ __('Tải lên ảnh bìa mới') }}</label>
                                        <input id="workspace-course-cover-input" type="file" name="cover" accept=".jpg,.jpeg,.png,.webp" required class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-soft file:text-brand hover:file:bg-teal-100 cursor-pointer">
                                        <span class="block text-xs text-muted mt-1">{{ __('JPEG, PNG, hoặc WebP. Tối đa 5 MiB, tối đa 4096px.') }}</span>
                                        <x-input-error :messages="$errors->get('cover')" class="mt-1" />
                                    </div>
                                    <div class="flex justify-end">
                                        <x-primary-button>
                                            {{ __('Lưu ảnh bìa') }}
                                        </x-primary-button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    {{-- Fallback: Nothing selected --}}
                    @else
                        <div class="py-12 text-center text-muted space-y-3">
                            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <h3 class="text-sm font-semibold text-ink">{{ __('Chọn một bài học hoặc chương để chỉnh sửa') }}</h3>
                            <p class="text-xs text-muted max-w-sm mx-auto">
                                {{ __('Sử dụng cây danh mục bên trái để duyệt nội dung khóa học, hoặc bấm thêm chương/bài học mới.') }}
                            </p>
                        </div>
                    @endif

                </div>
            </main>

        </div>
    </div>
</x-app-layout>
