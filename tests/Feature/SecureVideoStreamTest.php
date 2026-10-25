<?php

namespace Tests\Feature;

use App\Http\Controllers\SecureVideoStreamController;
use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SecureVideoStreamTest extends TestCase
{
    use RefreshDatabase;

    private Community $community;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->community = Community::factory()->create(['creator_id' => $user->id]);
        $this->actingAs($user);
    }

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
            ['viewer' => auth()->id(), 'community' => $this->community->id, 'path' => 'videos/sample.mp4']
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
            ['viewer' => auth()->id(), 'community' => $this->community->id, 'path' => 'videos/sample.mp4']
        );

        $blockedAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Internet Download Manager 6.38',
            'CocoCut Video Downloader v2.1',
            'yt-dlp/2024.08.01',
            'curl/7.88.1',
            'Wget/1.21.3',
        ];

        foreach ($blockedAgents as $agent) {
            $response = $this->withHeaders([
                'Referer' => config('app.url').'/classroom',
                'User-Agent' => $agent,
            ])->get($signedUrl);

            $response->assertForbidden();
        }
    }

    public function test_direct_browser_document_navigation_is_rejected(): void
    {
        $signedUrl = URL::temporarySignedRoute(
            'secure-stream.video',
            now()->addMinutes(10),
            ['viewer' => auth()->id(), 'community' => $this->community->id, 'path' => 'videos/sample.mp4']
        );

        $response = $this->withHeaders([
            'Referer' => config('app.url').'/classroom',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36',
            'Sec-Fetch-Dest' => 'document',
        ])->get($signedUrl);

        $response->assertForbidden();
    }

    public function test_valid_request_returns_obfuscated_octet_stream(): void
    {
        Storage::fake('public');
        $dummyPayload = str_repeat('ABCDEFGH', 100); // 800 bytes
        Storage::disk('public')->put('videos/sample.mp4', $dummyPayload);

        $path = 'videos/sample.mp4';
        $signedUrl = URL::temporarySignedRoute(
            'secure-stream.video',
            now()->addMinutes(10),
            ['viewer' => auth()->id(), 'community' => $this->community->id, 'path' => $path]
        );

        $response = $this->withHeaders([
            'Referer' => config('app.url').'/classroom/lesson-1',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
        ])->get($signedUrl);

        $response->assertOk();
        // Crucial Anti-Sniffing Assertion: Never send video/* MIME
        $response->assertHeader('Content-Type', 'application/octet-stream');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Stream-Mask-Length', (string) SecureVideoStreamController::MASK_BYTE_LENGTH);
        $response->assertHeader('Content-Disposition', 'inline; filename="stream.bin"');
        $response->assertHeader('Cache-Control', 'must-revalidate, no-cache, no-store, private');

        $streamedContent = $response->streamedContent();
        $this->assertEquals(strlen($dummyPayload), strlen($streamedContent));

        // The first 512 bytes must be obfuscated/XORed and NOT equal to the original payload
        $this->assertNotEquals(
            substr($dummyPayload, 0, SecureVideoStreamController::MASK_BYTE_LENGTH),
            substr($streamedContent, 0, SecureVideoStreamController::MASK_BYTE_LENGTH)
        );

        // Demasking the obfuscated bytes with the session key restores original payload
        $maskKey = SecureVideoStreamController::getMaskKey($path);
        $maskKeyLen = strlen($maskKey);
        $demaskedBytes = substr($streamedContent, 0, SecureVideoStreamController::MASK_BYTE_LENGTH);
        for ($i = 0; $i < strlen($demaskedBytes); $i++) {
            $demaskedBytes[$i] = chr(ord($demaskedBytes[$i]) ^ ord($maskKey[$i % $maskKeyLen]));
        }

        $this->assertEquals(
            substr($dummyPayload, 0, SecureVideoStreamController::MASK_BYTE_LENGTH),
            $demaskedBytes
        );

        // Bytes beyond MASK_BYTE_LENGTH match directly
        $this->assertEquals(
            substr($dummyPayload, SecureVideoStreamController::MASK_BYTE_LENGTH),
            substr($streamedContent, SecureVideoStreamController::MASK_BYTE_LENGTH)
        );
    }

    public function test_range_requests_are_supported_with_partial_content(): void
    {
        Storage::fake('public');
        $dummyPayload = str_repeat('0123456789', 200); // 2000 bytes
        Storage::disk('public')->put('videos/sample.mp4', $dummyPayload);

        $path = 'videos/sample.mp4';
        $signedUrl = URL::temporarySignedRoute(
            'secure-stream.video',
            now()->addMinutes(10),
            ['viewer' => auth()->id(), 'community' => $this->community->id, 'path' => $path]
        );

        // Request slice 100-599
        $response = $this->withHeaders([
            'Referer' => config('app.url').'/classroom/lesson-1',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36',
            'Range' => 'bytes=100-599',
        ])->get($signedUrl);

        $response->assertStatus(206);
        $response->assertHeader('Content-Range', 'bytes 100-599/2000');
        $response->assertHeader('Content-Length', '500');
        $response->assertHeader('Accept-Ranges', 'bytes');
        $response->assertHeader('Content-Type', 'application/octet-stream');
    }

    public function test_missing_media_file_returns_not_found(): void
    {
        Storage::fake('public');

        $signedUrl = URL::temporarySignedRoute(
            'secure-stream.video',
            now()->addMinutes(10),
            ['viewer' => auth()->id(), 'community' => $this->community->id, 'path' => 'videos/non-existent.mp4']
        );

        $response = $this->withHeaders([
            'Referer' => config('app.url').'/classroom/lesson-1',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36',
        ])->get($signedUrl);

        $response->assertNotFound();
    }
}
