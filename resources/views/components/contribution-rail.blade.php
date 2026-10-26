@props(['community', 'entries', 'embedded' => false])

<section aria-labelledby="contribution-ranking-heading" @class(['space-y-3', 'bg-white p-5 rounded-2xl border border-slate-200/80' => ! $embedded])>
    <h3 id="contribution-ranking-heading" class="font-bold text-sm text-slate-900">{{ __('Community contributions') }}</h3>
    <p class="text-xs text-slate-600">{{ __('Engagement points · Lifetime') }}</p>
    <p class="text-xs text-slate-500">{{ __('Valid likes received. Ties use member ID ascending; verified work and helpful answers are separate rankings.') }}</p>
    <ol class="space-y-3">
        @forelse ($entries as $entry)
            <li class="flex items-center gap-2 min-w-0 text-sm">
                <span class="text-slate-500 tabular-nums">#{{ $entry['rank'] }}</span>
                <a href="{{ route('communities.directory.show', [$community, $entry['id']]) }}" class="min-w-0 flex-1 break-words text-brand-700 underline">{{ $entry['name'] }}</a>
                <span class="shrink-0 tabular-nums text-slate-700">{{ $entry['points'] }} {{ __('pts') }}</span>
            </li>
        @empty
            <li class="text-sm text-slate-500">{{ __('No eligible members yet.') }}</li>
        @endforelse
    </ol>
    <a href="{{ route('communities.leaderboard', [$community, 'metric' => 'engagement', 'period' => 'lifetime']) }}" class="inline-flex min-h-11 items-center text-sm text-brand-700 underline">{{ __('View all rankings') }}</a>
</section>
