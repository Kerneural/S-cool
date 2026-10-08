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
                <button class="rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 hover:bg-gray-100" type="submit">{{ __($label) }}</button>
            </form>
        @endif
    @endforeach
</div>
