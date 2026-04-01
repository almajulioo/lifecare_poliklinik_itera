<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MedicationHistoryController;

Route::get('/debug-history', function () {
    try {
        // Test data
        $stats = [
            'overall_compliance' => 75,
            'total_days' => 30,
            'perfect_days' => 20,
            'zero_days' => 5,
        ];
        
        $dailyCompliance = [
            ['date' => '2026-03-01', 'count' => 2],
            ['date' => '2026-03-02', 'count' => 1],
            ['date' => '2026-03-03', 'count' => 3],
        ];
        
        $logs = collect();
        $medicines = collect();
        
        // Try to render view with test data
        return view('app.history.index', [
            'logs' => $logs,
            'stats' => $stats,
            'dailyCompliance' => $dailyCompliance,
            'medicines' => $medicines,
            'fromDate' => now()->subMonth()->toDateString(),
            'toDate' => now()->toDateString(),
            'selectedMedicineId' => null,
        ]);
    } catch (Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString()),
        ], 500);
    }
});
