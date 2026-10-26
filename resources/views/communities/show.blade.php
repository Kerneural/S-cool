<x-app-layout>
    @php
        $viewerIsCreator = $community->isCreator(auth()->user());
        $previewCourses = $roadmapCourses->take(3);
        $moreCourses = $roadmapCourses->slice(3);
        $hostAvatar = $community->creator?->avatar_path ? route('communities.directory.avatar', [$community, $community->creator]) : null;
    @endphp
    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="about" />

            @if (session('status'))
                <div class="p-4 font-medium text-sm text-emerald-800 bg-emerald-50 rounded-xl border border-emerald-200 shadow-xs flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            {{-- Orientation and the primary learning action come before any long content (design 11.10). --}}
            <section class="rounded-2xl border border-line bg-surface p-5 sm:p-6" aria-labelledby="community-entry-heading">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ __('About this Community') }}</p>
                        <h1 id="community-entry-heading" class="mt-1 break-words text-2xl font-bold tracking-tight text-ink">{{ $community->name }}</h1>
                        <p class="mt-1 max-w-prose text-sm text-muted">
                            {{ $viewerIsCreator
                                ? __('You host this community. Manage courses in Classroom and follow member discussion in the Feed.')
                                : __('Continue learning in Classroom or join the conversation in the Community Feed.') }}
                        </p>
                    </div>
                    <div class="flex shrink-0 flex-wrap gap-2">
                        <a href="{{ route('communities.classroom.index', $community) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-hover">{{ __('Open Classroom') }}</a>
                        <a href="{{ route('communities.posts.index', $community) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-line bg-surface px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-slate-50">{{ __('Community Feed') }}</a>
                        @can('update', $community)
                            <a href="{{ route('communities.edit', $community) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-brand-200 bg-brand-soft px-4 py-2.5 text-sm font-semibold text-brand transition hover:bg-teal-100">{{ __('Edit Settings') }}</a>
                        @endcan
                    </div>
                </div>
            </section>

            <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">
                <div class="min-w-0 space-y-6">
                    {{-- A bounded preview of the existing capped result; Classroom owns the full catalog. --}}
                    <section class="rounded-2xl border border-line bg-surface p-5 sm:p-6" aria-labelledby="about-course-preview">
                        <h2 id="about-course-preview" class="text-lg font-bold text-ink">{{ __('Courses you can access') }}</h2>
                        @if ($roadmapCourses->isEmpty())
                            <p class="mt-2 text-sm text-muted">
                                {{ $viewerIsCreator
                                    ? __('No published courses yet. Publish a course from Classroom to list it here.')
                                    : __('No published courses are available with your current access yet. Classroom shows the catalog and how to get access.') }}
                            </p>
                        @else
                            <p class="mt-1 text-sm text-muted">{{ __('Published courses you can open now. Classroom lists the complete catalog.') }}</p>
                            <ul class="mt-4 space-y-2">
                                @foreach ($previewCourses as $previewCourse)
                                    @include('communities.partials.course-preview-row', ['previewCourse' => $previewCourse])
                                @endforeach
                            </ul>
                            @if ($moreCourses->isNotEmpty())
                                <details class="rail-disclosure rail-disclosure--nested mt-2">
                                    <summary>
                                        <svg class="rail-disclosure__chevron h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                                        <span>{{ __('Show more courses (:count)', ['count' => $moreCourses->count()]) }}</span>
                                    </summary>
                                    <ul class="rail-disclosure__body space-y-2">
                                        @foreach ($moreCourses as $previewCourse)
                                            @include('communities.partials.course-preview-row', ['previewCourse' => $previewCourse])
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        @endif
                    </section>

                    <section class="rounded-2xl border border-line bg-surface p-5 sm:p-8 space-y-3" aria-labelledby="about-description">
                        <h2 id="about-description" class="text-lg font-bold text-ink">{{ __('Description') }}</h2>
                        <p class="max-w-prose whitespace-pre-line break-words text-sm leading-relaxed text-slate-700 sm:text-base">{{ $community->description ?: __('No description provided yet.') }}</p>
                    </section>

                    <!-- Introduction Video (if present) -->
                    @if ($community->intro_embed_url || $community->intro_video_url)
                        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 sm:p-8 space-y-4">
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ __('Introduction Video') }}</span>
                            </h2>
                            <div>
                                <x-secure-video-player :url="$community->intro_video_url" :title="$community->name" :community="$community" />
                            </div>
                        </div>
                    @endif

                    <!-- Community Rules & Guidelines -->
                    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 sm:p-8 space-y-4">
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>{{ __('Community Guidelines & Rules') }}</span>
                        </h2>
                        @if ($community->rules)
                            <div class="p-4 sm:p-5 bg-slate-50 rounded-xl border border-slate-100 text-slate-700 text-xs sm:text-sm whitespace-pre-line leading-relaxed max-w-prose">
                                {{ $community->rules }}
                            </div>
                        @else
                            <p class="text-xs sm:text-sm text-slate-400 italic">
                                {{ __('No community rules posted yet.') }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- One concise host/member context panel; destinations are already in the workspace tabs. --}}
                <aside class="min-w-0 rounded-2xl border border-line bg-surface p-5 space-y-4" aria-labelledby="about-context-heading">
                    <h2 id="about-context-heading" class="sr-only">{{ __('Community context') }}</h2>
                    @if ($community->creator)
                        <div class="flex items-center gap-3">
                            <x-media-frame :src="$hostAvatar" shape="avatar" :alt="$community->creator->name" class="h-12 w-12 shrink-0 border border-slate-200">
                                <span class="text-sm font-bold text-slate-700">{{ mb_strtoupper(mb_substr($community->creator->name, 0, 1)) }}</span>
                            </x-media-frame>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-muted">{{ __('Creator & Host') }}</p>
                                <p class="break-words text-sm font-bold text-ink">{{ $community->creator->name }}</p>
                            </div>
                        </div>
                    @endif
                    <dl class="space-y-2 border-t border-line pt-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-muted">{{ __('Active members') }}</dt>
                            <dd class="font-bold tabular-nums text-ink">{{ $activeMemberCount }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-muted">{{ __('Founded') }}</dt>
                            <dd class="font-medium text-ink">{{ $community->created_at->format('M Y') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-muted">{{ __('Your access') }}</dt>
                            <dd class="text-right font-medium text-ink">{{ $viewerIsCreator ? __('Creator host access') : __('Active member access') }}</dd>
                        </div>
                    </dl>
                    <p class="border-t border-line pt-3 text-xs leading-relaxed text-muted">
                        {{ __('Private learning community. Signed-in visitors can preview its introduction and course summaries before joining. Discussions, lessons and member information require the appropriate access.') }}
                    </p>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
