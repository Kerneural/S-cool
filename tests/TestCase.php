<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUpTraits()
    {
        // The application is booted, but RefreshDatabase has not run yet.
        $connections = property_exists($this, 'connectionsToTransact')
            ? $this->connectionsToTransact
            : [config('database.default')];

        foreach (array_unique([config('database.default'), ...$connections]) as $name) {
            // Resolve database URLs too, without opening a PDO connection.
            $connection = $this->app->make('db')->connection($name);
            if ($connection->getConfig('driver') !== 'mysql'
                || $connection->getDatabaseName() !== 'scool_test') {
                throw new \RuntimeException(
                    'SAFETY GUARD: Tests require the isolated MySQL database scool_test before migrations.'
                );
            }
        }

        return parent::setUpTraits();
    }
}
