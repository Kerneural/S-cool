<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LogicException;

class LocalDemoSyncSeeder extends Seeder
{
    public function run(): void
    {
        $environment = app()->environment();
        $database = match ($environment) {
            'local' => 'scool',
            'testing' => 'scool_test',
            default => throw new LogicException('Demo synchronization is restricted to local and testing environments.'),
        };
        $connection = DB::connection();
        if ($connection->getDriverName() !== 'mysql'
            || $connection->getDatabaseName() !== $database
            || ($environment === 'local' && config('app.url') !== 'http://127.0.0.1:8080')
            || $connection->selectOne('SELECT DATABASE() AS name')->name !== $database) {
            throw new LogicException('Demo synchronization requires canonical local scool or isolated MySQL scool_test.');
        }

        // Existing accounts retain passwords, verification and platform roles.
        $this->call(UserSeeder::class);
        // Bootstrap's explicit repair mode stays unchanged; routine sync only fills gaps.
        $this->callWith(LearningCommunitySeeder::class, ['preserveExisting' => true]);
        $this->call(RealCommunitySampleSeeder::class);
        $this->call(RealFreeCommunitySeeder::class);
        $this->call(SkoolMediaSyncSeeder::class);
    }
}
