<li>
    <a href="{{ route('communities.courses.show', [$community, $previewCourse]) }}" class="flex min-h-11 items-center gap-3 rounded-xl border border-line bg-slate-50 px-3 py-2 text-sm font-semibold text-ink transition hover:border-brand/40 hover:bg-brand-soft hover:text-brand">
        <svg class="h-4 w-4 shrink-0 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
        <span class="min-w-0 flex-1 break-words">{{ $previewCourse->title }}</span>
        <span aria-hidden="true" class="text-muted">&rarr;</span>
    </a>
</li>
