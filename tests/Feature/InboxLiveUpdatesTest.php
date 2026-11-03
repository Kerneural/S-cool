<?php

namespace Tests\Feature;

use App\Jobs\SendPostReplyNotification;
use App\Livewire\ConversationInbox;
use App\Livewire\ConversationThread;
use App\Livewire\InboxCenter;
use App\Models\AppNotification;
use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityConversation;
use App\Models\CommunityMembership;
use App\Models\CommunityMessage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class InboxLiveUpdatesTest extends TestCase
{
    use RefreshDatabase;

    private function pair(): array
    {
        $community = Community::factory()->create();
        $member = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);
        [$one, $two] = CommunityConversation::canonicalParticipants($community->creator_id, $member->id);
        $thread = CommunityConversation::create(['community_id' => $community->id, 'user_one_id' => $one, 'user_two_id' => $two]);

        return [$community, $community->creator, $member, $thread];
    }

    private function message($thread, $sender, string $body = 'A fresh private message'): CommunityMessage
    {
        return CommunityMessage::create(['conversation_id' => $thread->id, 'sender_id' => $sender->id, 'body' => $body]);
    }

    public function test_poll_updates_badge_and_preview_without_marking_messages_read_or_repeating_toasts(): void
    {
        [$community, $creator, $member, $thread] = $this->pair();
        $ui = Livewire::actingAs($member)->test(InboxCenter::class, ['community' => $community])->assertSet('messageCount', 0);
        $message = $this->message($thread, $creator);
        $ui->call('refreshInbox')->assertSet('messageCount', 1)->assertSee($message->body)->assertDispatched('inbox-arrived');
        $this->assertNull($message->fresh()->read_at);
        $ui = Livewire::actingAs($member)->test(InboxCenter::class, ['community' => $community]);
        $ui->call('refreshInbox')->assertNotDispatched('inbox-arrived');
        $message->update(['read_at' => now()]);
        $ui->call('refreshInbox')->assertSet('messageCount', 0)->assertNotDispatched('inbox-arrived');
    }

    public function test_only_opening_the_thread_marks_it_read_and_send_keeps_immediate_rendering(): void
    {
        [$community, $creator, $member, $thread] = $this->pair();
        $message = $this->message($thread, $creator);
        Livewire::actingAs($member)->test(InboxCenter::class, ['community' => $community])->call('refreshInbox');
        $this->assertNull($message->fresh()->read_at);
        Livewire::actingAs($member)->test(InboxCenter::class, ['community' => $community])->call('openConversation', $thread->id)->assertSee($message->body);
        $this->assertNotNull($message->fresh()->read_at);
        Livewire::actingAs($member)->test(ConversationThread::class, ['community' => $community, 'conversation' => $thread, 'compact' => true])
            ->set('body', "Reply immediately\nSecond line")->call('send')->assertSet('body', '')
            ->assertSee('Reply immediately')->assertDispatched('inbox-refresh');
        $this->assertSame(2, $thread->messages()->count());
    }

    public function test_poll_rechecks_both_participants_and_clears_revoked_member_metadata(): void
    {
        [$community, $creator, $member, $thread] = $this->pair();
        $this->message($thread, $creator, 'Private preview must disappear');
        $ui = Livewire::actingAs($member)->test(InboxCenter::class, ['community' => $community])->assertSet('messageCount', 1);
        CommunityMembership::where('community_id', $community->id)->where('user_id', $member->id)->update(['status' => 'SUSPENDED']);
        $ui->call('refreshInbox')->assertSet('available', false)->assertSet('messageCount', 0)->assertSet('threads', [])->assertDontSee('Private preview must disappear');
        Livewire::actingAs($creator)->test(InboxCenter::class, ['community' => $community])->assertSet('messageCount', 0)->assertSet('threads', []);
    }

    public function test_foreign_threads_and_locked_ids_cannot_be_used_to_read_another_tenant(): void
    {
        [$community, $creator, $member] = $this->pair();
        [$foreign, $other, $outsider, $thread] = $this->pair();
        $this->message($thread, $other, 'Foreign private body');
        Livewire::actingAs($member)->test(InboxCenter::class, ['community' => $community])->call('openConversation', $thread->id)->assertNotFound();
        Livewire::actingAs($outsider)->test(InboxCenter::class, ['community' => $community])->assertSet('available', false)->assertDontSee('Foreign private body');
        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::actingAs($member)->test(InboxCenter::class, ['community' => $community])->set('communityId', $foreign->id);
    }

    public function test_notifications_update_and_mark_all_is_scoped_to_visible_recipient_records(): void
    {
        [$community, $creator, $member] = $this->pair();
        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $creator->id]);
        $ui = Livewire::actingAs($creator)->test(InboxCenter::class, ['community' => $community])->assertSet('notificationCount', 0);
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $member->id]);
        (new SendPostReplyNotification($comment->id))->handle();
        $ui->call('refreshInbox')->assertSet('notificationCount', 1)->assertDispatched('inbox-arrived');
        $notice = AppNotification::where('notifiable_id', $creator->id)->firstOrFail();
        $this->assertNull($notice->fresh()->read_at);
        Livewire::actingAs($member)->test(InboxCenter::class, ['community' => $community])->call('markAllRead');
        $this->assertNull($notice->fresh()->read_at);
        $this->actingAs($creator);
        $ui->call('markAllRead')->assertSet('notificationCount', 0);
        $this->assertNotNull($notice->fresh()->read_at);
    }

    public function test_deleted_reply_disappears_from_the_next_notification_snapshot(): void
    {
        [$community, $creator, $member] = $this->pair();
        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $creator->id]);
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $member->id]);
        (new SendPostReplyNotification($comment->id))->handle();
        $ui = Livewire::actingAs($creator)->test(InboxCenter::class, ['community' => $community])->assertSet('notificationCount', 1);
        $comment->delete();
        $ui->call('refreshInbox')->assertSet('notificationCount', 0)->assertSet('notices', []);
    }

    public function test_inbox_list_refreshes_new_preview_and_thread_displays_vietnam_time_without_rewriting_storage(): void
    {
        [$community, $creator, $member, $thread] = $this->pair();
        $ui = Livewire::actingAs($member)->test(ConversationInbox::class, ['community' => $community]);
        $message = $this->message($thread, $creator, 'A newly arrived inbox row');
        $message->forceFill(['created_at' => '2026-11-03 23:05:00'])->save();
        $ui->call('$refresh')->assertSee('A newly arrived inbox row');
        Livewire::actingAs($member)->test(ConversationThread::class, ['community' => $community, 'conversation' => $thread])
            ->assertSee('04/11 · 06:05')->assertSee('UTC+7');
        $this->assertSame('2026-11-03 23:05:00', $message->fresh()->getRawOriginal('created_at'));
    }

    public function test_calendar_month_heading_uses_vietnamese_locale(): void
    {
        [$community, $creator] = $this->pair();
        app()->setLocale('vi');
        $this->actingAs($creator)->get(route('communities.events.index', [$community, 'month' => '2026-11']))
            ->assertOk()->assertViewHas('monthLabel', 'tháng 11 2026')->assertDontSee('November');
    }

    public function test_compact_chat_does_not_inherit_the_inbox_page_query(): void
    {
        [$community, $creator, $member, $thread] = $this->pair();
        for ($i = 1; $i <= 31; $i++) {
            $this->message($thread, $creator, sprintf('History marker %03d', $i));
        }
        Livewire::actingAs($member)->withQueryParams(['page' => 2])
            ->test(ConversationThread::class, ['community' => $community, 'conversation' => $thread, 'compact' => true])
            ->assertSee('History marker 031')->assertDontSee('History marker 001');
        $this->assertSame(30, $thread->messages()->whereNotNull('read_at')->count());
    }
}
