<?php

namespace Tests\Feature;

use App\Http\Controllers\SecureVideoStreamController;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StreamAuthorizationRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_playback_link_is_bound_to_viewer_and_live_membership(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('videos/sample.mp4', str_repeat('A', 800));
        $user = User::factory()->create();
        $community = Community::factory()->create();
        $membership = CommunityMembership::factory()->active()->create(['community_id' => $community->id, 'user_id' => $user->id]);
        $this->actingAs($user);
        $url = SecureVideoStreamController::signedUrl('videos/sample.mp4', 15, $community);
        $this->withHeaders(['Referer' => config('app.url'), 'User-Agent' => 'Mozilla/5.0'])->get($url)->assertOk();
        $membership->forceFill(['status' => 'SUSPENDED'])->save();
        $this->get($url)->assertNotFound();
        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
        auth()->logout();
        $this->get($url)->assertForbidden();
    }

    public function test_byte_zero_suffix_and_invalid_ranges_are_handled_precisely(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('videos/sample.mp4', str_repeat('A', 800));
        $user = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $user->id]);
        $this->actingAs($user);
        $url = SecureVideoStreamController::signedUrl('videos/sample.mp4', 15, $community);
        foreach (['bytes=0-0' => 'bytes 0-0/800', 'bytes=-10' => 'bytes 790-799/800'] as $range => $expected) {
            $this->withHeaders(['Referer' => config('app.url'), 'User-Agent' => 'Mozilla/5.0', 'Range' => $range])
                ->get($url)->assertStatus(206)->assertHeader('Content-Range', $expected);
        }
        foreach (['bytes=900-', 'bytes=20-10', 'bytes=0-1,4-5', 'bytes=-0'] as $range) {
            $this->withHeader('Range', $range)->get($url)->assertStatus(416);
        }
    }
}
