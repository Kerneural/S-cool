<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\User;
use Database\Seeders\RealCommunitySampleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunitySampleSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_reseeding_preserves_existing_content_and_revoked_access(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(RealCommunitySampleSeeder::class);
        $community = Community::where('slug', 'python-developers')->firstOrFail();
        $member = User::where('email', 'member@scool.local')->firstOrFail();
        $community->forceFill(['description' => 'Retained local edits', 'status' => 'SUSPENDED'])->save();
        $membership = $community->memberships()->where('user_id', $member->id)->firstOrFail();
        $membership->forceFill(['status' => 'SUSPENDED'])->save();
        $course = $community->courses()->firstOrFail();
        $course->forceFill(['description' => 'Retained course edits', 'status' => 'DRAFT'])->save();
        $count = Community::count();

        $this->seed(RealCommunitySampleSeeder::class);

        $this->assertSame($count, Community::count());
        $this->assertSame('Retained local edits', $community->fresh()->description);
        $this->assertSame('SUSPENDED', $community->fresh()->status);
        $this->assertSame('SUSPENDED', $membership->fresh()->status);
        $this->assertSame('Retained course edits', $course->fresh()->description);
        $this->assertSame('DRAFT', $course->fresh()->status);
    }

    public function test_matching_slug_never_overwrites_an_unrelated_existing_community(): void
    {
        $this->seed(UserSeeder::class);
        $creator = User::where('email', 'creator@scool.local')->firstOrFail();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'slug' => 'python-developers',
            'name' => 'Existing private workspace',
        ]);

        $this->seed(RealCommunitySampleSeeder::class);

        $this->assertSame('Existing private workspace', $community->fresh()->name);
        $this->assertSame(0, $community->memberships()->count());
        $this->assertSame(0, $community->courses()->count());
    }
}
