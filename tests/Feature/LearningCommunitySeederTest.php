<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\LearningCommunitySeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LearningCommunitySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seed_builds_four_complete_learning_communities_idempotently(): void
    {
        Storage::fake('community_media');

        $this->seed(DatabaseSeeder::class);

        $creator = User::query()->where('email', 'creator@scool.local')->firstOrFail();
        $member = User::query()->where('email', 'member@scool.local')->firstOrFail();
        $expected = [
            'networking-learning' => ['Networking Learning', 3, 6],
            'cloud-master' => ['Cloud Master', 4, 8],
            'docker-engineering' => ['Docker Engineering', 4, 8],
            'ai-engineer' => ['AI Engineer', 4, 8],
        ];

        foreach ($expected as $slug => [$name, $sections, $lessons]) {
            $community = Community::query()->where('slug', $slug)->firstOrFail();
            $course = $community->courses()->where('status', 'PUBLISHED')->firstOrFail();

            $this->assertSame($name, $community->name);
            $this->assertSame($creator->id, $community->creator_id);
            $this->assertSame('FREE', $community->access_mode);
            $this->assertSame('ACTIVE', $community->status);
            $this->assertTrue($community->onboarding_enabled);
            $this->assertNotEmpty($community->rules);
            $this->assertMatchesRegularExpression("#^{$community->id}/[a-f0-9]{40}\\.png$#", $community->cover_path);
            Storage::disk('community_media')->assertExists($community->cover_path);
            $this->assertTrue(is_readable(Storage::disk('community_media')->path($community->cover_path)));
            $this->assertSame(0755, fileperms(dirname(Storage::disk('community_media')->path($community->cover_path))) & 0777);

            $this->assertDatabaseHas('community_memberships', [
                'community_id' => $community->id,
                'user_id' => $member->id,
                'status' => 'ACTIVE',
                'access_kind' => 'LIFETIME',
            ]);
            $this->assertSame(1, $community->courses()->count());
            $this->assertSame($sections, $course->sections()->count());
            $this->assertSame($lessons, $course->lessons()->count());
            $this->assertSame($lessons, $course->publishedLessons()->count());
            $this->assertSame(3, $community->posts()->count());
            $this->assertSame(1, $community->posts()->whereNotNull('pinned_at')->count());
            $this->assertSame(1, $community->events()->where('status', 'SCHEDULED')->count());

            $course->lessons()->get()->each(function (Lesson $lesson): void {
                $this->assertStringContainsString('LEARNING OBJECTIVE', $lesson->content);
                $this->assertStringContainsString('HANDS-ON PRACTICE', $lesson->content);
                $this->assertStringContainsString('EVIDENCE OF COMPLETION', $lesson->content);
                $this->assertGreaterThan(450, strlen($lesson->content));
            });
        }

        $before = $this->learningDatasetCounts();
        $this->seed(DatabaseSeeder::class);

        $this->assertSame($before, $this->learningDatasetCounts());
    }

    public function test_networking_seed_repairs_known_placeholders_without_replacing_progress_bearing_records(): void
    {
        Storage::fake('community_media');
        $this->seed(UserSeeder::class);

        $creator = User::query()->where('email', 'creator@scool.local')->firstOrFail();
        $community = new Community;
        $community->forceFill([
            'creator_id' => $creator->id,
            'name' => 'Networking-learning',
            'slug' => 'networking-learning',
            'description' => 'Learning Networking for everyone int the world',
            'visibility' => 'PRIVATE',
            'access_mode' => 'PAID',
            'price_vnd' => 10000,
            'status' => 'ACTIVE',
        ])->save();
        $course = $community->courses()->create([
            'title' => 'PR17 Review',
            'description' => 'test course',
            'status' => 'PUBLISHED',
            'order' => 1,
        ]);
        $section = $course->sections()->create([
            'title' => 'Getting Started',
            'order' => 1,
        ]);
        $lesson = $section->lessons()->create([
            'title' => 'Introdution',
            'content' => 'placeholder',
            'status' => 'PUBLISHED',
            'order' => 1,
        ]);
        $community->courses()->create(['title' => 'test course', 'status' => 'DRAFT']);
        $community->courses()->create(['title' => 'test course', 'status' => 'DRAFT']);
        Post::query()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'A learner-created post',
            'body' => 'Preserve this unrelated content.',
        ]);

        $this->seed(LearningCommunitySeeder::class);

        $course->refresh();
        $lesson->refresh();
        $community->refresh();
        $this->assertSame('Networking Learning', $community->name);
        $this->assertSame('Networking Foundations: From Packet to Production', $course->title);
        $this->assertSame('How Data Moves Through a Network', $lesson->title);
        $this->assertSame($course->id, $community->courses()->where('status', 'PUBLISHED')->firstOrFail()->id);
        $this->assertSame($lesson->id, $course->lessons()->orderBy('lessons.order')->orderBy('lessons.id')->firstOrFail()->id);
        $this->assertDatabaseMissing('courses', [
            'community_id' => $community->id,
            'title' => 'test course',
        ]);
        $this->assertDatabaseHas('posts', [
            'community_id' => $community->id,
            'title' => 'A learner-created post',
        ]);
    }

    /** @return array<string, int> */
    private function learningDatasetCounts(): array
    {
        $communityIds = Community::query()
            ->whereIn('slug', ['networking-learning', 'cloud-master', 'docker-engineering', 'ai-engineer'])
            ->pluck('id');
        $courseIds = Course::query()->whereIn('community_id', $communityIds)->pluck('id');
        $sectionIds = CourseSection::query()->whereIn('course_id', $courseIds)->pluck('id');

        return [
            'communities' => $communityIds->count(),
            'courses' => $courseIds->count(),
            'sections' => $sectionIds->count(),
            'lessons' => Lesson::query()->whereIn('course_section_id', $sectionIds)->count(),
            'posts' => Post::query()->whereIn('community_id', $communityIds)->count(),
        ];
    }
}
