<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\MedicationSchedule;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Diagnostic command untuk troubleshoot OneSignal notification issues
 * Cek: configuration, user subscription, schedule data, API connectivity
 */
class DiagnoseOneSignalNotifications extends Command
{
    protected $signature = 'onesignal:diagnose {--user= : Check specific user email} {--schedule= : Check specific schedule ID} {--detail : Show detailed information}';

    protected $description = 'Diagnose OneSignal notification issues - check config, schedules, subscriptions';

    public function handle(): int
    {
        $verbose = $this->option('verbose');
        $userEmail = $this->option('user');
        $scheduleId = $this->option('schedule');

        $this->info("=== OneSignal Notification Diagnostic ===\n");

        // Step 1: Check Configuration
        $this->checkConfiguration();

        // Step 2: Check OneSignal API Connectivity
        $this->checkApiConnectivity();

        // Step 3: Check Users
        if ($userEmail) {
            $this->checkUserSubscription($userEmail);
        } else {
            $this->checkUsersOverview();
        }

        // Step 4: Check Schedules
        if ($scheduleId) {
            $this->checkScheduleDetail($scheduleId);
        } else {
            $this->checkSchedulesOverview();
        }

        // Step 5: Timezone Check
        $this->checkTimezones();

        $this->newLine();
        $this->info("=== Diagnostic Complete ===\n");

        return 0;
    }

    private function checkConfiguration(): void
    {
        $this->line("📋 Configuration Check:\n");

        $appId = config('services.onesignal.app_id');
        $apiKey = config('services.onesignal.rest_api_key');

        if ($appId) {
            $this->line("  ✅ OneSignal App ID configured");
            $this->line("     ID: " . substr($appId, 0, 8) . "..." . substr($appId, -4));
        } else {
            $this->error("  ❌ OneSignal App ID NOT configured");
        }

        if ($apiKey) {
            $this->line("  ✅ OneSignal API Key configured");
            $this->line("     Key: " . substr($apiKey, 0, 15) . "..." . substr($apiKey, -10));
        } else {
            $this->error("  ❌ OneSignal API Key NOT configured");
        }

        if ($appId && $apiKey) {
            $this->line("  ✅ Configuration looks correct\n");
        }
    }

    private function checkApiConnectivity(): void
    {
        $this->line("🔗 OneSignal API Connectivity Check:\n");

        try {
            $response = Http::withHeaders([
                'Authorization' => config('services.onesignal.rest_api_key'),
                'Content-Type' => 'application/json; charset=utf-8',
            ])->get('https://onesignal.com/api/v1/apps/' . config('services.onesignal.app_id'));

            if ($response->successful()) {
                $data = $response->json();
                $this->line("  ✅ API connection successful");
                $this->line("     App Name: " . ($data['app_name'] ?? 'N/A'));
                $this->line("     Created: " . ($data['created_at'] ?? 'N/A') . "\n");
            } else {
                $this->error("  ❌ API request failed: " . $response->status());
                $this->error("     Response: " . json_encode($response->json()) . "\n");
            }
        } catch (\Exception $e) {
            $this->error("  ❌ API connection error: " . $e->getMessage() . "\n");
        }
    }

    private function checkUsersOverview(): void
    {
        $this->line("👥 Users Overview:\n");

        $users = User::whereIn('role_user', ['user', 'mahasiswa', 'pegawai', 'pasien', 'patient'])
            ->select('id', 'email', 'role_user', 'timezone')
            ->get();

        if ($users->isEmpty()) {
            $this->warn("  No users found");
            return;
        }

        $this->line("  Total users: " . $users->count() . "\n");

        $usersWithoutTimezone = $users->where('timezone', null)->count();
        if ($usersWithoutTimezone > 0) {
            $this->warn("  ⚠️  Users without timezone: {$usersWithoutTimezone}");
            $this->line("     These will use default: " . config('app.timezone') . "\n");
        } else {
            $this->line("  ✅ All users have timezone set\n");
        }

        $this->table(
            ['Email', 'Role', 'Timezone'],
            $users->take(5)->map(fn($u) => [
                $u->email,
                $u->role_user,
                $u->timezone ?? config('app.timezone')
            ])
        );

        if ($users->count() > 5) {
            $this->line("  ... and " . ($users->count() - 5) . " more users\n");
        }
    }

    private function checkUserSubscription($userEmail): void
    {
        $this->line("👤 User Subscription Check: {$userEmail}\n");

        $user = User::where('email', $userEmail)->first();

        if (!$user) {
            $this->error("  ❌ User not found: {$userEmail}\n");
            return;
        }

        $this->line("  User ID: {$user->id}");
        $this->line("  Role: {$user->role_user}");
        $this->line("  Timezone: " . ($user->timezone ?? config('app.timezone')));

        $scheduleCount = $user->medicationSchedules()->where('is_active', true)->count();
        $this->line("  Active Schedules: {$scheduleCount}\n");

        if ($scheduleCount === 0) {
            $this->warn("  ⚠️  No active medication schedules for this user\n");
        }
    }

