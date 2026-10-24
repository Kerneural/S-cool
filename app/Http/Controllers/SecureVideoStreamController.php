<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecureVideoStreamController extends Controller
{
    /**
     * Number of header bytes obfuscated to scramble MP4 container signatures (ftyp/moov).
     */
    public const MASK_BYTE_LENGTH = 512;

    /**
     * Compute a deterministic demasking key for the given media path.
     */
    public static function getMaskKey(string $path): string
    {
        $appKey = config('app.key') ?: 'scool-video-protection-key';

        return substr(hash_hmac('sha256', $path, $appKey), 0, 32);
    }

    /**
     * Generate a temporary signed playback URL for a media asset.
     */
    public static function signedUrl(string $path, int $minutes = 15): string
    {
        return URL::temporarySignedRoute(
            'secure-stream.video',
            now()->addMinutes($minutes),
            ['path' => $path]
        );
    }

    /**
     * Stream a secured media asset to authorized viewers only.
     * Prevents direct access, IDM / downloaders, and unauthorized embedding.
     */
    public function stream(Request $request): Response
    {
        // 1. Verify Signed URL
        if (! $request->hasValidSignature()) {
            abort(Response::HTTP_FORBIDDEN, 'Invalid or expired media playback token.');
        }

        // 2. Strict Referer / Origin Verification to prevent external downloaders or hotlinking
        $referer = (string) $request->headers->get('referer', '');
        $origin = (string) $request->headers->get('origin', '');
        $appHost = (string) parse_url(config('app.url'), PHP_URL_HOST);
        $refererHost = (string) parse_url($referer, PHP_URL_HOST);
        $originHost = (string) parse_url($origin, PHP_URL_HOST);

        $allowedHosts = array_values(array_unique(array_filter([
            $appHost,
            $request->getHost(),
        ])));

        $validHost = (! empty($refererHost) && in_array($refererHost, $allowedHosts, true))
            || (! empty($originHost) && in_array($originHost, $allowedHosts, true));

        if (! $validHost) {
            abort(Response::HTTP_FORBIDDEN, 'Direct media requests from external tools or downloaders are prohibited.');
        }

        // 3. Prevent third-party bot/downloader User-Agents
        $userAgent = strtolower((string) $request->header('User-Agent', ''));
        $blockedAgents = [
            'idm',
            'internet download manager',
            'curl',
            'wget',
            'python',
            'postman',
            'httpclient',
            'aria2',
            'jdownloader',
            'ffmpeg',
            'yt-dlp',
            'youtube-dl',
            'videodownloadhelper',
            'cococut',
            'fetchv',
            'fdown',
            'videodownloader',
            'm3u8',
            'streamlink',
            'alohabrowser',
            'vget',
            'snaptube',
            'vidmate',
        ];
        foreach ($blockedAgents as $blocked) {
            if (str_contains($userAgent, $blocked)) {
                abort(Response::HTTP_FORBIDDEN, 'External downloader agent rejected.');
            }
        }

        // 4. Reject direct browser document navigation (address bar / <a> links)
        if ($request->header('Sec-Fetch-Dest') === 'document') {
            abort(Response::HTTP_FORBIDDEN, 'Direct browser document navigation to stream endpoint is prohibited.');
        }

        $path = $request->query('path');
        if (! $path || ! Storage::disk('public')->exists($path)) {
            abort(Response::HTTP_NOT_FOUND, 'Media source not found.');
        }

        $fileSize = Storage::disk('public')->size($path);
        $start = 0;
        $end = $fileSize - 1;
        $isRangeRequest = false;

        $rangeHeader = $request->header('Range');
        if ($rangeHeader && preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $rangeHeader, $matches)) {
            $isRangeRequest = true;
            $start = (int) $matches[1];
            if (! empty($matches[2])) {
                $end = min((int) $matches[2], $fileSize - 1);
            }
        }

        if ($start > $end || $start >= $fileSize) {
            return response('', Response::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE, [
                'Content-Range' => "bytes */{$fileSize}",
            ]);
        }

        $length = $end - $start + 1;
        $status = $isRangeRequest ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK;

        $maskKey = self::getMaskKey($path);
        $maskKeyLen = strlen($maskKey);

        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Length' => (string) $length,
            'Accept-Ranges' => 'bytes',
            'Content-Disposition' => 'inline; filename="stream.bin"',
            'X-Content-Type-Options' => 'nosniff',
            'X-Stream-Mask-Length' => (string) self::MASK_BYTE_LENGTH,
            'Cache-Control' => 'must-revalidate, no-cache, no-store, private',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
        ];

        if ($isRangeRequest) {
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$fileSize}";
        }

        $response = new StreamedResponse(function () use ($path, $start, $length, $maskKey, $maskKeyLen): void {
            $stream = Storage::disk('public')->readStream($path);
            if (! $stream) {
                return;
            }

            if ($start > 0) {
                fseek($stream, $start);
            }

            $bytesRemaining = $length;
            $currentPosition = $start;

            // Obfuscate the initial header bytes if this slice includes byte 0
            if ($currentPosition < self::MASK_BYTE_LENGTH && $bytesRemaining > 0) {
                $maskChunkSize = min(self::MASK_BYTE_LENGTH - $currentPosition, $bytesRemaining);
                $chunk = fread($stream, $maskChunkSize);
                if ($chunk !== false && $chunk !== '') {
                    $chunkLen = strlen($chunk);
                    for ($i = 0; $i < $chunkLen; $i++) {
                        $chunk[$i] = chr(ord($chunk[$i]) ^ ord($maskKey[($currentPosition + $i) % $maskKeyLen]));
                    }
                    echo $chunk;
                    flush();
                    $bytesRemaining -= $chunkLen;
                    $currentPosition += $chunkLen;
                }
            }

            // Stream remaining bytes cleanly in 64KB blocks
            $bufferSize = 65536;
            while ($bytesRemaining > 0 && ! feof($stream)) {
                $readSize = min($bufferSize, $bytesRemaining);
                $chunk = fread($stream, $readSize);
                if ($chunk === false || $chunk === '') {
                    break;
                }
                echo $chunk;
                flush();
                $bytesRemaining -= strlen($chunk);
            }

            fclose($stream);
        }, $status, $headers);

        return $response;
    }
}
