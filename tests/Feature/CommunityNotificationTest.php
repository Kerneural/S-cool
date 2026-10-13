<?php

namespace Tests\Feature;

use App\Jobs\SendCoursePublishedNotification;
use App\Jobs\SendMemberActivatedNotification;
use App\Jobs\SendPostReplyNotification;
use App\Models\AppNotification;
use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityInvitation;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CommunityNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_another_member_reply_emits_one_notice_to_author_after_commit_while_own_reply_emits_none(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        $author = User::factory()->create(['name' => 'Author User']);
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $author->id,
            'status' => 'ACTIVE',
        ]);

        $replier = User::factory()->create(['name' => 'Replier User']);
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $replier->id,
            'status' => 'ACTIVE',
        ]);

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $author->id,
            'title' => 'Original Post Title',
        ]);

        // 1. Another member replies via POST endpoint
        $this->actingAs($replier)->post(route('communities.posts.comments.store', [$community, $post]), [
            'body' => 'Great post!',
        ])->assertRedirect(route('communities.posts.show', [$community, $post]));

        $comment = Comment::where('post_id', $post->id)->where('user_id', $replier->id)->firstOrFail();

        // Process queued job synchronously for testing
        (new SendPostReplyNotification($comment->id))->handle();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $author->id,
            'community_id' => $community->id,
            'type' => 'post_reply',
            'dedupe_key' => "post_reply:comment_{$comment->id}:recipient_{$author->id}",
        ]);
        $this->assertEquals(1, AppNotification::where('notifiable_id', $author->id)->count());

        // 2. Author replies to their own post -> own reply sends none (AC-01)
        $this->actingAs($author)->post(route('communities.posts.comments.store', [$community, $post]), [
            'body' => 'My own followup comment.',
        ])->assertRedirect(route('communities.posts.show', [$community, $post]));

        $ownComment = Comment::where('post_id', $post->id)->where('user_id', $author->id)->firstOrFail();
        (new SendPostReplyNotification($ownComment->id))->handle();

        $this->assertEquals(1, AppNotification::where('notifiable_id', $author->id)->count());

        // Author views inbox and sees the reply notification
        $response = $this->actingAs($author)->get(route('communities.notifications.index', $community));
        $response->assertOk();
        $response->assertSee('Replier User replied to your post.');
        $response->assertSee('Notification Center');
    }

    public function test_delivery_retry_and_duplicate_source_emits_one_notification_per_recipient_and_rollback_emits_none(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        $author = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $author->id,
            'status' => 'ACTIVE',
        ]);

        $replier = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $replier->id,
            'status' => 'ACTIVE',
        ]);

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $author->id,
        ]);

        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $replier->id,
            'body' => 'First reply',
        ]);

        // Duplicate delivery / worker retry with the same source
        $job = new SendPostReplyNotification($comment->id);
        $job->handle();
        $job->handle();
        $job->handle();

        $this->assertEquals(1, AppNotification::where('notifiable_id', $author->id)->count());

        // Rolled-back write emits none (AC-02)
        try {
            DB::transaction(function () use ($post, $replier) {
                $failedComment = Comment::create([
                    'post_id' => $post->id,
                    'user_id' => $replier->id,
                    'body' => 'Will fail',
                ]);
                SendPostReplyNotification::dispatch($failedComment->id);
                throw new \Exception('Rollback simulation');
            });
        } catch (\Exception $e) {
            // Expected rollback
        }

        // Only original 1 notification exists
        $this->assertEquals(1, AppNotification::where('notifiable_id', $author->id)->count());
    }

    public function test_mark_read_and_read_all_enforces_ownership_and_updates_badge_persisting_after_reload(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        $userA = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $userA->id,
            'status' => 'ACTIVE',
        ]);

        $userB = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $userB->id,
            'status' => 'ACTIVE',
        ]);

        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $userA->id]);
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $userB->id]);

        $notification = AppNotification::createNotification(
            userId: $userA->id,
            communityId: $community->id,
            type: 'post_reply',
            dedupeKey: "post_reply:comment_{$comment->id}:recipient_{$userA->id}",
            data: ['post_id' => $post->id, 'comment_id' => $comment->id, 'commenter_id' => $userB->id],
        );

        $this->assertNull($notification->read_at);
        $this->assertEquals(1, AppNotification::unreadCountFor($userA, $community));

        // User B cannot mark User A's notification as read (403 Forbidden - AC-03)
        $this->actingAs($userB)
            ->patch(route('communities.notifications.read', [$community, $notification]))
            ->assertForbidden();

        $this->assertNull($notification->fresh()->read_at);

        // User A marks own notification read
        $this->actingAs($userA)
            ->patch(route('communities.notifications.read', [$community, $notification]))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertEquals(0, AppNotification::unreadCountFor($userA, $community));

        // Reload preserves updated badge (0)
        $response = $this->actingAs($userA)->get(route('communities.notifications.index', $community));
        $response->assertOk();
        $response->assertDontSee('Mark all as read');

        // Add 2 new notifications for User A, then mark all as read
        $comment2 = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $userB->id]);
        AppNotification::createNotification(
            userId: $userA->id,
            communityId: $community->id,
            type: 'post_reply',
            dedupeKey: "post_reply:comment_{$comment2->id}:recipient_{$userA->id}",
            data: ['post_id' => $post->id, 'comment_id' => $comment2->id, 'commenter_id' => $userB->id],
        );

        $this->assertEquals(1, AppNotification::unreadCountFor($userA, $community));

        $this->actingAs($userA)
            ->post(route('communities.notifications.read-all', $community))
            ->assertRedirect();

        $this->assertEquals(0, AppNotification::unreadCountFor($userA, $community));
    }

    public function test_removed_member_unpublished_course_deleted_post_or_suspended_community_yields_zero_leakage(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        $member = User::factory()->create();
        $membership = CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'title' => 'Secret Deleted Post']);
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $creator->id]);

        $postNotice = AppNotification::createNotification(
            userId: $member->id,
            communityId: $community->id,
            type: 'post_reply',
            dedupeKey: "post_reply:comment_{$comment->id}:recipient_{$member->id}",
            data: ['post_id' => $post->id, 'comment_id' => $comment->id, 'commenter_id' => $creator->id],
        );

        $course = Course::factory()->create([
            'community_id' => $community->id,
            'title' => 'Draft Unpublished Course',
            'status' => 'PUBLISHED',
            'published_revision' => 1,
        ]);

        $courseNotice = AppNotification::createNotification(
            userId: $member->id,
            communityId: $community->id,
            type: 'course_published',
            dedupeKey: "course_published:course_{$course->id}_rev_1:recipient_{$member->id}",
            data: ['course_id' => $course->id, 'published_revision' => 1],
        );

        // Before changes: both available
        $this->assertTrue($postNotice->isAvailableFor($member));
        $this->assertTrue($courseNotice->isAvailableFor($member));
        $this->assertEquals(2, AppNotification::unreadCountFor($member, $community));

        // 1. Delete post -> post notification immediately disappears from inbox & unread badge
        $post->delete();
        $this->assertFalse($postNotice->isAvailableFor($member));
        $this->assertEquals(1, AppNotification::unreadCountFor($member, $community));

        // Follow deep link on deleted post: safely redirected without leaking title (AC-04)
        $followResponse = $this->actingAs($member)->get(route('communities.notifications.follow', [$community, $postNotice]));
        $followResponse->assertRedirect(route('communities.notifications.index', $community));
        $this->followRedirects($followResponse)->assertSee('The requested content is no longer available.');

        // 2. Unpublish course (switch back to DRAFT) -> course notification disappears
        $course->update(['status' => 'DRAFT']);
        $this->assertFalse($courseNotice->isAvailableFor($member));
        $this->assertEquals(0, AppNotification::unreadCountFor($member, $community));

        // 3. Suspend community -> access denied (404)
        $community->forceFill(['status' => 'SUSPENDED'])->save();
        $this->actingAs($member)->get(route('communities.notifications.index', $community))->assertNotFound();

        // 4. Removed member -> access denied (404)
        $community->forceFill(['status' => 'ACTIVE'])->save();
        $membership->forceFill(['status' => 'REMOVED'])->save();
        $this->actingAs($member)->get(route('communities.notifications.index', $community))->assertNotFound();
    }

    public function test_course_publish_fires_and_editing_while_already_published_does_not_repeat_notifications(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        $member1 = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member1->id,
            'status' => 'ACTIVE',
        ]);

        $member2 = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member2->id,
            'status' => 'ACTIVE',
        ]);

        // 1. Create course as DRAFT
        $course = Course::factory()->create([
            'community_id' => $community->id,
            'status' => 'DRAFT',
            'published_revision' => 0,
        ]);

        // 2. Transition from DRAFT to PUBLISHED via PUT endpoint
        $this->actingAs($creator)->put(route('communities.courses.update', [$community, $course]), [
            'title' => 'Mastering Laravel',
            'status' => 'PUBLISHED',
        ])->assertRedirect();

        $course->refresh();
        $this->assertEquals('PUBLISHED', $course->status);
        $this->assertEquals(1, $course->published_revision);

        // Process fan-out job
        (new SendCoursePublishedNotification($course->id, $course->published_revision))->handle();

        $this->assertEquals(1, AppNotification::where('notifiable_id', $member1->id)->count());
        $this->assertEquals(1, AppNotification::where('notifiable_id', $member2->id)->count());
        // Creator should not receive member notification
        $this->assertEquals(0, AppNotification::where('notifiable_id', $creator->id)->count());

        // 3. Update course while ALREADY published (e.g. edit description) -> must NOT repeat (AC-05)
        $this->actingAs($creator)->put(route('communities.courses.update', [$community, $course]), [
            'title' => 'Mastering Laravel Advanced',
            'description' => 'Updated content',
            'status' => 'PUBLISHED',
        ])->assertRedirect();

        $course->refresh();
        $this->assertEquals(1, $course->published_revision); // Revision unchanged

        // Notifications count remains unchanged
        $this->assertEquals(1, AppNotification::where('notifiable_id', $member1->id)->count());
        $this->assertEquals(1, AppNotification::where('notifiable_id', $member2->id)->count());

        // Retry job handles only eligible recipients with idempotent dedupe keys
        (new SendCoursePublishedNotification($course->id, 1))->handle();
        $this->assertEquals(1, AppNotification::where('notifiable_id', $member1->id)->count());
    }

    public function test_member_activated_notification_sent_to_creator_and_deduplicated(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        $newMember = User::factory()->create(['name' => 'Charlie Newbie']);
        $membership = CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $newMember->id,
            'status' => 'ACTIVE',
        ]);

        $invitation = CommunityInvitation::factory()->create([
            'community_id' => $community->id,
            'status' => 'ACCEPTED',
        ]);

        // Send activation notification
        $job = new SendMemberActivatedNotification($community->id, $membership->id, 'invitation', $invitation->id);
        $job->handle();
        $job->handle(); // Replay

        $this->assertEquals(1, AppNotification::where('notifiable_id', $creator->id)->count());
        $creatorNotice = AppNotification::where('notifiable_id', $creator->id)->firstOrFail();
        $this->assertEquals('member_activated', $creatorNotice->type);
        $this->assertEquals('Charlie Newbie joined the community.', $creatorNotice->summary());

        // Creator views inbox
        $response = $this->actingAs($creator)->get(route('communities.notifications.index', $community));
        $response->assertOk();
        $response->assertSee('Charlie Newbie joined the community.');
    }

    public function test_real_database_queue_worker_probe_processes_persisted_jobs_without_duplicates(): void
    {
        // One real database-worker probe is required; fake queue tests alone are insufficient (Delivery Plan F-08 / AC-06)
        config(['queue.default' => 'database']);

        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        $author = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $author->id,
            'status' => 'ACTIVE',
        ]);

        $replier = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $replier->id,
            'status' => 'ACTIVE',
        ]);

        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $author->id]);
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $replier->id]);

        // Truncate jobs table to start clean probe
        DB::table('jobs')->truncate();

        // 1. Dispatch job onto database queue while queue worker is simulated stopped
        SendPostReplyNotification::dispatch($comment->id);

        // Verify the job was persisted to MySQL `jobs` table (queue stopped leaves business facts valid - AC-06)
        $this->assertEquals(1, DB::table('jobs')->count());
        $this->assertEquals(0, AppNotification::where('notifiable_id', $author->id)->count());

        // 2. Start the real database queue worker for one iteration
        Artisan::call('queue:work', [
            '--once' => true,
            '--queue' => 'default',
        ]);

        // 3. Worker processed the job: jobs table empty, notification persisted
        $this->assertEquals(0, DB::table('jobs')->count());
        $this->assertEquals(1, AppNotification::where('notifiable_id', $author->id)->count());

        // 4. Replay job: deduplication ensures zero duplicates
        SendPostReplyNotification::dispatch($comment->id);
        $this->assertEquals(1, DB::table('jobs')->count());

        Artisan::call('queue:work', [
            '--once' => true,
            '--queue' => 'default',
        ]);

        $this->assertEquals(0, DB::table('jobs')->count());
        $this->assertEquals(1, AppNotification::where('notifiable_id', $author->id)->count());
    }
}
