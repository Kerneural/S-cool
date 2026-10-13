<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunitySearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_query_returns_matching_posts_courses_lessons_and_members_in_same_community_with_direct_links(): void
    {
        $creatorA = User::factory()->create();
        $communityA = Community::factory()->create([
            'creator_id' => $creatorA->id,
            'status' => 'ACTIVE',
            'name' => 'Community Alpha',
        ]);

        $memberA = User::factory()->create(['name' => 'Alice Member']);
        CommunityMembership::factory()->create([
            'community_id' => $communityA->id,
            'user_id' => $memberA->id,
            'status' => 'ACTIVE',
        ]);

        // Community B (Other tenant)
        $creatorB = User::factory()->create();
        $communityB = Community::factory()->create([
            'creator_id' => $creatorB->id,
            'status' => 'ACTIVE',
            'name' => 'Community Beta',
        ]);

        $memberB = User::factory()->create(['name' => 'Bob Member']);
        CommunityMembership::factory()->create([
            'community_id' => $communityB->id,
            'user_id' => $memberB->id,
            'status' => 'ACTIVE',
        ]);

        // Items in Community A matching "EurusTarget"
        $postA = Post::factory()->create([
            'community_id' => $communityA->id,
            'user_id' => $creatorA->id,
            'title' => 'EurusTarget Discussion Post',
            'body' => 'Valuable ideas inside Community A.',
        ]);

        $courseA = Course::factory()->create([
            'community_id' => $communityA->id,
            'title' => 'EurusTarget Masterclass Course',
            'description' => 'Comprehensive course in Community A.',
            'status' => 'PUBLISHED',
        ]);

        $sectionA = CourseSection::factory()->create([
            'course_id' => $courseA->id,
            'title' => 'Module 1',
        ]);

        $lessonA = Lesson::factory()->create([
            'course_section_id' => $sectionA->id,
            'title' => 'EurusTarget Practical Lesson',
            'content' => 'Hands on practice for members.',
            'status' => 'PUBLISHED',
        ]);

        $targetUserA = User::factory()->create([
            'name' => 'EurusTarget Contributor',
            'bio' => 'Passionate learner in Alpha',
        ]);
        CommunityMembership::factory()->create([
            'community_id' => $communityA->id,
            'user_id' => $targetUserA->id,
            'status' => 'ACTIVE',
        ]);

        // Items in Community B also matching "EurusTarget"
        $postB = Post::factory()->create([
            'community_id' => $communityB->id,
            'user_id' => $creatorB->id,
            'title' => 'EurusTarget Post In Beta',
            'body' => 'Belongs strictly to Beta.',
        ]);

        $courseB = Course::factory()->create([
            'community_id' => $communityB->id,
            'title' => 'EurusTarget Course In Beta',
            'status' => 'PUBLISHED',
        ]);

        $sectionB = CourseSection::factory()->create(['course_id' => $courseB->id]);
        $lessonB = Lesson::factory()->create([
            'course_section_id' => $sectionB->id,
            'title' => 'EurusTarget Lesson In Beta',
            'status' => 'PUBLISHED',
        ]);

        $targetUserB = User::factory()->create(['name' => 'EurusTarget Beta Member']);
        CommunityMembership::factory()->create([
            'community_id' => $communityB->id,
            'user_id' => $targetUserB->id,
            'status' => 'ACTIVE',
        ]);

        // Search as Member A in Community A
        $this->actingAs($memberA);

        // 1. Search posts
        $postsResponse = $this->get(route('communities.search', [
            'community' => $communityA,
            'q' => 'EurusTarget',
            'type' => 'posts',
        ]));

        $postsResponse->assertOk()
            ->assertSee('EurusTarget Discussion Post')
            ->assertSee(route('communities.posts.show', [$communityA, $postA]))
            ->assertDontSee('EurusTarget Post In Beta');

        $viewCounts = $postsResponse->viewData('counts');
        $this->assertSame(1, $viewCounts['posts']);
        $this->assertSame(1, $viewCounts['courses']);
        $this->assertSame(1, $viewCounts['lessons']);
        $this->assertSame(1, $viewCounts['members']);

        // 2. Search courses
        $coursesResponse = $this->get(route('communities.search', [
            'community' => $communityA,
            'q' => 'EurusTarget',
            'type' => 'courses',
        ]));

        $coursesResponse->assertOk()
            ->assertSee('EurusTarget Masterclass Course')
            ->assertSee(route('communities.courses.show', [$communityA, $courseA]))
            ->assertDontSee('EurusTarget Course In Beta');

        // 3. Search lessons
        $lessonsResponse = $this->get(route('communities.search', [
            'community' => $communityA,
            'q' => 'EurusTarget',
            'type' => 'lessons',
        ]));

        $lessonsResponse->assertOk()
            ->assertSee('EurusTarget Practical Lesson')
            ->assertSee(route('communities.lessons.show', [$communityA, $courseA, $lessonA]))
            ->assertDontSee('EurusTarget Lesson In Beta');

        // 4. Search members
        $membersResponse = $this->get(route('communities.search', [
            'community' => $communityA,
            'q' => 'EurusTarget',
            'type' => 'members',
        ]));

        $membersResponse->assertOk()
            ->assertSee('EurusTarget Contributor')
            ->assertSee(route('communities.directory.show', [$communityA, $targetUserA]))
            ->assertDontSee('EurusTarget Beta Member');
    }

    public function test_member_sees_neither_draft_courses_nor_draft_lessons_while_creator_sees_drafts_with_badge(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        // Draft course with draft lesson
        $draftCourse = Course::factory()->create([
            'community_id' => $community->id,
            'title' => 'Secret Internal Draft Course',
            'description' => 'Unpublished syllabus details.',
            'status' => 'DRAFT',
        ]);
        $section1 = CourseSection::factory()->create(['course_id' => $draftCourse->id]);
        $draftLesson1 = Lesson::factory()->create([
            'course_section_id' => $section1->id,
            'title' => 'Secret Strategy Lesson',
            'content' => 'Top secret material never exposed.',
            'status' => 'DRAFT',
        ]);

        // Published course with draft lesson
        $publishedCourse = Course::factory()->create([
            'community_id' => $community->id,
            'title' => 'Public Course Title',
            'description' => 'Public course description.',
            'status' => 'PUBLISHED',
        ]);
        $section2 = CourseSection::factory()->create(['course_id' => $publishedCourse->id]);
        $draftLesson2 = Lesson::factory()->create([
            'course_section_id' => $section2->id,
            'title' => 'Secret Unpublished Lesson Two',
            'content' => 'Confidential upcoming chapter.',
            'status' => 'DRAFT',
        ]);

        // 1. As Member: must see 0 draft courses and 0 draft lessons
        $this->actingAs($member);

        $memberCourseSearch = $this->get(route('communities.search', [
            'community' => $community,
            'q' => 'Secret',
            'type' => 'courses',
        ]));
        $memberCourseSearch->assertOk()
            ->assertDontSee('Secret Internal Draft Course');
        $this->assertSame(0, $memberCourseSearch->viewData('counts')['courses']);

        $memberLessonSearch = $this->get(route('communities.search', [
            'community' => $community,
            'q' => 'Secret',
            'type' => 'lessons',
        ]));
        $memberLessonSearch->assertOk()
            ->assertDontSee('Secret Strategy Lesson')
            ->assertDontSee('Top secret material never exposed')
            ->assertDontSee('Secret Unpublished Lesson Two')
            ->assertDontSee('Confidential upcoming chapter');
        $this->assertSame(0, $memberLessonSearch->viewData('counts')['lessons']);

        // 2. As Creator: can preview both draft courses and draft lessons with draft badge
        $this->actingAs($creator);

        $creatorCourseSearch = $this->get(route('communities.search', [
            'community' => $community,
            'q' => 'Secret',
            'type' => 'courses',
        ]));
        $creatorCourseSearch->assertOk()
            ->assertSee('Secret Internal Draft Course')
            ->assertSee('Draft');
        $this->assertSame(1, $creatorCourseSearch->viewData('counts')['courses']);

        $creatorLessonSearch = $this->get(route('communities.search', [
            'community' => $community,
            'q' => 'Secret',
            'type' => 'lessons',
        ]));
        $creatorLessonSearch->assertOk()
            ->assertSee('Secret Strategy Lesson')
            ->assertSee('Secret Unpublished Lesson Two')
            ->assertSee('Draft');
        $this->assertSame(2, $creatorLessonSearch->viewData('counts')['lessons']);
    }

    public function test_search_validates_type_length_and_escapes_sql_wildcards(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($member);

        // Invalid type
        $invalidTypeResp = $this->get(route('communities.search', [
            'community' => $community,
            'q' => 'ValidQuery',
            'type' => 'malicious_type',
        ]));
        $invalidTypeResp->assertSessionHasErrors('type');

        // Too short query (< 2 chars)
        $shortResp = $this->get(route('communities.search', [
            'community' => $community,
            'q' => 'a',
            'type' => 'posts',
        ]));
        $shortResp->assertSessionHasErrors('q');

        // Too long query (> 100 chars)
        $longResp = $this->get(route('communities.search', [
            'community' => $community,
            'q' => str_repeat('x', 101),
            'type' => 'posts',
        ]));
        $longResp->assertSessionHasErrors('q');

        // Array query (?q[]=something)
        $arrayResp = $this->get(route('communities.search', [
            'community' => $community,
            'q' => ['injected_array'],
            'type' => 'posts',
        ]));
        $arrayResp->assertSessionHasErrors('q');

        // SQL wildcard escaping test:
        // Create posts where one contains literal "%" and another has different characters
        Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'Discount 50% Off Everything',
            'body' => 'Promo body text',
        ]);

        Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'Discount 5000 Items In Store',
            'body' => 'Regular stock update',
        ]);

        // Searching for "50%" should only match "Discount 50% Off Everything"
        $wildcardResp = $this->get(route('communities.search', [
            'community' => $community,
            'q' => '50%',
            'type' => 'posts',
        ]));

        $wildcardResp->assertOk()
            ->assertSee('Discount 50% Off Everything')
            ->assertDontSee('Discount 5000 Items In Store');

        $this->assertSame(1, $wildcardResp->viewData('counts')['posts']);
    }

    public function test_unauthorized_revoked_member_and_suspended_community_cannot_search_and_deleted_content_denied_on_open(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $activeMember = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $activeMember->id,
            'status' => 'ACTIVE',
        ]);

        $suspendedMember = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $suspendedMember->id,
            'status' => 'SUSPENDED',
        ]);

        $outsider = User::factory()->create();

        // Outsider -> 404
        $this->actingAs($outsider);
        $this->get(route('communities.search', $community))->assertNotFound();

        // Suspended Member -> 404
        $this->actingAs($suspendedMember);
        $this->get(route('communities.search', $community))->assertNotFound();

        // Suspended Community -> 404
        $suspendedCommunity = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'SUSPENDED',
        ]);
        CommunityMembership::factory()->create([
            'community_id' => $suspendedCommunity->id,
            'user_id' => $activeMember->id,
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($activeMember);
        $this->get(route('communities.search', $suspendedCommunity))->assertNotFound();

        // Soft-deleted content cannot be accessed after rendering
        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'Post to be deleted',
        ]);
        $post->delete();

        $this->get(route('communities.posts.show', [$community, $post]))->assertNotFound();

        // Draft course cannot be opened by active member
        $draftCourse = Course::factory()->create([
            'community_id' => $community->id,
            'title' => 'Draft Course',
            'status' => 'DRAFT',
        ]);

        $this->get(route('communities.courses.show', [$community, $draftCourse]))->assertNotFound();
    }

    public function test_search_pagination_preserves_query_and_zero_result_renders_empty_state(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        // Create 20 posts matching "PaginationBenchmark"
        for ($i = 1; $i <= 20; $i++) {
            Post::factory()->create([
                'community_id' => $community->id,
                'user_id' => $creator->id,
                'title' => sprintf('PaginationBenchmark Item %02d', $i),
                'body' => 'Post content body for pagination testing.',
            ]);
        }

        $this->actingAs($member);

        // Page 1
        $page1Resp = $this->get(route('communities.search', [
            'community' => $community,
            'q' => 'PaginationBenchmark',
            'type' => 'posts',
            'page' => 1,
        ]));

        $page1Resp->assertOk();
        $this->assertCount(15, $page1Resp->viewData('results'));
        $this->assertSame(20, $page1Resp->viewData('counts')['posts']);

        // Zero results state
        $zeroResp = $this->get(route('communities.search', [
            'community' => $community,
            'q' => 'CompletelyUnmatchedKeyword',
            'type' => 'posts',
        ]));

        $zeroResp->assertOk()
            ->assertSee('No results found')
            ->assertSee('CompletelyUnmatchedKeyword');
        $this->assertSame(0, $zeroResp->viewData('counts')['posts']);
    }
}
