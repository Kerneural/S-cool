@props(['community', 'post', 'preview' => false])
<div class="social-prose {{ $preview ? 'line-clamp-5' : '' }}">{!! \App\Support\SocialContent::html($post->body, $post->content_format) !!}</div>
<x-social-attachments :community="$community" :attachments="$post->attachments" />
@if (\App\Support\SocialContent::safeUrl($post->link_url))
    <a href="{{ $post->link_url }}" target="_blank" rel="nofollow noopener noreferrer" class="mt-4 block rounded-xl border border-line bg-slate-50 p-4 hover:border-brand" data-social-link-card x-data="{ title: @js($post->link_title ?: $post->link_url), description: '', init() { const observer = new IntersectionObserver(async entries => { if (!entries.some(e => e.isIntersecting)) return; observer.disconnect(); try { const r = await fetch(@js(route('communities.posts.link-preview', [$community, $post])), { headers: { Accept: 'application/json' } }); if (r.ok) { const data = await r.json(); if (!@js((bool) $post->link_title) &amp;&amp; data.title) this.title = data.title; this.description = data.description || ''; } } catch {} }); observer.observe(this.$el); this.stop = () => observer.disconnect(); }, destroy() { this.stop?.() } }">
        <p class="text-xs font-medium text-muted">{{ parse_url($post->link_url, PHP_URL_HOST) }}</p>
        <p class="mt-1 break-words text-sm font-semibold text-brand" x-text="title">{{ $post->link_title ?: $post->link_url }}</p>
        <p x-show="description" x-text="description" class="mt-1 text-sm text-muted"></p>
        <p class="mt-1 break-all text-xs text-muted">{{ __('Open link') }} ↗</p>
    </a>
@endif
@if ($video = \App\Services\VideoEmbedService::parse($post->video_url))
    @if ($preview)
        <a href="{{ route('communities.posts.show', [$community, $post]) }}" class="mt-4 flex min-h-14 items-center gap-3 rounded-lg border border-line bg-brand-soft px-4 text-sm font-medium text-brand">{{ __('Watch video') }} · {{ ucfirst($video['provider']) }} →</a>
    @else
        <div class="mt-4 aspect-video overflow-hidden rounded-xl bg-slate-100">
            <iframe src="{{ $video['embed_url'] }}" title="{{ __('Shared video') }}" loading="lazy" allowfullscreen referrerpolicy="strict-origin-when-cross-origin" class="h-full w-full border-0"></iframe>
        </div>
    @endif
@endif
@if ($post->poll)
    @livewire('post-poll', ['community' => $community, 'post' => $post], key('social-poll-'.$post->id.($preview ? '-feed' : '-full')))
@endif
