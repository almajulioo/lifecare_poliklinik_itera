<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\MedicationSchedule;
use App\Services\OneSignalSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScheduleMedicationNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'medication:schedule-notifications {--days=7}';

    /**
     * The description of the console command.
     */
    protected $description = 'Sync active medication schedules dengan OneSignal Scheduled Messages (batch job untuk 7 hari)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $this->info("Scheduling medication notifications for the next {$days} days...");

        try {
            $this->scheduleNotifications($days);
        } catch (\Exception $e) {
            $this->error('Error scheduling notifications: ' . $e->getMessage());
            Log::error('Error scheduling medications notifications', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Schedule notifications for all active medication schedules
     */
    private function scheduleNotifications(int $days)
    {
        $service = new OneSignalSyncService();

        // Work in UTC consistently
        $now = now('UTC');
        $startDate = $now->copy()->startOfDay();
        $endDate = $startDate->copy()->addDays($days)->endOfDay();

        // Get all active users with medication schedules
        $users = User::whereIn('role_user', ['user', 'mahasiswa', 'pegawai', 'pasien', 'patient'])
            ->whereHas('medicationSchedules', function($q) use ($endDate) {
                $q->where('is_active', true)
                  ->whereDate('start_date', '<=', $endDate)
                  ->where(function($q2) {
                      $q2->whereNull('end_date')->orWhereDate('end_date', '>=', now());
                  });
            })
            ->get();

        if ($users->isEmpty()) {
            $this->warn('No users with active medication schedules found');
            return;
        }

        $this->info("Current UTC time: {$now->format('Y-m-d H:i:s')}");
        $this->info("Scheduling for: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
        $this->info("Found {$users->count()} users with active schedules");

        $totalScheduled = 0;
        $errorCount = 0;

        foreach ($users as $user) {
            // Get active medication schedules for this user
            $schedules = MedicationSchedule::where('user_id', $user->id)
                ->where('is_active', true)
                ->whereDate('start_date', '<=', $endDate)
                ->where(function($q) {
                    $q->whereNull('end_date')->orWhereDate('end_date', '>=', now());
                })
                ->get();

            foreach ($schedules as $schedule) {
                try {
                    // Use OneSignalSyncService to schedule notifications
                    if ($service->syncScheduleToOneSignal($schedule)) {
                        $totalScheduled += 2; // First + second reminder
                        $this->line("  ✓ Synced: {$user->email} - {$schedule->medicine->name}");
                    } else {
                        $errorCount++;
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::warning('Failed to schedule medication notifications', [
                        'user_id' => $user->id,
                        'schedule_id' => $schedule->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        $this->info("✅ Successfully scheduled: {$totalScheduled} notifications");
        if ($errorCount > 0) {
            $this->warn("⚠️  Errors: {$errorCount}");
        }
    }
}
