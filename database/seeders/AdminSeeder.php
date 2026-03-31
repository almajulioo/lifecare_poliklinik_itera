<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create multiple admins with realistic Indonesian data
        $admins = [
            [
                'email' => 'admin@lifecare.test',
                'password' => Hash::make('admin12345'),
            ],
            [
                'email' => 'admin1@lifecare.local',
                'password' => Hash::make('password123'),
            ],
            [
                'email' => 'admin2@lifecare.local',
                'password' => Hash::make('password123'),
            ],
            [
                'email' => 'manager@lifecare.local',
                'password' => Hash::make('password123'),
            ],
            [
                'email' => 'supervisor@lifecare.local',
                'password' => Hash::make('password123'),
            ],
        ];

        foreach ($admins as $admin) {
            Admin::updateOrCreate(
                ['email' => $admin['email']],
                $admin
            );
        }

        // Use factory to create additional admins (5 more)
        \App\Models\Admin::factory(5)->create();
    }
}
