<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityConversation;
use App\Models\CommunityMembership;
use App\Models\CommunityMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityMessagingTest extends TestCase
{
    use RefreshDatabase;

    protected function createActiveMember(Community $community, ?User $user = null): User
    {
        $user = $user ?? User::factory()->create();
        CommunityMembership::forceCreate([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => 'ACTIVE',
            'access_kind' => CommunityMembership::ACCESS_LIFETIME,
        ]);

        return $user;
    }

    public function test_inbox_links_allow_reaching_conversations_after_the_first_page(): void
    {
        $community = Community::factory()->create();
        $viewer = $this->createActiveMember($community);
        $oldest = null;

        for ($index = 1; $index <= 16; $index++) {
            $peer = $this->createActiveMember($community, User::factory()->create(['name' => sprintf('InboxContact-%02d-end', $index)]));
            [$one, $two] = CommunityConversation::canonicalParticipants($viewer->id, $peer->id);
            CommunityConversation::create([
                'community_id' => $community->id,
                'user_one_id' => $one,
                'user_two_id' => $two,
            ]);
            $oldest ??= $peer;
        }

        $url = route('communities.messages.index', $community);
        $this->actingAs($viewer)->get($url)->assertOk()
            ->assertDontSee($oldest->name)
            ->assertSee('href="'.$url.'?page=2"', false);
        $this->get($url.'?page=2')->assertOk()->assertSee($oldest->name)
            ->assertSee('href="'.$url.'?page=1"', false);
    }

    public function test_ac01_participants_exchange_and_reload_ordered_messages_with_tenant_isolation(): void
    {
        $communityAlpha = Community::factory()->create(['name' => 'Community Alpha']);
        $communityBeta = Community::factory()->create(['name' => 'Community Beta']);

        $userA = User::factory()->create(['name' => 'Alice']);
        $userB = User::factory()->create(['name' => 'Bob']);

        // Both active in Community Alpha and Beta
        $this->createActiveMember($communityAlpha, $userA);
        $this->createActiveMember($communityAlpha, $userB);
        $this->createActiveMember($communityBeta, $userA);
        $this->createActiveMember($communityBeta, $userB);

        // User A starts conversation with User B in Community Alpha
        $response = $this->actingAs($userA)->post('/communities/'.$communityAlpha->slug.'/messages/start', [
            'recipient_id' => $userB->id,
        ]);

        $convAlpha = CommunityConversation::where('community_id', $communityAlpha->id)->first();
        $this->assertNotNull($convAlpha);
        $response->assertRedirect('/communities/'.$communityAlpha->slug.'/messages/'.$convAlpha->id);

        // User A sends message in Alpha
        $this->actingAs($userA)->post('/communities/'.$communityAlpha->slug.'/messages/'.$convAlpha->id, [
            'body' => 'Hello Bob in Alpha!',
            'client_send_id' => 'msg-alpha-1',
        ])->assertRedirect();

        // User B views conversation in Alpha: sees message and marks read
        $resB = $this->actingAs($userB)->get('/communities/'.$communityAlpha->slug.'/messages/'.$convAlpha->id);
        $resB->assertOk();
        $resB->assertSee('Hello Bob in Alpha!');

        $msgAlpha1 = CommunityMessage::where('conversation_id', $convAlpha->id)->first();
        $this->assertNotNull($msgAlpha1->read_at);

        // User B replies to User A in Alpha
        $this->actingAs($userB)->post('/communities/'.$communityAlpha->slug.'/messages/'.$convAlpha->id, [
            'body' => 'Hi Alice! Nice to connect in Alpha.',
            'client_send_id' => 'msg-alpha-2',
        ])->assertRedirect();

        $this->assertSame(2, $convAlpha->messages()->count());

        // Now start conversation between A and B in Community Beta
        $this->actingAs($userA)->post('/communities/'.$communityBeta->slug.'/messages/start', [
            'recipient_id' => $userB->id,
        ]);

        $convBeta = CommunityConversation::where('community_id', $communityBeta->id)->first();
        $this->assertNotNull($convBeta);
        $this->assertNotSame($convAlpha->id, $convBeta->id);

        // Beta has 0 messages initially (complete tenant isolation)
        $this->assertSame(0, $convBeta->messages()->count());

        $this->actingAs($userA)->get('/communities/'.$communityBeta->slug.'/messages/'.$convBeta->id)
            ->assertOk()
            ->assertDontSee('Hello Bob in Alpha!');

        // Inbox in Beta shows no messages yet, while Alpha shows Alpha messages
        $inboxAlpha = $this->actingAs($userA)->get('/communities/'.$communityAlpha->slug.'/messages');
        $inboxAlpha->assertOk();
        $inboxAlpha->assertSee('Bob');
        $inboxAlpha->assertSee('Hi Alice! Nice to connect in Alpha.');
    }

    public function test_ac02_non_participant_other_tenant_and_admin_cannot_read_or_send(): void
    {
        $community = Community::factory()->create();
        $userA = $this->createActiveMember($community);
        $userB = $this->createActiveMember($community);
        $userC = $this->createActiveMember($community); // Non-participant member
        $creator = $community->creator; // Creator not in this conversation
        $admin = User::factory()->create(['is_platform_admin' => true]); // Platform Admin

        [$one, $two] = CommunityConversation::canonicalParticipants($userA->id, $userB->id);
        $conv = CommunityConversation::create([
            'community_id' => $community->id,
            'user_one_id' => $one,
            'user_two_id' => $two,
        ]);

        CommunityMessage::create([
            'conversation_id' => $conv->id,
            'sender_id' => $userA->id,
            'body' => 'Secret message between Alice and Bob',
        ]);

        // User C (non-participant) gets 404
        $this->actingAs($userC)->get('/communities/'.$community->slug.'/messages/'.$conv->id)->assertNotFound();
        $this->actingAs($userC)->post('/communities/'.$community->slug.'/messages/'.$conv->id, [
            'body' => 'Intruder message',
        ])->assertNotFound();

        // Creator (not participant) gets 404
        $this->actingAs($creator)->get('/communities/'.$community->slug.'/messages/'.$conv->id)->assertNotFound();
        $this->actingAs($creator)->post('/communities/'.$community->slug.'/messages/'.$conv->id, [
            'body' => 'Creator eavesdropping message',
        ])->assertNotFound();

        // Platform Admin (not participant) gets 404
        $this->actingAs($admin)->get('/communities/'.$community->slug.'/messages/'.$conv->id)->assertNotFound();
        $this->actingAs($admin)->post('/communities/'.$community->slug.'/messages/'.$conv->id, [
            'body' => 'Admin eavesdropping message',
        ])->assertNotFound();

        // Conversation not shown in User C or Creator inbox
        $this->actingAs($userC)->get('/communities/'.$community->slug.'/messages')->assertDontSee('Secret message');
        $this->actingAs($creator)->get('/communities/'.$community->slug.'/messages')->assertDontSee('Secret message');
    }

    public function test_ac03_duplicate_first_conversation_and_repeated_send_id_are_idempotent(): void
    {
        $community = Community::factory()->create();
        $userA = $this->createActiveMember($community);
        $userB = $this->createActiveMember($community);

        // User A requests start
        $this->actingAs($userA)->post('/communities/'.$community->slug.'/messages/start', [
            'recipient_id' => $userB->id,
        ])->assertRedirect();

        // User B requests start with User A
        $this->actingAs($userB)->post('/communities/'.$community->slug.'/messages/start', [
            'recipient_id' => $userA->id,
        ])->assertRedirect();

        // Exactly 1 conversation exists in the community
        $this->assertSame(1, CommunityConversation::where('community_id', $community->id)->count());

        $conv = CommunityConversation::where('community_id', $community->id)->first();

        // Send message with client_send_id
        $this->actingAs($userA)->post('/communities/'.$community->slug.'/messages/'.$conv->id, [
            'body' => 'Network attempt 1',
            'client_send_id' => 'retry-token-999',
        ])->assertRedirect();

        $this->assertSame(1, $conv->messages()->count());

        // Repeated send with same client_send_id (e.g. browser retry or network double-submit)
        $this->actingAs($userA)->post('/communities/'.$community->slug.'/messages/'.$conv->id, [
            'body' => 'Network attempt 1 duplicate',
            'client_send_id' => 'retry-token-999',
        ])->assertRedirect();

        // Still exactly 1 message
        $this->assertSame(1, $conv->messages()->count());
        $this->assertSame('Network attempt 1', $conv->messages()->first()->body);
    }

    public function test_ac04_participant_removal_or_expiry_or_suspended_community_denies_access_and_hides_inbox(): void
    {
        $community = Community::factory()->create();
        $userA = $this->createActiveMember($community);
        $userB = User::factory()->create();

        // User B has TERM membership that is ACTIVE
        $membershipB = CommunityMembership::forceCreate([
            'community_id' => $community->id,
            'user_id' => $userB->id,
            'status' => 'ACTIVE',
            'access_kind' => CommunityMembership::ACCESS_TERM,
            'expires_at' => now('UTC')->addDays(5),
        ]);

        [$one, $two] = CommunityConversation::canonicalParticipants($userA->id, $userB->id);
        $conv = CommunityConversation::create([
            'community_id' => $community->id,
            'user_one_id' => $one,
            'user_two_id' => $two,
            'last_message_at' => now('UTC'),
        ]);

        CommunityMessage::create([
            'conversation_id' => $conv->id,
            'sender_id' => $userA->id,
            'body' => 'Active conversation',
        ]);

        // When both active: User A sees conversation in inbox
        $this->actingAs($userA)->get('/communities/'.$community->slug.'/messages')
            ->assertOk()
            ->assertSee($userB->name);

        // Now User B expires
        $membershipB->forceFill(['expires_at' => now('UTC')->subMinute()])->save();

        // Direct show returns 404 for both participants
        $this->actingAs($userA)->get('/communities/'.$community->slug.'/messages/'.$conv->id)->assertNotFound();
        $this->actingAs($userB)->get('/communities/'.$community->slug.'/messages/'.$conv->id)->assertNotFound();

        // Sends fail
        $this->actingAs($userA)->post('/communities/'.$community->slug.'/messages/'.$conv->id, [
            'body' => 'Trying to message expired member',
        ])->assertNotFound();

        // Hidden from inbox
        $this->actingAs($userA)->get('/communities/'.$community->slug.'/messages')
            ->assertOk()
            ->assertDontSee($userB->name);

        // Suspend community
        $membershipB->forceFill(['expires_at' => now('UTC')->addDays(5)])->save();
        $community->forceFill(['status' => 'SUSPENDED'])->save();

        // Suspended community denies inbox and conversation
        $this->actingAs($userA)->get('/communities/'.$community->slug.'/messages')->assertNotFound();
        $this->actingAs($userA)->get('/communities/'.$community->slug.'/messages/'.$conv->id)->assertNotFound();
    }

    public function test_ac05_oversize_html_and_invalid_recipient_validation(): void
    {
        $community = Community::factory()->create();
        $userA = $this->createActiveMember($community);
        $userB = $this->createActiveMember($community);

        [$one, $two] = CommunityConversation::canonicalParticipants($userA->id, $userB->id);
        $conv = CommunityConversation::create([
            'community_id' => $community->id,
            'user_one_id' => $one,
            'user_two_id' => $two,
        ]);

        // Cannot message self
        $this->actingAs($userA)->post('/communities/'.$community->slug.'/messages/start', [
            'recipient_id' => $userA->id,
        ])->assertSessionHasErrors('recipient_id');

        // Cannot message someone outside community
        $outsider = User::factory()->create();
        $this->actingAs($userA)->post('/communities/'.$community->slug.'/messages/start', [
            'recipient_id' => $outsider->id,
        ])->assertNotFound();

        // Oversize message (> 2000 chars) fails
        $longBody = str_repeat('A', 2001);
        $this->actingAs($userA)->post('/communities/'.$community->slug.'/messages/'.$conv->id, [
            'body' => $longBody,
        ])->assertSessionHasErrors('body');

        // Empty whitespace message fails
        $this->actingAs($userA)->post('/communities/'.$community->slug.'/messages/'.$conv->id, [
            'body' => "   \n\t   ",
        ])->assertSessionHasErrors('body');

        // HTML/script injection is safely stored as text and rendered escaped
        $htmlBody = '<script>alert("pwned")</script><b>Bold message</b>';
        $this->actingAs($userA)->post('/communities/'.$community->slug.'/messages/'.$conv->id, [
            'body' => $htmlBody,
            'client_send_id' => 'escaped-html-message',
        ])->assertRedirect();

        $response = $this->actingAs($userB)->get('/communities/'.$community->slug.'/messages/'.$conv->id);
        $response->assertOk();
        // Escaped HTML tags should be displayed, not unescaped raw HTML
        $response->assertSee('&lt;script&gt;alert(&quot;pwned&quot;)&lt;/script&gt;', false);
    }
}
