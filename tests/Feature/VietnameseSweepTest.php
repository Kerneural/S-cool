<?php

namespace Tests\Feature;

use App\Mail\CommunityInvitationMail;
use App\Models\AppNotification;
use App\Models\Community;
use App\Models\CommunityInvitation;
use App\Models\CommunityMembership;
use App\Models\ContentReport;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Post;
use App\Models\User;
use App\Support\EventWallTime;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
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
            ->assertSee('Bước 1 trên 5')
            ->assertSee('Cộng đồng thiết kế mẫu')
            ->assertSee('Duyệt các cộng đồng công khai và xem mô tả')
            ->assertSee('Chủ cộng đồng đã xác minh bài thực hành')
            ->assertDontSee('Learning resources and communities')
            ->assertDontSee('Browse public communities and their descriptions')
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

    public function test_creator_cover_and_populated_moderation_keep_authored_content_and_vietnamese_controls(): void
    {
        $owner = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $owner->id, 'status' => 'ACTIVE']);
        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $owner->id,
            'title' => 'Original English lesson question',
            'body' => 'Authored English content stays unchanged.',
        ]);
        $report = ContentReport::create([
            'community_id' => $community->id,
            'reporter_id' => $owner->id,
            'target_type' => 'POST',
            'target_id' => $post->id,
            'reason' => 'Original authored report reason.',
            'status' => 'OPEN',
        ]);

        $this->actingAs($owner)->get(route('communities.edit', $community))
            ->assertOk()->assertSee('Ảnh bìa cộng đồng')->assertSee('Tải ảnh bìa lên')
            ->assertDontSee('Private community cover')->assertDontSee('Upload cover');

        $this->get(route('communities.reports.index', $community))->assertOk()
            ->assertSee('Người báo cáo:')->assertSee('Nội dung bị báo cáo:')
            ->assertSee('Gỡ nội dung')->assertSee('Bạn có chắc chắn muốn gỡ nội dung bị báo cáo này?')
            ->assertSee('Xem chi tiết bài viết →')->assertDontSee('Xem chi tiết bài viết &rarr;')
            ->assertSee('Original English lesson question')->assertSee('Original authored report reason.')
            ->assertDontSee('Reported Content:')->assertDontSee('Remove Content');

        $post->delete();
        $report->update([
            'status' => 'RESOLVED',
            'resolution_action' => 'REMOVED',
            'resolved_by' => $owner->id,
            'resolved_at' => now(),
            'resolution_reason' => 'Original resolution note.',
        ]);
        $this->get(route('communities.reports.index', ['community' => $community, 'status' => 'RESOLVED']))
            ->assertOk()->assertSee('Nội dung đã bị gỡ')->assertSee('Người kiểm duyệt:')
            ->assertSee('Original resolution note.')->assertDontSee('[Content removed]');
        $this->assertSame('REMOVED', $report->fresh()->resolution_action);
    }

    public function test_community_and_saved_post_flashes_render_labels_instead_of_internal_keys(): void
    {
        $owner = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $owner->id, 'status' => 'ACTIVE']);

        $this->actingAs($owner)->withSession(['status' => 'community-created'])
            ->get(route('communities.show', $community))->assertOk()
            ->assertSee('Đã tạo cộng đồng.')->assertDontSee('community-created');
        $this->withSession(['status' => 'community-updated'])
            ->get(route('communities.show', $community))->assertOk()
            ->assertSee('Đã cập nhật cộng đồng.')->assertDontSee('community-updated');
        $this->withSession(['status' => 'post-unsaved'])
            ->get(route('communities.saved.index', $community))->assertOk()
            ->assertSee(__('post-unsaved'))->assertDontSee('post-unsaved');
    }

    public function test_creator_recent_payment_labels_do_not_change_stored_statuses(): void
    {
        $owner = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $owner->id, 'status' => 'ACTIVE']);
        foreach (['SUCCEEDED', 'PENDING', 'CANCELLED', 'FAILED'] as $status) {
            Payment::factory()->create([
                'community_id' => $community->id, 'user_id' => $owner->id, 'status' => $status,
            ]);
        }

        $response = $this->actingAs($owner)->get(route('communities.manage', $community))->assertOk();
        foreach (['SUCCEEDED', 'PENDING', 'CANCELLED', 'FAILED'] as $status) {
            $response->assertSee(__($status));
            $this->assertDatabaseHas('payments', ['community_id' => $community->id, 'status' => $status]);
        }
    }

    public function test_invitation_rejections_are_vietnamese_without_creating_another_invitation(): void
    {
        $owner = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $owner->id, 'status' => 'ACTIVE']);
        $pending = CommunityInvitation::factory()->create([
            'community_id' => $community->id,
            'normalized_email' => 'pending-sweep@example.test',
        ]);
        $this->actingAs($owner)->post(route('communities.invitations.store', $community), [
            'email' => $owner->email,
        ])->assertSessionHasErrors(['email' => 'Chủ cộng đồng đã có quyền truy cập.']);
        $this->post(route('communities.invitations.store', $community), [
            'email' => $pending->normalized_email,
        ])->assertSessionHasErrors([
            'email' => 'Đã có lời mời đang chờ. Hãy thu hồi lời mời đó trước khi gửi lại.',
        ]);
        $this->assertDatabaseCount('community_invitations', 1);
        $this->assertSame('PENDING', $pending->fresh()->status);
    }

    public function test_invalid_reorder_and_ambiguous_event_time_return_vietnamese_errors(): void
    {
        $owner = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $owner->id, 'status' => 'ACTIVE']);
        $course = Course::factory()->create(['community_id' => $community->id]);

        $this->actingAs($owner)->post(route('communities.courses.reorder', $community), [
            'order' => [$course->id, $course->id],
        ])->assertSessionHasErrors([
            'order' => 'Danh sách sắp xếp đã thay đổi hoặc có mục trùng lặp. Vui lòng tải lại trang rồi thử lại.',
        ]);

        foreach ([
            ['not-a-date', 'Asia/Ho_Chi_Minh', 'Nhập ngày giờ địa phương, không kèm độ lệch múi giờ.'],
            ['2026-11-01T01:30', 'America/New_York', 'Giờ địa phương này không tồn tại hoặc bị trùng do thay đổi múi giờ. Hãy chọn giờ khác.'],
        ] as [$value, $timezone, $message]) {
            try {
                EventWallTime::toUtc($value, $timezone, 'starts_at');
                $this->fail('An invalid or ambiguous local time must be rejected.');
            } catch (ValidationException $exception) {
                $this->assertSame([$message], $exception->errors()['starts_at']);
            }
        }
    }

    public function test_stale_notification_has_vietnamese_fallback_without_exposing_unavailable_content(): void
    {
        $owner = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $owner->id, 'status' => 'ACTIVE']);
        $notice = AppNotification::createNotification(
            userId: $owner->id,
            communityId: $community->id,
            type: 'course_published',
            dedupeKey: 'sweep:unavailable-course',
            data: ['course_id' => 99999999, 'published_revision' => 1],
        );

        $response = $this->actingAs($owner)
            ->get(route('communities.notifications.follow', [$community, $notice]));
        $response->assertRedirect(route('communities.notifications.index', $community));
        $this->followRedirects($response)->assertSee('Nội dung bạn yêu cầu không còn khả dụng.')
            ->assertDontSee('The requested content is no longer available.');
        $this->assertNotNull($notice->fresh()->read_at);
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
