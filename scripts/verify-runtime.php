<?php

// Local operational probe. Never print credentials, user rows or raw queue payloads.
use App\Jobs\TestQueueJob;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$mode = $argv[1] ?? '';

try {
    $connection = DB::connection();
    if (! $app->environment('local')
        || $connection->getConfig('driver') !== 'mysql'
        || $connection->getConfig('host') !== 'mysql'
        || $connection->getDatabaseName() !== 'scool'
        || $connection->selectOne('SELECT DATABASE() AS name')->name !== 'scool'
        || config('queue.default') !== 'database'
        || config('queue.connections.database.connection') !== null
        || config('queue.failed.database') !== 'mysql'
        || ! config('app.key')) {
        throw new RuntimeException('Unexpected local runtime configuration.');
    }
    // Test DB accessibility is checked without migrating/resetting either database.
    $testConfig = $connection->getConfig();
    $testConfig['database'] = 'scool_test';
    $testConfig['url'] = null;
    config(['database.connections.setup_test_probe' => $testConfig]);
    if (DB::connection('setup_test_probe')->selectOne('SELECT DATABASE() AS name')->name !== 'scool_test') {
        throw new RuntimeException('Test database is unavailable.');
    }

    if ($mode === 'config') {
        echo "[PASS] Keyed local app, MySQL scool and isolated scool_test are accessible.\n";
    } elseif ($mode === 'empty') {
        if ($connection->selectOne('SELECT COUNT(*) AS total FROM information_schema.tables WHERE table_schema = DATABASE()')->total != 0) {
            throw new RuntimeException('Bootstrap requires an empty local database.');
        }
        echo "[PASS] Local scool is empty; scool_test is accessible.\n";
    } elseif ($mode === 'seed') {
        if (User::count() !== 0) {
            throw new RuntimeException('Bootstrap seed requires an empty users table.');
        }
        $app->make(DatabaseSeeder::class)->run();
        $snapshot = fn () => User::orderBy('id')->get()->map->getAttributes()->all();
        $before = $snapshot();
        $app->make(DatabaseSeeder::class)->run();
        if (count($before) !== 5 || $before !== $snapshot()) {
            throw new RuntimeException('Seed baseline/rerun preservation failed.');
        }
        echo "[PASS] Five personas; all attributes unchanged on rerun.\n";
    } elseif ($mode === 'queue') {
        if (config('logging.default') !== 'stack' || config('logging.channels.stack.channels') !== ['single']) {
            throw new RuntimeException('Smoke expects the documented single-file local log.');
        }
        $marker = 'EUR20-smoke-'.Str::uuid();
        $jobId = Queue::connection('database')->push(new TestQueueJob($marker), '', 'default');
        $deadline = microtime(true) + 40;
        do {
            $pending = DB::table('jobs')->where('id', $jobId)->exists();
            $failed = DB::table('failed_jobs')->where('payload', 'like', '%'.$marker.'%')->exists();
            $log = is_file(storage_path('logs/laravel.log')) ? file_get_contents(storage_path('logs/laravel.log')) : '';
            $processed = substr_count($log, 'TestQueueJob processed message: '.$marker);
            if ($failed || $processed > 1) {
                throw new RuntimeException('Marked queue job failed or was processed more than once.');
            }
            if (! $pending && $processed === 1) {
                echo "[PASS] {$marker}: pending=0, failed=0, handler=1.\n";
                exit(0);
            }
            usleep(500000);
        } while (microtime(true) < $deadline);
        throw new RuntimeException('Queue smoke timed out.');
    } else {
        throw new RuntimeException('Unknown runtime probe mode.');
    }
} catch (Throwable $exception) {
    // Deliberately no exception trace / connection credentials in shared evidence.
    fwrite(STDERR, "[FAIL] Runtime probe {$mode}: validation or execution failed. Inspect local configuration/state; no reset was performed.\n");
    exit(1);
}
