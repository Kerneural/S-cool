<?php

namespace Tests\Feature;

use App\Jobs\SendCommunityInvitation;
use App\Models\Community;
use App\Models\CommunityInvitation;
use App\Models\CommunityMembership;
use App\Models\User;
use Illuminate\Database\Connection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class InvitationProcessIntegrationTest extends TestCase
{
    // Committed fixtures are visible to child processes; RefreshDatabase without connection transaction keeps schema intact.
    use RefreshDatabase;

    protected array $connectionsToTransact = [];

    private ?Connection $fixtureConnection = null;

    private array $ownedUserIds = [];

    private array $ownedCommunityIds = [];

    private array $ownedQueues = [];

    private array $childProcesses = [];

    protected function setUp(): void
    {
        parent::setUp();
        // Capture only after the shared pre-migration safety guard succeeds.
        $this->fixtureConnection = DB::connection();
    }

    protected function tearDown(): void
    {
        try {
            foreach ($this->childProcesses as $process) {
                $process->stop(1);
            }
            $this->cleanupFixtures();
        } finally {
            parent::tearDown();
        }
    }

    private function cleanupFixtures(): void
    {
        $db = $this->fixtureConnection;
        if ($db === null) {
            return;
        }
        if ($db->getConfig('driver') !== 'mysql' || $db->getDatabaseName() !== 'scool_test'
            || $db->selectOne('SELECT DATABASE() AS name')->name !== 'scool_test') {
            throw new \RuntimeException('Fixture cleanup requires the guarded MySQL scool_test connection.');
        }
        while ($db->transactionLevel() > 0) {
            $db->rollBack();
        }
        $db->transaction(function () use ($db): void {
            $db->table('jobs')->whereIn('queue', $this->ownedQueues)->delete();
            $db->table('failed_jobs')->whereIn('queue', $this->ownedQueues)->delete();
            $db->table('community_memberships')->whereIn('community_id', $this->ownedCommunityIds)->delete();
            $db->table('community_invitations')->whereIn('community_id', $this->ownedCommunityIds)->delete();
            $db->table('communities')->whereIn('id', $this->ownedCommunityIds)->delete();
            $db->table('users')->whereIn('id', $this->ownedUserIds)->delete();
        });
    }

    private function user(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $this->ownedUserIds[] = $user->id;

        return $user;
    }

    private function invitation(array $attributes): CommunityInvitation
    {
        $creator = $this->user();
        $community = Community::factory()->create(['creator_id' => $creator->id]);
        $this->ownedCommunityIds[] = $community->id;

        return CommunityInvitation::factory()->create(['community_id' => $community->id, ...$attributes]);
    }

    private function process(array $input): Process
    {
        $db = DB::connection()->getConfig();
        $process = new Process([PHP_BINARY, base_path('tests/Fixtures/invitation-process.php')], base_path(), [
            'APP_ENV' => 'testing', 'APP_DEBUG' => 'false', 'APP_KEY' => config('app.key'),
            'APP_URL' => 'http://127.0.0.1:8080',
            'DB_CONNECTION' => 'mysql', 'DB_URL' => '', 'DATABASE_URL' => '',
            'DB_HOST' => $db['host'], 'DB_PORT' => (string) $db['port'], 'DB_DATABASE' => 'scool_test',
            'DB_USERNAME' => $db['username'], 'DB_PASSWORD' => $db['password'],
            'MAIL_HOST' => 'mailpit', 'MAIL_PORT' => '1025', 'MAIL_USERNAME' => '', 'MAIL_PASSWORD' => '',
        ]);
        $process->setInput(json_encode($input, JSON_THROW_ON_ERROR));
        $process->setTimeout(25);
        $this->childProcesses[] = $process;

        return $process;
    }

    private function waitForReady(Process $process): bool
    {
        $deadline = microtime(true) + 10;
        do {
            // Cumulative output remains available even if a status poll drained stdout.
            if (str_contains($process->getOutput(), "READY\n")) {
                return true;
            }
            if (! $process->isRunning()) {
                return false;
            }
            $process->checkTimeout();
            usleep(10000);
        } while (microtime(true) < $deadline);

        return false;
    }

    public function test_two_actual_mysql_processes_cannot_accept_one_token_twice(): void
    {
        $user = $this->user();
        $token = bin2hex(random_bytes(32));
        $invite = $this->invitation(['normalized_email' => $user->email, 'token_hash' => hash('sha256', $token)]);
        $input = ['mode' => 'accept', 'invitation_id' => $invite->id, 'user_id' => $user->id, 'token' => $token];
        $first = $this->process($input);
        $second = $this->process($input);
        DB::beginTransaction();
        try {
            // Hold the actor lock until both child applications are ready to compete.
            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $first->start();
            $second->start();
            $this->assertTrue($this->waitForReady($first));
            $this->assertTrue($this->waitForReady($second));
            $this->assertTrue($first->isRunning());
            $this->assertTrue($second->isRunning());
        } finally {
            DB::rollBack();
        }
        $this->assertSame(0, $first->wait(), 'First isolated acceptance process failed.');
        $this->assertSame(0, $second->wait(), 'Second isolated acceptance process failed.');
        $results = [trim(str_replace("READY\n", '', $first->getOutput())), trim(str_replace("READY\n", '', $second->getOutput()))];
        sort($results);
        $this->assertSame(['ACCEPTED', 'REJECTED'], $results);
        $this->assertDatabaseCount('community_memberships', 1);
        $this->assertSame('ACCEPTED', $invite->refresh()->status);
    }

    public function test_real_database_worker_delivers_mailpit_invitation_then_verified_user_joins(): void
    {
        $base = 'http://mailpit:8025';
        $this->assertTrue(Http::timeout(5)->get($base.'/api/v1/info')->successful(), 'Local Mailpit is required for the SMTP integration test.');
        $user = $this->user(['email' => 'invite-'.Str::uuid().'@scool.local']);
        $invite = $this->invitation(['normalized_email' => $user->email, 'token_hash' => null]);
        $queue = 'm2-test-'.Str::uuid();
        $this->ownedQueues[] = $queue;
        Queue::connection('database')->push(new SendCommunityInvitation($invite->id), '', $queue);
        $payload = DB::table('jobs')->where('queue', $queue)->value('payload');
        $this->assertStringNotContainsString($user->email, $payload);
        $this->assertStringNotContainsString('token_hash', $payload);
        $worker = $this->process(['mode' => 'queue', 'queue' => $queue]);
        $this->assertSame(0, $worker->run(), 'Isolated real queue worker failed.');
        $this->assertSame('WORKED', trim($worker->getOutput()));
        $this->assertSame(0, DB::table('jobs')->where('queue', $queue)->count());
        $this->assertNotNull($invite->refresh()->sent_at);
        // Inspect only this run's UUID recipient. Never delete the shared inbox.
        $messages = Http::timeout(5)->get($base.'/api/v1/search', ['query' => 'to:'.$user->email])->json('messages');
        $this->assertCount(1, $messages);
        $detail = Http::timeout(5)->get($base.'/api/v1/message/'.$messages[0]['ID'])->json();
        $body = ($detail['Text'] ?? '').' '.($detail['HTML'] ?? '');
        // Assert the origin without including the private message/token in failure output.
        $this->assertSame(1, preg_match('~http://127\.0\.0\.1:8080/invitations/'.$invite->id.'#token=[a-f0-9]{64}~', $body), 'Worker invitation must use the configured IPv4 origin.');
        preg_match('/#token=([a-f0-9]{64})/', $body, $matches);
        $this->assertCount(2, $matches, 'Expected an invitation fragment token in the locally captured message.');
        $this->assertTrue($invite->matchesToken($matches[1]));
        $this->actingAs($user)->post('/invitations/'.$invite->id.'/accept', ['token' => $matches[1]])->assertRedirect();
        $this->assertDatabaseHas('community_memberships', ['community_id' => $invite->community_id, 'user_id' => $user->id, 'status' => 'ACTIVE']);
    }

    public function test_cleanup_preserves_unrelated_records_and_queue_jobs(): void
    {
        $owned = $this->invitation(['token_hash' => null]);
        $foreign = CommunityInvitation::factory()->create();
        $foreignMember = CommunityMembership::factory()->active()->create(['community_id' => $foreign->community_id]);
        $foreignQueue = 'm2-preserve-'.Str::uuid();
        $foreignJob = Queue::connection('database')->push(new SendCommunityInvitation($foreign->id), '', $foreignQueue);
        try {
            $this->cleanupFixtures();
            $this->assertDatabaseMissing('community_invitations', ['id' => $owned->id]);
            $this->assertDatabaseHas('community_invitations', ['id' => $foreign->id]);
            $this->assertDatabaseHas('community_memberships', ['id' => $foreignMember->id]);
            $this->assertDatabaseHas('communities', ['id' => $foreign->community_id]);
            $this->assertDatabaseHas('users', ['id' => $foreign->inviter_id]);
            $this->assertDatabaseHas('users', ['id' => $foreignMember->user_id]);
            $this->assertDatabaseHas('jobs', ['id' => $foreignJob, 'queue' => $foreignQueue]);
        } finally {
            // These sentinels belong to this test; reclaim only after the assertion.
            $this->ownedCommunityIds[] = $foreign->community_id;
            $this->ownedUserIds[] = $foreign->inviter_id;
            $this->ownedUserIds[] = $foreignMember->user_id;
            $this->ownedQueues[] = $foreignQueue;
        }
    }

    public function test_readiness_handles_already_buffered_and_late_output(): void
    {
        $buffered = \Mockery::mock(Process::class);
        $buffered->shouldReceive('getOutput')->once()->andReturn("READY\n");
        $this->assertTrue($this->waitForReady($buffered));

        $late = \Mockery::mock(Process::class);
        $late->shouldReceive('getOutput')->twice()->andReturn('', "READY\n");
        $late->shouldReceive('isRunning')->once()->andReturn(true);
        $late->shouldReceive('checkTimeout')->once();
        $this->assertTrue($this->waitForReady($late));
    }
}
