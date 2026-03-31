<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Order matters: create data that others depend on first
        $this->call([
            AdminSeeder::class,           // Create admins first
            MedicineSeeder::class,        // Create medicines
            UserSeeder::class,            // Create users
            ClinicPatientSeeder::class,   // Create clinic patients (some linked to users)
            MedicationScheduleSeeder::class, // Create schedules for users
            MedicationLogSeeder::class,   // Create logs for schedules
            NotificationLogSeeder::class, // Create notification logs
        ]);
    }
}
