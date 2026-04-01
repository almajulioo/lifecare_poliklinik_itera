<?php
// Quick test to check what the history controller returns

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\MedicationLog;

// Add a debug route
Route::get('/debug/history', function () {
    // Find the test user
    $user = User::where('email', 'user@lifecare.test')->first();
    
    if (!$user) {
        return response()->json(['error' => 'Test user not found'], 404);
    }
    
    // Manually set auth just for testing
    Auth::setUser($user);
    
    // Get logs
    $logs = MedicationLog::where('user_id', $user->id)
        ->with(['medicationSchedule.medicine'])
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    
    return response()->json([
        'user_id' => $user->id,
        'user_email' => $user->email,
        'total_logs' => MedicationLog::where('user_id', $user->id)->count(),
        'schedules' => DB::table('medication_schedules')->where('user_id', $user->id)->count(),
        'medicines' => DB::table('medicines')->where('source_id', $user->id)->count(),
        'recent_logs' => $logs->map(fn($l) => [
            'id' => $l->id,
            'medicine' => $l->medicationSchedule?->medicine->name,
            'status' => $l->status,
            'created_at' => $l->created_at,
        ])
    ]);
});
