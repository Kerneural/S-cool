@props(['community', 'categories', 'currentCategory' => null, 'currentSort' => 'newest', 'currentCourseId' => null])
@php
    $filterUrl = fn ($slug) => route('communities.posts.index', [$community, 'category' => $slug, 'sort' => $currentSort, 'course_id' => $currentCourseId]);
    $chip = 'inline-flex min-h-10 max-w-full items-center rounded-full border px-3 py-2 text-sm font-medium break-words';
@endphp
<nav class="space-y-3" aria-label="{{ __('Filter discussions') }}">
    <span class="sr-only">{{ __('Filter:') }}</span>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ $filterUrl(null) }}" @if (!$currentCategory || $currentCategory === 'all') aria-current="page" @endif class="{{ $chip }} {{ !$currentCategory || $currentCategory === 'all' ? 'border-brand bg-brand text-white' : 'border-line bg-white text-muted hover:border-brand' }}">{{ __('All') }}</a>
        @foreach ($categories->take(5) as $cat)
            <a href="{{ $filterUrl($cat->slug) }}" @if ($currentCategory === $cat->slug) aria-current="page" @endif class="{{ $chip }} {{ $currentCategory === $cat->slug ? 'border-brand bg-brand text-white' : 'border-line bg-white text-muted hover:border-brand' }}">{{ $cat->display_name }}</a>
        @endforeach
    </div>
    @if ($categories->count() > 5)
        <details class="feed-more-categories" @if($categories->skip(5)->contains('slug', $currentCategory)) open @endif>
            <summary class="inline-flex min-h-10 cursor-pointer items-center rounded-lg px-2 text-sm font-medium text-brand"><span class="when-closed">{{ __('More categories') }} ({{ $categories->count() - 5 }})</span><span class="when-open">{{ __('Fewer categories') }}</span></summary>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($categories->skip(5) as $cat)
                    <a href="{{ $filterUrl($cat->slug) }}" @if ($currentCategory === $cat->slug) aria-current="page" @endif class="{{ $chip }} {{ $currentCategory === $cat->slug ? 'border-brand bg-brand text-white' : 'border-line bg-white text-muted hover:border-brand' }}">{{ $cat->display_name }}</a>
                @endforeach
            </div>
        </details>
    @endif
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line pb-3">
        <div class="flex flex-wrap gap-2 text-sm">
            <a href="{{ route('communities.saved.index', $community) }}" class="inline-flex min-h-11 items-center rounded-lg px-2 font-medium text-muted hover:text-brand">{{ __('Saved Posts') }}</a>
            <a href="{{ $filterUrl('uncategorized') }}" class="inline-flex min-h-11 items-center rounded-lg px-2 {{ $currentCategory === 'uncategorized' ? 'font-semibold text-brand' : 'text-muted' }}">{{ __('Uncategorized') }}</a>
        </div>
        <form method="GET" class="flex items-center gap-2 text-sm">
            @if ($currentCategory)<input type="hidden" name="category" value="{{ $currentCategory }}">@endif
            @if ($currentCourseId)<input type="hidden" name="course_id" value="{{ $currentCourseId }}">@endif
            <label for="feed-sort" class="text-muted">{{ __('Sort:') }}</label>
            <select id="feed-sort" name="sort" class="min-h-11 max-w-full rounded-lg border-line text-sm" onchange="this.form.requestSubmit()">
                <option value="newest" @selected($currentSort === 'newest')>{{ __('Newest') }}</option>
                <option value="most_commented" @selected($currentSort === 'most_commented')>{{ __('Most Commented') }}</option>
            </select>
            <noscript><button type="submit">{{ __('Apply') }}</button></noscript>
        </form>
    </div>
</nav>
