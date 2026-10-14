<?php

namespace Tests\Feature;

use App\Jobs\SendContentReportedNotification;
use App\Models\AppNotification;
use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\ContentReport;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ContentReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'mysql']);
    }

    public function test_ac01_report_content_creates_one_open_record_and_one_deduplicated_creator_notification_and_duplicate_does_not_multiply_rows(): void
    {
        $creator = User::factory()->create();
        $member = User::factory()->create();

        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'Sample Post',
            'body' => 'Some content to report',
        ]);

        // 1. Member reports post
        $response = $this->actingAs($member)->post(route('communities.reports.store', $community), [
            'target_type' => 'POST',
            'target_id' => $post->id,
            'reason' => 'This post violates community standards with spam content.',
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('content_reports', [
            'community_id' => $community->id,
            'reporter_id' => $member->id,
            'target_type' => 'POST',
            'target_id' => $post->id,
            'status' => 'OPEN',
        ]);
        $this->assertSame(1, ContentReport::count());

        // Exactly one deduplicated notification for creator
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $creator->id,
            'community_id' => $community->id,
            'type' => 'content_reported',
        ]);
        $this->assertSame(1, AppNotification::where('type', 'content_reported')->count());

        // 2. Duplicate report replay by the same reporter
        $replay = $this->actingAs($member)->post(route('communities.reports.store', $community), [
            'target_type' => 'POST',
            'target_id' => $post->id,
            'reason' => 'Duplicate attempt to submit report on same target.',
        ]);

        $replay->assertSessionHas('info');
        $this->assertSame(1, ContentReport::count());
        $this->assertSame(1, AppNotification::where('type', 'content_reported')->count());
    }

    public function test_ac02_report_wrong_target_deleted_source_short_reason_or_outsider_is_rejected(): void
    {
        $creator = User::factory()->create();
        $member = User::factory()->create();
        $outsider = User::factory()->create();

        $communityA = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $communityB = Community::factory()->create(['status' => 'ACTIVE']);

        CommunityMembership::factory()->create([
            'community_id' => $communityA->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $postB = Post::factory()->create([
            'community_id' => $communityB->id,
            'body' => 'Post in community B',
        ]);

        $deletedPostA = Post::factory()->create([
            'community_id' => $communityA->id,
            'body' => 'Deleted post in community A',
        ]);
        $deletedPostA->delete();

        // Target from community B submitted to community A
        $resWrongTenant = $this->actingAs($member)->post(route('communities.reports.store', $communityA), [
            'target_type' => 'POST',
            'target_id' => $postB->id,
            'reason' => 'Reporting post that belongs to another tenant.',
        ]);
        $resWrongTenant->assertSessionHasErrors('target_id');
        $this->assertSame(0, ContentReport::count());

        // Deleted target
        $resDeleted = $this->actingAs($member)->post(route('communities.reports.store', $communityA), [
            'target_type' => 'POST',
            'target_id' => $deletedPostA->id,
            'reason' => 'Reporting deleted post in community A.',
        ]);
        $resDeleted->assertSessionHasErrors('target_id');
        $this->assertSame(0, ContentReport::count());

        // Reason too short (< 10 chars)
        $validPostA = Post::factory()->create(['community_id' => $communityA->id]);
        $resShortReason = $this->actingAs($member)->post(route('communities.reports.store', $communityA), [
            'target_type' => 'POST',
            'target_id' => $validPostA->id,
            'reason' => 'Too short',
        ]);
        $resShortReason->assertSessionHasErrors('reason');
        $this->assertSame(0, ContentReport::count());

        // Outsider not member of community A
        $resOutsider = $this->actingAs($outsider)->post(route('communities.reports.store', $communityA), [
            'target_type' => 'POST',
            'target_id' => $validPostA->id,
            'reason' => 'Outsider trying to report content without membership.',
        ]);
        $resOutsider->assertStatus(403);
        $this->assertSame(0, ContentReport::count());
    }

    public function test_ac03_creator_removes_or_dismisses_report_and_unauthorized_users_are_denied(): void
    {
        $creatorA = User::factory()->create();
        $creatorB = User::factory()->create();
        $memberA = User::factory()->create();

        $communityA = Community::factory()->create(['creator_id' => $creatorA->id, 'status' => 'ACTIVE']);
        $communityB = Community::factory()->create(['creator_id' => $creatorB->id, 'status' => 'ACTIVE']);

        CommunityMembership::factory()->create(['community_id' => $communityA->id, 'user_id' => $memberA->id, 'status' => 'ACTIVE']);

        $post = Post::factory()->create(['community_id' => $communityA->id]);

        $report = ContentReport::create([
            'community_id' => $communityA->id,
            'reporter_id' => $memberA->id,
            'target_type' => 'POST',
            'target_id' => $post->id,
            'reason' => 'Harassment and abusive content violation.',
            'status' => 'OPEN',
        ]);

        // Member cannot read moderation queue
        $this->actingAs($memberA)->get(route('communities.reports.index', $communityA))->assertStatus(404);

        // Creator B cannot read community A queue
        $this->actingAs($creatorB)->get(route('communities.reports.index', $communityA))->assertStatus(404);

        // Creator A can read queue and see report
        $indexResponse = $this->actingAs($creatorA)->get(route('communities.reports.index', $communityA));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Harassment and abusive content violation.');

        // Creator B cannot dismiss or remove report of community A
        $this->actingAs($creatorB)->post(route('communities.reports.dismiss', [$communityA, $report]), [
            'resolution_reason' => 'Unauthorized attempt by creator B',
        ])->assertStatus(404);

        // Creator A dismisses report
        $dismissRes = $this->actingAs($creatorA)->post(route('communities.reports.dismiss', [$communityA, $report]), [
            'resolution_reason' => 'Reviewed and found no violation.',
        ]);
        $dismissRes->assertSessionHas('status');

        $report->refresh();
        $this->assertSame('DISMISSED', $report->status);
        $this->assertSame('DISMISSED', $report->resolution_action);
        $this->assertSame('Reviewed and found no violation.', $report->resolution_reason);
        $this->assertSame($creatorA->id, $report->resolved_by);
        $this->assertNotNull($report->resolved_at);

        // Target post is still intact (not deleted)
        $this->assertFalse($post->fresh()->trashed());
    }

    public function test_ac04_remove_retains_report_and_soft_deletes_target_and_conflicting_resolutions_cannot_overwrite_terminal_outcome(): void
    {
        $creator = User::factory()->create();
        $member = User::factory()->create();

        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);

        $post = Post::factory()->create(['community_id' => $community->id]);
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $member->id, 'body' => 'Rude comment']);

        $report = ContentReport::create([
            'community_id' => $community->id,
            'reporter_id' => $member->id,
            'target_type' => 'COMMENT',
            'target_id' => $comment->id,
            'reason' => 'Offensive comment in discussion.',
            'status' => 'OPEN',
        ]);

        // Remove target
        $removeRes = $this->actingAs($creator)->post(route('communities.reports.remove', [$community, $report]), [
            'resolution_reason' => 'Deleted comment violating guidelines.',
        ]);
        $removeRes->assertSessionHas('status');

        $report->refresh();
        $this->assertSame('RESOLVED', $report->status);
        $this->assertSame('REMOVED', $report->resolution_action);
        $this->assertSame('Deleted comment violating guidelines.', $report->resolution_reason);
        $this->assertTrue($comment->fresh()->trashed()); // Comment is soft-deleted

        // Conflicting subsequent action (e.g. dismiss or another remove) cannot overwrite terminal outcome
        $this->actingAs($creator)->post(route('communities.reports.dismiss', [$community, $report]), [
            'resolution_reason' => 'Trying to dismiss after resolved.',
        ]);

        $report->refresh();
        $this->assertSame('RESOLVED', $report->status);
        $this->assertSame('REMOVED', $report->resolution_action);
        $this->assertSame('Deleted comment violating guidelines.', $report->resolution_reason);
    }

    public function test_ac05_unavailable_display_for_deleted_reporter_or_target_and_sensitive_text_escaping(): void
    {
        $creator = User::factory()->create();
        $member = User::factory()->create();

        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);

        $post = Post::factory()->create(['community_id' => $community->id]);
        $sensitiveReason = '<script>alert("XSS")</script> & sensitive details';

        // Submit report with HTML/sensitive text
        $this->actingAs($member)->post(route('communities.reports.store', $community), [
            'target_type' => 'POST',
            'target_id' => $post->id,
            'reason' => $sensitiveReason,
        ]);

        // Verify notification does NOT leak the sensitive reason string
        $notification = AppNotification::where('type', 'content_reported')->first();
        $this->assertNotNull($notification);
        $this->assertArrayNotHasKey('reason', $notification->data);
        $this->assertStringNotContainsString('alert', $notification->summary());

        // Soft delete the post and delete reporter (clearing membership so user delete cascades to reports via nullOnDelete)
        $post->delete();
        $member->memberships()->delete();
        $member->delete();

        // View queue
        $queueRes = $this->actingAs($creator)->get(route('communities.reports.index', $community));
        $queueRes->assertOk();
        // Target display indicates removed content
        $queueRes->assertSee('[Content removed]');
        // Reporter display indicates unavailable deleted user
        $queueRes->assertSee('Unavailable user');
        // Sensitive text is escaped in HTML
        $queueRes->assertSee('&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;', false);
        $queueRes->assertDontSee('<script>alert("XSS")</script>', false);
    }

    public function test_remove_and_dismiss_re_authorize_community_and_creator_under_lock(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $post = Post::factory()->create(['community_id' => $community->id]);
        $report = ContentReport::create([
            'community_id' => $community->id,
            'reporter_id' => $creator->id,
            'target_type' => ContentReport::TARGET_POST,
            'target_id' => $post->id,
            'reason' => 'Testing authorization re-verification under lock',
            'status' => ContentReport::STATUS_OPEN,
        ]);

        // 1. Deactivated community fails
        $community->status = 'ARCHIVED';
        $community->save();
        $resDeactivated = $this->actingAs($creator)->post(route('communities.reports.remove', [$community, $report]));
        $resDeactivated->assertNotFound();
        $this->assertFalse($post->fresh()->trashed());
        $this->assertTrue($report->fresh()->isOpen());

        // 2. Non-creator user fails
        $community->status = 'ACTIVE';
        $community->save();
        $resNonCreator = $this->actingAs($otherUser)->post(route('communities.reports.remove', [$community, $report]));
        $resNonCreator->assertNotFound();
        $this->assertFalse($post->fresh()->trashed());
        $this->assertTrue($report->fresh()->isOpen());

        // 3. Dismiss on deactivated community fails
        $community->status = 'ARCHIVED';
        $community->save();
        $resDismissDeactivated = $this->actingAs($creator)->post(route('communities.reports.dismiss', [$community, $report]));
        $resDismissDeactivated->assertNotFound();
        $this->assertTrue($report->fresh()->isOpen());
    }

    public function test_notification_job_handles_retry_and_prevents_lost_notifications(): void
    {
        $creator = User::factory()->create();
        $member = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);
        $post = Post::factory()->create(['community_id' => $community->id]);

        // Submit report
        $res1 = $this->actingAs($member)->post(route('communities.reports.store', $community), [
            'target_type' => 'POST',
            'target_id' => $post->id,
            'reason' => 'First submission testing retry safety mechanism',
        ]);
        $res1->assertSessionHas('status');
        $this->assertSame(1, AppNotification::where('type', 'content_reported')->count());

        // Delete the notification as if initial delivery or network failed
        AppNotification::where('type', 'content_reported')->delete();
        $this->assertSame(0, AppNotification::where('type', 'content_reported')->count());

        // Replay/retry the report submission
        $res2 = $this->actingAs($member)->post(route('communities.reports.store', $community), [
            'target_type' => 'POST',
            'target_id' => $post->id,
            'reason' => 'Retry submission after transient failure',
        ]);
        $res2->assertSessionHas('info');

        // Retry re-dispatched the notification job and safely restored notification without duplicating reports
        $this->assertSame(1, ContentReport::count());
        $this->assertSame(1, AppNotification::where('type', 'content_reported')->count());
    }

    public function test_array_input_for_status_query_does_not_cause_500_or_type_error(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        // Probe status[]=OPEN array query parameter
        $response = $this->actingAs($creator)->get(route('communities.reports.index', [
            'community' => $community,
            'status' => ['OPEN'],
        ]));

        $response->assertOk();
        $response->assertViewHas('status', ContentReport::STATUS_OPEN);
    }

    public function test_deleting_reporter_preserves_content_report_history_with_null_reporter_id(): void
    {
        $creator = User::factory()->create();
        $member = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);
        $post = Post::factory()->create(['community_id' => $community->id]);

        $this->actingAs($member)->post(route('communities.reports.store', $community), [
            'target_type' => 'POST',
            'target_id' => $post->id,
            'reason' => 'Report history should survive reporter account deletion.',
        ]);

        $report = ContentReport::firstOrFail();
        $this->assertSame($member->id, $report->reporter_id);

        // Delete membership first (RESTRICT FK), then delete member
        $member->memberships()->delete();
        $member->delete();

        // Report row is NOT cascade-deleted by foreign key; reporter_id is set to null
        $report->refresh();
        $this->assertNull($report->reporter_id);
        $this->assertSame(1, ContentReport::count());

        // Moderation queue renders report with 'Unavailable user'
        $queueRes = $this->actingAs($creator)->get(route('communities.reports.index', $community));
        $queueRes->assertOk();
        $queueRes->assertSee('Unavailable user');
        $queueRes->assertSee('Report history should survive reporter account deletion.');
    }

    public function test_report_submission_rechecks_access_and_target_after_preflight(): void
    {
        foreach (['community', 'membership', 'post'] as $changedState) {
            $creator = User::factory()->create();
            $member = User::factory()->create();
            $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
            $membership = CommunityMembership::factory()->create([
                'community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE',
            ]);
            $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $creator->id]);
            $injected = false;

            // The initial target lookup succeeds, but state changes before the write locks.
            Post::retrieved(function (Post $retrieved) use ($post, $community, $membership, $changedState, &$injected): void {
                if ($retrieved->id !== $post->id || $injected) {
                    return;
                }
                $injected = true;
                match ($changedState) {
                    'community' => DB::table('communities')->where('id', $community->id)->update(['status' => 'SUSPENDED']),
                    'membership' => DB::table('community_memberships')->where('id', $membership->id)->update(['status' => 'REMOVED']),
                    'post' => DB::table('posts')->where('id', $post->id)->update(['deleted_at' => now()]),
                };
            });

            try {
                $response = $this->actingAs($member)->post(route('communities.reports.store', $community), [
                    'target_type' => 'POST', 'target_id' => $post->id, 'reason' => 'Report submitted during a state transition.',
                ]);
                $response->assertStatus($changedState === 'membership' ? 403 : 404);
                $this->assertTrue($injected);
                $this->assertSame(0, ContentReport::where('community_id', $community->id)->count());
                $this->assertSame(0, AppNotification::where('community_id', $community->id)->count());
            } finally {
                Post::flushEventListeners();
            }
        }
    }

    public function test_remove_and_dismiss_recheck_changes_after_route_binding(): void
    {
        foreach (['remove', 'dismiss'] as $action) {
            foreach (['community', 'creator'] as $changedState) {
                $creator = User::factory()->create();
                $replacement = User::factory()->create();
                $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
                $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $creator->id]);
                $report = ContentReport::create([
                    'community_id' => $community->id, 'reporter_id' => $creator->id,
                    'target_type' => 'POST', 'target_id' => $post->id, 'reason' => 'Reported content before a state transition.', 'status' => 'OPEN',
                ]);
                $injected = false;
                Community::retrieved(function (Community $retrieved) use ($community, $replacement, $changedState, &$injected): void {
                    if ($retrieved->id !== $community->id || $injected) {
                        return;
                    }
                    $injected = true;
                    DB::table('communities')->where('id', $community->id)->update(
                        $changedState === 'community' ? ['status' => 'SUSPENDED'] : ['creator_id' => $replacement->id]
                    );
                });
                try {
                    $response = $this->actingAs($creator)->post(route("communities.reports.{$action}", [$community, $report]), [
                        'resolution_reason' => 'Review completed after a concurrent change.',
                    ]);
                    $response->assertStatus($changedState === 'community' ? 404 : 403);
                    $this->assertTrue($injected);
                    $this->assertFalse($post->fresh()->trashed());
                    $this->assertTrue($report->fresh()->isOpen());
                } finally {
                    Community::flushEventListeners();
                }
            }
        }
    }

    public function test_distinct_reports_on_same_target_get_distinct_notifications_without_retry_duplicates(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $creator->id]);
        foreach ([1, 2] as $number) {
            $member = User::factory()->create();
            CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);
            $payload = ['target_type' => 'POST', 'target_id' => $post->id, 'reason' => 'An independent member reports the same content.'];
            $this->actingAs($member)->post(route('communities.reports.store', $community), $payload)->assertSessionHas('status');
            $this->assertSame($number, AppNotification::where('community_id', $community->id)->count());
            $this->assertSame(1, AppNotification::unreadCountFor($creator, $community));
            $this->post(route('communities.reports.store', $community), $payload)->assertSessionHas('info');
            $this->assertSame($number, AppNotification::where('community_id', $community->id)->count());
            AppNotification::where('community_id', $community->id)->whereNull('read_at')->update(['read_at' => now()]);
        }
        $this->assertSame(2, ContentReport::where('community_id', $community->id)->count());
    }

    public function test_legacy_report_notice_is_not_duplicated_by_new_delivery_key(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $post = Post::factory()->create(['community_id' => $community->id]);
        $report = ContentReport::create([
            'community_id' => $community->id, 'reporter_id' => $creator->id, 'target_type' => 'POST',
            'target_id' => $post->id, 'reason' => 'Existing report before notification key upgrade.', 'status' => 'OPEN',
        ]);
        $notice = AppNotification::createNotification($creator->id, $community->id, 'content_reported',
            "content_reported:community_{$community->id}:POST_{$post->id}", ['report_id' => $report->id]);
        $notice->markAsRead();
        (new SendContentReportedNotification($community->id, $report->id))->handle();
        $this->assertSame(1, AppNotification::where('community_id', $community->id)->count());
        $this->assertSame(0, AppNotification::unreadCountFor($creator, $community));
    }

    public function test_report_notice_database_worker_retries_failed_delivery_and_rollback_enqueues_nothing(): void
    {
        config(['queue.default' => 'database']);
        $creator = User::factory()->create();
        $member = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);
        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $creator->id]);
        DB::table('jobs')->delete();
        $this->actingAs($member)->post(route('communities.reports.store', $community), [
            'target_type' => 'POST', 'target_id' => $post->id, 'reason' => 'Testing delivery through a real database worker.',
        ])->assertSessionHas('status');
        $this->assertSame(1, DB::table('jobs')->count());
        $this->assertSame(0, AppNotification::where('community_id', $community->id)->count());
        AppNotification::saving(function (): void {
            throw new \RuntimeException('Synthetic transient notification write failure');
        });
        try {
            Artisan::call('queue:work', ['--once' => true, '--tries' => 2, '--backoff' => 0]);
        } finally {
            AppNotification::flushEventListeners();
        }
        $this->assertSame(1, DB::table('jobs')->count());
        $this->assertSame(0, AppNotification::where('community_id', $community->id)->count());
        Artisan::call('queue:work', ['--once' => true, '--tries' => 2]);
        $this->assertSame(0, DB::table('jobs')->count());
        $this->assertSame(1, AppNotification::where('community_id', $community->id)->count());

        $jobsBefore = DB::table('jobs')->count();
        try {
            DB::transaction(function () use ($community): void {
                SendContentReportedNotification::dispatch($community->id, 0);
                throw new \RuntimeException('Synthetic rollback');
            });
        } catch (\RuntimeException $e) {
            $this->assertSame('Synthetic rollback', $e->getMessage());
        }
        $this->assertSame($jobsBefore, DB::table('jobs')->count());
    }
}
