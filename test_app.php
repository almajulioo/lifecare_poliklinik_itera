<?php
// Test file to check history page rendering

require __DIR__ . '/vendor/autoload.php';

// Start Laravel
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Create a test request
$request = \Illuminate\Http\Request::create('/app/history', 'GET');

// Test without auth first
try {
    $response = $kernel->handle($request);
    echo "Status (unauthenticated): " . $response->status() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Now test with auth
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

$user = User::where('email', 'user@lifecare.test')->first() ??
         User::where('email', 'budi@example.com')->first();

if ($user) {
    // Manually authenticate
    Auth::setUser($user);
    echo "Authenticated as: " . $user->email . "\n";
    
    // Check data
    echo "\n=== User Data ===\n";
    echo "User ID: " . $user->id . "\n";
    
    $logCount = \App\Models\MedicationLog::where('user_id', $user->id)->count();
    echo "Medication Logs: " . $logCount . "\n";
    
    $scheduleCount = \App\Models\MedicationSchedule::where('user_id', $user->id)->count();
    echo "Active Schedules: " . $scheduleCount . "\n";
    
    $medicineCount = \App\Models\Medicine::whereHas('schedules', function ($q) use ($user) {
        $q->where('user_id', $user->id);
    })->count();
    echo "Medicines in Schedules: " . $medicineCount . "\n";
} else {
    echo "No test user found!\n";
}
