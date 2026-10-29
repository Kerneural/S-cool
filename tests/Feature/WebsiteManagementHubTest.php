<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class WebsiteManagementHubTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('scool.deployment_mode', 'expert');
        Config::set('scool.owner_email', 'owner@example.com');
    }

    public function test_ac01_owner_with_two_communities_sees_both_and_links_open_existing_tools(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'email_verified_at' => now(),
        ]);

        $comm1 = Community::factory()->create([
            'creator_id' => $owner->id,
            'name' => 'Community Alpha',
            'status' => 'ACTIVE',
        ]);

        $course1 = Course::factory()->create([
            'community_id' => $comm1->id,
            'status' => 'PUBLISHED',
            'title' => 'Alpha Master Course',
        ]);

        $comm2 = Community::factory()->create([
            'creator_id' => $owner->id,
            'name' => 'Community Beta',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($owner)->get(route('website-hub'));

        $response->assertOk();
        $response->assertSee('Community Alpha');
        $response->assertSee('Community Beta');

        // Check header actions
        $response->assertSee(route('communities.create'));
        $response->assertSee(route('admin.communities.index'));

        // Check direct tools for Community 1 (with published course)
        $response->assertSee(route('communities.manage', $comm1));
        $response->assertSee(route('communities.manage.courses.show', [$comm1, $course1]));
        $response->assertSee(route('communities.members.index', $comm1));
        $response->assertSee(route('communities.invitations.index', $comm1));
        $response->assertSee(route('communities.reports.index', $comm1));
        $response->assertSee(route('communities.orders.index', $comm1));
        $response->assertSee(route('communities.manage.proof-of-work.index', $comm1));
        $response->assertSee(route('communities.edit', $comm1));

        // Check direct tools for Community 2 (without courses, safely falls back to classroom)
        $response->assertSee(route('communities.manage', $comm2));
        $response->assertSee(route('communities.classroom.index', $comm2));
        $response->assertSee(route('communities.members.index', $comm2));
        $response->assertSee(route('communities.invitations.index', $comm2));
        $response->assertSee(route('communities.reports.index', $comm2));
        $response->assertSee(route('communities.orders.index', $comm2));
        $response->assertSee(route('communities.manage.proof-of-work.index', $comm2));
        $response->assertSee(route('communities.edit', $comm2));
    }

    public function test_ac02_learner_and_non_owner_receive_403_and_nav_link_is_absent(): void
    {
        $learner = User::factory()->create([
            'email' => 'learner@example.com',
            'email_verified_at' => now(),
        ]);

        // 1. Learner visits dashboard - nav link must be absent
        $dashResponse = $this->actingAs($learner)->get(route('dashboard'));
        $dashResponse->assertOk();
        $dashResponse->assertDontSee(route('website-hub'));

        // 2. Learner direct access to hub returns 403 Forbidden
        $hubResponse = $this->actingAs($learner)->get(route('website-hub'));
        $hubResponse->assertForbidden();

        // 3. Non-owner with platform_admin flag in expert mode still receives 403
        $adminNonOwner = User::factory()->create([
            'email' => 'admin@example.com',
            'is_platform_admin' => true,
            'email_verified_at' => now(),
        ]);
        $adminResponse = $this->actingAs($adminNonOwner)->get(route('website-hub'));
        $adminResponse->assertForbidden();
    }

    public function test_guest_unauthenticated_user_is_redirected_to_login(): void
    {
        $guestResponse = $this->get(route('website-hub'));
        $guestResponse->assertRedirect(route('login'));
    }

    public function test_ac03_hub_page_is_responsive_without_horizontal_overflow_and_keyboard_accessible(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'email_verified_at' => now(),
        ]);

        Community::factory()->create([
            'creator_id' => $owner->id,
            'name' => 'Responsive Community',
        ]);

        $response = $this->actingAs($owner)->get(route('website-hub'));

        $response->assertOk();
        // Verify responsive adaptive grid classes avoiding horizontal scroll
        $response->assertSee('grid-cols-1 md:grid-cols-2 lg:grid-cols-3', false);
        $response->assertSee('grid-cols-2 sm:grid-cols-4', false);

        // Verify touch targets and accessibility
        $response->assertSee('min-h-[44px]', false);
        $response->assertSee('aria-label=', false);
    }

    public function test_ac04_every_label_on_the_hub_is_vietnamese(): void
    {
        App::setLocale('vi');

        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'email_verified_at' => now(),
        ]);

        Community::factory()->create([
            'creator_id' => $owner->id,
            'name' => 'Cộng đồng Chuyên gia',
            'status' => 'ACTIVE',
        ]);

        Community::factory()->create([
            'creator_id' => $owner->id,
            'name' => 'Cộng đồng Lưu trữ',
            'status' => 'ARCHIVED',
        ]);

        $response = $this->actingAs($owner)->get(route('website-hub'));

        $response->assertOk();
        $response->assertSee('Quản trị website');
        $response->assertSee('Tạo cộng đồng');
        $response->assertSee('Quản trị hệ thống');
        $response->assertSee('Công cụ trực tiếp');
        $response->assertSee('Tổng quan');
        $response->assertSee('Khóa học');
        $response->assertSee('Thành viên');
        $response->assertSee('Lời mời');
        $response->assertSee('Báo cáo');
        $response->assertSee('Đơn hàng');
        $response->assertSee('Bài nộp');
        $response->assertSee('Cài đặt');
        $response->assertSee('thành viên');
        $response->assertSee('Hoạt động');
        $response->assertSee('Đã lưu trữ');
    }

    public function test_owner_with_zero_communities_sees_helpful_empty_state(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($owner)->get(route('website-hub'));

        $response->assertOk();
        $response->assertSee('No communities found.');
        $response->assertSee(route('communities.create'));
    }

    public function test_communities_from_other_creators_are_not_leaked_on_hub(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'email_verified_at' => now(),
        ]);

        $otherCreator = User::factory()->create([
            'email' => 'other@example.com',
            'email_verified_at' => now(),
        ]);

        $ownedComm = Community::factory()->create([
            'creator_id' => $owner->id,
            'name' => 'My Secret Owned Community',
        ]);

        $foreignComm = Community::factory()->create([
            'creator_id' => $otherCreator->id,
            'name' => 'Foreign Community Not Mine',
        ]);

        $response = $this->actingAs($owner)->get(route('website-hub'));

        $response->assertOk();
        $response->assertSee('My Secret Owned Community');
        $response->assertDontSee('Foreign Community Not Mine');
    }
}
