<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ClassroomPublishingTest extends TestCase
{
    use RefreshDatabase;

    private function createCommunityWithCreator(): array
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        return [$community, $creator];
    }

    private function createActiveMember(Community $community): User
    {
        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        return $member;
    }

    public function test_schema_has_courses_sections_lessons_tables_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('courses'));
        $this->assertTrue(Schema::hasTable('course_sections'));
        $this->assertTrue(Schema::hasTable('lessons'));

        $this->assertTrue(Schema::hasColumns('courses', [
            'id', 'community_id', 'title', 'description', 'status', 'order', 'created_at', 'updated_at',
        ]));

        $this->assertTrue(Schema::hasColumns('course_sections', [
            'id', 'course_id', 'title', 'order', 'created_at', 'updated_at',
        ]));

        $this->assertTrue(Schema::hasColumns('lessons', [
            'id', 'course_section_id', 'title', 'content', 'video_url', 'status', 'order', 'created_at', 'updated_at',
        ]));
    }

    public function test_ac01_owning_creator_creates_edits_and_persists_courses_sections_and_lessons(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();

        // 1. Creator creates a course
        $response = $this->actingAs($creator)->post(route('communities.courses.store', $community), [
            'title' => 'Mastering Laravel',
            'description' => 'A complete masterclass on Laravel architecture.',
            'status' => 'DRAFT',
        ]);
        $response->assertRedirect(route('communities.classroom.index', $community));

        $course = Course::where('title', 'Mastering Laravel')->firstOrFail();
        $this->assertEquals($community->id, $course->community_id);
        $this->assertTrue($course->isDraft());
        $this->assertEquals(1, $course->order);

        // 2. Creator creates a section
        $secResponse = $this->actingAs($creator)->post(route('communities.courses.sections.store', [$community, $course]), [
            'title' => 'Section 1: Getting Started',
        ]);
        $secResponse->assertRedirect();
        $section = CourseSection::where('title', 'Section 1: Getting Started')->firstOrFail();
        $this->assertEquals($course->id, $section->course_id);

        // 3. Creator creates a lesson with YouTube video
        $lesResponse = $this->actingAs($creator)->post(route('communities.lessons.store', [$community, $course, $section]), [
            'title' => 'Lesson 1: Welcome',
            'content' => 'Welcome to the course notes.',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'status' => 'DRAFT',
        ]);
        $lesResponse->assertRedirect();
        $lesson = Lesson::where('title', 'Lesson 1: Welcome')->firstOrFail();
        $this->assertEquals($section->id, $lesson->course_section_id);
        $this->assertTrue($lesson->isDraft());
        $this->assertEquals('https://www.youtube.com/embed/dQw4w9WgXcQ', $lesson->embed_url);

        // 4. Creator updates course to PUBLISHED
        $upCourseResponse = $this->actingAs($creator)->put(route('communities.courses.update', [$community, $course]), [
            'title' => 'Mastering Laravel Pro',
            'description' => 'Updated description.',
            'status' => 'PUBLISHED',
        ]);
        $upCourseResponse->assertRedirect();
        $course->refresh();
        $this->assertEquals('Mastering Laravel Pro', $course->title);
        $this->assertTrue($course->isPublished());

        // 5. Creator updates lesson to PUBLISHED
        $upLessonResponse = $this->actingAs($creator)->put(route('communities.lessons.update', [$community, $course, $section, $lesson]), [
            'title' => 'Lesson 1: Welcome Pro',
            'content' => 'Updated content.',
            'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'status' => 'PUBLISHED',
        ]);
        $upLessonResponse->assertRedirect();
        $lesson->refresh();
        $this->assertEquals('Lesson 1: Welcome Pro', $lesson->title);
        $this->assertTrue($lesson->isPublished());
        $this->assertEquals('https://www.youtube.com/embed/dQw4w9WgXcQ', $lesson->embed_url);
    }

    public function test_ac02_active_member_access_predicate_and_draft_combinations(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $member = $this->createActiveMember($community);

        $course = Course::factory()->create([
            'community_id' => $community->id,
            'status' => 'DRAFT',
        ]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create([
            'course_section_id' => $section->id,
            'status' => 'PUBLISHED',
        ]);

        // Case A: Course DRAFT + Lesson PUBLISHED
        // Creator can preview both
        $this->actingAs($creator)->get(route('communities.courses.show', [$community, $course]))
            ->assertOk();
        $this->actingAs($creator)->get(route('communities.lessons.show', [$community, $course, $lesson]))
            ->assertOk();

        // Member is denied with 404
        $this->actingAs($member)->get(route('communities.courses.show', [$community, $course]))
            ->assertNotFound();
        $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course, $lesson]))
            ->assertNotFound();

        // Member Classroom list excludes the DRAFT course
        $this->actingAs($member)->get(route('communities.classroom.index', $community))
            ->assertOk()
            ->assertDontSee($course->title);

        // Case B: Course PUBLISHED + Lesson DRAFT
        $course->update(['status' => 'PUBLISHED']);
        $lesson->update(['status' => 'DRAFT']);

        // Member can see course outline
        $this->actingAs($member)->get(route('communities.courses.show', [$community, $course]))
            ->assertOk();

        // But member is denied on draft lesson direct URL
        $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course, $lesson]))
            ->assertNotFound();

        // Case C: Course PUBLISHED + Lesson PUBLISHED
        $lesson->update(['status' => 'PUBLISHED']);

        // Member can see both
        $this->actingAs($member)->get(route('communities.courses.show', [$community, $course]))
            ->assertOk()
            ->assertSee($course->title);
        $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course, $lesson]))
            ->assertOk()
            ->assertSee($lesson->title);
    }

    public function test_ac03_unpublish_removes_member_access_and_republish_restores_it(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $member = $this->createActiveMember($community);

        $course = Course::factory()->published()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->published()->create(['course_section_id' => $section->id]);

        // Initially published: Member has access
        $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course, $lesson]))
            ->assertOk();

        // Creator unpublishes course
        $this->actingAs($creator)->put(route('communities.courses.update', [$community, $course]), [
            'title' => $course->title,
            'description' => $course->description,
            'status' => 'DRAFT',
        ]);
        $course->refresh();
        $this->assertTrue($course->isDraft());

        // Member is immediately blocked with 404
        $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course, $lesson]))
            ->assertNotFound();

        // Creator republishes course
        $this->actingAs($creator)->put(route('communities.courses.update', [$community, $course]), [
            'title' => $course->title,
            'description' => $course->description,
            'status' => 'PUBLISHED',
        ]);

        // Member access is restored
        $this->actingAs($member)->get(route('communities.lessons.show', [$community, $course, $lesson]))
            ->assertOk();
    }

    public function test_ac04_isolation_outsiders_cross_tenant_and_unauthorized_writes_denied(): void
    {
        [$communityA, $creatorA] = $this->createCommunityWithCreator();
        [$communityB, $creatorB] = $this->createCommunityWithCreator();
        $memberA = $this->createActiveMember($communityA);

        $courseA = Course::factory()->published()->create(['community_id' => $communityA->id]);
        $sectionA = CourseSection::factory()->create(['course_id' => $courseA->id]);
        $lessonA = Lesson::factory()->published()->create(['course_section_id' => $sectionA->id]);

        // 1. Member cannot manage/write content (create course, section, lesson)
        $this->actingAs($memberA)->post(route('communities.courses.store', $communityA), [
            'title' => 'Hacker Course',
            'status' => 'PUBLISHED',
        ])->assertNotFound();

        $this->actingAs($memberA)->put(route('communities.courses.update', [$communityA, $courseA]), [
            'title' => 'Tampered Title',
            'status' => 'PUBLISHED',
        ])->assertNotFound();

        $this->actingAs($memberA)->delete(route('communities.courses.destroy', [$communityA, $courseA]))
            ->assertNotFound();

        // 2. Creator B cannot access or modify Community A's content
        $this->actingAs($creatorB)->get(route('communities.courses.show', [$communityA, $courseA]))
            ->assertNotFound();

        $this->actingAs($creatorB)->put(route('communities.courses.update', [$communityA, $courseA]), [
            'title' => 'Malicious Edit',
            'status' => 'PUBLISHED',
        ])->assertNotFound();

        // 3. Substituted IDs across communities return 404
        $courseB = Course::factory()->published()->create(['community_id' => $communityB->id]);
        $this->actingAs($creatorA)->get("/communities/{$communityA->slug}/courses/{$courseB->id}")
            ->assertNotFound();

        // 4. Inactive/suspended community denies both member and creator
        $communityA->forceFill(['status' => 'SUSPENDED'])->save();
        $this->actingAs($memberA)->get(route('communities.lessons.show', [$communityA, $courseA, $lessonA]))
            ->assertNotFound();
        $this->actingAs($creatorA)->get(route('communities.lessons.show', [$communityA, $courseA, $lessonA]))
            ->assertNotFound();
    }

    public function test_ac05_transactional_reordering_validates_exact_sibling_set(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();

        $c1 = Course::factory()->create(['community_id' => $community->id, 'order' => 1]);
        $c2 = Course::factory()->create(['community_id' => $community->id, 'order' => 2]);
        $c3 = Course::factory()->create(['community_id' => $community->id, 'order' => 3]);

        // 1. Successful reorder
        $this->actingAs($creator)->post(route('communities.courses.reorder', $community), [
            'order' => [$c3->id, $c1->id, $c2->id],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertEquals(1, $c3->fresh()->order);
        $this->assertEquals(2, $c1->fresh()->order);
        $this->assertEquals(3, $c2->fresh()->order);

        // 2. Duplicate sibling IDs in payload fails without partial writes
        $this->actingAs($creator)->post(route('communities.courses.reorder', $community), [
            'order' => [$c1->id, $c1->id, $c2->id],
        ])->assertSessionHasErrors('order');

        // Verify order remained unchanged
        $this->assertEquals(1, $c3->fresh()->order);

        // 3. Foreign ID from another community fails
        [$otherCommunity] = $this->createCommunityWithCreator();
        $foreignCourse = Course::factory()->create(['community_id' => $otherCommunity->id]);

        $this->actingAs($creator)->post(route('communities.courses.reorder', $community), [
            'order' => [$c3->id, $c1->id, $foreignCourse->id],
        ])->assertSessionHasErrors('order');

        // 4. Section reordering
        $s1 = CourseSection::factory()->create(['course_id' => $c1->id, 'order' => 1]);
        $s2 = CourseSection::factory()->create(['course_id' => $c1->id, 'order' => 2]);

        $this->actingAs($creator)->post(route('communities.courses.sections.reorder', [$community, $c1]), [
            'order' => [$s2->id, $s1->id],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertEquals(1, $s2->fresh()->order);
        $this->assertEquals(2, $s1->fresh()->order);

        // 5. Lesson reordering
        $l1 = Lesson::factory()->create(['course_section_id' => $s1->id, 'order' => 1]);
        $l2 = Lesson::factory()->create(['course_section_id' => $s1->id, 'order' => 2]);

        $this->actingAs($creator)->post(route('communities.lessons.reorder', [$community, $c1, $s1]), [
            'order' => [$l2->id, $l1->id],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertEquals(1, $l2->fresh()->order);
        $this->assertEquals(2, $l1->fresh()->order);
    }

    public function test_ac06_video_url_validation_and_embed_generation(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $course = Course::factory()->published()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);

        // Valid YouTube URLs
        $validUrls = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'https://vimeo.com/76979871' => 'https://player.vimeo.com/video/76979871',
            'https://player.vimeo.com/video/76979871' => 'https://player.vimeo.com/video/76979871',
        ];

        foreach ($validUrls as $inputUrl => $expectedEmbed) {
            $this->actingAs($creator)->post(route('communities.lessons.store', [$community, $course, $section]), [
                'title' => 'Video Lesson '.md5($inputUrl),
                'video_url' => $inputUrl,
                'status' => 'PUBLISHED',
            ])->assertRedirect()->assertSessionHasNoErrors();

            $lesson = Lesson::where('video_url', $inputUrl)->firstOrFail();
            $this->assertEquals($expectedEmbed, $lesson->embed_url);
        }

        // Invalid, raw HTML, scripts, lookalikes, or arbitrary hosts must be rejected
        $invalidUrls = [
            '<iframe src="https://youtube.com"></iframe>',
            'javascript:alert(1)',
            'http://www.youtube.com/watch?v=dQw4w9WgXcQ', // Non-HTTPS
            'https://evil-site.com/video.mp4',
            'https://youtube.com.attacker.com/watch?v=dQw4w9WgXcQ',
            'https://notvimeo.com/76979871',
            'https://user:pass@youtube.com/watch?v=dQw4w9WgXcQ',
        ];

        foreach ($invalidUrls as $badUrl) {
            $this->actingAs($creator)->post(route('communities.lessons.store', [$community, $course, $section]), [
                'title' => 'Bad Video Lesson',
                'video_url' => $badUrl,
                'status' => 'PUBLISHED',
            ])->assertSessionHasErrors('video_url');
        }

        // Script content in lesson is escaped in HTML view
        $xssLesson = Lesson::factory()->published()->create([
            'course_section_id' => $section->id,
            'content' => '<script>alert("xss")</script>',
        ]);

        $response = $this->actingAs($creator)->get(route('communities.lessons.show', [$community, $course, $xssLesson]));
        $response->assertOk();
        $response->assertDontSee('<script>alert("xss")</script>', false);
        $response->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false);
    }

    public function test_ac07_draft_metadata_and_lesson_counts_do_not_leak_to_members(): void
    {
        [$community, $creator] = $this->createCommunityWithCreator();
        $member = $this->createActiveMember($community);

        $course = Course::factory()->published()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);

        $publishedLesson = Lesson::factory()->published()->create([
            'course_section_id' => $section->id,
            'title' => 'Public Lesson Title',
        ]);

        $draftLesson = Lesson::factory()->draft()->create([
            'course_section_id' => $section->id,
            'title' => 'Secret Draft Lesson Title',
        ]);

        // Creator sees both lessons and total count of 2
        $creatorResponse = $this->actingAs($creator)->get(route('communities.classroom.index', $community));
        $creatorResponse->assertOk()
            ->assertSee('2 lessons');

        $creatorCourseResponse = $this->actingAs($creator)->get(route('communities.courses.show', [$community, $course]));
        $creatorCourseResponse->assertOk()
            ->assertSee('Public Lesson Title')
            ->assertSee('Secret Draft Lesson Title');

        // Member sees only 1 lesson in count and no draft title in course outline
        $memberIndexResponse = $this->actingAs($member)->get(route('communities.classroom.index', $community));
        $memberIndexResponse->assertOk()
            ->assertSee('1 lesson')
            ->assertDontSee('2 lessons');

        $memberCourseResponse = $this->actingAs($member)->get(route('communities.courses.show', [$community, $course]));
        $memberCourseResponse->assertOk()
            ->assertSee('Public Lesson Title')
            ->assertDontSee('Secret Draft Lesson Title');
    }
}
