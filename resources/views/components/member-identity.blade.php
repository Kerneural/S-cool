@props(['community', 'user', 'avatar' => true, 'name' => true])
@php
    $profileUrl = \App\Support\CommunityIdentity::profile($community, $user);
    $displayName = $user?->name ?? __('Unknown');
@endphp
<span {{ $attributes->class(['inline-flex min-w-0 max-w-full items-center gap-2.5 align-middle']) }}>
    @if ($profileUrl)
        <a href="{{ $profileUrl }}" class="inline-flex min-w-0 max-w-full items-center gap-2.5 rounded-lg hover:text-brand focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand" aria-label="{{ __('View profile of :name', ['name' => $displayName]) }}">
    @else
        <span class="inline-flex min-w-0 max-w-full items-center gap-2.5" title="{{ __('Profile is unavailable in this community.') }}">
    @endif
    @if ($avatar)
        <x-media-frame :src="$profileUrl && $user?->avatar_path ? route('communities.directory.avatar', [$community, $user]) : null" shape="avatar" loading="lazy" class="h-10 w-10 shrink-0 border border-line bg-brand-soft">
            <span class="text-sm font-semibold text-brand">{{ mb_strtoupper(mb_substr($displayName, 0, 1)) }}</span>
        </x-media-frame>
    @endif
    @if ($name)
        <span class="min-w-0 break-words text-sm font-semibold [overflow-wrap:anywhere]">{{ $displayName }}</span>
    @endif
    @if ($profileUrl)</a>@else</span>@endif
</span>
