<?php

namespace Tests\Feature;

use App\Livewire\CommunitySearchTypeahead;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseEntitlement;
use App\Models\CourseSection;
use App\Models\Event;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireCommunitySearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_typeahead_requires_at_least_two_characters_and_resets_below_threshold(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'Laravel Livewire Deep Dive',
        ]);

        $this->actingAs($member);

        Livewire::test(CommunitySearchTypeahead::class, ['community' => $community])
            ->assertSet('isOpen', false)
            ->set('query', 'L')
            ->assertSet('isOpen', false)
            ->assertSet('totalResultsCount', 0)
            ->set('query', 'La')
            ->assertSet('isOpen', true)
            ->assertSet('totalResultsCount', 1)
            ->set('query', '')
            ->assertSet('isOpen', false)
            ->assertSet('totalResultsCount', 0);
    }

    public function test_typeahead_groups_results_across_posts_courses_lessons_members_and_events(): void
    {
        $creator = User::factory()->create(['name' => 'Alice Master']);
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $member = User::factory()->create(['name' => 'Bob Apprentice']);
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        // Post
        Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'Mastering Architectures in PHP',
            'body' => 'Comprehensive guide to domain driven design.',
        ]);

        // Course & Lesson
        $course = Course::factory()->create([
            'community_id' => $community->id,
            'title' => 'Master Course on Clean Code',
            'status' => 'PUBLISHED',
            'access_mode' => 'INCLUDED',
        ]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        Lesson::factory()->create([
            'course_section_id' => $section->id,
            'title' => 'Lesson on Master Principles',
            'status' => 'PUBLISHED',
            'content' => 'Deep dive into clean architecture.',
        ]);

        // Event
        Event::factory()->create([
            'community_id' => $community->id,
            'creator_id' => $creator->id,
            'title' => 'Master Class Live Session',
            'description' => 'Interactive Q&A',
            'starts_at' => Carbon::now()->addDays(2),
            'ends_at' => Carbon::now()->addDays(2)->addHour(),
            'timezone' => 'Asia/Ho_Chi_Minh',
            'status' => 'SCHEDULED',
        ]);

        $this->actingAs($member);

        $test = Livewire::test(CommunitySearchTypeahead::class, ['community' => $community])
            ->set('query', 'Master')
            ->assertSet('isOpen', true);

        $results = $test->get('results');
        $this->assertCount(1, $results['posts']);
        $this->assertSame('Mastering Architectures in PHP', $results['posts'][0]['title']);

        $this->assertCount(2, $results['courses_lessons']);
        $this->assertSame('Master Course on Clean Code', $results['courses_lessons'][0]['title']);
        $this->assertSame('Lesson on Master Principles', $results['courses_lessons'][1]['title']);

        $this->assertCount(1, $results['members']);
        $this->assertSame('Alice Master', $results['members'][0]['title']);

        $this->assertCount(1, $results['events']);
        $this->assertSame('Master Class Live Session', $results['events'][0]['title']);

        $this->assertSame(5, $test->get('totalResultsCount'));
    }

    public function test_typeahead_enforces_tenant_isolation_and_cross_community_denial(): void
    {
        $creator = User::factory()->create();
        $communityA = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $communityB = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $communityA->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        // Content in Community B only
        Post::factory()->create([
            'community_id' => $communityB->id,
            'user_id' => $creator->id,
            'title' => 'Secret Knowledge in B',
        ]);
        Course::factory()->create([
            'community_id' => $communityB->id,
            'title' => 'Secret Course in B',
            'status' => 'PUBLISHED',
        ]);
        Event::factory()->create([
            'community_id' => $communityB->id,
            'creator_id' => $creator->id,
            'title' => 'Secret Event in B',
            'status' => 'SCHEDULED',
            'starts_at' => Carbon::now()->addDay(),
            'ends_at' => Carbon::now()->addDay()->addHour(),
        ]);

        $this->actingAs($member);

        Livewire::test(CommunitySearchTypeahead::class, ['community' => $communityA])
            ->set('query', 'Secret')
            ->assertSet('totalResultsCount', 0);
    }

    public function test_typeahead_filters_draft_courses_and_unentitled_paid_courses_for_regular_member(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        // Draft course
        $draftCourse = Course::factory()->create([
            'community_id' => $community->id,
            'title' => 'Draft Python Course',
            'status' => 'DRAFT',
        ]);
        $draftSec = CourseSection::factory()->create(['course_id' => $draftCourse->id]);
        Lesson::factory()->create([
            'course_section_id' => $draftSec->id,
            'title' => 'Draft Python Lesson',
            'status' => 'DRAFT',
        ]);

        // Paid course without entitlement
        $paidCourse = Course::factory()->create([
            'community_id' => $community->id,
            'title' => 'Paid Premium Course',
            'status' => 'PUBLISHED',
            'access_mode' => 'ONE_TIME',
            'price' => 50000,
        ]);
        $paidSec = CourseSection::factory()->create(['course_id' => $paidCourse->id]);
        Lesson::factory()->create([
            'course_section_id' => $paidSec->id,
            'title' => 'Paid Premium Lesson',
            'status' => 'PUBLISHED',
        ]);

        // Member search
        $this->actingAs($member);

        $memberTest = Livewire::test(CommunitySearchTypeahead::class, ['community' => $community])
            ->set('query', 'Python')
            ->assertSet('totalResultsCount', 0);

        // Member cannot see unentitled paid courses or lessons inside them
        $paidSearch = Livewire::test(CommunitySearchTypeahead::class, ['community' => $community])
            ->set('query', 'Premium');
        $results = $paidSearch->get('results');
        $this->assertCount(0, $results['courses_lessons']);
        $this->assertSame(0, $paidSearch->get('totalResultsCount'));

        // When member is granted entitlement to the course:
        CourseEntitlement::create([
            'community_id' => $community->id,
            'course_id' => $paidCourse->id,
            'user_id' => $member->id,
        ]);

        $entitledSearch = Livewire::test(CommunitySearchTypeahead::class, ['community' => $community])
            ->set('query', 'Premium');
        $entitledResults = $entitledSearch->get('results');
        // Now both the entitled course and its published lesson appear
        $this->assertCount(1, array_filter($entitledResults['courses_lessons'], fn ($i) => $i['badge'] === 'Course'));
        $this->assertCount(1, array_filter($entitledResults['courses_lessons'], fn ($i) => $i['badge'] === 'Lesson'));
        $this->assertSame(2, $entitledSearch->get('totalResultsCount'));

        // Creator search can see draft courses & lessons as well as paid courses
        $this->actingAs($creator);
        $creatorTest = Livewire::test(CommunitySearchTypeahead::class, ['community' => $community])
            ->set('query', 'Python')
            ->assertSet('totalResultsCount', 2);

        $creatorPaidTest = Livewire::test(CommunitySearchTypeahead::class, ['community' => $community])
            ->set('query', 'Premium')
            ->assertSet('totalResultsCount', 2);
    }

    public function test_typeahead_filters_cancelled_events_and_inactive_members(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        // Suspended member
        $suspendedUser = User::factory()->create(['name' => 'Suspended Ghost']);
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $suspendedUser->id,
            'status' => 'SUSPENDED',
        ]);

        // Cancelled event
        Event::factory()->create([
            'community_id' => $community->id,
            'creator_id' => $creator->id,
            'title' => 'Ghost Gathering Event',
            'status' => 'CANCELLED',
            'starts_at' => Carbon::now()->addDay(),
            'ends_at' => Carbon::now()->addDay()->addHour(),
        ]);

        $this->actingAs($member);

        Livewire::test(CommunitySearchTypeahead::class, ['community' => $community])
            ->set('query', 'Ghost')
            ->assertSet('totalResultsCount', 0);
    }

    public function test_typeahead_keyboard_navigation_selection_and_enter_redirect(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $post1 = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'Alpha Discussion Topic',
        ]);
        $post2 = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'Beta Discussion Topic',
        ]);

        $this->actingAs($member);

        $test = Livewire::test(CommunitySearchTypeahead::class, ['community' => $community])
            ->set('query', 'Discussion')
            ->assertSet('selectedIndex', -1)
            ->call('selectNext')
            ->assertSet('selectedIndex', 0)
            ->call('selectNext')
            ->assertSet('selectedIndex', 1)
            ->call('selectPrevious')
            ->assertSet('selectedIndex', 0);

        // Selecting active item redirects to its target URL
        $test->call('selectActive')
            ->assertRedirect(route('communities.posts.show', [$community, $post2]));

        // With nothing selected (selectedIndex = -1), Enter redirects to the stable full results search URL
        $testFallback = Livewire::test(CommunitySearchTypeahead::class, ['community' => $community])
            ->set('query', 'Discussion')
            ->assertSet('selectedIndex', -1)
            ->call('selectActive')
            ->assertRedirect(route('communities.search', [$community, 'q' => 'Discussion']));
    }

    public function test_typeahead_denies_unauthorized_user_or_suspended_community(): void
    {
        $creator = User::factory()->create();
        $suspendedCommunity = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'SUSPENDED']);
        $outsider = User::factory()->create();

        $this->actingAs($outsider);

        Livewire::test(CommunitySearchTypeahead::class, ['community' => $suspendedCommunity])
            ->set('query', 'Hello')
            ->assertSet('totalResultsCount', 0)
            ->assertSet('errorMessage', 'Unauthorized or inactive community.');
    }

    public function test_typeahead_protects_against_stale_or_out_of_order_requests(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'First Query Post Alpha',
        ]);

        Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'Second Query Post Beta',
        ]);

        $this->actingAs($member);

        $test = Livewire::test(CommunitySearchTypeahead::class, ['community' => $community]);

        // 1. Initial query: Alpha (requestId = 1, lastProcessedRequestId = 1)
        $test->set('query', 'Alpha');
        $this->assertSame(1, $test->get('requestId'));
        $this->assertSame(1, $test->get('lastProcessedRequestId'));
        $this->assertSame(1, $test->get('totalResultsCount'));
        $this->assertSame('First Query Post Alpha', $test->get('results')['posts'][0]['title']);

        // 2. Subsequent query: Beta (requestId = 2, lastProcessedRequestId = 2)
        $test->set('query', 'Beta');
        $this->assertSame(2, $test->get('requestId'));
        $this->assertSame(2, $test->get('lastProcessedRequestId'));
        $this->assertSame(1, $test->get('totalResultsCount'));
        $this->assertSame('Second Query Post Beta', $test->get('results')['posts'][0]['title']);

        // 3. Stale request simulation: Request 1 (Alpha) arrives late after Request 2 has completed
        $test->call('executeSearch', 1, 'Alpha');

        // State remains at request 2; stale results from request 1 were discarded
        $this->assertSame(2, $test->get('requestId'));
        $this->assertSame(2, $test->get('lastProcessedRequestId'));
        $this->assertSame(1, $test->get('totalResultsCount'));
        $this->assertSame('Second Query Post Beta', $test->get('results')['posts'][0]['title']);

        // 4. Stale request with request ID 0 or negative is also discarded
        $test->call('executeSearch', 0, 'Alpha');
        $this->assertSame(2, $test->get('lastProcessedRequestId'));
        $this->assertSame('Second Query Post Beta', $test->get('results')['posts'][0]['title']);

        // 5. Calling clearQuery advances requestId and immediately invalidates any prior in-flight request
        $test->call('clearQuery');
        $this->assertSame(3, $test->get('requestId'));
        $this->assertSame(3, $test->get('lastProcessedRequestId'));
        $this->assertSame(0, $test->get('totalResultsCount'));
        $this->assertFalse($test->get('isOpen'));

        // Stale request 2 arriving after clear is ignored
        $test->call('executeSearch', 2, 'Beta');
        $this->assertSame(3, $test->get('lastProcessedRequestId'));
        $this->assertSame(0, $test->get('totalResultsCount'));
        $this->assertFalse($test->get('isOpen'));
    }
}
