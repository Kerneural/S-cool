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
     * Generate a temporary signed playback URL for a media asset.
     */
    public static function signedUrl(string $path, int $minutes = 60): string
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

        $allowedHosts = array_filter([$appHost, '127.0.0.1', 'localhost']);

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
            'fdown',
            'videodownloader',
        ];
        foreach ($blockedAgents as $blocked) {
            if (str_contains($userAgent, $blocked)) {
                abort(Response::HTTP_FORBIDDEN, 'External downloader agent rejected.');
            }
        }

        $path = $request->query('path');
        if (! $path || ! Storage::disk('public')->exists($path)) {
            abort(Response::HTTP_NOT_FOUND, 'Media source not found.');
        }

        $fileSize = Storage::disk('public')->size($path);
        $fullPath = Storage::disk('public')->path($path);
        $mimeType = file_exists($fullPath) ? (mime_content_type($fullPath) ?: 'video/mp4') : 'video/mp4';

        $response = new StreamedResponse(function () use ($path): void {
            $stream = Storage::disk('public')->readStream($path);
            if ($stream) {
                fpassthru($stream);
                fclose($stream);
            }
        }, Response::HTTP_OK, [
            'Content-Type' => $mimeType,
            'Content-Length' => (string) $fileSize,
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'must-revalidate, no-cache, no-store, private',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
        ]);

        return $response;
    }
}
