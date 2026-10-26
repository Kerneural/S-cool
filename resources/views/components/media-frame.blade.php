@props([
    'src' => null,
    'alt' => '',
    'shape' => 'cover',
    // Announce busy only for meaningful regions, not every avatar in a list.
    'busy' => null,
    'loading' => null,
    'width' => null,
    'height' => null,
    'imgClass' => '',
])

@php
    $announceBusy = $busy ?? $shape === 'cover';
@endphp

<div {{ $attributes->class(['media-frame', 'media-frame--'.$shape]) }}
     @if ($src)
         x-data="mediaFrame"
         :data-media-state="state"
         @if ($announceBusy) :aria-busy="(state === 'pending').toString()" @endif
     @endif>
    <div class="media-frame__fallback" @if ($src) aria-hidden="true" @endif>{{ $slot }}</div>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $alt }}" @if ($loading) loading="{{ $loading }}" @endif @if ($width) width="{{ $width }}" @endif @if ($height) height="{{ $height }}" @endif class="media-frame__img {{ $imgClass }}">
        <span class="media-frame__glass" aria-hidden="true"></span>
    @endif
</div>
