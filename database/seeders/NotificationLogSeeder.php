<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationLog;
use App\Models\MedicationSchedule;
use Carbon\Carbon;

class NotificationLogSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = MedicationSchedule::where('is_active', true)
            ->with('user')
            ->get();

        if ($schedules->isEmpty()) {
            return;
        }

        // Buat notifikasi log untuk 14 hari terakhir
        foreach ($schedules as $schedule) {
            for ($daysAgo = 14; $daysAgo >= 0; $daysAgo--) {
                $logDate = now()->subDays($daysAgo)->startOfDay();
                
                $startDate = Carbon::parse($schedule->start_date);
                $endDate = $schedule->end_date ? Carbon::parse($schedule->end_date) : now();
                
                if ($logDate->greaterThanOrEqualTo($startDate) && $logDate->lessThanOrEqualTo($endDate)) {
                    // Parse waktu dari jadwal
                    $time = $schedule->time ?? '08:00';
                    [$hour, $minute] = explode(':', $time);
                    
                    $scheduledTime = $logDate->clone()
                        ->setHour((int)$hour)
                        ->setMinute((int)$minute);
                    
                    // Status: 85% sent, 10% snoozed, 5% pending
                    $randStatus = rand(1, 100);
                    if ($randStatus <= 85) {
                        $status = 'sent';
                    } elseif ($randStatus <= 95) {
                        $status = 'snoozed';
                    } else {
                        $status = 'pending';
                    }
                    
                    NotificationLog::updateOrCreate(
                        [
                            'user_id' => $schedule->user_id,
                            'medication_schedule_id' => $schedule->id,
                            'scheduled_time' => $scheduledTime,
                        ],
                        [
                            'sent_at' => $status === 'sent' ? $scheduledTime->addMinutes(rand(0, 5)) : null,
                            'status' => $status,
                            'notification_type' => 'browser',
                        ]
                    );
                }
            }
        }
    }
}
