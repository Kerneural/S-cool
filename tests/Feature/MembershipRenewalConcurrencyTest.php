<?php

namespace Tests\Feature;

use App\Actions\Payments\InitiateMembershipRenewal;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Payment;
use App\Models\ProcessedWebhookEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class MembershipRenewalConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    public function test_two_competing_events_extend_one_attempt_only_once(): void
    {
        $this->compete(false);
    }

    public function test_waiting_renewal_rechecks_revocation_committed_after_event_preflight(): void
    {
        $this->compete(true);
    }

    private function compete(bool $revoke): void
    {
        $community = Community::factory()->create(['access_mode' => 'PAID', 'price_vnd' => 200000, 'billing_period' => 'TERM_30_DAYS']);
        $user = User::factory()->create();
        $expiry = now('UTC')->addDays(5)->startOfSecond();
        $membership = CommunityMembership::forceCreate(['community_id' => $community->id, 'user_id' => $user->id, 'status' => 'ACTIVE', 'access_kind' => 'TERM', 'expires_at' => $expiry]);
        $payment = app(InitiateMembershipRenewal::class)->handle($membership, $user);
        $db = DB::connection();
        $this->assertSame('mysql', $db->getConfig('driver'));
        $this->assertSame('scool_test', $db->getDatabaseName());
        $directory = sys_get_temp_dir().'/scool-renewal-'.Str::uuid();
        mkdir($directory, 0700);
        $environment = [
            'APP_ENV' => 'testing', 'DB_URL' => '', 'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $db->getConfig('host'), 'DB_PORT' => (string) $db->getConfig('port'),
            'DB_DATABASE' => 'scool_test', 'DB_USERNAME' => $db->getConfig('username'), 'DB_PASSWORD' => $db->getConfig('password'),
            'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array',
        ];
        $processes = [];
        try {
            $writers = $revoke ? 1 : 2;
            for ($i = 0; $i < $writers; $i++) {
                $process = new Process([PHP_BINARY, base_path('tests/Support/membership-renewal-writer.php'), (string) $payment->id, (string) (1950000000 + $i), $directory, (string) $i], base_path(), $environment, null, 30);
                $process->start();
                $processes[] = $process;
            }
            $this->awaitSignals($directory, 'ready', $processes);
            $db->beginTransaction();
            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            touch($directory.'/release');
            $this->awaitSignals($directory, 'attempting', $processes);
            foreach ($processes as $process) {
                $this->assertTrue($process->isRunning(), 'Writer must wait for the held actor lock.');
            }
            if ($revoke) {
                $membership->forceFill(['status' => 'REMOVED'])->save();
            }
            $db->commit();
            foreach ($processes as $process) {
                $this->assertSame(0, $process->wait(), 'Renewal child failed: '.$process->getErrorOutput());
                $this->assertSame($revoke ? 409 : 200, json_decode($process->getOutput(), true)['status']);
            }
            if ($revoke) {
                $this->assertTrue($membership->fresh()->expires_at->equalTo($expiry));
                $this->assertSame('REMOVED', $membership->fresh()->status);
                $this->assertSame('PENDING', $payment->fresh()->status);
            } else {
                $this->assertTrue($membership->fresh()->expires_at->equalTo($expiry->copy()->addDays(30)));
                $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->fresh()->status);
                $this->assertSame(2, ProcessedWebhookEvent::count());
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
            foreach (glob($directory.'/*') as $file) {
                unlink($file);
            }
            rmdir($directory);
        }
    }

    private function awaitSignals(string $directory, string $suffix, array $processes): void
    {
        $deadline = microtime(true) + 20;
        do {
            $ready = true;
            foreach ($processes as $i => $process) {
                $ready = $ready && is_file($directory.'/'.$i.'.'.$suffix);
                $this->assertTrue($process->isRunning(), 'Renewal child stopped before barrier: '.$process->getErrorOutput());
            }
            if ($ready) {
                return;
            }
            usleep(10000);
        } while (microtime(true) < $deadline);
        $this->fail('Renewal concurrency barrier timed out.');
    }
}
