<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseEntitlement;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\LessonProofOfWork;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProofOfWorkAuditRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        Storage::fake('community_media');
        $creator = User::factory()->create();
        $member = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);
        $membership = CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);
        $course = Course::factory()->published()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->published()->create(['course_section_id' => $section->id, 'is_practical' => true, 'skill_label' => 'Network troubleshooting']);
        $proof = LessonProofOfWork::create([
            'community_id' => $community->id, 'course_id' => $course->id, 'lesson_id' => $lesson->id,
            'user_id' => $member->id, 'status' => LessonProofOfWork::STATUS_WAITING,
            'summary' => 'PRIVATE evidence from a learner', 'url' => 'https://example.com/result',
            'attachment_path' => "$community->id/proof-of-work/$member->id/retained.png", 'submitted_at' => now(),
        ]);
        Storage::disk('community_media')->put($proof->attachment_path, 'private evidence bytes');

        return [$community, $creator, $member, $course, $lesson, $proof, $membership];
    }

    public function test_private_media_and_review_deny_unpublished_or_unentitled_owner_context(): void
    {
        [$community, $creator, $member, $course, $lesson, $proof] = $this->fixture();
        $image = route('communities.proof-of-work.image', [$community, $proof]);
        $detail = route('communities.manage.proof-of-work.show', [$community, $proof]);
        $review = route('communities.manage.proof-of-work.review', [$community, $proof]);
        $queue = route('communities.manage.proof-of-work.index', [$community, 'status' => 'all']);

        $this->actingAs($member)->get($image)->assertOk();
        $lesson->update(['status' => 'DRAFT']);
        $this->get($image)->assertNotFound();
        $this->actingAs($creator)->get($image)->assertNotFound();
        $this->get($detail)->assertNotFound();
        $this->get($queue)->assertOk()->assertDontSee($proof->summary);
        $this->post($review, ['status' => 'VERIFIED'])->assertNotFound();
        $this->assertSame('WAITING', $proof->fresh()->status);

        $lesson->update(['status' => 'PUBLISHED']);
        $course->update(['access_mode' => 'ONE_TIME', 'price' => 200000]);
        $this->actingAs($member)->get($image)->assertNotFound();
        $this->actingAs($creator)->get($detail)->assertNotFound();
        $this->get($queue)->assertOk()->assertDontSee($proof->summary);
        $this->post($review, ['status' => 'VERIFIED'])->assertNotFound();
        CourseEntitlement::create(['community_id' => $community->id, 'course_id' => $course->id, 'user_id' => $member->id]);
        $this->get($detail)->assertOk()->assertSee($proof->summary);
        $this->actingAs($member)->get($image)->assertOk();
    }

    public function test_revoked_membership_hides_retained_evidence_and_queue_counts(): void
    {
        [$community, $creator, $member, , , $proof, $membership] = $this->fixture();
        $membership->forceFill(['status' => 'REMOVED'])->save();
        $this->actingAs($creator)->get(route('communities.manage.proof-of-work.index', $community))
            ->assertOk()->assertDontSee($proof->summary)->assertViewHas('counts', fn ($counts) => $counts['WAITING'] === 0);
        $this->get(route('communities.manage.proof-of-work.show', [$community, $proof]))->assertNotFound();
        $this->get(route('communities.proof-of-work.image', [$community, $proof]))->assertNotFound();
        $this->actingAs($member)->get(route('communities.proof-of-work.image', [$community, $proof]))->assertNotFound();
        $this->assertDatabaseHas('lesson_proof_of_works', ['id' => $proof->id, 'status' => 'WAITING']);
    }

    public function test_evidence_image_cannot_reference_another_learner_storage_directory(): void
    {
        [$community, $creator, $member, , , $proof] = $this->fixture();
        $other = User::factory()->create();
        $foreignPath = "$community->id/proof-of-work/$other->id/foreign.png";
        Storage::disk('community_media')->put($foreignPath, 'other private bytes');
        $proof->update(['attachment_path' => $foreignPath]);
        $this->actingAs($member)->get(route('communities.proof-of-work.image', [$community, $proof]))->assertNotFound();
        $this->actingAs($creator)->get(route('communities.proof-of-work.image', [$community, $proof]))->assertNotFound();
    }

    public function test_creator_filters_and_history_never_expose_unsent_private_drafts(): void
    {
        [$community, $creator, $member, $course, $lesson, $proof] = $this->fixture();
        $proof->update(['status' => 'DRAFT', 'summary' => 'UNSENT PRIVATE DRAFT']);
        $this->actingAs($creator)->get(route('communities.manage.proof-of-work.index', [$community, 'status' => 'all']))
            ->assertOk()->assertDontSee('UNSENT PRIVATE DRAFT');
        $this->get(route('communities.manage.proof-of-work.show', [$community, $proof]))->assertNotFound();
        $this->get(route('communities.proof-of-work.image', [$community, $proof]))->assertNotFound();
        $this->getJson(route('communities.manage.proof-of-work.index', [$community, 'status' => ['WAITING']]))->assertUnprocessable();
        $this->actingAs($member)->get(route('communities.proof-of-work.image', [$community, $proof]))->assertOk();
    }

    public function test_revision_drafts_preserve_reviewed_text_feedback_and_attachment_history(): void
    {
        [$community, $creator, $member, $course, $lesson, $proof] = $this->fixture();
        $proof->update(['status' => 'NEEDS_REVISION', 'feedback' => 'Add packet captures.', 'reviewer_id' => $creator->id, 'reviewed_at' => now()]);
        $oldPath = $proof->attachment_path;
        $store = route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]);
        $this->actingAs($member)->post($store, ['action' => 'draft', 'summary' => 'NEW PRIVATE REVISION', 'url' => 'https://example.com/revision'])->assertRedirect()->assertSessionHasNoErrors();
        $draft = LessonProofOfWork::query()->current()->firstOrFail();
        $this->assertSame(2, $draft->revision);
        $this->assertSame('DRAFT', $draft->status);
        $this->assertFalse($proof->fresh()->is_current);
        $this->assertSame('PRIVATE evidence from a learner', $proof->fresh()->summary);
        $this->assertSame('Add packet captures.', $proof->fresh()->feedback);
        $this->assertSame($oldPath, $draft->attachment_path);
        $this->actingAs($creator)->get(route('communities.manage.proof-of-work.show', [$community, $proof]))
            ->assertOk()->assertDontSee('NEW PRIVATE REVISION');

        $this->actingAs($member)->post($store, ['action' => 'draft', 'summary' => '', 'url' => '', 'attachment' => UploadedFile::fake()->image('replacement.png')])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertNull($draft->fresh()->url);
        $this->assertNull($draft->fresh()->summary);
        Storage::disk('community_media')->assertExists($oldPath);
        Storage::disk('community_media')->assertExists($draft->fresh()->attachment_path);
        $this->post($store, ['action' => 'submit', 'summary' => 'Updated packet capture evidence', 'url' => 'https://example.com/revision'])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('WAITING', $draft->fresh()->status);
        $this->assertSame(1, LessonProofOfWork::query()->current()->count());
        $this->assertSame(2, LessonProofOfWork::count());
    }

    public function test_verification_requires_current_nonempty_skill_label_and_preserves_progress(): void
    {
        [$community, $creator, , , $lesson, $proof] = $this->fixture();
        $lesson->update(['skill_label' => '  ']);
        $this->actingAs($creator)->post(route('communities.manage.proof-of-work.review', [$community, $proof]), ['status' => 'VERIFIED'])
            ->assertSessionHasErrors('status');
        $this->assertSame('WAITING', $proof->fresh()->status);
        $this->assertNull($proof->fresh()->skill_label_snapshot);
        $this->assertDatabaseCount('lesson_progresses', 0);
    }

    public function test_lesson_access_is_rechecked_inside_submission_transaction_before_file_storage(): void
    {
        [$community, , $member, $course, $lesson, $proof] = $this->fixture();
        $proof->delete();
        Storage::disk('community_media')->deleteDirectory((string) $community->id);
        $fired = false;
        DB::connection()->beforeExecuting(function ($sql) use (&$fired, $lesson): void {
            if (! $fired && str_contains(strtolower($sql), 'from `communities`') && str_contains(strtolower($sql), 'for update')) {
                $fired = true;
                DB::table('lessons')->where('id', $lesson->id)->update(['status' => 'DRAFT']);
            }
        });
        $this->actingAs($member)->post(route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]), [
            'summary' => 'Evidence after stale route preflight', 'url' => 'https://example.com/result', 'attachment' => UploadedFile::fake()->image('proof.png'),
        ])->assertNotFound();
        $this->assertTrue($fired);
        $this->assertDatabaseCount('lesson_proof_of_works', 0);
        $this->assertSame([], Storage::disk('community_media')->allFiles());
    }

    public function test_creator_ownership_is_rechecked_under_parent_lock(): void
    {
        [$community, $creator, , , , $proof] = $this->fixture();
        $newCreator = User::factory()->create();
        $fired = false;
        DB::connection()->beforeExecuting(function ($sql) use (&$fired, $community, $newCreator): void {
            if (! $fired && str_contains(strtolower($sql), 'from `communities`') && str_contains(strtolower($sql), 'for update')) {
                $fired = true;
                DB::table('communities')->where('id', $community->id)->update(['creator_id' => $newCreator->id]);
            }
        });
        $this->actingAs($creator)->post(route('communities.manage.proof-of-work.review', [$community, $proof]), ['status' => 'VERIFIED'])->assertNotFound();
        $this->assertTrue($fired);
        $this->assertSame('WAITING', $proof->fresh()->status);
    }

    public function test_database_rejects_duplicate_current_submission_for_same_lesson_and_owner(): void
    {
        [, , , , , $proof] = $this->fixture();
        try {
            LessonProofOfWork::create([
                'community_id' => $proof->community_id, 'course_id' => $proof->course_id,
                'lesson_id' => $proof->lesson_id, 'user_id' => $proof->user_id,
                'revision' => 2, 'is_current' => true,
            ]);
            $this->fail('Duplicate current work must be rejected by the database.');
        } catch (QueryException $exception) {
            $this->assertSame(1062, $exception->errorInfo[1]);
            $this->assertStringContainsString('pow_one_current_per_learner', $exception->getMessage());
        }
    }

    public function test_invalid_review_preserves_the_requested_decision_for_retry(): void
    {
        [$community, $creator, , , , $proof] = $this->fixture();
        $detail = route('communities.manage.proof-of-work.show', [$community, $proof]);
        $this->actingAs($creator)->from($detail)->post(route('communities.manage.proof-of-work.review', [$community, $proof]), [
            'status' => 'REJECTED', 'feedback' => '',
        ])->assertRedirect($detail)->assertSessionHasErrors('feedback');
        $html = $this->get($detail)->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/name="status" value="REJECTED"[^>]*checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/name="status" value="VERIFIED"[^>]*checked/', $html);
        $this->assertSame('WAITING', $proof->fresh()->status);
    }

    public function test_failed_submission_insert_rolls_back_and_cleans_staged_image(): void
    {
        [$community, , $member, $course, $lesson, $proof] = $this->fixture();
        $proof->delete();
        Storage::disk('community_media')->deleteDirectory((string) $community->id);
        LessonProofOfWork::creating(function (): void {
            throw new \RuntimeException('Simulated evidence persistence failure.');
        });
        $this->withoutExceptionHandling();
        try {
            $this->actingAs($member)->post(route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]), [
                'summary' => 'Evidence for rollback test', 'url' => 'https://example.com/result',
                'attachment' => UploadedFile::fake()->image('staged.png'),
            ]);
            $this->fail('Expected simulated persistence failure.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated evidence persistence failure.', $exception->getMessage());
        }
        $this->assertDatabaseCount('lesson_proof_of_works', 0);
        $this->assertSame([], Storage::disk('community_media')->allFiles());
    }

    public function test_unknown_actions_and_url_controls_cannot_silently_submit_work(): void
    {
        [$community, , $member, $course, $lesson, $proof] = $this->fixture();
        $proof->delete();
        $store = route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]);
        $payload = ['summary' => 'Valid submission summary', 'url' => 'https://example.com/result'];
        $this->actingAs($member)->post($store, ['action' => 'verify'] + $payload)->assertSessionHasErrors('action');
        $this->post($store, ['action' => 'draft', 'url' => "https://example.com/res\nult"] + $payload)->assertSessionHasErrors('url');
        $this->assertDatabaseCount('lesson_proof_of_works', 0);
    }
}
