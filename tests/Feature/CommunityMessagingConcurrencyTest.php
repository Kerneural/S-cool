<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityConversation;
use App\Models\CommunityMembership;
use App\Models\CommunityMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class CommunityMessagingConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    public function test_start_rechecks_revocation_committed_after_preflight(): void
    {
        $this->compete(true);
    }

    public function test_two_reverse_starts_complete_with_one_conversation(): void
    {
        $this->compete(false);
    }

    public function test_show_does_not_mark_read_after_revocation_commits(): void
    {
        $this->compete(true, 'show');
    }

    public function test_duplicate_concurrent_sends_create_one_message(): void
    {
        $this->compete(false, 'send');
    }

    public function test_send_rechecks_revocation_committed_after_preflight(): void
    {
        $this->compete(true, 'send');
    }

    private function compete(bool $revoke, string $mode = 'start'): void
    {
        $community = Community::factory()->create();
        $users = User::factory()->count(2)->create();
        foreach ($users as $user) {
            CommunityMembership::forceCreate(['community_id' => $community->id, 'user_id' => $user->id, 'status' => 'ACTIVE']);
        }
        $conversation = $mode !== 'start' ? CommunityConversation::create(['community_id' => $community->id,
            'user_one_id' => $users[0]->id, 'user_two_id' => $users[1]->id]) : null;
        $message = $mode === 'show' ? CommunityMessage::create(['conversation_id' => $conversation->id,
            'sender_id' => $users[1]->id, 'body' => 'Retained unread private body']) : null;
        $db = DB::connection();
        $this->assertSame('scool_test', $db->getDatabaseName());
        $directory = sys_get_temp_dir().'/scool-messages-'.Str::uuid();
        mkdir($directory, 0700);
        $environment = ['APP_ENV' => 'testing', 'DB_URL' => '', 'DB_CONNECTION' => 'mysql', 'DB_HOST' => $db->getConfig('host'),
            'DB_PORT' => (string) $db->getConfig('port'), 'DB_DATABASE' => 'scool_test', 'DB_USERNAME' => $db->getConfig('username'),
            'DB_PASSWORD' => $db->getConfig('password'), 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array'];
        $processes = [];
        try {
            for ($i = 0; $i < ($revoke ? 1 : 2); $i++) {
                $process = new Process([PHP_BINARY, base_path('tests/Support/community-message-starter.php'), (string) $community->id,
                    (string) $users[$mode === 'send' ? 0 : $i]->id, (string) $users[1 - $i]->id, $directory, (string) $i, $mode, (string) ($conversation?->id ?? '')], base_path(), $environment, null, 30);
                $process->start();
                $processes[] = $process;
            }
            $this->awaitSignals($directory, 'ready', $processes);
            $db->beginTransaction();
            Community::whereKey($community->id)->lockForUpdate()->firstOrFail();
            touch($directory.'/release');
            $this->awaitSignals($directory, 'attempting', $processes);
            if ($revoke) {
                CommunityMembership::where('community_id', $community->id)->where('user_id', $users[1]->id)->update(['status' => 'REMOVED']);
            }
            $db->commit();
            touch($directory.'/continue');
            foreach ($processes as $process) {
                $this->assertSame(0, $process->wait(), 'Starter failed: '.$process->getErrorOutput());
                $this->assertSame($revoke ? 404 : 302, json_decode($process->getOutput(), true)['status']);
            }
            $this->assertSame($mode !== 'start' || ! $revoke ? 1 : 0, CommunityConversation::count());
            if ($message) {
                $this->assertNull($message->fresh()->read_at);
                $this->assertSame('Retained unread private body', $message->fresh()->body);
            }
            if ($mode === 'send') {
                $this->assertSame($revoke ? 0 : 1, CommunityMessage::count());
                $this->assertSame(! $revoke, $conversation->fresh()->last_message_at !== null);
            }
        } finally {
            if ($db->transactionLevel() > 0) {
                $db->rollBack();
            }
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->stop(1);
                }
            }
            foreach (glob($directory.'/*') ?: [] as $file) {
                unlink($file);
            }
            rmdir($directory);
        }
    }

    private function awaitSignals(string $directory, string $suffix, array $processes): void
    {
        $deadline = microtime(true) + 15;
        while (count(glob($directory.'/*.'.$suffix) ?: []) !== count($processes)) {
            foreach ($processes as $process) {
                if (! $process->isRunning()) {
                    $this->fail('Starter exited before barrier: '.$process->getErrorOutput());
                }
            }
            if (microtime(true) >= $deadline) {
                $this->fail('Messaging barrier timeout: '.$suffix);
            }
            usleep(10000);
        }
    }
}
