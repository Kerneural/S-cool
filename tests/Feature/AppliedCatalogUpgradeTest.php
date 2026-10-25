<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\AppliedCatalogUpgradeSeeder;
use Database\Seeders\RealCommunitySampleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AppliedCatalogUpgradeTest extends TestCase
{
    use RefreshDatabase;

    private function legacy(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(RealCommunitySampleSeeder::class);
        $catalog = json_decode(file_get_contents(database_path('data/applied-catalog-baselines.json')), true, 512, JSON_THROW_ON_ERROR)['legacy-0e109e2'];
        foreach ($catalog as $slug => $baseline) {
            $community = Community::where('slug', $slug)->firstOrFail();
            $community->forceFill($baseline['community'])->save();
            $course = $community->courses()->firstOrFail();
            // Only synthetic test records on the guarded scool_test database.
            $course->sections()->each(fn ($section) => $section->lessons()->delete());
            $course->sections()->delete();
            $course->forceFill(['description' => $baseline['course']['description']])->save();
            foreach ($baseline['course']['sections'] as $sectionSpec) {
                $section = new CourseSection;
                $section->forceFill(['course_id' => $course->id, 'title' => $sectionSpec['title'], 'order' => $sectionSpec['order']])->save();
                foreach ($sectionSpec['lessons'] as $lessonSpec) {
                    $lesson = new Lesson;
                    $lesson->forceFill($lessonSpec + ['course_section_id' => $section->id, 'status' => 'PUBLISHED'])->save();
                }
            }
        }
    }

    public function test_preview_does_not_write_and_upgrade_preserves_semantic_lesson_ids_progress_and_access(): void
    {
        $this->legacy();
        $lesson = Lesson::where('title', 'Webhook Architecture & Error Handling')->firstOrFail();
        $community = $lesson->section->course->community;
        $membership = $community->memberships()->firstOrFail();
        $membership->forceFill(['status' => 'SUSPENDED'])->save();
        $community->forceFill(['status' => 'SUSPENDED', 'cover_path' => 'custom-cover.jpg'])->save();
        $course = $lesson->section->course;
        $course->forceFill(['status' => 'DRAFT', 'cover_path' => 'custom-course.jpg'])->save();
        $lesson->forceFill(['status' => 'DRAFT'])->save();
        DB::table('lesson_progresses')->insert(['user_id' => $membership->user_id, 'lesson_id' => $lesson->id, 'completed_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        $progress = DB::table('lesson_progresses')->first();
        $users = User::orderBy('id')->get()->map->getAttributes()->all();
        $membershipBefore = $membership->fresh()->getAttributes();
        $upgrader = new AppliedCatalogUpgradeSeeder;
        $preview = $upgrader->upgrade();
        $this->assertStringStartsWith('WOULD_UPGRADE:', $preview[$community->slug]);
        $this->assertSame('Webhook Architecture & Error Handling', $lesson->fresh()->title);

        $report = $upgrader->upgrade(true);
        $this->assertCount(5, array_filter($report, fn ($status) => str_starts_with($status, 'UPGRADED:')));
        $this->assertSame('Webhook Architecture, Security & Payload Normalization', $lesson->fresh()->title);
        $this->assertSame('Phase 1: Architecture, Scoping & Webhook Security', $lesson->fresh()->section->title);
        $this->assertSame('DRAFT', $lesson->fresh()->status);
        $this->assertSame('DRAFT', $course->fresh()->status);
        $this->assertSame('SUSPENDED', $community->fresh()->status);
        $this->assertSame('custom-cover.jpg', $community->fresh()->cover_path);
        $this->assertSame('custom-course.jpg', $course->fresh()->cover_path);
        $this->assertSame($membershipBefore, $membership->fresh()->getAttributes());
        $this->assertEquals($progress, DB::table('lesson_progresses')->first());
        $this->assertSame($users, User::orderBy('id')->get()->map->getAttributes()->all());
        $this->assertSame(20, Lesson::count());
        $before = Lesson::orderBy('id')->get()->map->getAttributes()->all();
        $this->assertSame(array_fill_keys(array_keys($report), 'CURRENT'), $upgrader->upgrade(true));
        $this->assertSame($before, Lesson::orderBy('id')->get()->map->getAttributes()->all());
    }

    public function test_personal_content_and_unknown_outlines_are_reported_as_conflicts_and_preserved(): void
    {
        $this->legacy();
        $lesson = Lesson::where('title', 'Environment Setup & Virtual Environments')->firstOrFail();
        $lesson->forceFill(['content' => 'My personal notes'])->save();
        $course = $lesson->section->course;
        $before = $lesson->fresh()->getAttributes();
        $result = (new AppliedCatalogUpgradeSeeder)->upgrade(true);
        $this->assertStringStartsWith('CONFLICT:', $result['python-developers']);
        $this->assertSame($before, $lesson->fresh()->getAttributes());
        $this->assertSame(3, $course->lessons()->count());
    }

    public function test_foreign_owner_and_production_are_never_upgraded(): void
    {
        $this->legacy();
        $community = Community::where('slug', 'python-developers')->firstOrFail();
        $community->forceFill(['creator_id' => User::factory()->create()->id])->save();
        $before = $community->courses()->firstOrFail()->getAttributes();
        $report = (new AppliedCatalogUpgradeSeeder)->upgrade(true);
        $this->assertStringStartsWith('CONFLICT:', $report['python-developers']);
        $this->assertSame($before, $community->courses()->firstOrFail()->getAttributes());
        $this->app->detectEnvironment(fn () => 'production');
        $this->expectException(\LogicException::class);
        (new AppliedCatalogUpgradeSeeder)->upgrade(true);
    }
}