    private function checkSchedulesOverview(): void
    {
        $this->line("💊 Medication Schedules Overview:\n");

        $activeSchedules = MedicationSchedule::where('is_active', true)
            ->whereDate('start_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', now());
            })
            ->count();

        $this->line("  Active schedules: {$activeSchedules}");

        if ($activeSchedules === 0) {
            $this->warn("  ⚠️  No active medication schedules\n");
            return;
        }

        // Check which schedules have times in the past TODAY
        $now = now();
        $today = $now->format('Y-m-d');

        $pastTodayCount = 0;
        $futureTodayCount = 0;

        foreach (MedicationSchedule::where('is_active', true)->get() as $schedule) {
            $scheduleTime = Carbon::createFromFormat('Y-m-d H:i', $today . ' ' . $schedule->time, 'UTC');
            if ($scheduleTime < $now) {
                $pastTodayCount++;
            } else {
                $futureTodayCount++;
            }
        }

        $this->line("  Today's status:");
        $this->line("    • Past times (won't be scheduled): {$pastTodayCount}");
        $this->line("    • Future times (will be scheduled): {$futureTodayCount}\n");

        // Sample schedules
        $samples = MedicationSchedule::where('is_active', true)
            ->with('user', 'medicine')
            ->limit(5)
            ->get();

        if ($samples->isNotEmpty()) {
            $this->line("  Sample schedules:");
            foreach ($samples as $s) {
                $this->line("    • {$s->user->email} - {$s->medicine->name} @ {$s->time}");
            }
            $this->newLine();
        }
    }

    private function checkScheduleDetail($scheduleId): void
    {
        $this->line("💊 Schedule Detail: ID {$scheduleId}\n");

        $schedule = MedicationSchedule::find($scheduleId);

        if (!$schedule) {
            $this->error("  ❌ Schedule not found: {$scheduleId}\n");
            return;
        }

        $this->line("  Medicine: " . $schedule->medicine->name);
        $this->line("  Dose: " . $schedule->medicine->dose . " " . ($schedule->medicine->unit ?? ''));
        $this->line("  User: " . $schedule->user->email);
        $this->line("  User Timezone: " . ($schedule->user->timezone ?? config('app.timezone')));
        $this->line("  Time: " . $schedule->time);
        $this->line("  Start: " . $schedule->start_date->format('Y-m-d'));
        $this->line("  End: " . ($schedule->end_date ? $schedule->end_date->format('Y-m-d') : 'None (ongoing)'));
        $this->line("  Active: " . ($schedule->is_active ? 'Yes' : 'No'));

        $this->newLine();
        $this->line("  Timezone Conversion Example (for today):");

        $userTimezone = $schedule->user->timezone ?? config('app.timezone');
        $localTime = Carbon::createFromFormat('Y-m-d H:i', now()->format('Y-m-d') . ' ' . $schedule->time, $userTimezone);
        $utcTime = $localTime->copy()->setTimezone('UTC');

        $this->line("    • Local ({$userTimezone}): " . $localTime->format('Y-m-d H:i'));
        $this->line("    • UTC: " . $utcTime->format('Y-m-d H:i'));
        $this->line("    • OneSignal Format: " . $utcTime->format('Y-m-d\TH:i:s\Z'));

        $this->newLine();

        if ($utcTime > now('UTC')) {
            $this->line("    ✅ This notification WILL be scheduled (time is in the future)\n");
        } else {
            $this->warn("    ❌ This notification will NOT be scheduled (time is in the past)\n");
        }
    }

    private function checkTimezones(): void
    {
        $this->line("🕐 Timezone Information:\n");

        $this->line("  App Default Timezone: " . config('app.timezone'));
        $this->line("  Current UTC Time: " . now('UTC')->format('Y-m-d H:i:s'));
        $this->line("  Current Local Time: " . now()->format('Y-m-d H:i:s'));

        $uniqueTimezones = User::whereNotNull('timezone')->distinct()->pluck('timezone')->sort();

        if ($uniqueTimezones->isNotEmpty()) {
            $this->newLine();
            $this->line("  User Timezones in use:");
            foreach ($uniqueTimezones as $tz) {
                $userCount = User::where('timezone', $tz)->count();
                $time = now($tz)->format('H:i:s');
                $this->line("    • $tz: $time (users: $userCount)");
            }
        }

        $this->newLine();
    }
}
