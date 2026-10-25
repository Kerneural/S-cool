<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SecureVideoStreamTest extends TestCase
{
    public function test_unsigned_video_stream_request_is_rejected(): void
    {
        $response = $this->get('/secure-stream/video?path=videos/sample.mp4');
        $response->assertForbidden();
    }

    public function test_signed_request_without_valid_referer_is_rejected(): void
    {
        $signedUrl = URL::temporarySignedRoute(
            'secure-stream.video',
            now()->addMinutes(10),
            ['path' => 'videos/sample.mp4']
        );

        // No referer header
        $response = $this->get($signedUrl);
        $response->assertForbidden();

        // Malicious external referer
        $responseWithEvilReferer = $this->withHeaders([
            'Referer' => 'https://evil-pirate-site.com/download',
        ])->get($signedUrl);
        $responseWithEvilReferer->assertForbidden();
    }

    public function test_downloader_user_agents_are_blocked(): void
    {
        $signedUrl = URL::temporarySignedRoute(
            'secure-stream.video',
            now()->addMinutes(10),
            ['path' => 'videos/sample.mp4']
        );

        $response = $this->withHeaders([
            'Referer' => config('app.url').'/classroom',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Internet Download Manager 6.38',
        ])->get($signedUrl);

        $response->assertForbidden();
    }

    public function test_valid_request_for_existing_media_returns_stream_with_security_headers(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('videos/sample.mp4', 'dummy-binary-video-stream-content');

        $signedUrl = URL::temporarySignedRoute(
            'secure-stream.video',
            now()->addMinutes(10),
            ['path' => 'videos/sample.mp4']
        );

        $response = $this->withHeaders([
            'Referer' => config('app.url').'/classroom/lesson-1',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
        ])->get($signedUrl);

        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'inline');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Cache-Control', 'must-revalidate, no-cache, no-store, private');
    }

    public function test_missing_media_file_returns_not_found(): void
    {
        Storage::fake('public');

        $signedUrl = URL::temporarySignedRoute(
            'secure-stream.video',
            now()->addMinutes(10),
            ['path' => 'videos/non-existent.mp4']
        );

        $response = $this->withHeaders([
            'Referer' => config('app.url').'/classroom/lesson-1',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36',
        ])->get($signedUrl);

        $response->assertNotFound();
    }
}
