<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\LessonProofOfWork;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProofOfWorkUiFollowupTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        Storage::fake('community_media');
        $creator = User::factory()->create();
        $member = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);
        CommunityMembership::factory()->active()->create(['community_id' => $community->id, 'user_id' => $member->id]);
        $course = Course::factory()->published()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->published()->create([
            'course_section_id' => $section->id, 'is_practical' => true, 'skill_label' => 'DNS troubleshooting',
        ]);
        $proof = LessonProofOfWork::create([
            'community_id' => $community->id, 'course_id' => $course->id, 'lesson_id' => $lesson->id,
            'user_id' => $member->id, 'status' => LessonProofOfWork::STATUS_DRAFT,
            'summary' => 'Retained learner draft', 'url' => 'https://example.com/result',
        ]);

        return [$community, $creator, $member, $course, $lesson, $proof];
    }

    public function test_learner_validation_retry_handles_array_inputs_and_retains_valid_scalar_input(): void
    {
        [$community, , $member, $course, $lesson, $proof] = $this->fixture();
        $lessonUrl = route('communities.lessons.show', [$community, $course, $lesson]);
        $store = route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]);
        $this->actingAs($member);

        foreach (['summary', 'url'] as $invalidField) {
            $payload = ['action' => 'submit', 'summary' => 'Valid retained summary', 'url' => 'https://example.com/retained'];
            $payload[$invalidField] = ['malformed'];
            $this->from($lessonUrl)->post($store, $payload)->assertRedirect($lessonUrl)->assertSessionHasErrors($invalidField);
            $response = $this->get($lessonUrl)->assertOk()->assertSee('The '.$invalidField.' field must be a string.');
            $response->assertSee($invalidField === 'summary' ? 'https://example.com/retained' : 'Valid retained summary');
        }

        $this->from($lessonUrl)->post($store, [
            'action' => 'submit', 'summary' => 'Keep this scalar draft', 'url' => 'http://example.com/invalid',
        ])->assertRedirect($lessonUrl)->assertSessionHasErrors('url');
        $this->get($lessonUrl)->assertOk()->assertSee('Keep this scalar draft')->assertSee('http://example.com/invalid');
        $this->assertSame('Retained learner draft', $proof->fresh()->summary);
        $this->assertSame(LessonProofOfWork::STATUS_DRAFT, $proof->fresh()->status);
    }

    public function test_creator_validation_retry_handles_array_feedback_without_mutating_review(): void
    {
        [$community, $creator, , , , $proof] = $this->fixture();
        $proof->update(['status' => LessonProofOfWork::STATUS_WAITING, 'submitted_at' => now()]);
        $detail = route('communities.manage.proof-of-work.show', [$community, $proof]);
        $review = route('communities.manage.proof-of-work.review', [$community, $proof]);

        $this->actingAs($creator)->from($detail)->post($review, [
            'status' => LessonProofOfWork::STATUS_NEEDS_REVISION, 'feedback' => ['malformed'],
        ])->assertRedirect($detail)->assertSessionHasErrors('feedback');
        $this->get($detail)->assertOk()->assertSee('The feedback field must be a string.')
            ->assertSee('value="NEEDS_REVISION" x-model="selectedStatus" checked', false);
        $this->assertSame(LessonProofOfWork::STATUS_WAITING, $proof->fresh()->status);
        $this->assertNull($proof->fresh()->reviewed_at);
    }

    public function test_portrait_evidence_has_uncropped_preview_and_authorized_original_link_for_both_roles(): void
    {
        [$community, $creator, $member, $course, $lesson, $proof] = $this->fixture();
        $path = Storage::disk('community_media')->putFile(
            "$community->id/proof-of-work/$member->id",
            UploadedFile::fake()->image('portrait.png', 800, 1600)
        );
        $proof->update(['status' => LessonProofOfWork::STATUS_WAITING, 'submitted_at' => now(), 'attachment_path' => $path]);
        $image = route('communities.proof-of-work.image', [$community, $proof]);

        foreach ([
            [$member, route('communities.lessons.show', [$community, $course, $lesson])],
            [$creator, route('communities.manage.proof-of-work.show', [$community, $proof])],
        ] as [$viewer, $page]) {
            $this->actingAs($viewer)->get($page)->assertOk()
                ->assertSee('class="w-full h-full object-contain"', false)
                ->assertSee('href="'.$image.'" target="_blank" rel="noopener noreferrer"', false)
                ->assertSee('Open original evidence image')->assertDontSee($path);
            $this->get($image)->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        }

        $this->actingAs(User::factory()->create())->get($image)->assertNotFound();
    }
}
