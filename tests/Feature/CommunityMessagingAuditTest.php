<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityConversation;
use App\Models\CommunityMembership;
use App\Models\CommunityMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class CommunityMessagingAuditTest extends TestCase
{
    use RefreshDatabase;

    private function pair(): array
    {
        $community = Community::factory()->create();
        $users = User::factory()->count(2)->create();
        foreach ($users as $user) {
            CommunityMembership::forceCreate(['community_id' => $community->id, 'user_id' => $user->id, 'status' => 'ACTIVE']);
        }
        $conversation = CommunityConversation::create(['community_id' => $community->id,
            'user_one_id' => $users[0]->id, 'user_two_id' => $users[1]->id]);

        return [$community, $users[0], $users[1], $conversation];
    }

    public function test_send_requires_a_nonempty_retry_id_and_zero_is_a_valid_id(): void
    {
        [$community, $sender, $recipient, $conversation] = $this->pair();
        $url = route('communities.messages.store', [$community, $conversation]);
        foreach ([[], ['client_send_id' => '']] as $extra) {
            $this->actingAs($sender)->post($url, ['body' => 'Missing retry protection'] + $extra)->assertSessionHasErrors('client_send_id');
        }
        $this->assertSame(0, $conversation->messages()->count());
        foreach (range(1, 2) as $attempt) {
            $this->post($url, ['body' => 'Zero key', 'client_send_id' => '0'])->assertRedirect();
        }
        $this->assertSame(1, $conversation->messages()->count());
    }

    public function test_zero_retry_id_is_deduplicated(): void
    {
        [$community, $sender, $recipient, $conversation] = $this->pair();
        foreach (range(1, 2) as $attempt) {
            $this->actingAs($sender)->post(route('communities.messages.store', [$community, $conversation]), ['body' => 'Zero key', 'client_send_id' => '0'])->assertRedirect();
        }
        $this->assertSame(1, $conversation->messages()->count());
    }

    public function test_only_displayed_messages_are_marked_read(): void
    {
        [$community, $sender, $recipient, $conversation] = $this->pair();
        for ($i = 1; $i <= 35; $i++) {
            CommunityMessage::create(['conversation_id' => $conversation->id, 'sender_id' => $sender->id, 'body' => 'Private message '.$i]);
        }
        $this->actingAs($recipient)->get(route('communities.messages.show', [$community, $conversation]))->assertOk();
        $this->assertSame(30, $conversation->messages()->whereNotNull('read_at')->count());
        $this->assertSame(5, $conversation->messages()->whereNull('read_at')->count());
        $this->get(route('communities.messages.show', [$community, $conversation]).'?page=2')->assertOk();
        $this->assertSame(35, $conversation->messages()->whereNotNull('read_at')->count());
    }

    public function test_default_page_shows_latest_messages_in_chronological_order(): void
    {
        [$community, $sender, $recipient, $conversation] = $this->pair();
        for ($i = 1; $i <= 35; $i++) {
            CommunityMessage::create(['conversation_id' => $conversation->id, 'sender_id' => $sender->id, 'body' => 'Message marker '.str_pad((string) $i, 3, '0', STR_PAD_LEFT)]);
        }
        $this->actingAs($sender)->get(route('communities.messages.show', [$community, $conversation]))
            ->assertOk()->assertViewHas('messages', fn ($rows) => $rows->pluck('body')->all() === array_map(
                fn ($i) => 'Message marker '.str_pad((string) $i, 3, '0', STR_PAD_LEFT), range(6, 35)
            ));
    }

    public function test_reply_form_has_server_rendered_retry_id_and_preserves_failed_input(): void
    {
        [$community, $sender, $recipient, $conversation] = $this->pair();
        $url = route('communities.messages.show', [$community, $conversation]);
        $response = $this->actingAs($sender)->get($url)->assertOk();
        $this->assertTrue(preg_match('/name="client_send_id"\s+value="[^"\s]+"/', $response->getContent()) === 1);
        $this->from($url)->post(route('communities.messages.store', [$community, $conversation]), ['body' => 'Preserve this draft', 'client_send_id' => str_repeat('x', 65)])
            ->assertRedirect($url)->assertSessionHasErrors('client_send_id');
        $this->get($url)->assertOk()->assertSee('Preserve this draft')->assertSee('client send id', false);
        $this->assertSame(0, $conversation->messages()->count());
        $this->from($url)->post(route('communities.messages.store', [$community, $conversation]), ['body' => ' ', 'client_send_id' => 'valid-draft-key'])
            ->assertRedirect($url)->assertSessionHasErrors('body');
        $retry = $this->get($url)->assertOk();
        $this->assertTrue(str_contains($retry->getContent(), 'name="client_send_id" value="valid-draft-key"'));
    }

    public function test_unavailable_recipient_ids_have_identical_responses(): void
    {
        [$community, $sender] = $this->pair();
        $outsider = User::factory()->create();
        foreach ([$outsider->id, User::max('id') + 1] as $id) {
            $this->actingAs($sender)->postJson(route('communities.messages.start', $community), ['recipient_id' => $id])->assertNotFound();
        }
    }

    public function test_invalid_array_body_returns_readable_validation_feedback(): void
    {
        [$community, $sender, $recipient, $conversation] = $this->pair();
        $url = route('communities.messages.show', [$community, $conversation]);
        $this->actingAs($sender)->from($url)->post(route('communities.messages.store', [$community, $conversation]), [
            'body' => ['malformed draft'], 'client_send_id' => 'array-body-key',
        ])->assertRedirect($url)->assertSessionHasErrors('body');
        $response = $this->get($url);
        $this->assertSame(200, $response->status(), 'Invalid draft must not break the reply view');
        $response->assertSee('body field must be a string');
        $this->assertSame(0, $conversation->messages()->count());
    }

    public function test_inbox_is_paginated_after_eligibility_filter_and_unread_total_spans_pages(): void
    {
        [$community, $actor, $first, $conversation] = $this->pair();
        for ($i = 1; $i <= 17; $i++) {
            $other = User::factory()->create();
            CommunityMembership::forceCreate(['community_id' => $community->id, 'user_id' => $other->id, 'status' => 'ACTIVE']);
            [$one, $two] = CommunityConversation::canonicalParticipants($actor->id, $other->id);
            $thread = CommunityConversation::create(['community_id' => $community->id, 'user_one_id' => $one, 'user_two_id' => $two]);
            CommunityMessage::create(['conversation_id' => $thread->id, 'sender_id' => $other->id, 'body' => 'Inbox private '.$i]);
        }
        CommunityMembership::where('community_id', $community->id)->where('user_id', $first->id)->update(['status' => 'REMOVED']);
        CommunityMessage::create(['conversation_id' => $conversation->id, 'sender_id' => $first->id, 'body' => 'Hidden removed participant']);
        $response = $this->actingAs($actor)->get(route('communities.messages.index', $community))->assertOk()->assertDontSee('Hidden removed participant');
        $response->assertViewHas('conversations', fn ($rows) => $rows instanceof LengthAwarePaginator && $rows->total() === 17 && $rows->count() === 15);
        $response->assertViewHas('totalUnread', 17);
    }

    public function test_message_and_thread_timestamp_rollback_together_then_retry_succeeds(): void
    {
        [$community, $sender, $recipient, $conversation] = $this->pair();
        CommunityConversation::updating(function (): void {
            throw new \RuntimeException('Injected message timestamp failure');
        });
        try {
            $this->withoutExceptionHandling()->actingAs($sender)->post(route('communities.messages.store', [$community, $conversation]), ['body' => 'Atomic reply', 'client_send_id' => 'rollback-key']);
            $this->fail('Expected injected failure');
        } catch (\RuntimeException $error) {
            $this->assertSame('Injected message timestamp failure', $error->getMessage());
        } finally {
            CommunityConversation::flushEventListeners();
        }
        $this->assertSame(0, $conversation->messages()->count());
        $this->assertNull($conversation->fresh()->last_message_at);
        $this->post(route('communities.messages.store', [$community, $conversation]), ['body' => 'Atomic reply', 'client_send_id' => 'rollback-key'])->assertRedirect();
        $this->assertSame(1, $conversation->messages()->count());
    }
}
