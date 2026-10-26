{{-- Actions for one onboarding step; shared by the next-step summary and the full list. --}}
@if ($step['completed'])
    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">{{ __('Done') }}</span>
@elseif ($step['unavailable'])
    <span class="text-xs text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">{{ __('Unavailable') }}</span>
@elseif ($step['key'] === 'video')
    <a href="{{ $step['about_url'] }}" class="inline-flex min-h-9 items-center text-xs font-semibold text-brand hover:underline">{{ __('Watch Video') }}</a>
    <form method="POST" action="{{ route('communities.onboarding.video', $community) }}" class="inline">
        @csrf
        <button type="submit" class="min-h-9 px-3 py-1 text-xs font-semibold text-white bg-brand hover:bg-brand-hover rounded-lg transition">{{ __('Acknowledge') }}</button>
    </form>
@elseif ($step['key'] === 'comment')
    <a href="{{ $step['action_url'] }}" class="inline-flex min-h-9 items-center px-3 py-1 text-xs font-semibold text-brand bg-brand-soft hover:bg-teal-100 rounded-lg transition">{{ __('Browse Posts') }}</a>
@elseif ($step['key'] === 'post')
    <a href="{{ $step['action_url'] }}" class="inline-flex min-h-9 items-center px-3 py-1 text-xs font-semibold text-brand bg-brand-soft hover:bg-teal-100 rounded-lg transition">{{ __('Introduce Yourself') }}</a>
@endif
