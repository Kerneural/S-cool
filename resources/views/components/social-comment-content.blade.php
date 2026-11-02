@props(['community', 'comment'])
@if ($comment->parent_id)
    <p class="mb-2 border-l-2 border-brand pl-2 text-xs text-muted">{{ __('Replying to') }} {{ $comment->parent?->trashed() ? __('Unavailable comment') : ($comment->parent?->author?->name ?? __('Unavailable comment')) }}</p>
@endif
<div class="social-prose social-comment-prose text-sm">{!! \App\Support\SocialContent::html($comment->body, $comment->content_format) !!}</div>
<x-social-attachments :community="$community" :attachments="$comment->attachments" compact />
