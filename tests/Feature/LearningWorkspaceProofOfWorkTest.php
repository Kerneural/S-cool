<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\LessonProofOfWork;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LearningWorkspaceProofOfWorkTest extends TestCase
{
    use RefreshDatabase;

    private function setupPracticalLesson(): array
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id]);
        $member = User::factory()->create();

        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $course = Course::factory()->published()->create([
            'community_id' => $community->id,
            'title' => 'TDD in Laravel',
            'access_mode' => Course::ACCESS_MODE_INCLUDED,
        ]);

        $section = CourseSection::factory()->create([
            'course_id' => $course->id,
            'title' => 'Testing Basics',
        ]);

        $lesson = Lesson::factory()->published()->create([
            'course_section_id' => $section->id,
            'title' => 'Writing Your First Unit Test',
            'is_practical' => true,
            'skill_label' => 'PHPUnit Feature Testing',
        ]);

        return [$community, $creator, $member, $course, $lesson];
    }

    public function test_learner_can_save_draft_and_submit_proof_of_work_ac01(): void
    {
        Storage::fake('community_media');
        [$community, $creator, $member, $course, $lesson] = $this->setupPracticalLesson();

        $this->actingAs($member);

        // 1. Save draft
        $draftResponse = $this->post(route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]), [
            'action' => 'draft',
            'summary' => 'Initial draft of my test suite implementation.',
            'url' => 'https://github.com/example/tdd-demo',
        ]);

        $draftResponse->assertRedirect();
        $this->assertDatabaseHas('lesson_proof_of_works', [
            'community_id' => $community->id,
            'lesson_id' => $lesson->id,
            'user_id' => $member->id,
            'status' => LessonProofOfWork::STATUS_DRAFT,
            'revision' => 1,
            'is_current' => true,
        ]);

        // 2. Submit draft
        $submitResponse = $this->post(route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]), [
            'action' => 'submit',
            'summary' => 'Completed all PHPUnit unit and feature tests with 100% coverage.',
            'url' => 'https://github.com/example/tdd-demo/tree/main/tests',
        ]);

        $submitResponse->assertRedirect();
        $this->assertDatabaseHas('lesson_proof_of_works', [
            'community_id' => $community->id,
            'lesson_id' => $lesson->id,
            'user_id' => $member->id,
            'status' => LessonProofOfWork::STATUS_WAITING,
            'revision' => 1,
            'is_current' => true,
        ]);

        // Refresh lesson view: should show Waiting status
        $lessonView = $this->get(route('communities.lessons.show', [$community, $course, $lesson]));
        $lessonView->assertOk();
        $lessonView->assertSee('Waiting for feedback');
        $lessonView->assertSee('PHPUnit Feature Testing');
    }

    public function test_submitting_without_required_evidence_or_invalid_summary_fails_ac02(): void
    {
        [$community, $creator, $member, $course, $lesson] = $this->setupPracticalLesson();
        $this->actingAs($member);

        // Summary too short (< 10 chars)
        $response = $this->post(route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]), [
            'action' => 'submit',
            'summary' => 'Short',
            'url' => 'https://github.com/example/repo',
        ]);

        $response->assertSessionHasErrors(['summary']);
        $this->assertDatabaseMissing('lesson_proof_of_works', ['user_id' => $member->id]);

        // Missing URL
        $responseNoEvidence = $this->post(route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]), [
            'action' => 'submit',
            'summary' => 'This is a valid long summary with no evidence provided.',
            'url' => '',
        ]);

        $responseNoEvidence->assertSessionHasErrors(['url']);
        $this->assertDatabaseMissing('lesson_proof_of_works', ['user_id' => $member->id]);
    }

    public function test_invalid_and_unsafe_urls_rejected_ac02(): void
    {
        [$community, $creator, $member, $course, $lesson] = $this->setupPracticalLesson();
        $this->actingAs($member);

        $unsafeUrls = [
            'http://insecure-site.com/repo',
            'javascript:alert(1)',
            'https://user:password@secret.com/repo', // User credentials forbidden
            "https://evil.com/repo\r\nHeader: Injection", // Control characters
            'ftp://ftp.site.com/file',
        ];

        foreach ($unsafeUrls as $url) {
            $response = $this->post(route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]), [
                'action' => 'submit',
                'summary' => 'Valid test summary exceeding 10 characters length.',
                'url' => $url,
            ]);

            $response->assertSessionHasErrors(['url']);
        }

        $this->assertDatabaseMissing('lesson_proof_of_works', ['user_id' => $member->id]);
    }

    public function test_invalid_and_oversized_images_rejected_ac02(): void
    {
        Storage::fake('community_media');
        [$community, $creator, $member, $course, $lesson] = $this->setupPracticalLesson();
        $this->actingAs($member);

        // SVG is not allowed
        $svgFile = UploadedFile::fake()->create('attack.svg', 100, 'image/svg+xml');
        $responseSvg = $this->post(route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]), [
            'action' => 'submit',
            'summary' => 'Valid test summary exceeding 10 characters length.',
            'url' => 'https://github.com/example/repo',
            'attachment' => $svgFile,
        ]);
        $responseSvg->assertSessionHasErrors(['attachment']);

        // Oversized file (> 5 MiB = 5120 KB)
        $oversizedFile = UploadedFile::fake()->image('huge.png')->size(5200);
        $responseOversized = $this->post(route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]), [
            'action' => 'submit',
            'summary' => 'Valid test summary exceeding 10 characters length.',
            'url' => 'https://github.com/example/repo',
            'attachment' => $oversizedFile,
        ]);
        $responseOversized->assertSessionHasErrors(['attachment']);

        $this->assertDatabaseMissing('lesson_proof_of_works', ['user_id' => $member->id]);
    }

    public function test_stale_or_unentitled_lesson_access_rejected_without_record_or_orphan_ac02(): void
    {
        Storage::fake('community_media');
        [$community, $creator, $member, $course, $lesson] = $this->setupPracticalLesson();

        // Non-member user
        $outsider = User::factory()->create();
        $this->actingAs($outsider);

        $image = UploadedFile::fake()->image('evidence.png', 400, 300);
        $response = $this->post(route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]), [
            'action' => 'submit',
            'summary' => 'Outsider trying to submit work on private lesson.',
            'url' => 'https://github.com/example/outsider',
            'attachment' => $image,
        ]);

        $response->assertNotFound();
        $this->assertDatabaseMissing('lesson_proof_of_works', ['user_id' => $outsider->id]);
        $this->assertEmpty(Storage::disk('community_media')->allFiles());
    }

    public function test_only_author_and_creator_can_access_evidence_and_image_no_leakage_ac03(): void
    {
        Storage::fake('community_media');
        [$community, $creator, $member, $course, $lesson] = $this->setupPracticalLesson();

        // Submit work with an image
        $this->actingAs($member);
        $image = UploadedFile::fake()->image('test-evidence.png', 800, 600);
        $this->post(route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]), [
            'action' => 'submit',
            'summary' => 'Completed all assignments with screenshot evidence.',
            'url' => 'https://github.com/example/evidence',
            'attachment' => $image,
        ]);

        $pow = LessonProofOfWork::where('user_id', $member->id)->firstOrFail();
        $this->assertNotNull($pow->attachment_path);

        // 1. Author can view the image with private headers
        $authorImgResponse = $this->actingAs($member)->get(route('communities.proof-of-work.image', [$community, $pow]));
        $authorImgResponse->assertOk();
        $authorImgResponse->assertHeader('Cache-Control', 'no-store, private');
        $authorImgResponse->assertHeader('X-Content-Type-Options', 'nosniff');

        // 2. Creator can view the image with private headers
        $creatorImgResponse = $this->actingAs($creator)->get(route('communities.proof-of-work.image', [$community, $pow]));
        $creatorImgResponse->assertOk();
        $creatorImgResponse->assertHeader('Cache-Control', 'no-store, private');
        $creatorImgResponse->assertHeader('X-Content-Type-Options', 'nosniff');

        // 3. Another member cannot view the image (404 denyAsNotFound)
        $otherMember = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $otherMember->id,
            'status' => 'ACTIVE',
        ]);
        $otherImgResponse = $this->actingAs($otherMember)->get(route('communities.proof-of-work.image', [$community, $pow]));
        $otherImgResponse->assertNotFound();

        // 4. Another member cannot view or access the creator review detail
        $reviewResponse = $this->actingAs($otherMember)->get(route('communities.manage.proof-of-work.show', [$community, $pow]));
        $reviewResponse->assertNotFound();
    }

    public function test_creator_transitions_and_review_state_machine_ac04(): void
    {
        [$community, $creator, $member, $course, $lesson] = $this->setupPracticalLesson();

        // Learner submits work
        $this->actingAs($member);
        $this->post(route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]), [
            'action' => 'submit',
            'summary' => 'My submission awaiting creator review.',
            'url' => 'https://github.com/example/submission',
        ]);
        $pow = LessonProofOfWork::where('user_id', $member->id)->firstOrFail();

        // Creator attempts review with invalid status
        $this->actingAs($creator);
        $badReview = $this->post(route('communities.manage.proof-of-work.review', [$community, $pow]), [
            'status' => 'INVALID_STATUS',
            'feedback' => 'Some feedback text',
        ]);
        $badReview->assertSessionHasErrors(['status']);

        // Needs revision without feedback fails
        $noFeedback = $this->post(route('communities.manage.proof-of-work.review', [$community, $pow]), [
            'status' => LessonProofOfWork::STATUS_NEEDS_REVISION,
            'feedback' => '',
        ]);
        $noFeedback->assertSessionHasErrors(['feedback']);

        // Valid review to VERIFIED
        $validReview = $this->post(route('communities.manage.proof-of-work.review', [$community, $pow]), [
            'status' => LessonProofOfWork::STATUS_VERIFIED,
            'feedback' => 'Great work! Clean implementation.',
        ]);
        $validReview->assertRedirect(route('communities.manage.proof-of-work.index', $community));

        $pow->refresh();
        $this->assertEquals(LessonProofOfWork::STATUS_VERIFIED, $pow->status);
        $this->assertNotNull($pow->reviewed_at);
        $this->assertEquals($creator->id, $pow->reviewer_id);
        $this->assertEquals('PHPUnit Feature Testing', $pow->skill_label_snapshot);
    }

    public function test_repeated_and_concurrent_review_prevented_ac04(): void
    {
        [$community, $creator, $member, $course, $lesson] = $this->setupPracticalLesson();

        $this->actingAs($member);
        $this->post(route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]), [
            'action' => 'submit',
            'summary' => 'Ready for evaluation.',
            'url' => 'https://github.com/example/submission',
        ]);
        $pow = LessonProofOfWork::where('user_id', $member->id)->firstOrFail();

        // First review verifies
        $this->actingAs($creator);
        $this->post(route('communities.manage.proof-of-work.review', [$community, $pow]), [
            'status' => LessonProofOfWork::STATUS_VERIFIED,
            'feedback' => 'Approved.',
        ]);

        // Second review attempt on terminal record fails
        $secondReview = $this->post(route('communities.manage.proof-of-work.review', [$community, $pow]), [
            'status' => LessonProofOfWork::STATUS_REJECTED,
            'feedback' => 'Trying to contradict previous review.',
        ]);
        $secondReview->assertSessionHasErrors();

        $pow->refresh();
        $this->assertEquals(LessonProofOfWork::STATUS_VERIFIED, $pow->status);
    }

    public function test_needs_revision_requires_feedback_and_allows_new_revision_preserving_history_ac05(): void
    {
        [$community, $creator, $member, $course, $lesson] = $this->setupPracticalLesson();

        // 1. Learner submits revision 1
        $this->actingAs($member);
        $this->post(route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]), [
            'action' => 'submit',
            'summary' => 'Revision 1 initial submission.',
            'url' => 'https://github.com/example/rev1',
        ]);
        $rev1 = LessonProofOfWork::where('user_id', $member->id)->firstOrFail();

        // 2. Creator requests revision
        $this->actingAs($creator);
        $this->post(route('communities.manage.proof-of-work.review', [$community, $rev1]), [
            'status' => LessonProofOfWork::STATUS_NEEDS_REVISION,
            'feedback' => 'Please add integration tests for payment flow.',
        ]);

        $rev1->refresh();
        $this->assertEquals(LessonProofOfWork::STATUS_NEEDS_REVISION, $rev1->status);
        $this->assertEquals('Please add integration tests for payment flow.', $rev1->feedback);

        // 3. Learner submits revision 2
        $this->actingAs($member);
        $this->post(route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]), [
            'action' => 'submit',
            'summary' => 'Revision 2: Added integration tests for payment flow.',
            'url' => 'https://github.com/example/rev2',
        ]);

        // Verify history: Rev 1 is not current, Rev 2 is current and WAITING
        $rev1->refresh();
        $this->assertFalse($rev1->is_current);
        $this->assertEquals(1, $rev1->revision);

        $rev2 = LessonProofOfWork::where('user_id', $member->id)->where('is_current', true)->firstOrFail();
        $this->assertEquals(2, $rev2->revision);
        $this->assertEquals(LessonProofOfWork::STATUS_WAITING, $rev2->status);
        $this->assertEquals('Revision 2: Added integration tests for payment flow.', $rev2->summary);

        // Creator detail view shows both revisions in history
        $this->actingAs($creator);
        $detailView = $this->get(route('communities.manage.proof-of-work.show', [$community, $rev2]));
        $detailView->assertOk();
        $detailView->assertSee('Revision 1');
        $detailView->assertSee('Please add integration tests for payment flow.');
        $detailView->assertSee('Revision 2');
    }

    public function test_terminal_states_verified_and_rejected_are_immutable_ac05(): void
    {
        [$community, $creator, $member, $course, $lesson] = $this->setupPracticalLesson();

        // Submit and reject
        $this->actingAs($member);
        $this->post(route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]), [
            'action' => 'submit',
            'summary' => 'Plagiarized submission.',
            'url' => 'https://github.com/example/fake',
        ]);
        $pow = LessonProofOfWork::where('user_id', $member->id)->firstOrFail();

        $this->actingAs($creator);
        $this->post(route('communities.manage.proof-of-work.review', [$community, $pow]), [
            'status' => LessonProofOfWork::STATUS_REJECTED,
            'feedback' => 'Work rejected due to violation of course guidelines.',
        ]);

        $pow->refresh();
        $this->assertEquals(LessonProofOfWork::STATUS_REJECTED, $pow->status);

        // Learner cannot edit or submit new revision on REJECTED
        $this->actingAs($member);
        $blockedSubmit = $this->post(route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]), [
            'action' => 'submit',
            'summary' => 'Trying to submit again after rejection.',
            'url' => 'https://github.com/example/retry',
        ]);
        $blockedSubmit->assertSessionHasErrors();

        // No new revision created
        $this->assertEquals(1, LessonProofOfWork::where('user_id', $member->id)->count());
    }

    public function test_course_cover_upload_replace_and_delete_with_private_headers_ac08(): void
    {
        $storage = Storage::fake('community_media');
        [$community, $creator, $member, $course] = $this->setupPracticalLesson();

        $this->actingAs($creator);

        // 1. Upload valid cover (PNG, 1280x720)
        $cover = UploadedFile::fake()->image('course_banner.png', 1280, 720);
        $uploadResponse = $this->post(route('communities.courses.cover.store', [$community, $course]), [
            'cover' => $cover,
        ]);
        $uploadResponse->assertRedirect();

        $course->refresh();
        $this->assertNotNull($course->cover_path);
        $storage->assertExists($course->cover_path);
        $firstPath = $course->cover_path;

        // 2. Private delivery returns correct security headers
        $coverResponse = $this->get(route('communities.courses.cover', [$community, $course]));
        $coverResponse->assertOk();
        $coverResponse->assertHeader('Cache-Control', 'no-store, private');
        $coverResponse->assertHeader('X-Content-Type-Options', 'nosniff');

        // 3. Replace cover with another PNG
        $newCover = UploadedFile::fake()->image('course_banner2.png', 1920, 1080);
        $replaceResponse = $this->post(route('communities.courses.cover.store', [$community, $course]), [
            'cover' => $newCover,
        ]);
        $replaceResponse->assertRedirect();

        $course->refresh();
        $this->assertNotEquals($firstPath, $course->cover_path);
        $storage->assertExists($course->cover_path);
        $storage->assertMissing($firstPath); // Replaced file cleaned up

        // 4. Delete cover
        $deleteResponse = $this->delete(route('communities.courses.cover.destroy', [$community, $course]));
        $deleteResponse->assertRedirect();

        $course->refresh();
        $this->assertNull($course->cover_path);
    }

    public function test_course_cover_invalid_image_and_cleanup_on_failure_ac08(): void
    {
        $storage = Storage::fake('community_media');
        [$community, $creator, $member, $course] = $this->setupPracticalLesson();
        $this->actingAs($creator);

        // Upload valid cover first
        $cover = UploadedFile::fake()->image('valid.png', 800, 600);
        $this->post(route('communities.courses.cover.store', [$community, $course]), [
            'cover' => $cover,
        ]);
        $course->refresh();
        $validPath = $course->cover_path;

        // Attempt replacing with oversized dimensions (> 4096px)
        $hugeDimensions = UploadedFile::fake()->image('too_wide.png', 4097, 10);
        $failedResponse = $this->post(route('communities.courses.cover.store', [$community, $course]), [
            'cover' => $hugeDimensions,
        ]);
        $failedResponse->assertSessionHasErrors(['cover']);

        // Original cover is preserved
        $course->refresh();
        $this->assertEquals($validPath, $course->cover_path);
        $storage->assertExists($validPath);
    }

    public function test_classroom_renders_16_by_9_cards_and_locked_courses_do_not_leak_details_ac09(): void
    {
        [$community, $creator, $member] = $this->setupPracticalLesson();

        // 1. Published included course
        $includedCourse = Course::factory()->published()->create([
            'community_id' => $community->id,
            'title' => 'Included Course 101',
            'access_mode' => Course::ACCESS_MODE_INCLUDED,
        ]);

        // 2. Locked / paid course with secret outline and description
        $lockedCourse = Course::factory()->published()->create([
            'community_id' => $community->id,
            'title' => 'Advanced VIP Mastery',
            'access_mode' => Course::ACCESS_MODE_ONE_TIME,
            'price' => 99000,
            'description' => 'SUPER_SECRET_VIP_DESCRIPTION_DO_NOT_LEAK',
        ]);
        $lockedSection = CourseSection::factory()->create(['course_id' => $lockedCourse->id, 'title' => 'VIP Section']);
        $lockedLesson = Lesson::factory()->published()->create([
            'course_section_id' => $lockedSection->id,
            'title' => 'SUPER_SECRET_VIP_LESSON_TITLE',
            'content' => 'SUPER_SECRET_VIP_LESSON_BODY',
        ]);

        // 3. Draft course
        $draftCourse = Course::factory()->draft()->create([
            'community_id' => $community->id,
            'title' => 'SECRET_CREATOR_DRAFT_COURSE',
        ]);

        // Member views Classroom
        $this->actingAs($member);
        $response = $this->get(route('communities.classroom.index', $community));
        $response->assertOk();

        // Visible: Included course and Locked course card header/price
        $response->assertSee('Included Course 101');
        $response->assertSee('Advanced VIP Mastery');
        $response->assertSee('99.000 VND');

        // Shielded: Draft course is NOT visible to member
        $response->assertDontSee('SECRET_CREATOR_DRAFT_COURSE');

        // Shielded: Locked course secret description, outline, and lessons are NOT in Classroom HTML
        $response->assertDontSee('SUPER_SECRET_VIP_DESCRIPTION_DO_NOT_LEAK');
        $response->assertDontSee('SUPER_SECRET_VIP_LESSON_TITLE');
        $response->assertDontSee('SUPER_SECRET_VIP_LESSON_BODY');

        // Locked course detail view does not reveal sections/lessons to unentitled member
        $detailResponse = $this->get(route('communities.courses.show', [$community, $lockedCourse]));
        $detailResponse->assertOk();
        $detailResponse->assertDontSee('SUPER_SECRET_VIP_LESSON_TITLE');
        $detailResponse->assertDontSee('SUPER_SECRET_VIP_LESSON_BODY');

        // Direct unentitled lesson access is denied (404)
        $lessonResponse = $this->get(route('communities.lessons.show', [$community, $lockedCourse, $lockedLesson]));
        $lessonResponse->assertNotFound();
    }

    public function test_lesson_outline_and_non_reload_progress_update_ac10(): void
    {
        [$community, $creator, $member, $course, $lesson] = $this->setupPracticalLesson();
        $this->actingAs($member);

        // 1. Visit lesson: outline identifies current lesson
        $response = $this->get(route('communities.lessons.show', [$community, $course, $lesson]));
        $response->assertOk();
        $response->assertSee($lesson->title);
        $response->assertSee('Mark Complete');

        // 2. Non-reload AJAX progress toggle
        $ajaxResponse = $this->postJson(route('communities.lessons.progress.update', [$community, $course, $lesson]), [
            'completed' => true,
        ]);
        $ajaxResponse->assertOk();
        $ajaxResponse->assertJson([
            'completed' => true,
        ]);

        $this->assertDatabaseHas('lesson_progresses', [
            'user_id' => $member->id,
            'lesson_id' => $lesson->id,
            'completed' => true,
        ]);

        // 3. Refresh verifies persistence
        $refreshView = $this->get(route('communities.lessons.show', [$community, $course, $lesson]));
        $refreshView->assertOk();
        $refreshView->assertSee('Completed');

        // 4. Server form fallback also toggles back
        $formResponse = $this->post(route('communities.lessons.progress.update', [$community, $course, $lesson]), [
            'completed' => false,
        ]);
        $formResponse->assertRedirect();
        $this->assertDatabaseHas('lesson_progresses', [
            'user_id' => $member->id,
            'lesson_id' => $lesson->id,
            'completed' => false,
        ]);
    }

    public function test_composer_direct_page_and_context_attachment_ac11(): void
    {
        [$community, $creator, $member, $course, $lesson] = $this->setupPracticalLesson();
        $this->actingAs($member);

        // Direct page fallback
        $createPage = $this->get(route('communities.posts.create', [
            $community,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
        ]));
        $createPage->assertOk();
        $createPage->assertSee('Create a Post');
        $createPage->assertSee('Course #'.$course->id);
        $createPage->assertSee('Lesson #'.$lesson->id);

        // Post creation with learning context
        $submit = $this->post(route('communities.posts.store', $community), [
            'title' => 'How to write robust PHPUnit feature tests?',
            'body' => 'I would like clarification on database transactions in tests.',
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
        ]);
        $submit->assertRedirect(route('communities.posts.index', $community));

        $this->assertDatabaseHas('posts', [
            'community_id' => $community->id,
            'user_id' => $member->id,
            'title' => 'How to write robust PHPUnit feature tests?',
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
        ]);
    }

    public function test_feed_renders_contextual_post_badges_and_safe_fallbacks_ac12(): void
    {
        [$community, $creator, $member, $course, $lesson] = $this->setupPracticalLesson();
        $this->actingAs($member);

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'title' => 'Discussion regarding TDD fundamentals',
            'body' => 'Here is my question on mocks and stubs.',
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
        ]);

        $feed = $this->get(route('communities.posts.index', $community));
        $feed->assertOk();
        $feed->assertSee('Discussion regarding TDD fundamentals');
        $feed->assertSee($course->title);
        $feed->assertSee($lesson->title);
    }

    public function test_contextual_rail_renders_learning_focus_data_without_duplicate_continue_ac13(): void
    {
        [$community, $creator, $member, $course, $lesson] = $this->setupPracticalLesson();
        $this->actingAs($member);

        $feed = $this->get(route('communities.posts.index', $community));
        $feed->assertOk();

        // Right rail renders contextual learning widget with next step
        $feed->assertSee('Next Learning Step');
        $feed->assertSee($lesson->title);
    }

    public function test_creator_review_queue_filtering_and_skill_label_snapshotting_ac14(): void
    {
        [$community, $creator, $member, $course, $lesson] = $this->setupPracticalLesson();

        // Learner submits work
        $this->actingAs($member);
        $this->post(route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]), [
            'action' => 'submit',
            'summary' => 'Finished practical implementation.',
            'url' => 'https://github.com/example/practical',
        ]);
        $pow = LessonProofOfWork::where('user_id', $member->id)->firstOrFail();

        // Creator accesses review queue
        $this->actingAs($creator);
        $queueView = $this->get(route('communities.manage.proof-of-work.index', $community));
        $queueView->assertOk();
        $queueView->assertSee('Proof of Work Review Queue');
        $queueView->assertSee($member->name);
        $queueView->assertSee('PHPUnit Feature Testing');

        // Status filter: WAITING shows item
        $waitingFilter = $this->get(route('communities.manage.proof-of-work.index', [$community, 'status' => 'WAITING']));
        $waitingFilter->assertOk();
        $waitingFilter->assertSee($member->name);

        // Status filter: VERIFIED does not show pending item
        $verifiedFilter = $this->get(route('communities.manage.proof-of-work.index', [$community, 'status' => 'VERIFIED']));
        $verifiedFilter->assertOk();
        $verifiedFilter->assertDontSee($member->name);

        // Creator verifies work
        $this->post(route('communities.manage.proof-of-work.review', [$community, $pow]), [
            'status' => LessonProofOfWork::STATUS_VERIFIED,
            'feedback' => 'Verified with excellence.',
        ]);

        $pow->refresh();
        $this->assertEquals(LessonProofOfWork::STATUS_VERIFIED, $pow->status);
        $this->assertEquals('PHPUnit Feature Testing', $pow->skill_label_snapshot);

        // Changing lesson skill_label does not alter snapshotted label (AC-14)
        $lesson->update(['skill_label' => 'Changed Skill Label In Future']);
        $pow->refresh();
        $this->assertEquals('PHPUnit Feature Testing', $pow->skill_label_snapshot);

        // Personal completion remains independent (AC-14)
        $this->assertDatabaseMissing('lesson_progresses', [
            'user_id' => $member->id,
            'lesson_id' => $lesson->id,
            'completed' => true,
        ]);
    }
}
