<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\MedicationSchedule;
use App\Services\OneSignalSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Sync jadwal obat yang sudah ada (belum ter-sync) ke OneSignal
 * Berguna untuk retroactive sync setelah menambah OneSignal integration
 */
class SyncExistingSchedulesToOneSignal extends Command
{
    protected $signature = 'medication:sync-existing-to-onesignal {--days=30 : Jadwalkan notifikasi untuk berapa hari ke depan} {--force : Skip confirmation}';

    protected $description = 'Sinkronisasi jadwal obat yang sudah ada ke OneSignal Scheduled Messages (untuk recovery atau backfill)';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $force = $this->option('force');

        $this->info("=== Sync Existing Medication Schedules to OneSignal ===\n");

        // Get semua active schedules yang masih berlaku
        $schedules = MedicationSchedule::where('is_active', true)
            ->whereDate('start_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', now());
            })
            ->with('user', 'medicine')
            ->get();

        if ($schedules->isEmpty()) {
            $this->warn('No active medication schedules found');
            return 1;
        }

        $this->info("Found {$schedules->count()} active medication schedules\n");

        // Summary
        $totalNotifications = 0;
        $schedulesByUser = [];

        foreach ($schedules as $schedule) {
            $userEmail = $schedule->user->email;
            if (!isset($schedulesByUser[$userEmail])) {
                $schedulesByUser[$userEmail] = 0;
            }
            $schedulesByUser[$userEmail]++;
            $totalNotifications += 2; // First + second reminder
        }

        $this->table(
            ['User Email', 'Number of Schedules'],
            collect($schedulesByUser)->map(fn($count, $email) => [$email, $count])->values()
        );

        $totalDays = $days * count($schedules);
        $this->line("\n📊 Summary:");
        $this->line("  • Total schedules to sync: " . $schedules->count());
        $this->line("  • Lookahead days: {$days}");
        $this->line("  • Estimated notifications: " . ($schedules->count() * $days * 2) . " (first + second reminder)");
        $this->line("  • Current UTC time: " . now('UTC')->format('Y-m-d H:i:s'));

        if (!$force) {
            if (!$this->confirm("\n❓ Proceed with syncing to OneSignal?")) {
                $this->info('Cancelled');
                return 0;
            }
        }

        $this->newLine();
        $this->info("Starting sync...\n");

        $service = new OneSignalSyncService();
        $successCount = 0;
        $errorCount = 0;
        $skippedCount = 0;

        $progressBar = $this->output->createProgressBar($schedules->count());
        $progressBar->start();

        foreach ($schedules as $schedule) {
            try {
                // Manually set lookahead days untuk service
                $result = $service->syncScheduleToOneSignal($schedule, $days);

                if ($result) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $errorCount++;
                Log::error('Error syncing schedule to OneSignal', [
                    'schedule_id' => $schedule->id,
                    'user_id' => $schedule->user_id,
                    'error' => $e->getMessage(),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Results
        $totalNotificationsScheduled = $successCount * ($days * 2);

        $this->info("=== Sync Complete ===\n");
        $this->line("✅ Successfully synced: {$successCount} schedules");
        if ($errorCount > 0) {
            $this->warn("❌ Errors: {$errorCount}");
        }

        $this->line("\n📊 Notifications scheduled:");
        $this->line("  • Total: " . $totalNotificationsScheduled);
        $this->line("  • Period: Next {$days} days");
        $this->line("  • Check OneSignal dashboard: Campaigns → Messages → Scheduled\n");

        Log::info('Batch sync existing schedules completed', [
            'total_schedules' => $schedules->count(),
            'synced_successfully' => $successCount,
            'errors' => $errorCount,
            'notifications_created' => $totalNotificationsScheduled,
            'days_lookahead' => $days,
        ]);

        return 0;
    }
}
