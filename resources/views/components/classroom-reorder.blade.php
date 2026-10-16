@props(['ids', 'id', 'action'])
@php
    $ids = array_values($ids);
    $position = array_search($id, $ids);
@endphp
<div class="flex flex-wrap gap-2">
    @foreach ([-1 => 'Move up', 1 => 'Move down'] as $offset => $label)
        @if ($position !== false && isset($ids[$position + $offset]))
            @php
                $ordered = $ids;
                [$ordered[$position], $ordered[$position + $offset]] = [$ordered[$position + $offset], $ordered[$position]];
            @endphp
            <form method="POST" action="{{ $action }}">
                @csrf
                @foreach ($ordered as $siblingId)
                    <input type="hidden" name="order[]" value="{{ $siblingId }}">
                @endforeach
                <button class="rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 active:scale-[0.98] transition shadow-xs" type="submit">{{ __($label) }}</button>
            </form>
        @endif
    @endforeach
</div>
