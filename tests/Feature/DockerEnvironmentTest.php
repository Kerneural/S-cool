<?php

namespace Tests\Feature;

use App\Jobs\TestQueueJob;
use Dotenv\Dotenv;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DockerEnvironmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_url_template_uses_ipv4_without_replacing_container_service_names(): void
    {
        $defaults = Dotenv::parse(file_get_contents(base_path('.env.example')));

        $this->assertSame('http://127.0.0.1:8080', $defaults['APP_URL']);
        $this->assertSame('mysql', $defaults['DB_HOST']);
        $this->assertSame('mailpit', $defaults['MAIL_HOST']);
    }

    public function test_application_healthcheck_endpoint_returns_200_ok(): void
    {
        $response = $this->get('/up');

        $response->assertStatus(200);
        $response->assertSee('Application up', false);
    }

    public function test_queue_tables_are_migrated_and_ready(): void
    {
        foreach (['jobs', 'failed_jobs', 'job_batches'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing queue table: {$table}");
        }
    }

    public function test_queue_job_can_be_pushed_to_database(): void
    {
        // PHPUnit defaults to sync. Explicitly exercise the real database driver
        // on the connection already guarded as scool_test, never the dev queue.
        config(['queue.connections.database.connection' => config('database.default')]);

        TestQueueJob::dispatch('EUR-18 verification job')
            ->onConnection('database')
            ->onQueue('eur18-test');

        $this->assertDatabaseCount('jobs', 1);
        $job = DB::table('jobs')->where('queue', 'eur18-test')->sole();
        $payload = json_decode($job->payload, true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame(TestQueueJob::class, $payload['displayName']);
        $this->assertStringContainsString('EUR-18 verification job', $payload['data']['command']);
        $this->assertSame(0, $job->attempts);
        $this->assertNull($job->reserved_at);
    }
}
