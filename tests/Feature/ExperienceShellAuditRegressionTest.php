<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExperienceShellAuditRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_shell_has_one_scoped_search_and_switcher_and_never_links_to_creator_members(): void
    {
        $member = User::factory()->create();
        $communities = Community::factory()->count(2)->create(['status' => 'ACTIVE', 'cover_path' => 'audit-cover.webp']);
        foreach ($communities as $community) {
            CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);
        }

        foreach ($communities as $community) {
            $response = $this->actingAs($member)->get(route('communities.posts.index', $community))->assertOk();
            $html = $response->getContent();
            // Livewire's loading rules must precede the shell islands, including on idle render.
            $response->assertSee('<!-- Livewire Styles -->', false);
            $this->assertLessThan(strpos($html, '</head>'), strpos($html, '<!-- Livewire Styles -->'));
            $this->assertSame(1, substr_count($html, 'id="community-typeahead-input"'));
            $this->assertSame(1, substr_count($html, 'aria-label="Switch Community"'));
            foreach (['notifications.index', 'messages.index', 'directory.index'] as $suffix) {
                $response->assertSee('href="'.route('communities.'.$suffix, $community).'"', false);
            }
            $response->assertDontSee('href="'.route('communities.members.index', $community).'"', false);
            // No duplicate full-size shell cover: only the small identity image in collapsed details.
            $this->assertSame(1, substr_count($html, 'src="'.route('communities.cover.show', $community).'"'));
            $this->assertStringNotContainsString('media-frame--cover', $html);
            $this->assertStringContainsString('media-frame--thumb', $html);
        }

        $this->get(route('account.memberships.index'))->assertOk()
            ->assertDontSee('id="community-typeahead-input"', false)
            ->assertDontSee('id="shell-community-switcher"', false);
    }

    public function test_about_roadmap_only_exposes_published_courses_available_to_the_viewer(): void
    {
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $member = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);
        $included = Course::factory()->published()->included()->create(['community_id' => $community->id, 'title' => 'Available roadmap course']);
        $locked = Course::factory()->published()->oneTime()->create(['community_id' => $community->id, 'title' => 'LOCKED-ROADMAP-CONTEXT']);
        Course::factory()->draft()->create(['community_id' => $community->id, 'title' => 'DRAFT-ROADMAP-CONTEXT']);

        $response = $this->actingAs($member)->get(route('communities.show', $community))->assertOk();
        $response->assertSee('Courses you can access')->assertSee($included->title)
            ->assertDontSee($locked->title)->assertDontSee('DRAFT-ROADMAP-CONTEXT');
        $this->assertSame([$included->id], $response->viewData('roadmapCourses')->modelKeys());

        $this->actingAs($community->creator)->get(route('communities.show', $community))->assertOk()
            ->assertSee($locked->title)->assertDontSee('DRAFT-ROADMAP-CONTEXT');
    }

    public function test_creator_can_discover_review_queue_and_members_cannot_enter_it(): void
    {
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $this->actingAs($community->creator)->get(route('communities.manage', $community))->assertOk()
            ->assertSee('href="'.route('communities.manage.proof-of-work.index', $community).'"', false);
        $this->get(route('communities.manage.proof-of-work.index', $community))->assertOk()
            ->assertSee('aria-current="page"', false)->assertSee('Review submissions');

        $member = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);
        $this->actingAs($member)->get(route('communities.manage.proof-of-work.index', $community))->assertNotFound();
    }

    public function test_account_detail_pages_keep_shared_navigation_and_self_only_records(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $membership = CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $user->id, 'status' => 'ACTIVE']);
        $payment = Payment::factory()->succeeded()->create(['community_id' => $community->id, 'user_id' => $user->id]);
        $this->actingAs($user);
        foreach ([route('account.memberships.show', $membership), route('account.payment-history.show', $payment)] as $url) {
            $this->get($url)->assertOk()->assertSee('aria-label="Account navigation"', false)
                ->assertSee(route('profile.edit', ['section' => 'security']))
                ->assertDontSee('id="shell-community-switcher"', false);
        }

        $this->actingAs(User::factory()->create());
        $this->get(route('account.memberships.show', $membership))->assertNotFound();
        $this->get(route('account.payment-history.show', $payment))->assertNotFound();
    }

    public function test_about_puts_open_classroom_first_and_previews_three_courses_behind_a_disclosure(): void
    {
        $community = Community::factory()->create(['status' => 'ACTIVE', 'description' => 'ABOUT-DESCRIPTION-TEXT', 'rules' => 'ABOUT-RULES-TEXT']);
        $member = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;
        $url = route('communities.show', $community);

        $empty = $this->actingAs($member)->get($url)->assertOk();
        $empty->assertSeeInOrder(['Open Classroom', 'Courses you can access', 'Classroom shows the catalog and how to get access.', 'ABOUT-DESCRIPTION-TEXT', 'ABOUT-RULES-TEXT'])
            ->assertSee('href="'.route('communities.classroom.index', $community).'"', false)
            ->assertDontSee('Show more courses')
            ->assertDontSee('Tenant Slug')->assertDontSee('Useful Destinations')
            ->assertDontSee('Non-members cannot discover')->assertDontSee('skool.com');
        $this->assertSame(1, substr_count($empty->getContent(), 'ABOUT-DESCRIPTION-TEXT'), 'Description appears once');

        $courses = collect(range(1, 8))->map(fn ($i) => Course::factory()->published()->included()->create(['community_id' => $community->id, 'order' => $i, 'title' => "PREVIEW-COURSE-{$i}"]));
        Course::factory()->published()->oneTime()->create(['community_id' => $community->id, 'order' => 0, 'title' => 'LOCKED-PREVIEW-COURSE']);
        Course::factory()->draft()->create(['community_id' => $community->id, 'order' => 0, 'title' => 'DRAFT-PREVIEW-COURSE']);
        $foreign = Community::factory()->create(['status' => 'ACTIVE']);
        Course::factory()->published()->included()->create(['community_id' => $foreign->id, 'title' => 'FOREIGN-PREVIEW-COURSE']);

        $html = $this->get($url)->assertOk()
            ->assertSeeInOrder(['Open Classroom', 'PREVIEW-COURSE-1', 'PREVIEW-COURSE-3', 'Show more courses (5)', 'PREVIEW-COURSE-4', 'PREVIEW-COURSE-8', 'ABOUT-DESCRIPTION-TEXT'])
            ->assertDontSee('LOCKED-PREVIEW-COURSE')->assertDontSee('DRAFT-PREVIEW-COURSE')->assertDontSee('FOREIGN-PREVIEW-COURSE')
            ->getContent();
        // The first three rows sit outside the native disclosure; the remainder is inside it.
        [$visible, $disclosed] = explode('<details', $html, 2);
        foreach ([1, 2, 3] as $i) {
            $this->assertStringContainsString("PREVIEW-COURSE-{$i}", $visible);
        }
        foreach ([4, 5, 6, 7, 8] as $i) {
            $this->assertStringNotContainsString("PREVIEW-COURSE-{$i}", $visible);
            $this->assertStringContainsString("PREVIEW-COURSE-{$i}", $disclosed);
        }
        $this->assertStringNotContainsString('<details class="rail-disclosure rail-disclosure--nested mt-2" open', $html);

        $courses->slice(1)->each->delete();
        $this->get($url)->assertOk()->assertSee('PREVIEW-COURSE-1')->assertDontSee('Show more courses');

        $this->actingAs($community->creator)->get($url)->assertOk()
            ->assertSee('Edit Settings')->assertSee('You host this community.')->assertSee('LOCKED-PREVIEW-COURSE');
    }

    public function test_feed_rail_leads_with_learning_focus_and_collapses_secondary_context_once(): void
    {
        $community = Community::factory()->create(['status' => 'ACTIVE', 'description' => 'FEED-RAIL-DESCRIPTION', 'onboarding_enabled' => true]);
        $member = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;
        $course = Course::factory()->published()->included()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        Lesson::factory()->published()->create(['course_section_id' => $section->id, 'title' => 'FEED-RAIL-LESSON']);

        $html = $this->actingAs($member)->get(route('communities.posts.index', $community))->assertOk()
            ->assertSeeInOrder(['Learning Focus', 'FEED-RAIL-LESSON', 'Community Feed', 'Welcome Checklist', 'Next step', 'All steps', 'Community details', 'FEED-RAIL-DESCRIPTION', 'Contributions', 'Engagement points · Lifetime', 'View all rankings'])
            ->assertSee('href="'.route('communities.show', $community).'"', false)
            ->assertDontSee('skool.com')->assertDontSee('JOIN GROUP')->assertDontSee('>Online<', false)
            ->getContent();

        $this->assertSame(1, substr_count($html, 'FEED-RAIL-DESCRIPTION'), 'The community description is not repeated');
        $this->assertSame(1, substr_count($html, 'Learning Focus'), 'One learning focus block');
        // Secondary disclosures start collapsed; the server never renders them open.
        $this->assertSame(0, preg_match('/<details class="rail-[^"]*"[^>]*\sopen[\s>]/', $html));
        $this->assertSame(3, substr_count($html, '<details class="rail-'));
    }

    public function test_empty_search_tab_points_to_sections_that_have_matches(): void
    {
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $member = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;
        Course::factory()->published()->included()->create(['community_id' => $community->id, 'title' => 'Kubernetes basics']);

        $html = $this->actingAs($member)->get(route('communities.search', [$community, 'q' => 'kubernetes']))->assertOk()
            ->assertSee('No results found')->assertSee('discussions matching')
            ->assertSee('Matches in other sections:')
            ->assertSee('href="'.e(route('communities.search', [$community, 'type' => 'courses', 'q' => 'kubernetes'])).'"', false)
            ->getContent();
        $this->assertSame(1, substr_count($html, '<h1'));
    }
}
