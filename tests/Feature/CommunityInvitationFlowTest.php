<?php

namespace Tests\Feature;

use App\Jobs\SendCommunityInvitation;
use App\Mail\CommunityInvitationMail;
use App\Models\Community;
use App\Models\CommunityInvitation;
use App\Models\CommunityMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CommunityInvitationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        Mail::fake();
    }

    private function invite(Community $community, User $user): array
    {
        $this->actingAs($community->creator)->post('/communities/'.$community->slug.'/invitations', [
            'email' => strtoupper($user->email),
            'community_id' => 999999, 'inviter_id' => $user->id, 'status' => 'ACCEPTED',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $invite = $community->invitations()->latest('id')->firstOrFail();
        $this->assertNull($invite->token_hash);
        $this->assertSame($community->creator_id, $invite->inviter_id);
        $this->assertSame(strtolower($user->email), $invite->normalized_email);
        Queue::assertPushed(SendCommunityInvitation::class, fn ($job) => $job->invitationId === $invite->id);
        $job = new SendCommunityInvitation($invite->id);
        $payload = serialize($job);
        $this->assertStringNotContainsString($user->email, $payload);
        $this->assertStringNotContainsString('token', $payload);
        $job->handle();
        $token = null;
        Mail::assertSent(CommunityInvitationMail::class, function ($mail) use ($user, &$token): bool {
            if (! $mail->hasTo(strtolower($user->email))) {
                return false;
            }
            parse_str(parse_url($mail->acceptUrl, PHP_URL_FRAGMENT), $fragment);
            $token = $fragment['token'] ?? null;
            $this->assertNull(parse_url($mail->acceptUrl, PHP_URL_QUERY));

            return is_string($token) && strlen($token) === 64;
        });
        $this->assertTrue($invite->refresh()->matchesToken($token));

        return [$invite, $token];
    }

    public function test_verified_matching_email_accepts_once_and_get_never_grants_access(): void
    {
        $community = Community::factory()->create();
        $user = User::factory()->create();
        [$invite, $token] = $this->invite($community, $user);
        $this->actingAs($user)->get('/invitations/'.$invite->id)->assertOk()->assertDontSee($community->name);
        $this->assertDatabaseCount('community_memberships', 0);
        $this->post('/invitations/'.$invite->id.'/accept', ['token' => $token])->assertRedirect('/communities/'.$community->slug);
        $this->assertDatabaseHas('community_memberships', ['community_id' => $community->id, 'user_id' => $user->id, 'status' => 'ACTIVE']);
        $this->assertDatabaseHas('community_invitations', ['id' => $invite->id, 'status' => 'ACCEPTED', 'accepted_by_user_id' => $user->id]);
        $this->get('/communities/'.$community->slug)->assertOk();
        $this->post('/invitations/'.$invite->id.'/accept', ['token' => $token])->assertNotFound();
        $this->assertDatabaseCount('community_memberships', 1);
    }

    public function test_wrong_email_unverified_user_and_bad_tokens_cannot_join(): void
    {
        $community = Community::factory()->create();
        $user = User::factory()->create();
        [$invite, $token] = $this->invite($community, $user);
        $url = '/invitations/'.$invite->id.'/accept';
        $this->actingAs(User::factory()->create())->post($url, ['token' => $token])->assertNotFound();
        $this->actingAs($user);
        foreach ([null, [], 'short', str_repeat('a', 64)] as $bad) {
            $this->post($url, ['token' => $bad])->assertNotFound();
        }
        $this->post('/invitations/999999/accept', ['token' => $token])->assertNotFound();
        $user->email_verified_at = null;
        $user->save();
        $this->actingAs($user)->post($url, ['token' => $token])->assertRedirect(route('verification.notice'));
        $this->assertDatabaseCount('community_memberships', 0);
        $this->assertSame('PENDING', $invite->refresh()->status);
    }

    public function test_terminal_expired_and_inactive_community_invites_fail_closed(): void
    {
        $user = User::factory()->create();
        foreach (['expired', 'revoked', 'accepted', 'SUSPENDED', 'ARCHIVED'] as $case) {
            $community = Community::factory()->create();
            $token = bin2hex(random_bytes(32));
            $invite = CommunityInvitation::factory()->create([
                'community_id' => $community->id, 'normalized_email' => $user->email,
                'token_hash' => hash('sha256', $token),
            ]);
            if ($case === 'expired') {
                $invite->expires_at = now()->subSecond();
                $invite->save();
            } elseif (in_array($case, ['revoked', 'accepted'], true)) {
                $invite->status = strtoupper($case);
                $invite->save();
            } else {
                $community->status = $case;
                $community->save();
            }
            $this->actingAs($user)->post('/invitations/'.$invite->id.'/accept', ['token' => $token])->assertNotFound();
        }
        $this->assertDatabaseCount('community_memberships', 0);
    }

    public function test_revoke_is_creator_only_and_nested_binding_rejects_foreign_id(): void
    {
        $first = Community::factory()->create();
        $second = Community::factory()->create(['creator_id' => $first->creator_id]);
        $user = User::factory()->create();
        [$invite, $token] = $this->invite($first, $user);
        $base = '/communities/'.$first->slug.'/invitations';
        $this->actingAs($user)->delete($base.'/'.$invite->id)->assertNotFound();
        $this->actingAs($first->creator)->delete('/communities/'.$second->slug.'/invitations/'.$invite->id)->assertNotFound();
        $this->delete($base.'/'.$invite->id)->assertRedirect();
        $this->delete($base.'/'.$invite->id)->assertNotFound();
        $this->actingAs($user)->post('/invitations/'.$invite->id.'/accept', ['token' => $token])->assertNotFound();
        [$new, $newToken] = $this->invite($first, $user);
        $this->assertNotSame($invite->id, $new->id);
        $this->assertNotSame($invite->token_hash, $new->token_hash);
        $this->actingAs($user)->post('/invitations/'.$new->id.'/accept', ['token' => $newToken])->assertRedirect();
    }

    public function test_left_members_can_rejoin_but_other_nonactive_states_cannot_bypass_controls(): void
    {
        foreach (['LEFT', 'SUSPENDED', 'REMOVED', 'PENDING_PAYMENT'] as $state) {
            $membership = CommunityMembership::factory()->create(['status' => $state]);
            [$invite, $token] = $this->invite($membership->community, $membership->user);
            $response = $this->actingAs($membership->user)->post('/invitations/'.$invite->id.'/accept', ['token' => $token]);
            if ($state === 'LEFT') {
                $response->assertRedirect();
                $this->assertSame('ACTIVE', $membership->refresh()->status);
            } else {
                $response->assertNotFound();
                $this->assertSame($state, $membership->refresh()->status);
                $this->assertSame('PENDING', $invite->refresh()->status);
            }
            $this->assertSame(1, $membership->community->memberships()->count());
        }
    }

    public function test_paid_invitation_does_not_grant_access_or_flash_token_to_database_session(): void
    {
        $community = Community::factory()->create(['access_mode' => 'PAID']);
        $user = User::factory()->create();
        [$invite, $token] = $this->invite($community, $user);
        $this->actingAs($user)->from('/invitations/'.$invite->id)->post('/invitations/'.$invite->id.'/accept', ['token' => $token])
            ->assertSessionHasErrors('invitation');
        $this->assertNull(session()->getOldInput('token'));
        $this->assertDatabaseCount('community_memberships', 0);
        $this->assertSame('PENDING', $invite->refresh()->status);
        $this->get('/communities/'.$community->slug)->assertNotFound();
    }

    public function test_pending_duplicates_are_rejected_and_expired_invites_are_not_reopened(): void
    {
        $community = Community::factory()->create();
        $user = User::factory()->create();
        [$invite] = $this->invite($community, $user);
        $this->actingAs($community->creator)->post('/communities/'.$community->slug.'/invitations', ['email' => $user->email])->assertSessionHasErrors('email');
        $this->assertSame(1, $community->invitations()->count());
        $invite->expires_at = now()->subSecond();
        $invite->save();
        [$new] = $this->invite($community, $user);
        $this->assertSame('EXPIRED', $invite->refresh()->status);
        $this->assertNotSame($invite->id, $new->id);
    }

    public function test_delivery_retries_do_not_rotate_sent_tokens_or_mail_revoked_records(): void
    {
        $community = Community::factory()->create();
        $user = User::factory()->create();
        [$invite, $token] = $this->invite($community, $user);
        (new SendCommunityInvitation($invite->id))->handle();
        Mail::assertSentCount(1);
        $this->assertTrue($invite->refresh()->matchesToken($token));
        $revoked = CommunityInvitation::factory()->revoked()->create();
        (new SendCommunityInvitation($revoked->id))->handle();
        $expired = CommunityInvitation::factory()->expired()->create();
        (new SendCommunityInvitation($expired->id))->handle();
        Mail::assertSentCount(1);
    }

    public function test_membership_and_acceptance_roll_back_together_on_save_failure(): void
    {
        $community = Community::factory()->create();
        $user = User::factory()->create();
        [$invite, $token] = $this->invite($community, $user);
        CommunityInvitation::saving(function ($record): void {
            if ($record->status === 'ACCEPTED') {
                throw new \RuntimeException('Simulated persistence failure.');
            }
        });
        try {
            $this->actingAs($user)->post('/invitations/'.$invite->id.'/accept', ['token' => $token])->assertStatus(500);
            $this->assertDatabaseCount('community_memberships', 0);
            $this->assertSame('PENDING', $invite->refresh()->status);
        } finally {
            CommunityInvitation::flushEventListeners();
        }
    }

    public function test_mail_failure_rolls_back_hash_and_suppresses_sensitive_exception(): void
    {
        $invite = CommunityInvitation::factory()->create(['token_hash' => null]);
        Mail::shouldReceive('mailer')->with('smtp')->andReturnSelf();
        Mail::shouldReceive('to')->with($invite->normalized_email)->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('Sensitive SMTP payload'));
        try {
            (new SendCommunityInvitation($invite->id))->handle();
            $this->fail('Failed mail was reported as delivered.');
        } catch (\RuntimeException $exception) {
            $this->assertStringNotContainsString('Sensitive', $exception->getMessage());
            $this->assertNull($exception->getPrevious());
            $this->assertNull($invite->refresh()->sent_at);
            $this->assertNull($invite->token_hash);
            $this->assertSame('PENDING', $invite->status);
        }
    }

    public function test_invitation_endpoint_rate_limits_guesses_without_granting_access(): void
    {
        $this->actingAs(User::factory()->create());
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->post('/invitations/999999/accept', ['token' => str_repeat('a', 64)])->assertNotFound();
        }
        $this->post('/invitations/999999/accept', ['token' => str_repeat('a', 64)])->assertStatus(429);
        $this->assertDatabaseCount('community_memberships', 0);
    }

    public function test_exception_traces_do_not_capture_sensitive_action_arguments(): void
    {
        $this->assertSame('1', ini_get('zend.exception_ignore_args'));
        $throw = function (string $secret): void {
            throw new \RuntimeException('Safe failure message.');
        };
        try {
            $throw('synthetic-secret');
        } catch (\RuntimeException $exception) {
            $this->assertStringNotContainsString('synthetic-secret', json_encode($exception->getTrace(), JSON_THROW_ON_ERROR));
            foreach ($exception->getTrace() as $frame) {
                // PHP retains include/require filenames even with argument capture disabled.
                // These language frames are not function arguments; check all callable frames.
                if (in_array($frame['function'] ?? '', ['include', 'include_once', 'require', 'require_once'], true)) {
                    continue;
                }
                $this->assertArrayNotHasKey('args', $frame);
            }
        }
    }

    public function test_trimmed_ascii_email_is_normalized_and_duplicate_pending_invite_is_rejected(): void
    {
        $community = Community::factory()->create();
        $this->actingAs($community->creator)->post('/communities/'.$community->slug.'/invitations', [
            'email' => '  MIXED.Case@SCOOL.LOCAL  ',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('mixed.case@scool.local', $community->invitations()->firstOrFail()->normalized_email);
        $this->post('/communities/'.$community->slug.'/invitations', ['email' => 'mixed.case@scool.local'])->assertSessionHasErrors('email');
        $this->assertSame(1, $community->invitations()->count());
    }

    public function test_acceptance_checks_fresh_email_and_verification_not_stale_authenticated_model(): void
    {
        $community = Community::factory()->create();
        $user = User::factory()->create();
        [$invite, $token] = $this->invite($community, $user);
        $staleActor = clone $user;
        $user->email = 'changed-email@scool.local';
        $user->email_verified_at = null;
        $user->save();
        $this->actingAs($staleActor)->post('/invitations/'.$invite->id.'/accept', ['token' => $token])->assertNotFound();
        $user->email_verified_at = now();
        $user->save();
        $this->post('/invitations/'.$invite->id.'/accept', ['token' => $token])->assertNotFound();
        $this->assertDatabaseCount('community_memberships', 0);
        $this->assertSame('PENDING', $invite->refresh()->status);
    }
}
