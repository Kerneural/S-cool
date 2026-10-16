<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseCoverAuditRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $community = Community::factory()->create();
        $member = User::factory()->create();
        $membership = CommunityMembership::factory()->active()->create([
            'community_id' => $community->id, 'user_id' => $member->id,
        ]);
        $course = Course::factory()->published()->create([
            'community_id' => $community->id, 'access_mode' => Course::ACCESS_MODE_ONE_TIME,
            'price' => 200000, 'description' => 'PRIVATE-CATALOG-CONTENT',
        ]);

        return [$community, $member, $membership, $course];
    }

    public function test_catalog_access_queries_do_not_grow_per_card(): void
    {
        [$community, $member] = $this->fixture();
        $this->actingAs($member);
        $measure = function () use ($community): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->get(route('communities.classroom.index', $community))->assertOk()
                ->assertDontSee('PRIVATE-CATALOG-CONTENT');
            $queries = collect(DB::getQueryLog())->filter(fn ($entry) => str_contains($entry['query'], 'community_memberships')
                || str_contains($entry['query'], 'course_entitlements')
            )->count();
            DB::disableQueryLog();

            return $queries;
        };
        $oneCard = $measure();
        Course::factory()->count(8)->published()->create([
            'community_id' => $community->id, 'access_mode' => Course::ACCESS_MODE_ONE_TIME,
            'price' => 200000,
        ]);
        $this->assertLessThanOrEqual($oneCard + 1, $measure(), 'Catalog membership/entitlement queries must be batched, not repeated per card.');
    }

    public function test_failed_cover_persistence_cleans_staged_upload_and_keeps_previous_file(): void
    {
        Storage::fake('community_media');
        [$community, , , $course] = $this->fixture();
        $url = route('communities.courses.cover.store', [$community, $course]);
        $this->actingAs($community->creator)->post($url, [
            'cover' => UploadedFile::fake()->image('original.png', 800, 450),
        ])->assertRedirect();
        $oldPath = $course->fresh()->cover_path;
        Course::updating(function (): void {
            throw new \RuntimeException('Synthetic course cover database failure.');
        });
        try {
            $this->post($url, ['cover' => UploadedFile::fake()->image('replacement.png', 800, 450)])->assertServerError();
            $this->assertSame($oldPath, $course->fresh()->cover_path);
            $this->assertSame([$oldPath], Storage::disk('community_media')->allFiles());
        } finally {
            Course::flushEventListeners();
        }
    }

    public function test_locked_catalog_cover_is_allowed_but_draft_expired_and_wrong_tenant_are_denied(): void
    {
        Storage::fake('community_media');
        [$community, $member, $membership, $course] = $this->fixture();
        $this->actingAs($community->creator)->post(route('communities.courses.cover.store', [$community, $course]), [
            'cover' => UploadedFile::fake()->image('cover.png', 800, 450),
        ])->assertRedirect();
        $url = route('communities.courses.cover', [$community, $course]);
        $this->actingAs($member)->get($url)->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')->assertHeader('X-Content-Type-Options', 'nosniff');
        $course->update(['status' => 'DRAFT']);
        $this->get($url)->assertNotFound();
        $this->actingAs($community->creator)->get($url)->assertOk();
        $course->update(['status' => 'PUBLISHED']);
        $membership->forceFill(['status' => 'REMOVED'])->save();
        $this->actingAs($member)->get($url)->assertNotFound();
        $other = Community::factory()->create();
        $this->actingAs($other->creator)->get(route('communities.courses.cover', [$other, $course]))->assertNotFound();
    }

    public function test_forged_cover_reference_never_delivers_another_community_file(): void
    {
        Storage::fake('community_media');
        [$community, $member, , $course] = $this->fixture();
        $other = Community::factory()->create();
        $foreignPath = $other->id.'/courses/foreign.png';
        Storage::disk('community_media')->put($foreignPath, 'foreign-private-file');
        $course->update(['cover_path' => $foreignPath]);
        $this->actingAs($member)->get(route('communities.courses.cover', [$community, $course]))->assertNotFound();
        Storage::disk('community_media')->assertExists($foreignPath);
    }
}
