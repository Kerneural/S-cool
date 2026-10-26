<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_seed_builds_a_deterministic_multi_community_demo_dataset(): void
    {
        $this->seed(DatabaseSeeder::class);

        $creator = User::query()->where('email', 'creator@scool.local')->firstOrFail();
        $member = User::query()->where('email', 'member@scool.local')->firstOrFail();
        $free = Community::query()->where('slug', 'demo-free-learning')->firstOrFail();
        $paid = Community::query()->where('slug', 'demo-paid-learning')->firstOrFail();

        $this->assertSame($creator->id, $free->creator_id);
        $this->assertSame($creator->id, $paid->creator_id);
        $this->assertSame('FREE', $free->access_mode);
        $this->assertNull($free->price_vnd);
        $this->assertSame('PAID', $paid->access_mode);
        $this->assertSame(100000, $paid->price_vnd);
        $this->assertDatabaseHas('community_memberships', [
            'community_id' => $free->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);
        $this->assertDatabaseMissing('community_memberships', [
            'community_id' => $paid->id,
            'user_id' => $member->id,
        ]);

        foreach ([$free, $paid] as $community) {
            $this->assertSame(1, $community->events()->count());
            $this->assertSame(1, $community->courses()->count());
            $this->assertSame(2, $community->courses()->firstOrFail()->lessons()->count());
            $this->assertGreaterThanOrEqual(1, $community->posts()->count());
        }

        $completedLesson = Lesson::query()
            ->whereHas('section.course', fn ($query) => $query->where('community_id', $free->id))
            ->orderBy('order')
            ->firstOrFail();
        $this->assertDatabaseHas('lesson_progresses', [
            'user_id' => $member->id,
            'lesson_id' => $completedLesson->id,
            'completed' => true,
        ]);

        $counts = [
            'communities' => Community::query()->count(),
            'posts' => $free->posts()->count() + $paid->posts()->count(),
            'events' => $free->events()->count() + $paid->events()->count(),
            'courses' => $free->courses()->count() + $paid->courses()->count(),
        ];
        $this->seed(DatabaseSeeder::class);

        $this->assertSame($counts['communities'], Community::query()->count());
        $this->assertSame($counts['posts'], $free->posts()->count() + $paid->posts()->count());
        $this->assertSame($counts['events'], $free->events()->count() + $paid->events()->count());
        $this->assertSame($counts['courses'], $free->courses()->count() + $paid->courses()->count());
    }

    public function test_demo_personas_can_walk_the_free_tenant_without_leaking_paid_content(): void
    {
        $this->seed(DatabaseSeeder::class);

        $creator = User::query()->where('email', 'creator@scool.local')->firstOrFail();
        $member = User::query()->where('email', 'member@scool.local')->firstOrFail();
        $admin = User::query()->where('email', 'admin@scool.local')->firstOrFail();
        $free = Community::query()->where('slug', 'demo-free-learning')->firstOrFail();
        $paid = Community::query()->where('slug', 'demo-paid-learning')->firstOrFail();
        $freeCourse = $free->courses()->firstOrFail();
        $freeLesson = $freeCourse->lessons()->orderBy('order')->firstOrFail();

        $this->actingAs($creator)->get(route('dashboard'))
            ->assertOk()
            ->assertSee($free->name)
            ->assertSee($paid->name);

        $this->actingAs($member)->get(route('dashboard'))
            ->assertOk()
            ->assertSee($free->name)
            ->assertSee($paid->name)
            ->assertSee('View community');
        $this->get(route('communities.index', ['filter' => 'joined']))
            ->assertOk()->assertSee($free->name)->assertDontSee($paid->name);
        $this->get(route('communities.show', $paid))->assertNotFound();
        $this->get(route('communities.posts.index', $free))->assertOk()->assertSee('Welcome to the free demo community');
        $this->get(route('communities.classroom.index', $free))->assertOk()->assertSee($freeCourse->title);
        $this->get(route('communities.lessons.show', [$free, $freeCourse, $freeLesson]))->assertOk()->assertSee($freeLesson->title);
        $this->get(route('communities.events.index', $free))->assertOk()->assertSee('Free Demo Workshop');

        $this->get(route('communities.show', $paid))->assertNotFound();
        $this->get(route('communities.posts.index', $paid))->assertNotFound();
        $this->get(route('communities.classroom.index', $paid))->assertNotFound();
        $this->get(route('communities.events.index', $paid))->assertNotFound();

        $this->actingAs($admin)->get(route('admin.communities.index'))
            ->assertOk()
            ->assertSee($free->name)
            ->assertSee($paid->name);
    }

    public function test_creator_can_rehearse_validation_and_empty_states(): void
    {
        $this->seed(DatabaseSeeder::class);

        $creator = User::query()->where('email', 'creator@scool.local')->firstOrFail();

        $this->actingAs($creator)->post(route('communities.store'), [
            'name' => '',
            'slug' => 'invalid slug',
        ])->assertSessionHasErrors(['name', 'slug']);

        $this->assertDatabaseMissing('communities', ['slug' => 'demo-empty-state']);

        $this->post(route('communities.store'), [
            'name' => 'Demo Empty State',
            'slug' => 'demo-empty-state',
            'description' => 'Temporary community for the empty-state rehearsal.',
        ])->assertRedirect();

        $community = Community::query()->where('slug', 'demo-empty-state')->firstOrFail();

        $this->get(route('communities.posts.index', $community))->assertOk()->assertSee('No posts yet');
        $this->get(route('communities.classroom.index', $community))->assertOk()->assertSee('No courses available');
        $this->get(route('communities.events.index', $community))->assertOk()->assertSee('No events scheduled yet');
    }
}
