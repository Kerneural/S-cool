@props(['ids', 'id', 'action', 'upLabel' => null, 'downLabel' => null, 'compact' => false])
@php
    $ids = array_values($ids);
    $position = array_search($id, $ids);
    $labels = [
        -1 => $upLabel ?? 'Move up',
        1 => $downLabel ?? 'Move down',
    ];
@endphp
<div class="flex items-center gap-1">
    @foreach ($labels as $offset => $label)
        @if ($position !== false && isset($ids[$position + $offset]))
            @php
                $ordered = $ids;
                [$ordered[$position], $ordered[$position + $offset]] = [$ordered[$position + $offset], $ordered[$position]];
            @endphp
            <form method="POST" action="{{ $action }}" class="inline">
                @csrf
                @foreach ($ordered as $siblingId)
                    <input type="hidden" name="order[]" value="{{ $siblingId }}">
                @endforeach
                @if ($compact)
                    <button class="inline-flex items-center justify-center w-6 h-6 rounded border border-line bg-surface text-slate-500 hover:text-brand hover:border-brand/40 hover:bg-slate-50 active:scale-95 transition shadow-2xs cursor-pointer"
                            type="submit"
                            title="{{ __($label) }}"
                            aria-label="{{ __($label) }}">
                        @if ($offset === -1)
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                            </svg>
                        @else
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        @endif
                    </button>
                @else
                    <button class="rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 active:scale-[0.98] transition shadow-xs" type="submit">{{ __($label) }}</button>
                @endif
            </form>
        @endif
    @endforeach
</div>
