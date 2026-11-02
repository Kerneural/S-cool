<div class="mt-4 space-y-3 rounded-xl border border-line bg-slate-50 p-4">
@if ($snapshot)
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h3 class="text-sm font-semibold">{{ __('Poll') }}</h3>
        <span class="text-xs text-muted">{{ $snapshot['closed'] ? __('Closed') : __('One choice per member') }}</span>
    </div>
    @error('poll')<p class="text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror
    <div class="space-y-2">
        @foreach ($snapshot['options'] as $option)
            <form method="POST" action="{{ route('communities.posts.poll.vote', [$community, $post]) }}" wire:submit="vote({{ $option['id'] }})">
                @csrf<input type="hidden" name="option_id" value="{{ $option['id'] }}">
                <button type="submit" @disabled($snapshot['closed']) wire:loading.attr="disabled" aria-pressed="{{ (int) $snapshot['selected'] === $option['id'] ? 'true' : 'false' }}" class="relative min-h-12 w-full overflow-hidden rounded-lg border bg-white p-3 text-left text-sm disabled:cursor-default {{ (int) $snapshot['selected'] === $option['id'] ? 'border-brand font-semibold' : 'border-line hover:border-brand' }}">
                    <span class="absolute inset-y-0 left-0 bg-teal-100/70" style="width: {{ $option['percent'] }}%" aria-hidden="true"></span>
                    <span class="relative flex items-center justify-between gap-3"><span class="break-words">{{ (int) $snapshot['selected'] === $option['id'] ? '✓ ' : '' }}{{ $option['label'] }}</span><span class="shrink-0 text-xs">{{ $option['percent'] }}% · {{ $option['count'] }}</span></span>
                </button>
            </form>
        @endforeach
    </div>
    <p class="text-xs text-muted" aria-live="polite">{{ $snapshot['total'] }} {{ __('votes') }}{{ $snapshot['selected'] ? ' · '.__('Your vote is saved') : '' }}</p>
    @can('delete', $post)
        @unless ($snapshot['closed'])
            <form method="POST" action="{{ route('communities.posts.poll.close', [$community, $post]) }}" wire:submit="close">
                @csrf<button type="submit" wire:loading.attr="disabled" class="min-h-11 text-sm font-medium text-muted hover:text-ink">{{ __('Close poll') }}</button>
            </form>
        @endunless
    @endcan
@endif
</div>
