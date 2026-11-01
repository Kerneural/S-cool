<?php

namespace Tests\Feature;

use App\Mail\CommunityInvitationMail;
use App\Models\AppNotification;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VietnameseSweepTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('vi');
        config(['app.locale' => 'vi']);
    }

    /**
     * AC-01 & AC-04: Account screens render in Vietnamese and contain correct terms.
     */
    public function test_account_screens_render_in_vietnamese(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create();
        $membership = CommunityMembership::factory()->create([
            'user_id' => $user->id,
            'community_id' => $community->id,
            'status' => 'ACTIVE',
        ]);

        // Memberships list
        $response = $this->actingAs($user)->get(route('account.memberships.index'));
        $response->assertOk()
            ->assertSee('lang="vi"', false)
            ->assertSee('Tư cách thành viên cộng đồng của bạn')
            ->assertSee('Cài đặt tài khoản');

        // Membership detail
        $showResponse = $this->actingAs($user)->get(route('account.memberships.show', $membership));
        $showResponse->assertOk()
            ->assertSee('lang="vi"', false)
            ->assertSee('Thông tin thành viên cộng đồng và chi tiết quyền lợi');

        // Referrals page with Test Mode notice (AC-04)
        $referralResponse = $this->actingAs($user)->get(route('account.referrals'));
        $referralResponse->assertOk()
            ->assertSee('lang="vi"', false)
            ->assertSee('Giới thiệu của tôi & Thu nhập mô phỏng')
            ->assertSee('Chế độ thử nghiệm SePay');
    }

    /**
     * AC-01 & AC-04: Payment history and checkout screens render in Vietnamese with SePay Test Mode.
     */
    public function test_commerce_screens_render_in_vietnamese_with_test_mode_labels(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create(['access_mode' => 'PAID', 'price_vnd' => 100000]);
        $payment = Payment::factory()->create([
            'user_id' => $user->id,
            'community_id' => $community->id,
            'status' => 'PENDING',
            'amount' => 100000,
            'currency' => 'VND',
            'external_reference' => 'TEST-SWEEP-001',
        ]);

        // Payment History Index
        $historyResponse = $this->actingAs($user)->get(route('account.payment-history.index'));
        $historyResponse->assertOk()
            ->assertSee('lang="vi"', false)
            ->assertSee('Lịch sử thanh toán cá nhân');

        // Payment History Detail (AC-04)
        $detailResponse = $this->actingAs($user)->get(route('account.payment-history.show', $payment));
        $detailResponse->assertOk()
            ->assertSee('lang="vi"', false)
            ->assertSee('Chi tiết biên lai thanh toán')
            ->assertSee('Chế độ thử nghiệm');

        // Payment Checkout (AC-04)
        $checkoutResponse = $this->actingAs($user)->get(route('payments.checkout', $payment->external_reference));
        $checkoutResponse->assertOk()
            ->assertSee('lang="vi"', false)
            ->assertSee('Chế độ thử nghiệm SePay')
            ->assertSee('Nội dung chuyển khoản (Bắt buộc)');
    }

    /**
     * AC-02: Invitation mail subject and body are in Vietnamese, link still works.
     */
    public function test_invitation_mail_renders_in_vietnamese_and_preserves_link(): void
    {
        $acceptUrl = 'http://localhost/invitations/accept#token=secret-token-12345';
        $mail = new CommunityInvitationMail('Dev Community', $acceptUrl);
        $renderedMail = $mail->render();

        // AC-02: Subject is Vietnamese
        $this->assertEquals(
            'Lời mời tham gia cộng đồng riêng tư của bạn',
            $mail->envelope()->subject
        );

        // AC-02: Body contains Vietnamese text and valid accept URL
        $this->assertStringContainsString('Bạn đã được mời tham gia Dev Community.', $renderedMail);
        $this->assertStringContainsString('Mở lời mời riêng tư của bạn', $renderedMail);
        $this->assertStringContainsString($acceptUrl, $renderedMail);
    }

    /**
     * AC-01: Admin community supervision screen renders in Vietnamese.
     */
    public function test_admin_community_screen_renders_in_vietnamese(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true]);
        $community = Community::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.communities.index'));
        $response->assertOk()
            ->assertSee('lang="vi"', false)
            ->assertSee('Quản trị nền tảng — Cộng đồng')
            ->assertSee('Quản trị nền tảng')
            ->assertSee('Tổng số cộng đồng')
            ->assertSee('Đang hoạt động');
    }

    /**
     * AC-01 & AC-03: Help Center and Community Preview (About) render in Vietnamese.
     */
    public function test_help_center_and_preview_render_in_vietnamese(): void
    {
        $user = User::factory()->create();
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'access_mode' => 'PAID',
            'price_vnd' => 200000,
        ]);

        // Help Center
        $helpResponse = $this->actingAs($user)->get(route('help.index'));
        $helpResponse->assertOk()
            ->assertSee('lang="vi"', false)
            ->assertSee('Chào mừng đến với Trung tâm trợ giúp S-cool')
            ->assertSee('Môi trường Chế độ thử nghiệm SePay');

        // Community About Preview (non-member sees preview)
        $previewResponse = $this->actingAs($user)->get(route('communities.about', $community));
        $previewResponse->assertOk()
            ->assertSee('lang="vi"', false)
            ->assertSee('Xem trước cộng đồng')
            ->assertSee('Tham gia cộng đồng này');
    }

    public function test_landing_static_interface_and_illustrations_render_in_vietnamese(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Xây dựng cộng đồng nơi')
            ->assertSee('học tập trở thành thực hành.')
            ->assertSee('Hành trình học tập')
            ->assertSee('Tình huống minh họa')
            ->assertSee('Chính sách riêng tư')
            ->assertDontSee('Build a community where')
            ->assertDontSee('Live demo screenshot:');
    }

    public function test_verification_and_password_reset_mail_render_vietnamese_without_changing_links(): void
    {
        $user = User::factory()->unverified()->create();
        $verification = (new VerifyEmail)->toMail($user);
        $reset = (new ResetPassword('synthetic-reset-token'))->toMail($user);

        $this->assertSame('Xác minh địa chỉ email', $verification->subject);
        $this->assertSame('Xác minh địa chỉ email', $verification->actionText);
        $this->assertStringContainsString('/verify-email/', $verification->actionUrl);
        $this->assertStringContainsString('signature=', $verification->actionUrl);
        $this->assertStringContainsString('Vui lòng nhấn nút', $verification->render());
        $this->assertSame('Thông báo đặt lại mật khẩu', $reset->subject);
        $this->assertSame('Đặt lại mật khẩu', $reset->actionText);
        $this->assertStringContainsString('synthetic-reset-token', $reset->actionUrl);
        $this->assertStringContainsString('Liên kết đặt lại mật khẩu này sẽ hết hạn', $reset->render());
    }

    public function test_notification_summary_translates_interface_and_preserves_authored_names(): void
    {
        $author = User::factory()->create(['name' => 'Original English Name']);
        $notification = new AppNotification([
            'type' => 'post_reply',
            'data' => ['commenter_id' => $author->id],
        ]);

        $this->assertSame('Original English Name đã trả lời bài viết của bạn.', $notification->summary());
        $notification->type = 'content_reported';
        $this->assertSame('Có báo cáo nội dung mới cần kiểm duyệt.', $notification->summary());
    }

    public function test_legacy_admin_has_no_platform_navigation_in_expert_mode(): void
    {
        config(['scool.deployment_mode' => 'expert', 'scool.owner_email' => 'owner@example.test']);
        $legacyAdmin = User::factory()->create(['is_platform_admin' => true]);

        $this->actingAs($legacyAdmin)->get('/dashboard')->assertOk()
            ->assertDontSee(route('admin.communities.index'), false)
            ->assertDontSee(route('website-hub'), false);
        $this->get(route('admin.communities.index'))->assertForbidden();
    }
}
