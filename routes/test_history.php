<?php
// Test history page access with authentication

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/test-history', function () {
        try {
            $user = auth()->user();
            return view('app.history.index', [
                'logs' => collect(),
                'stats' => [
                    'overall_compliance' => 75,
                    'total_days' => 30,
                    'perfect_days' => 20,
                    'zero_days' => 5,
                    'total_expected' => 60,
                    'total_taken' => 45,
                ],
                'dailyCompliance' => [],
                'medicines' => collect(),
                'fromDate' => now()->subMonth()->toDateString(),
                'toDate' => now()->toDateString(),
                'selectedMedicineId' => null,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    });
});
