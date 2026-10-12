<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use LogicException;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Demo users may only be seeded in local or testing environments.');
        }

        $personas = [
            [
                'name' => 'Local Developer',
                'email' => 'devops-demo@scool.local',
            ],
            [
                'name' => 'Demo Creator',
                'email' => 'creator@scool.local',
            ],
            [
                'name' => 'Demo Member',
                'email' => 'member@scool.local',
            ],
            [
                'name' => 'Demo Platform Admin',
                'email' => 'admin@scool.local',
                'is_platform_admin' => true,
            ],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ],
        ];

        foreach ($personas as $persona) {
            User::firstOrCreate(
                ['email' => $persona['email']],
                [
                    'name' => $persona['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'is_platform_admin' => $persona['is_platform_admin'] ?? false,
                ]
            );
        }
    }
}
