<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommunityDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_discovery_and_covers_still_require_login(): void
    {
        $community = Community::factory()->create();

        $this->get('/communities')->assertRedirect('/login');
        $this->get(route('communities.cover.show', $community))->assertRedirect('/login');
    }

    public function test_new_account_discovers_active_community_metadata_without_content_access(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create(['name' => 'Discoverable Learning Hub']);
        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $community->creator_id, 'title' => 'Private discussion title']);
        $course = Course::factory()->published()->create(['community_id' => $community->id, 'title' => 'Private course title']);
        $inactive = Community::factory()->suspended()->create();

        $this->actingAs($user);
        foreach (['/communities', '/communities?filter=all', '/dashboard'] as $url) {
            $response = $this->get($url)->assertOk()->assertSee($community->name)
                ->assertSee('Not joined')->assertSee('View community')
                ->assertDontSee('Role: Member')->assertDontSee('Open Community')
                ->assertDontSee($inactive->name)->assertDontSee($post->title)->assertDontSee($course->title);
            $this->assertSame(1, $response->viewData('communities')->total());
        }
        $this->get(route('communities.about', $community))->assertOk()->assertViewIs('communities.preview')
            ->assertSee($course->title)->assertDontSee($post->title);
        foreach (['communities.show', 'communities.posts.index', 'communities.classroom.index', 'communities.events.index', 'communities.directory.index', 'communities.search'] as $route) {
            $this->get(route($route, $community))->assertNotFound();
        }
        $this->get(route('communities.posts.show', [$community, $post]))->assertNotFound();
        $this->get(route('communities.courses.show', [$community, $course]))->assertNotFound();
        $this->assertDatabaseCount('community_memberships', 0);
    }

    public function test_discovery_preserves_owned_and_joined_filters_and_literal_search_pagination(): void
    {
        $user = User::factory()->create();
        $owned = Community::factory()->create(['creator_id' => $user->id]);
        $joined = CommunityMembership::factory()->active()->create(['user_id' => $user->id])->community;
        $other = Community::factory()->create(['name' => 'Independent 50% Learning']);
        Community::factory()->create(['name' => 'Independent 500 Learning']);

        $this->actingAs($user)->get('/communities?filter=owned')->assertOk()
            ->assertSee($owned->name)->assertDontSee($joined->name)->assertDontSee($other->name);
        $this->get('/communities?filter=joined')->assertOk()
            ->assertSee($joined->name)->assertDontSee($owned->name)->assertDontSee($other->name);
        $this->get('/communities?q=50%25')->assertOk()->assertSee($other->name)->assertDontSee('Independent 500 Learning');

        Community::factory()->count(13)->create(['name' => 'Discovery pagination']);
        $first = $this->get('/communities?filter=all&q=Discovery')->assertOk();
        $this->assertSame(13, $first->viewData('communities')->total());
        $this->assertCount(12, $first->viewData('communities')->items());
        $first->assertSee('filter=all', false)->assertSee('q=Discovery', false);
        $second = $this->get('/communities?filter=all&q=Discovery&page=2')->assertOk();
        $this->assertCount(1, $second->viewData('communities')->items());
    }

    public function test_cover_is_discoverable_but_upload_and_inactive_media_remain_protected(): void
    {
        Storage::fake('community_media');
        $community = Community::factory()->create();
        $url = route('communities.cover.show', $community);
        $this->actingAs($community->creator)->post(route('communities.cover.store', $community), [
            'cover' => UploadedFile::fake()->image('directory.png'),
        ])->assertRedirect();

        $outsider = User::factory()->create();
        $this->actingAs($outsider)->get($url)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->post(route('communities.cover.store', $community), ['cover' => UploadedFile::fake()->image('attack.png')])->assertNotFound();
        $community->forceFill(['status' => 'SUSPENDED'])->save();
        $this->get($url)->assertNotFound();
        $this->get('/communities')->assertDontSee($community->name);
    }
}
