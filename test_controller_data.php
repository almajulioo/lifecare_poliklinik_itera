<?php
// Test script to check if controller method works correctly and what data is returned

use App\Models\User;
use App\Models\MedicationLog;
use App\Models\MedicationSchedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;

// Bootstrap Laravel
$app = require __DIR__ . '/bootstrap/app.php';  
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

//Bind services
$app->make(\Illuminate\Contracts\Console\Kernel::class);

try {
    // Get test user
    $user = User::where('email', 'user@lifecare.test')->first();
    if (!$user) {
        $user = User::where('email', 'budi@example.com')->first();
    }
    
    if (!$user) {
        echo "❌ No test user found!\n";
        exit(1);
    }
    
    echo "✅ Found user: " . $user->email . "\n\n";
    
    // Test data like in controller
    $userId = $user->id;
    $fromDate = now()->subMonth();
    $toDate = now();
    
    echo "=== DATA AVAILABLE ===\n";
    echo "User ID: $userId\n";
    
    $logs = MedicationLog::with(['medicationSchedule.medicine', 'user'])
        ->where('user_id', $userId)
        ->whereDate('created_at', '>=', $fromDate)
        ->whereDate('created_at', '<=', $toDate)
        ->orderBy('created_at', 'desc')
        ->paginate(20);
    
    echo "Logs found: " . $logs->total() . "\n";
    
    // Get medicines for filter
    $medicines = MedicationSchedule::where('user_id', $userId)
        ->where('is_active', true)
        ->with('medicine')
        ->distinct()
        ->get()
        ->pluck('medicine')
        ->unique('id');
    
    echo "Medicines for filter: " . $medicines->count() . "\n";
    
    // Get daily compliance data
    $dailyLogs = MedicationLog::where('user_id', $userId)
        ->where('status', 'taken')
        ->whereDate('created_at', '>=', $fromDate)
        ->whereDate('created_at', '<=', $toDate)
        ->get()
        ->groupBy(function ($log) {
            return $log->created_at->toDateString();
        })
        ->map(fn($group) => $group->count());
    
    echo "Daily compliance data points: " . count($dailyLogs) . "\n";
    
    // Try to calculate stats
    echo "\n=== TESTING STATS CALCULATION ===\n";
    
    $period = CarbonPeriod::create($fromDate, $toDate);
    $schedules = MedicationSchedule::where('user_id', $userId)
        ->where('is_active', true)
        ->whereDate('start_date', '<=', $toDate)
        ->where(function ($q) use ($toDate) {
            $q->whereNull('end_date')->orWhereDate('end_date', '>=', $toDate);
        })
        ->with(['logs' => function($q) use ($fromDate, $toDate) {
            $q->whereDate('created_at', '>=', $fromDate)
              ->whereDate('created_at', '<=', $toDate);
        }])
        ->get();
    
    echo "Active schedules: " . $schedules->count() . "\n";
    
    $totalExpected = 0;
    $totalTaken = 0;
    $dayBreakdown = [];
    $errorCount = 0;
    
    foreach ($period as $date) {
        try {
            $dayCount = 0;
            $dayTaken = 0;
            
            foreach ($schedules as $schedule) {
                if ($schedule->start_date->lte($date) && 
                    ($schedule->end_date === null || $schedule->end_date->gte($date))) {
                    $dayCount++;
                    
                    // This might be the problematic line
                    $log = $schedule->logs
                        ->where('status', 'taken')
                        ->first(function($log) use ($date) {
                            return $log->created_at->toDateString() === $date->toDateString();
                        });
                    
                    if ($log) {
                        $dayTaken++;
                    }
                }
            }
            
            if ($dayCount > 0) {
                $totalExpected += $dayCount;
                $totalTaken += $dayTaken;
                $dayBreakdown[$date->toDateString()] = [
                    'date' => $date->toDateString(),
                    'expected' => $dayCount,
                    'taken' => $dayTaken,
                    'compliance' => round(($dayTaken / $dayCount) * 100),
                ];
            }
        } catch (Exception $e) {
            $errorCount++;
        }
    }
    
    echo "Days calculated: " . count($dayBreakdown) . "\n";
    if ($errorCount > 0) echo "⚠️  Errors during calculation: $errorCount\n";
    
    $overallCompliance = $totalExpected > 0 
        ? round(($totalTaken / $totalExpected) * 100) 
        : 0;
    
    echo "\n=== STATS RESULT ===\n";
    echo "Overall Compliance: $overallCompliance%\n";
    echo "Total Expected: $totalExpected\n";
    echo "Total Taken: $totalTaken\n";
    echo "Total Days: " . count($dayBreakdown) . "\n";
    
    $perfectDays = collect($dayBreakdown)->filter(fn($d) => $d['compliance'] === 100)->count();
    $zeroDays = collect($dayBreakdown)->filter(fn($d) => $d['taken'] === 0)->count();
    
    echo "Perfect Days: $perfectDays\n";
    echo "Zero Days: $zeroDays\n";
    
    echo "\n✅ Stats calculation completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
