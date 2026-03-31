<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedicationLog;
use App\Models\MedicationSchedule;
use App\Models\User;

class MedicationLogSeeder extends Seeder
{
    public function run(): void
    {
        // Get all medication schedules
        $schedules = MedicationSchedule::where('is_active', true)
            ->with('user')
            ->get();

        if ($schedules->isEmpty()) {
            return;
        }

        // Create logs for the past 30 days
        foreach ($schedules as $schedule) {
            // Each active schedule has logs for the past 30 days
            for ($daysAgo = 30; $daysAgo >= 0; $daysAgo--) {
                // Skip creation for future dates
                $logDate = now()->subDays($daysAgo)->startOfDay();
                
                if ($logDate->isAfter($schedule->start_date) && 
                    ($schedule->end_date === null || $logDate->isBefore($schedule->end_date->endOfDay()))) {
                    
                    // 75% chance of taking the medication
                    $status = rand(1, 100) <= 75 ? 'taken' : (rand(1, 100) <= 50 ? 'missed' : 'pending');
                    
                    MedicationLog::factory()
                        ->for($schedule->user)
                        ->state([
                            'medication_schedule_id' => $schedule->id,
                            'status' => $status,
                            'created_at' => $logDate,
                        ])
                        ->create();
                }
            }
        }

        // Ensure minimum logs (at least 1000)
        $currentCount = MedicationLog::count();
        if ($currentCount < 1000) {
            $toCreate = 1000 - $currentCount;
            MedicationLog::factory($toCreate)->create();
        }
    }
}
