@props([
    'url' => null,
    'title' => 'Video Player',
    'class' => '',
])

@php
    $embedUrl = $url ? \App\Services\VideoEmbedService::getEmbedUrl($url) : null;
    $provider = null;
    $isDirectMedia = false;

    if ($url) {
        $parsed = \App\Services\VideoEmbedService::parse($url);
        $provider = $parsed['provider'] ?? null;
        if (! $provider && (str_ends_with(strtolower($url), '.mp4') || str_contains($url, 'secure-stream'))) {
            $isDirectMedia = true;
        }
    }

    // Enhance embed parameters to restrict third-party hooks and branding
    $hardenedEmbedUrl = $embedUrl;
    if ($embedUrl && $provider === 'youtube') {
        $separator = str_contains($embedUrl, '?') ? '&' : '?';
        $hardenedEmbedUrl = $embedUrl . $separator . 'modestbranding=1&rel=0&iv_load_policy=3&playsinline=1&controls=1';
    } elseif ($embedUrl && $provider === 'loom') {
        $separator = str_contains($embedUrl, '?') ? '&' : '?';
        $hardenedEmbedUrl = $embedUrl . $separator . 'hide_owner=true&hide_share=true&hide_title=true&hideEmbedTopBar=true';
    }

    $viewerIdentifier = auth()->check() ? auth()->user()->email : 'Verified Viewer';
@endphp

@if ($hardenedEmbedUrl || $isDirectMedia)
    <style>
        /* Suppress IDM (Internet Download Manager) floating panels */
        div[id*="idm"], div[class*="idm"], div[id^="__idm"], [id*="idm_panel"], [id*="idm_editor"], #idm-panel, .idm-panel {
            display: none !important;
            visibility: hidden !important;
            pointer-events: none !important;
            opacity: 0 !important;
            width: 0 !important;
            height: 0 !important;
        }
    </style>
    <div
        x-data="{
            isBlobLoaded: false,
            blobUrl: null,
            initSecurePlayer() {
                @if ($isDirectMedia)
                    // Zero-Cost Blob URL Streaming Protection
                    // Fetches media into in-memory ArrayBuffer/Blob to hide underlying server paths from IDM/extensions
                    fetch('{{ $url }}', {
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Stream rejected');
                        return res.blob();
                    })
                    .then(blob => {
                        this.blobUrl = URL.createObjectURL(blob);
                        this.isBlobLoaded = true;
                    })
                    .catch(err => {
                        console.warn('Direct stream secured fallback', err);
                    });
                @endif
            },
            handleKey(e) {
                // Intercept and disable inspection and scraping hotkeys
                if (
                    e.key === 'F12' ||
                    ((e.ctrlKey || e.metaKey) && (e.key === 'u' || e.key === 'U' || e.key === 's' || e.key === 'S' || e.key === 'p' || e.key === 'P')) ||
                    ((e.ctrlKey || e.metaKey) && e.shiftKey && (e.key === 'I' || e.key === 'i' || e.key === 'J' || e.key === 'j' || e.key === 'C' || e.key === 'c'))
                ) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }
        }"
        x-init="initSecurePlayer()"
        @keydown.window="handleKey($event)"
        @contextmenu.prevent.stop="return false;"
        @dragstart.prevent.stop="return false;"
        @selectstart.prevent.stop="return false;"
        class="relative w-full aspect-video rounded-xl sm:rounded-2xl overflow-hidden bg-slate-950 shadow-md border border-slate-800 select-none {{ $class }}"
        oncontextmenu="return false;"
    >
        @if ($isDirectMedia)
            <!-- HTML5 Video Player with in-memory Blob URL to prevent extension scraping -->
            <template x-if="isBlobLoaded">
                <video
                    :src="blobUrl"
                    controls
                    controlsList="nodownload noplaybackrate nofullscreen"
                    disablePictureInPicture
                    class="w-full h-full object-contain pointer-events-auto"
                    oncontextmenu="return false;"
                ></video>
            </template>
            <template x-if="!isBlobLoaded">
                <div class="w-full h-full flex flex-col items-center justify-center gap-3 text-slate-400 bg-slate-900/80">
                    <svg class="w-8 h-8 text-emerald-400 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span class="text-xs font-mono tracking-wider uppercase text-slate-300">Initializing Secured Blob Stream...</span>
                </div>
            </template>
        @else
            <!-- Sandboxed Secure Video Embed with Cross-Origin Isolation -->
            <iframe
                src="{{ $embedUrl }}"
                title="{{ $title }}"
                class="w-full h-full border-0 pointer-events-auto"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
                loading="lazy"
                referrerpolicy="strict-origin-when-cross-origin"
                sandbox="allow-scripts allow-same-origin allow-presentation"
            ></iframe>
        @endif

        <!-- Anti-Tamper Security & Dynamic Watermarking Badge -->
        <div class="absolute bottom-2 right-2 pointer-events-none opacity-50 hover:opacity-100 transition-opacity flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-900/90 backdrop-blur-xs text-[10px] font-mono text-slate-300 border border-slate-700/60 shadow-xs">
            <svg class="w-3 h-3 text-emerald-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <span>S-cool Protected Stream • {{ $viewerIdentifier }}</span>
        </div>
    </div>
@else
    <div class="w-full aspect-video rounded-xl bg-slate-900/60 border border-slate-800 flex items-center justify-center text-slate-400 text-sm">
        <span class="flex items-center gap-2">
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            {{ __('No video available for this lesson.') }}
        </span>
    </div>
@endif
