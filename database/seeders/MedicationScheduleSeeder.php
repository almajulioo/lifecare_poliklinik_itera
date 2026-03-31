<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedicationSchedule;
use App\Models\User;
use App\Models\Medicine;

class MedicationScheduleSeeder extends Seeder
{
    public function run(): void
    {
        // Get all users
        $users = User::all();
        $medicines = Medicine::all();

        if ($users->isEmpty() || $medicines->isEmpty()) {
            return;
        }

        // Create multiple schedules per user (realistic scenario)
        foreach ($users as $user) {
            // Each user gets 2-5 medication schedules
            $count = rand(2, 5);
            
            for ($i = 0; $i < $count; $i++) {
                MedicationSchedule::factory()
                    ->for($user)
                    ->for($medicines->random())
                    ->create();
            }
        }

        // Ensure at least 200 total schedules
        $currentCount = MedicationSchedule::count();
        if ($currentCount < 200) {
            $toCreate = 200 - $currentCount;
            MedicationSchedule::factory($toCreate)->create();
        }
    }
}
