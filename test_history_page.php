<?php
// Test script to fetch the history page with authentication

// Start session and load Laravel
require __DIR__ . '/bootstrap/app.php';

// Get Laravel app instance
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Get Illuminate\Foundation\Application
use Illuminate\Support\Facades\DB;
use App\Models\User;

// Create a test user if needed
$user = User::where('email', 'budi@example.com')->first();

if (!$user) {
    echo "User not found. Please run seeder first.\n";
    exit(1);
}

echo "Found user: " . $user->email . "\n";
echo "Checking medication history for this user...\n\n";

// Get medication logs for this user
$logs = \App\Models\MedicationLog::where('user_id', $user->id)
    ->with(['medicationSchedule.medicine'])
    ->orderBy('created_at', 'desc')
    ->take(10)
    ->get();

echo "Total medication logs in database: " . \App\Models\MedicationLog::where('user_id', $user->id)->count() . "\n";
echo "Recent logs:\n";
foreach ($logs as $log) {
    echo "- " . ($log->medicationSchedule?->medicine->name ?? 'Unknown') . " - " . $log->status . " - " . $log->created_at . "\n";
}

// Check medication schedules
$schedules = \App\Models\MedicationSchedule::where('user_id', $user->id)->get();
echo "\n\nTotal medication schedules: " . $schedules->count() . "\n";

// Check medicines
$medicines = \App\Models\Medicine::where('source_type', 'PATIENT')
    ->where('source_id', $user->id)
    ->get();
echo "User's personal medicines: " . $medicines->count() . "\n";
