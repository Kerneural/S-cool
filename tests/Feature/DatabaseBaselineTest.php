<?php

namespace Tests\Feature;

use App\Jobs\TestQueueJob;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Tests\Fixtures\Jobs\FailingTestJob;
use Tests\TestCase;

class DatabaseBaselineTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_core_tables_exist_after_migration(): void
    {
        $expectedTables = [
            'users',
            'password_reset_tokens',
            'sessions',
            'cache',
            'cache_locks',
            'jobs',
            'failed_jobs',
            'job_batches',
            'migrations',
        ];

        foreach ($expectedTables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Expected core table {$table} does not exist.");
        }
    }

    public function test_seeder_creates_deterministic_user_personas(): void
    {
        $this->seed(DatabaseSeeder::class);

        $expectedPersonas = [
            'devops-demo@scool.local' => 'Local Developer',
            'creator@scool.local' => 'Demo Creator',
            'member@scool.local' => 'Demo Member',
            'admin@scool.local' => 'Demo Platform Admin',
            'test@example.com' => 'Test User',
        ];

        $this->assertDatabaseCount('users', count($expectedPersonas));

        foreach ($expectedPersonas as $email => $name) {
            $user = User::where('email', $email)->first();
            $this->assertNotNull($user, "Persona with email {$email} was not seeded.");
            $this->assertSame($name, $user->name);
            $this->assertNotNull($user->email_verified_at);
            $this->assertTrue(Hash::check('password', $user->password));
        }
    }

    public function test_seeder_is_idempotent_and_safe_on_rerun(): void
    {
        // First run
        $this->seed(DatabaseSeeder::class);
        $countAfterFirstSeed = User::count();
        $usersAfterFirstSeed = User::orderBy('id')->get()->map->getAttributes()->all();

        // Second run must not throw duplicate entry exception or duplicate rows
        $this->seed(DatabaseSeeder::class);
        $countAfterSecondSeed = User::count();

        $this->assertSame($countAfterFirstSeed, $countAfterSecondSeed);
        $this->assertSame(5, $countAfterSecondSeed);
        $this->assertSame($usersAfterFirstSeed, User::orderBy('id')->get()->map->getAttributes()->all());
    }

    public function test_seeder_preserves_existing_user_data(): void
    {
        // Keep all existing attributes, including an unverified email and password.
        $existingUser = User::factory()->unverified()->create([
            'name' => 'Customized Developer',
            'email' => 'devops-demo@scool.local',
            'password' => Hash::make('custom-secret-password'),
            'remember_token' => 'synthetic-remember-token',
        ]);
        $originalAttributes = $existingUser->fresh()->getAttributes();
        $unrelatedUser = User::factory()->create();
        $unrelatedAttributes = $unrelatedUser->fresh()->getAttributes();

        // Run seed baseline
        $this->seed(DatabaseSeeder::class);

        // Name and password must be preserved
        $user = User::where('email', 'devops-demo@scool.local')->first();
        $this->assertNotNull($user);
        $this->assertSame('Customized Developer', $user->name);
        $this->assertTrue(Hash::check('custom-secret-password', $user->password));
        $this->assertSame($originalAttributes, $user->getAttributes());
        $this->assertSame($unrelatedAttributes, $unrelatedUser->fresh()->getAttributes());
        $this->assertDatabaseCount('users', 6);
    }

    public function test_demo_seeder_rejects_non_local_environments_before_writing(): void
    {
        foreach (['production', 'staging'] as $environment) {
            $this->app['env'] = $environment;

            try {
                $this->app->make(DatabaseSeeder::class)->run();
                $this->fail("Demo seed unexpectedly accepted {$environment}.");
            } catch (LogicException $exception) {
                $this->assertSame(
                    'Demo users may only be seeded in local or testing environments.',
                    $exception->getMessage()
                );
            } finally {
                $this->app['env'] = 'testing';
            }

            $this->assertDatabaseCount('users', 0);
        }
    }

    public function test_database_queue_worker_executes_and_clears_job(): void
    {
        config([
            'queue.connections.database.connection' => config('database.default'),
            'queue.failed.database' => config('database.default'),
        ]);
        Log::spy();

        TestQueueJob::dispatch('EUR-19 Worker Execution Test')
            ->onConnection('database')
            ->onQueue('default');

        $this->assertDatabaseCount('jobs', 1);

        // Execute one job using Artisan queue:work
        $exitCode = Artisan::call('queue:work', [
            'connection' => 'database',
            '--queue' => 'default',
            '--once' => true,
        ]);

        // The job should be successfully processed and deleted from jobs table
        $this->assertDatabaseCount('jobs', 0);
        $this->assertSame(0, $exitCode);
        $this->assertDatabaseCount('failed_jobs', 0);
        Log::shouldHaveReceived('info')
            ->with('TestQueueJob processed message: EUR-19 Worker Execution Test')
            ->once();
    }

    public function test_database_queue_records_failure_in_failed_jobs(): void
    {
        config([
            'queue.connections.database.connection' => config('database.default'),
            'queue.failed.database' => config('database.default'),
        ]);

        FailingTestJob::dispatch('EUR-19 Expected Failure for Inspection')
            ->onConnection('database')
            ->onQueue('default');

        $this->assertDatabaseCount('jobs', 1);
        $this->assertDatabaseCount('failed_jobs', 0);

        // Execute one job which fails
        Artisan::call('queue:work', [
            'connection' => 'database',
            '--queue' => 'default',
            '--once' => true,
        ]);

        // The failed job should now be recorded in failed_jobs table and cleared from active jobs
        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('failed_jobs', 1);

        $failedJob = DB::table('failed_jobs')->first();
        $this->assertNotNull($failedJob);
        $this->assertSame('database', $failedJob->connection);
        $this->assertSame('default', $failedJob->queue);
        $payload = json_decode($failedJob->payload, true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(FailingTestJob::class, $payload['displayName']);
        $this->assertStringContainsString('EUR-19 Expected Failure for Inspection', $failedJob->exception);
    }
}
