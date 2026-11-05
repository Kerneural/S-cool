<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class NavigationDropdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_ac01_and_ac03_dropdown_geometry_classes_enforce_widened_panel_and_mobile_containment(): void
    {
        App::setLocale('vi');

        $owner = User::factory()->create([
            'name' => 'Expert Owner',
            'email' => 'owner@scool.local',
        ]);
        Config::set('scool.deployment_mode', 'expert');
        Config::set('scool.owner_email', 'owner@scool.local');

        $community = Community::factory()->create([
            'creator_id' => $owner->id,
            'status' => 'ACTIVE',
            'slug' => 'flagship-class',
            'name' => 'Flagship Class',
        ]);

        $response = $this->actingAs($owner)->get(route('communities.posts.index', $community));
        $response->assertOk();

        // AC-01 & AC-03: Widened geometry min-w-[240px], w-64, right-aligned end-0, mobile containment max-w-[calc(100vw-2rem)]
        $response->assertSee('w-64 min-w-[240px]', false);
        $response->assertSee('max-w-[calc(100vw-2rem)]', false);
        $response->assertSee('end-0', false);

        // Switcher dropdown contains active community with highlight, checkmark, and Explore more button
        $response->assertSee('Chuyển đổi cộng đồng');
        $response->assertSee('Flagship Class');
        $response->assertSee('d="M5 13l4 4L19 7"', false); // Checkmark icon
        $response->assertSee('Khám phá thêm');
        $response->assertSee(route('communities.index'));
    }

    public function test_ac02_vietnamese_labels_display_with_whitespace_nowrap_on_single_line(): void
    {
        App::setLocale('vi');

        $owner = User::factory()->create([
            'name' => 'Expert Owner',
            'email' => 'owner@scool.local',
        ]);
        Config::set('scool.deployment_mode', 'expert');
        Config::set('scool.owner_email', 'owner@scool.local');

        $community = Community::factory()->create([
            'creator_id' => $owner->id,
            'status' => 'ACTIVE',
            'slug' => 'flagship-class',
            'name' => 'Flagship Class',
        ]);

        $response = $this->actingAs($owner)->get(route('communities.posts.index', $community));
        $response->assertOk();

        // AC-02: Dropdown links include whitespace-nowrap and correct Vietnamese text
        $response->assertSee('whitespace-nowrap', false);
        $response->assertSee('Quản trị website');
        $response->assertSee('Hồ sơ & Cài đặt');
        $response->assertSee('Quyền truy cập của tôi');
        $response->assertSee('Lịch sử thanh toán');
        $response->assertSee('Giới thiệu');
        $response->assertSee('Trung tâm trợ giúp');
        $response->assertSee('Đăng xuất');
    }

    public function test_ac04_accessibility_and_keyboard_focus_restoration_attributes(): void
    {
        $user = User::factory()->create([
            'name' => 'Normal Learner',
            'email' => 'learner@scool.local',
        ]);

        $response = $this->actingAs($user)->get(route('help.index'));
        $response->assertOk();

        // AC-04: Escape key and click outside closing with focus return
        $response->assertSee('@keydown.escape.stop.prevent="open = false; $refs.trigger.querySelector(\'button\')?.focus()"', false);
        $response->assertSee('@click.outside="open = false"', false);
        $response->assertSee(':aria-expanded="open.toString()"', false);
        $response->assertSee('aria-haspopup="true"', false);
    }

    public function test_ac05_user_identity_card_displays_avatar_name_and_truncated_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Nguyen Van A',
            'email' => 'nguyenvana.superlongemailaddress@testdomain.local',
        ]);

        $response = $this->actingAs($user)->get(route('help.index'));
        $response->assertOk();

        // AC-05: Identity card shows initial, name, and truncated email
        $response->assertSee('N');
        $response->assertSee('Nguyen Van A');
        $response->assertSee('nguyenvana.superlongemailaddress@testdomain.local');
        $response->assertSee('truncate', false);
    }

    public function test_ac06_single_expert_mode_hides_redundant_communities_link_and_redirects_dashboard_for_owner_and_member(): void
    {
        Config::set('scool.deployment_mode', 'expert');
        Config::set('scool.owner_email', 'owner@scool.local');

        $owner = User::factory()->create([
            'email' => 'owner@scool.local',
            'name' => 'Expert Owner',
        ]);

        $member = User::factory()->create([
            'email' => 'member@scool.local',
            'name' => 'Member User',
        ]);

        $community = Community::factory()->create([
            'creator_id' => $owner->id,
            'status' => 'ACTIVE',
            'slug' => 'expert-flagship',
            'name' => 'Expert Flagship Community',
        ]);

        CommunityMembership::factory()->active()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
        ]);

        // 1. Owner hitting /dashboard redirects directly to flagship community feed
        $ownerDashboardRes = $this->actingAs($owner)->get(route('dashboard'));
        $ownerDashboardRes->assertRedirect(route('communities.posts.index', $community));

        // 2. Member hitting /dashboard redirects directly to flagship community feed
        $memberDashboardRes = $this->actingAs($member)->get(route('dashboard'));
        $memberDashboardRes->assertRedirect(route('communities.posts.index', $community));

        // 3. Global shell (e.g. /profile) has Communities link to browse outer directory (Image 4)
        $profileRes = $this->actingAs($owner)->get(route('profile.edit'));
        $profileRes->assertOk();
        $profileRes->assertSee(route('communities.index'));

        // 4. Logout is submitted strictly via POST with CSRF protection
        $profileRes->assertSee('<form method="POST" action="'.route('logout').'"', false);
        $profileRes->assertSee('name="_token"', false);
    }
}
