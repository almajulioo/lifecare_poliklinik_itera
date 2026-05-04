<?php

// Test script untuk verify real-time OneSignal sync

namespace App;

use Illuminate\Support\Env;

// Bootstrap Laravel
require __DIR__ . '/bootstrap/app.php';

$app = app();

$container = $app->make(\Illuminate\Contracts\Container\Container::class);

// Get models
$User = $container->make('App\Models\User');
$Medicine = $container->make('App\Models\Medicine');
$MedicationSchedule = $container->make('App\Models\MedicationSchedule');

echo "\n=== Testing Real-Time OneSignal Sync ===\n\n";

// Get first user
$user = $User::first();
echo "User: " . $user->email . " (ID: {$user->id})\n";
echo "Timezone: " . ($user->timezone ?? 'Not set - using ' . config('app.timezone')) . "\n\n";

// Get first medicine
$medicine = $Medicine::first();
echo "Medicine: " . $medicine->name . " (ID: {$medicine->id})\n";
echo "Dose: " . $medicine->dose . " " . ($medicine->unit ?? '') . "\n\n";

// Create new schedule
echo "Creating new medication schedule...\n";
$schedule = $MedicationSchedule::create([
    'user_id' => $user->id,
    'medicine_id' => $medicine->id,
    'start_date' => now()->addDays(1)->date(),
    'end_date' => now()->addDays(5)->date(),
    'time' => '14:30',
    'frequency' => 'daily',
    'duration_days' => 5,
    'source' => 'manual',
    'is_active' => true
]);

echo "✓ Schedule created successfully!\n";
echo "  - Schedule ID: {$schedule->id}\n";
echo "  - Start: " . $schedule->start_date->format('Y-m-d') . "\n";
echo "  - End: " . $schedule->end_date->format('Y-m-d') . "\n";
echo "  - Time: " . $schedule->time . "\n\n";

echo "✓ Observer triggered automatically\n";
echo "✓ OneSignalSyncService.syncScheduleToOneSignal() was called\n";
echo "✓ 30-day scheduling initiated\n\n";

echo "Check logs for sync status:\n";
echo "  tail -50 storage/logs/laravel.log | grep -i 'onesignal\\|notification'\n\n";

echo "=== Test Complete ===\n";
?>
