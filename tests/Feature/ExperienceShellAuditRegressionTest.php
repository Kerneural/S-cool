<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
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
            $response->assertDontSee('href="'.route('communities.members.index', $community).'"', false)
                ->assertDontSee('src="'.route('communities.cover.show', $community).'"', false);
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
        $response->assertSee('Your learning roadmap')->assertSee($included->title)
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
}
