@props([
    'url' => null,
    'title' => 'Video Player',
    'class' => '',
])

@php
    $embedUrl = $url ? \App\Services\VideoEmbedService::getEmbedUrl($url) : null;
    $provider = null;
    $isDirectMedia = false;
    $maskKey = '';
    $streamUrl = $url;

    if ($url) {
        $parsed = \App\Services\VideoEmbedService::parse($url);
        $provider = $parsed['provider'] ?? null;
        if (! $provider && (str_ends_with(strtolower($url), '.mp4') || str_contains($url, 'secure-stream'))) {
            $isDirectMedia = true;
            $rawPath = $url;
            if (str_contains($url, 'path=')) {
                $queryParts = [];
                parse_str((string) parse_url($url, PHP_URL_QUERY), $queryParts);
                $rawPath = $queryParts['path'] ?? $url;
            }

            $maskKey = \App\Http\Controllers\SecureVideoStreamController::getMaskKey($rawPath);
            if (! str_contains($url, 'signature=')) {
                $streamUrl = \App\Http\Controllers\SecureVideoStreamController::signedUrl($rawPath);
            }
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
        /* Suppress IDM (Internet Download Manager) and extension sniffer floating panels */
        [id^="__idm"], [id*="idm_panel"], [id*="idm_editor"], [id*="idm_btn"], #idm-panel, .idm-panel, .cococut-panel, .vdl-panel {
            display: none !important;
            visibility: hidden !important;
            pointer-events: none !important;
            opacity: 0 !important;
            width: 0 !important;
            height: 0 !important;
            clip: rect(0, 0, 0, 0) !important;
            position: absolute !important;
            left: -9999px !important;
            top: -9999px !important;
        }
    </style>
    <div
        x-data="{
            isBlobLoaded: false,
            blobUrl: null,
            loadError: false,
            watermarkPos: 0,
            watermarkTimer: null,
            initSecurePlayer() {
                // Eradicate IDM injected download elements from DOM continuously
                const suppressIdmNodes = () => {
                    document.querySelectorAll('[id^=\'__idm\'], [id*=\'idm_panel\'], [id*=\'idm_editor\'], [id*=\'idm_btn\'], #idm-panel, .idm-panel').forEach(el => {
                        try { el.remove(); } catch (e) { el.style.display = 'none'; }
                    });
                };
                suppressIdmNodes();
                try {
                    const idmObserver = new MutationObserver(suppressIdmNodes);
                    idmObserver.observe(document.documentElement, { childList: true, subtree: true });
                } catch (e) {}

                // Periodically drift the security watermark to prevent clean screen recording
                this.watermarkTimer = setInterval(() => {
                    this.watermarkPos = (this.watermarkPos + 1) % 4;
                }, 12000);

                @if ($isDirectMedia)
                    // Zero-Cost Obfuscated Binary Stream Protection (Anti-Sniffing / Anti-IDM)
                    // Requests binary octet stream, demasks header bytes in RAM, and revokes Blob URL on playback
                    const streamEndpoint = @js($streamUrl);
                    const maskKey = @js($maskKey);
                    const maskLen = {{ \App\Http\Controllers\SecureVideoStreamController::MASK_BYTE_LENGTH }};

                    fetch(streamEndpoint, {
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Stream rejected: ' + res.status);
                        return res.arrayBuffer();
                    })
                    .then(buffer => {
                        const view = new Uint8Array(buffer);
                        if (maskKey && maskKey.length > 0) {
                            const bytesToDemask = Math.min(maskLen, view.length);
                            for (let i = 0; i < bytesToDemask; i++) {
                                view[i] ^= maskKey.charCodeAt(i % maskKey.length);
                            }
                        }
                        const blob = new Blob([view], { type: 'video/mp4' });
                        this.blobUrl = URL.createObjectURL(blob);
                        this.isBlobLoaded = true;
                    })
                    .catch(err => {
                        console.warn('Direct media stream error:', err);
                        this.loadError = true;
                    });
                @endif
            },
            onVideoLoaded() {
                // Anti-Scraping: Immediately revoke the Blob URL from the browser's registry
                // Playback continues seamlessly because HTMLMediaElement already holds the stream handle
                // Any attempt by DevTools console or extensions to fetch video.src will result in ERR_FILE_NOT_FOUND
                const video = this.$refs.videoTag;
                if (this.blobUrl) {
                    const toRevoke = this.blobUrl;
                    setTimeout(() => {
                        try {
                            URL.revokeObjectURL(toRevoke);
                        } catch (e) {}
                    }, 100);
                }

                if (video) {
                    // Strip DOM src attribute so Element Inspector shows no media reference
                    try {
                        video.removeAttribute('src');
                    } catch (e) {}

                    // Define property traps so script / console inspections return empty string
                    try {
                        Object.defineProperty(video, 'src', {
                            get: () => '',
                            set: () => {},
                            configurable: false
                        });
                        Object.defineProperty(video, 'currentSrc', {
                            get: () => '',
                            configurable: false
                        });
                    } catch (e) {}
                }
            },
            handleVisibility() {
                // Anti-Recording: Pause stream when tab is backgrounded or minimized
                if (document.hidden && this.$refs.videoTag && !this.$refs.videoTag.paused) {
                    this.$refs.videoTag.pause();
                }
            },
            handleKey(e) {
                // Intercept scraping and developer tools hotkeys
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
        @visibilitychange.window="handleVisibility()"
        @contextmenu.prevent.stop="return false;"
        @dragstart.prevent.stop="return false;"
        @selectstart.prevent.stop="return false;"
        class="relative w-full aspect-video rounded-xl sm:rounded-2xl overflow-hidden bg-slate-950 shadow-md border border-slate-800 select-none group {{ $class }}"
        oncontextmenu="return false;"
    >
        @if ($isDirectMedia)
            <!-- HTML5 Secured Video Player with In-Memory Demasked Stream -->
            <template x-if="isBlobLoaded">
                <video
                    x-ref="videoTag"
                    :src="blobUrl"
                    @loadeddata="onVideoLoaded()"
                    controls
                    controlsList="nodownload noplaybackrate nofullscreen"
                    disablePictureInPicture
                    class="w-full h-full object-contain pointer-events-auto"
                    oncontextmenu="return false;"
                ></video>
            </template>
            <template x-if="!isBlobLoaded && !loadError">
                <div class="w-full h-full flex flex-col items-center justify-center gap-3 text-slate-400 bg-slate-900/80">
                    <svg class="w-8 h-8 text-emerald-400 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span class="text-xs font-mono tracking-wider uppercase text-slate-300">{{ __('Securing Protected Stream...') }}</span>
                </div>
            </template>
            <template x-if="loadError">
                <div class="w-full h-full flex flex-col items-center justify-center gap-2 text-slate-400 bg-slate-900/90 p-4 text-center">
                    <svg class="w-8 h-8 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="text-sm font-medium text-slate-200">{{ __('Secure video stream unavailable or expired.') }}</p>
                    <p class="text-xs text-slate-500">{{ __('Please refresh the page to renew playback authorization.') }}</p>
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

        <!-- Dynamic Anti-Screen-Capture & Watermark Badge (Drifts position periodically) -->
        <div
            :class="{
                'top-3 left-3': watermarkPos === 0,
                'top-3 right-3': watermarkPos === 1,
                'bottom-10 right-3': watermarkPos === 2,
                'bottom-10 left-3': watermarkPos === 3
            }"
            class="absolute pointer-events-none transition-all duration-1000 ease-in-out opacity-40 group-hover:opacity-80 flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-950/80 backdrop-blur-xs text-[10px] font-mono text-slate-300 border border-slate-700/60 shadow-xs"
        >
            <svg class="w-3 h-3 text-emerald-400 inline shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <span class="truncate max-w-[200px]">S-cool Protected • {{ $viewerIdentifier }}</span>
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
