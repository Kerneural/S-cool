<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class DatabaseSafetyGuardTest extends TestCase
{
    public function test_unsafe_databases_are_rejected_before_refresh(): void
    {
        foreach (['scool', 'production', ''] as $database) {
            $this->assertGuardRejects(['database' => $database, 'url' => null]);
        }
    }

    public function test_database_url_cannot_bypass_guard(): void
    {
        $this->assertGuardRejects([
            'database' => 'scool_test',
            'url' => 'mysql://example:example@mysql:3306/scool',
        ]);
    }

    public function test_isolated_database_reaches_refresh(): void
    {
        $probe = new DatabaseRefreshProbe('test_probe');
        $probe->bootTraits($this->app);
        $this->assertTrue($probe->refreshReached);
    }

    private function assertGuardRejects(array $overrides): void
    {
        $original = config('database.connections.mysql');
        config(['database.connections.mysql' => array_replace($original, $overrides)]);
        $this->app->make('db')->purge('mysql');
        $probe = new DatabaseRefreshProbe('test_probe');

        try {
            $probe->bootTraits($this->app);
            $this->fail('Unsafe database reached trait setup.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('SAFETY GUARD', $exception->getMessage());
            $this->assertFalse($probe->refreshReached);
        } finally {
            config(['database.connections.mysql' => $original]);
            $this->app->make('db')->purge('mysql');
        }
    }
}

// Exercise the real lifecycle dispatch, replacing migrations with a safe sentinel.
class DatabaseRefreshProbe extends TestCase
{
    use RefreshDatabase;

    public bool $refreshReached = false;

    public function bootTraits($application): void
    {
        $this->app = $application;
        $this->setUpTraits();
    }

    public function refreshDatabase(): void
    {
        $this->refreshReached = true;
    }

    public function test_probe(): void {}
}
