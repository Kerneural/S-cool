<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\LocalDemoSyncSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LocalDemoSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_catalog_and_private_images_are_available_without_legacy_qa_data(): void
    {
        Storage::fake('community_media');
        $this->seed(LocalDemoSyncSeeder::class);

        $this->assertSame(5, User::count());
        $this->assertSame(10, Community::count());
        $this->assertSame(63, Course::count());
        $this->assertSame(73, count(Storage::disk('community_media')->allFiles()));
        $member = User::where('email', 'member@scool.local')->firstOrFail();
        $this->actingAs($member);
        foreach (Community::all() as $community) {
            $this->get(route('communities.cover.show', $community))->assertOk();
            $course = $community->courses()->firstOrFail();
            $this->get(route('communities.courses.cover', [$community, $course]))->assertOk();
        }
        $this->assertDatabaseMissing('communities', ['slug' => 'demo-free-learning']);
        $this->assertDatabaseMissing('communities', ['slug' => 'demo-paid-learning']);
        $this->assertSame(0, DB::table('payments')->count());
        $this->assertSame(0, DB::table('platform_admin_actions')->count());
    }

    public function test_rerun_preserves_accounts_learning_edits_access_progress_and_custom_covers(): void
    {
        Storage::fake('community_media');
        $this->seed(LocalDemoSyncSeeder::class);
        $creator = User::where('email', 'creator@scool.local')->firstOrFail();
        $creator->forceFill(['name' => 'Edited creator', 'password' => Hash::make('retained'), 'email_verified_at' => null])->save();
        User::where('email', 'admin@scool.local')->update(['is_platform_admin' => false]);
        $usersBefore = User::orderBy('id')->get()->map->getAttributes()->all();
        $community = Community::where('slug', 'docker-engineering')->firstOrFail();
        $community->forceFill(['description' => 'Retained local content', 'status' => 'SUSPENDED'])->save();
        $membership = $community->memberships()->firstOrFail();
        $membership->forceFill(['status' => 'SUSPENDED'])->save();
        $course = $community->courses()->firstOrFail();
        $course->forceFill(['description' => 'Retained course', 'status' => 'DRAFT'])->save();
        $lesson = $course->lessons()->firstOrFail();
        $lesson->forceFill(['content' => 'Retained lesson'])->save();
        DB::table('lesson_progresses')->insert([
            'user_id' => $membership->user_id, 'lesson_id' => $lesson->id,
            'completed_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $progressBefore = DB::table('lesson_progresses')->first();
        $cover = $community->id.'/'.str_repeat('a', 40).'.jpg';
        $courseCover = $community->id.'/courses/'.str_repeat('b', 40).'.jpg';
        Storage::disk('community_media')->put($cover, 'retained custom community bytes');
        Storage::disk('community_media')->put($courseCover, 'retained custom course bytes');
        $community->forceFill(['cover_path' => $cover])->save();
        $course->forceFill(['cover_path' => $courseCover])->save();
        $counts = [Community::count(), Course::count(), Lesson::count()];

        $this->seed(LocalDemoSyncSeeder::class);

        $this->assertSame($counts, [Community::count(), Course::count(), Lesson::count()]);
        $this->assertSame($usersBefore, User::orderBy('id')->get()->map->getAttributes()->all());
        $this->assertSame('Retained local content', $community->fresh()->description);
        $this->assertSame('SUSPENDED', $community->fresh()->status);
        $this->assertSame('SUSPENDED', $membership->fresh()->status);
        $this->assertSame('Retained course', $course->fresh()->description);
        $this->assertSame('DRAFT', $course->fresh()->status);
        $this->assertSame('Retained lesson', $lesson->fresh()->content);
        $this->assertEquals($progressBefore, DB::table('lesson_progresses')->first());
        $this->assertSame($cover, $community->fresh()->cover_path);
        $this->assertSame($courseCover, $course->fresh()->cover_path);
        $this->assertSame('retained custom community bytes', Storage::disk('community_media')->get($cover));
        $this->assertSame('retained custom course bytes', Storage::disk('community_media')->get($courseCover));
    }

    public function test_foreign_owned_catalog_slug_is_not_overwritten(): void
    {
        Storage::fake('community_media');
        $existing = Community::factory()->create(['slug' => 'networking-learning']);
        $before = $existing->fresh()->getAttributes();
        try {
            $this->seed(LocalDemoSyncSeeder::class);
            $this->fail('Expected a catalog ownership collision.');
        } catch (\LogicException $exception) {
            $this->assertSame($before, $existing->fresh()->getAttributes());
            $this->assertSame([], Storage::disk('community_media')->allFiles());
        }
    }

    public function test_production_is_rejected_before_demo_accounts_are_created(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        try {
            $this->seed(LocalDemoSyncSeeder::class);
            $this->fail('Production must reject demo synchronization.');
        } catch (\LogicException $exception) {
            $this->assertSame(0, User::count());
        }
    }

    public function test_local_environment_cannot_seed_an_unexpected_database(): void
    {
        // RefreshDatabase has already passed TestCase's scool_test safety guard.
        $this->app->detectEnvironment(fn () => 'local');
        try {
            $this->seed(LocalDemoSyncSeeder::class);
            $this->fail('Local mode must reject scool_test rather than seed it.');
        } catch (\LogicException $exception) {
            $this->assertSame(0, User::count());
        }
    }
}
