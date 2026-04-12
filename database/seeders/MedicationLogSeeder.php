<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedicationLog;
use App\Models\MedicationSchedule;
use Carbon\Carbon;

class MedicationLogSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = MedicationSchedule::with('user')->get();

        if ($schedules->isEmpty()) {
            return;
        }

        // Buat log untuk 14 hari terakhir
        foreach ($schedules as $schedule) {
            $startDate = Carbon::parse($schedule->start_date);
            $endDate = $schedule->end_date ? Carbon::parse($schedule->end_date) : now();
            
            for ($daysAgo = 14; $daysAgo >= 0; $daysAgo--) {
                $logDate = now()->subDays($daysAgo)->startOfDay();
                
                // Cek apakah tanggal dalam range jadwal
                if ($logDate->greaterThanOrEqualTo($startDate) && $logDate->lessThanOrEqualTo($endDate)) {
                    // Status: 80% taken, 15% missed, 5% pending
                    $randStatus = rand(1, 100);
                    if ($randStatus <= 80) {
                        $status = 'taken';
                    } elseif ($randStatus <= 95) {
                        $status = 'missed';
                    } else {
                        $status = 'pending';
                    }
                    
                    MedicationLog::updateOrCreate(
                        [
                            'user_id' => $schedule->user_id,
                            'medication_schedule_id' => $schedule->id,
                            'created_at' => $logDate,
                        ],
                        [
                            'taken_at' => $status === 'taken' ? $logDate->addHours(rand(6, 18)) : null,
                            'status' => $status,
                            'offline_synced' => false,
                        ]
                    );
                }
            }
        }
    }
}
