<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationLog;
use App\Models\MedicationSchedule;

class NotificationLogSeeder extends Seeder
{
    public function run(): void
    {
        // Get all active medication schedules
        $schedules = MedicationSchedule::where('is_active', true)
            ->with('user')
            ->get();

        if ($schedules->isEmpty()) {
            return;
        }

        // Create notification logs for past 14 days
        foreach ($schedules as $schedule) {
            for ($daysAgo = 14; $daysAgo >= 0; $daysAgo--) {
                $logDate = now()->subDays($daysAgo)->startOfDay();
                
                if ($logDate->isAfter($schedule->start_date) && 
                    ($schedule->end_date === null || $logDate->isBefore($schedule->end_date->endOfDay()))) {
                    
                    // Parse time from schedule
                    $times = is_array($schedule->time) ? $schedule->time : explode(',', $schedule->time);
                    
                    foreach ($times as $time) {
                        $time = trim($time);
                        [$hour, $minute] = explode(':', $time);
                        
                        $scheduledTime = $logDate
                            ->setHour((int)$hour)
                            ->setMinute((int)$minute);
                        
                        // 90% of notifications are sent successfully
                        $status = rand(1, 100) <= 90 
                            ? (rand(1, 100) <= 70 ? 'sent' : 'snoozed')
                            : (rand(1, 100) <= 50 ? 'dismissed' : 'pending');
                        
                        NotificationLog::factory()
                            ->for($schedule->user)
                            ->state([
                                'medication_schedule_id' => $schedule->id,
                                'scheduled_time' => $scheduledTime,
                                'status' => $status,
                            ])
                            ->create();
                    }
                }
            }
        }

        // Ensure minimum notifications (at least 500)
        $currentCount = NotificationLog::count();
        if ($currentCount < 500) {
            $toCreate = 500 - $currentCount;
            NotificationLog::factory($toCreate)->create();
        }
    }
}
