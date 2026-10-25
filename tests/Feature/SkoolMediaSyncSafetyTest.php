<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\User;
use Database\Seeders\RealCommunitySampleSeeder;
use Database\Seeders\SkoolMediaSyncSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SkoolMediaSyncSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_seeder_rejects_production_before_writes(): void
    {
        Storage::fake('community_media');
        $this->app->detectEnvironment(fn () => 'production');
        $this->expectException(\LogicException::class);
        $this->seed(SkoolMediaSyncSeeder::class);
    }

    public function test_slug_collision_cannot_replace_another_creators_media(): void
    {
        Storage::fake('community_media');
        $this->seed(UserSeeder::class);
        $community = Community::factory()->create(['slug' => 'python-developers']);

        $this->seed(SkoolMediaSyncSeeder::class);

        $this->assertNull($community->fresh()->cover_path);
        $this->assertSame([], Storage::disk('community_media')->allFiles());
    }

    public function test_existing_custom_community_and_course_covers_are_retained(): void
    {
        Storage::fake('community_media');
        $community = $this->samples();
        $course = $community->courses()->firstOrFail();
        $cover = $community->id.'/'.str_repeat('a', 40).'.jpg';
        $courseCover = $community->id.'/courses/'.str_repeat('b', 40).'.jpg';
        Storage::disk('community_media')->put($cover, 'retained-community');
        Storage::disk('community_media')->put($courseCover, 'retained-course');
        $community->forceFill(['cover_path' => $cover])->save();
        $course->forceFill(['cover_path' => $courseCover])->save();

        $this->seed(SkoolMediaSyncSeeder::class);

        $this->assertSame($cover, $community->fresh()->cover_path);
        $this->assertSame($courseCover, $course->fresh()->cover_path);
        $this->assertSame('retained-community', Storage::disk('community_media')->get($cover));
        $this->assertSame('retained-course', Storage::disk('community_media')->get($courseCover));
    }

    public function test_bundled_media_is_repeatable_and_stays_behind_private_routes(): void
    {
        Storage::fake('community_media');
        $community = $this->samples();
        $this->seed(SkoolMediaSyncSeeder::class);
        $files = Storage::disk('community_media')->allFiles();
        $this->assertNotEmpty($files);
        $this->seed(SkoolMediaSyncSeeder::class);
        $this->assertSame($files, Storage::disk('community_media')->allFiles());
        $course = $community->courses()->firstOrFail();
        $member = User::where('email', 'member@scool.local')->firstOrFail();
        $this->actingAs($member)->get(route('communities.cover.show', $community))->assertOk();
        $this->get(route('communities.courses.cover', [$community, $course]))->assertOk();
        $this->actingAs(User::factory()->create())->get(route('communities.cover.show', $community))->assertOk();
        $this->get(route('communities.courses.cover', [$community, $course]))->assertNotFound();
    }

    public function test_failed_storage_never_publishes_a_missing_cover_path(): void
    {
        $community = $this->samples();
        $disk = \Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('exists')->andReturnFalse();
        $disk->shouldReceive('put')->andReturnFalse();
        Storage::shouldReceive('disk')->with('community_media')->andReturn($disk);

        try {
            $this->seed(SkoolMediaSyncSeeder::class);
            $this->fail('Expected failed storage to stop media publication.');
        } catch (\RuntimeException $exception) {
            $this->assertNull($community->fresh()->cover_path);
        }
    }

    private function samples(): Community
    {
        $this->seed(UserSeeder::class);
        $this->seed(RealCommunitySampleSeeder::class);

        return Community::where('slug', 'python-developers')->firstOrFail();
    }
}
