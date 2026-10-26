@props([
    'community',
])

<div class="relative">
    @php
        $compact = $variant === 'compact';
        $coverUrl = $community->cover_path ? route('communities.cover.show', $community) : null;
    @endphp
    <div @class(['space-y-3' => $compact, 'bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden' => ! $compact])>
        @if ($compact)
            {{-- Small identity image: the Feed never repeats a full-size shell cover. --}}
            <div class="flex items-center gap-3">
                <x-media-frame :src="$coverUrl" shape="thumb" :busy="false" class="h-12 w-12 shrink-0 rounded-xl border border-line">
                    <span class="text-sm font-bold text-brand">{{ mb_strtoupper(mb_substr($community->name, 0, 1)) }}</span>
                </x-media-frame>
                <div class="min-w-0">
                    <h3 class="break-words text-sm font-bold text-ink">{{ $community->name }}</h3>
                    <p class="text-xs text-muted">{{ __('Private learning community') }}</p>
                </div>
            </div>
        @else
            <x-media-frame :src="$coverUrl" alt="" class="w-full">
                <span class="w-full h-full bg-gradient-to-br from-brand-700 via-teal-800 to-slate-900 flex items-center justify-center p-4">
                    <span class="text-white/90 text-sm font-bold line-clamp-1">{{ $community->name }}</span>
                </span>
            </x-media-frame>
        @endif

        <div @class(['space-y-3' => $compact, 'p-5 space-y-4' => ! $compact])>
            @unless ($compact)
                <div>
                    <h3 class="text-lg font-bold text-slate-900 leading-snug break-words">{{ $community->name }}</h3>
                    <p class="text-xs text-muted mt-0.5">{{ __('Private learning community') }}</p>
                </div>
            @endunless

            @if ($community->description)
                <p class="text-sm text-slate-700 leading-relaxed line-clamp-3 break-words">{{ $community->description }}</p>
            @endif

            <dl class="grid grid-cols-2 text-center py-2.5 border-y border-slate-100">
                <div class="min-w-0 px-2 flex flex-col">
                    <dt class="text-xs text-slate-500 font-medium">{{ __('Members') }}</dt>
                    <dd class="order-first text-lg font-bold text-slate-900 tabular-nums">{{ $formattedMemberCount }}</dd>
                </div>
                <div class="min-w-0 px-2 flex flex-col border-l border-slate-200">
                    <dt class="text-xs text-slate-500 font-medium">{{ __('Admins') }}</dt>
                    <dd class="order-first text-lg font-bold text-slate-900 tabular-nums">{{ $adminCount }}</dd>
                </div>
            </dl>

            @if ($sampleMembers->isNotEmpty())
                {{-- Decorative sample; names stay in the member directory. --}}
                <div class="flex items-center -space-x-1.5 overflow-hidden py-1" role="img" aria-label="{{ __('Recent members') }}">
                    @foreach ($sampleMembers as $member)
                        <x-media-frame :src="$member->avatar_path ? route('communities.directory.avatar', [$community, $member]) : null" shape="avatar" loading="lazy" class="h-8 w-8 shrink-0 ring-2 ring-white" aria-hidden="true">
                            <span class="text-xs font-bold text-slate-700">{{ mb_strtoupper(mb_substr($member->name ?? '?', 0, 1)) }}</span>
                        </x-media-frame>
                    @endforeach
                </div>
            @endif

            @if ($isCreator)
                <p class="rounded-xl border border-brand-200 bg-brand-soft px-4 py-2.5 text-center text-sm font-semibold text-brand">{{ __('You host this community') }}</p>
            @elseif ($isMember)
                <p class="flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-800">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ __('Joined') }}</span>
                </p>
            @else
                <a href="{{ route('communities.about', $community) }}" class="flex min-h-11 w-full items-center justify-center rounded-xl bg-brand px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-hover">
                    {{ __('View community') }}
                </a>
            @endif

            <a href="{{ route('communities.about', $community) }}" class="inline-flex min-h-11 items-center text-sm font-semibold text-brand underline-offset-2 hover:underline">{{ __('About this community') }}</a>
        </div>
    </div>

</div>
