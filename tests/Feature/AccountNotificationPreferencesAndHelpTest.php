<?php

namespace Tests\Feature;

use App\Jobs\SendCoursePublishedNotification;
use App\Jobs\SendMemberActivatedNotification;
use App\Jobs\SendPostReplyNotification;
use App\Models\AppNotification;
use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountNotificationPreferencesAndHelpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'mysql']);
    }

    public function test_ac01_user_disables_replies_and_future_reply_notice_is_not_delivered_while_others_are_unaffected(): void
    {
        $creator = User::factory()->create();
        $authorDisabled = User::factory()->create(['notify_replies' => false]);
        $authorEnabled = User::factory()->create(['notify_replies' => true]);
        $commenter = User::factory()->create();

        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $authorDisabled->id, 'status' => 'ACTIVE']);
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $authorEnabled->id, 'status' => 'ACTIVE']);
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $commenter->id, 'status' => 'ACTIVE']);

        // Post 1 by author with disabled replies
        $postDisabled = Post::factory()->create(['community_id' => $community->id, 'user_id' => $authorDisabled->id]);
        $comment1 = Comment::factory()->create(['post_id' => $postDisabled->id, 'user_id' => $commenter->id]);

        // Post 2 by author with enabled replies
        $postEnabled = Post::factory()->create(['community_id' => $community->id, 'user_id' => $authorEnabled->id]);
        $comment2 = Comment::factory()->create(['post_id' => $postEnabled->id, 'user_id' => $commenter->id]);

        // Dispatch job for post 1
        (new SendPostReplyNotification($comment1->id))->handle();
        // Dispatch job for post 2
        (new SendPostReplyNotification($comment2->id))->handle();

        // Author with disabled replies receives NO notification
        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $authorDisabled->id,
            'type' => 'post_reply',
        ]);

        // Author with enabled replies receives exactly ONE notification
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $authorEnabled->id,
            'type' => 'post_reply',
        ]);
        $this->assertSame(1, AppNotification::where('type', 'post_reply')->count());
    }

    public function test_ac02_re_enabling_applies_to_future_events_without_backfilling_and_preference_persists(): void
    {
        $creator = User::factory()->create();
        $author = User::factory()->create(['notify_replies' => false]);
        $commenter = User::factory()->create();

        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $author->id, 'status' => 'ACTIVE']);
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $commenter->id, 'status' => 'ACTIVE']);

        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $author->id]);

        // Comment 1 while disabled
        $oldComment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $commenter->id]);
        (new SendPostReplyNotification($oldComment->id))->handle();

        $this->assertSame(0, AppNotification::where('notifiable_id', $author->id)->count());

        // Re-enable via profile notifications route
        $patchRes = $this->actingAs($author)->patch(route('profile.notifications.update'), [
            'notify_replies' => '1',
            'notify_courses' => '1',
        ]);
        $patchRes->assertSessionHas('status', 'notification-preferences-updated');

        // Check preference persisted in database
        $author->refresh();
        $this->assertTrue($author->notify_replies);
        $this->assertTrue($author->notify_courses);

        // Verify it did not retroactively backfill old comment
        $this->assertSame(0, AppNotification::where('notifiable_id', $author->id)->count());

        // Comment 2 while enabled
        $newComment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $commenter->id]);
        (new SendPostReplyNotification($newComment->id))->handle();

        // Exactly one notification created for future event
        $this->assertSame(1, AppNotification::where('notifiable_id', $author->id)->where('type', 'post_reply')->count());
    }

    public function test_ac03_creator_operational_notices_cannot_be_disabled_and_settings_explain_the_distinction(): void
    {
        $creator = User::factory()->create([
            'notify_replies' => false,
            'notify_courses' => false,
        ]);

        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $newMember = User::factory()->create();
        $membership = CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $newMember->id,
            'status' => 'ACTIVE',
        ]);

        // Send creator operational notice (member_activated)
        (new SendMemberActivatedNotification($community->id, $membership->id, 'invitation', $membership->id))->handle();
        $notif = AppNotification::where('type', 'member_activated')->where('notifiable_id', $creator->id)->first();

        $this->assertNotNull($notif);
        $this->assertTrue($notif->isAvailableFor($creator));

        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $creator->id]);
        $this->actingAs($newMember)->post(route('communities.reports.store', $community), [
            'target_type' => 'POST', 'target_id' => $post->id, 'reason' => 'Required moderation notice despite optional preferences.',
        ])->assertSessionHas('status');
        $this->assertSame(1, AppNotification::where('type', 'content_reported')->where('notifiable_id', $creator->id)->count());

        // Settings view renders and explains the distinction
        $viewRes = $this->actingAs($creator)->get(route('profile.edit'));
        $viewRes->assertOk();
        $viewRes->assertSee('Account Notification Preferences');
        $viewRes->assertSee('Mandatory Creator Operational Notifications');
        $viewRes->assertSee('cannot be disabled');
    }

    public function test_ac04_invalid_spoofed_fields_cannot_change_roles_ownership_or_bypass_email_verification(): void
    {
        $user = User::factory()->create([
            'email' => 'original@example.com',
            'is_platform_admin' => false,
            'email_verified_at' => now(),
        ]);

        // Attempting to spoof is_platform_admin or email via notifications update
        $res = $this->actingAs($user)->patch(route('profile.notifications.update'), [
            'notify_replies' => '0',
            'notify_courses' => '0',
            'is_platform_admin' => 1,
            'email' => 'spoofed@example.com',
        ]);
        $res->assertSessionHas('status', 'notification-preferences-updated');

        $user->refresh();
        $this->assertFalse($user->notify_replies);
        $this->assertFalse($user->notify_courses);
        $this->assertFalse((bool) $user->is_platform_admin);
        $this->assertSame('original@example.com', $user->email);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_ac05_help_page_works_for_guest_and_user_with_explicit_sepay_test_mode_prohibition(): void
    {
        // 1. Guest can access help
        $guestRes = $this->get(route('help.index'));
        $guestRes->assertOk();
        $guestRes->assertSee('Help Center');
        // Explicit prohibition of real money transfer
        $guestRes->assertSee('DO NOT transfer real money');
        $guestRes->assertSee('SePay Test Mode');
        $guestRes->assertSee(route('communities.index'));

        // 2. Authenticated user can access help
        $user = User::factory()->create();
        $userRes = $this->actingAs($user)->get(route('help.index'));
        $userRes->assertOk();
        $userRes->assertSee('DO NOT transfer real money');
        $userRes->assertSee(route('profile.edit'));
        $userRes->assertSee(route('account.payment-history.index'));

        // 3. Navigation bars contain help link
        $dashRes = $this->actingAs($user)->get(route('dashboard'));
        $dashRes->assertOk();
        $dashRes->assertSee(route('help.index'));
    }

    public function test_course_delivery_respects_preferences_and_existing_notices_remain_visible(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $enabled = User::factory()->create(['notify_courses' => true]);
        $disabled = User::factory()->create(['notify_courses' => false]);
        foreach ([$enabled, $disabled] as $member) {
            CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);
        }
        $course = Course::factory()->create(['community_id' => $community->id, 'status' => 'PUBLISHED', 'published_revision' => 1]);
        $job = new SendCoursePublishedNotification($course->id, 1);
        $job->handle();
        $job->handle();
        $this->assertSame(1, AppNotification::where('type', 'course_published')->where('notifiable_id', $enabled->id)->count());
        $this->assertSame(0, AppNotification::where('type', 'course_published')->where('notifiable_id', $disabled->id)->count());

        $this->actingAs($enabled)->patch(route('profile.notifications.update'), [
            'notify_replies' => true, 'notify_courses' => false,
        ])->assertSessionHas('status', 'notification-preferences-updated');
        $course2 = Course::factory()->create(['community_id' => $community->id, 'status' => 'PUBLISHED', 'published_revision' => 1]);
        (new SendCoursePublishedNotification($course2->id, 1))->handle();
        $this->assertSame(1, AppNotification::where('type', 'course_published')->where('community_id', $community->id)->count());
        $this->get(route('communities.notifications.index', $community))->assertOk()->assertSee($course->title);
    }
}
