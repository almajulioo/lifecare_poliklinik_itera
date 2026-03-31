<?php
// Test file untuk debug compliance stats
// Bisa diakses dari: http://127.0.0.1:8000/test-stats.php

// Jangan jalankan jika tidak authenticated
if (empty($_GET['key']) || $_GET['key'] !== 'debug123') {
    die('Access denied');
}

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Http\Kernel::class)->handle(
    $request = \Illuminate\Http\Request::capture()
);

use App\Models\MedicationSchedule;
use App\Models\MedicationLog;
use Carbon\Carbon;

echo "<h1>Compliance Stats Debug</h1>";

// User 3 = Test 123
$userId = 3;
$today = Carbon::parse('2026-03-25')->startOfDay();

echo "<h2>Checking Schedules for User 3 on 2026-03-25</h2>";

$schedules = MedicationSchedule::where('user_id', $userId)
    ->where('is_active', true)
    ->whereDate('start_date', '<=', $today)
    ->where(function ($q) use ($today) {
        $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
    })
    ->with(['logs' => function($q) use ($today) {
        $q->whereDate('created_at', '>=', $today)
          ->whereDate('created_at', '<=', $today);
    }])
    ->get();

echo "<pre>";
echo "Query Result Count: " . $schedules->count() . "\n\n";

foreach($schedules as $schedule) {
    echo "Schedule ID: " . $schedule->id . "\n";
    echo "Medicine: " . $schedule->medicine->name . "\n";
    echo "Start Date: " . $schedule->start_date . "\n";
    echo "End Date: " . $schedule->end_date . "\n";
    echo "Is Active: " . $schedule->is_active . "\n";
    echo "Time: " . $schedule->time . "\n";
    echo "Logs Count: " . $schedule->logs->count() . "\n";
    
    // Check if today is within range
    $isToday = $schedule->start_date->lte($today) && 
              ($schedule->end_date === null || $schedule->end_date->gte($today));
    echo "Is Today Within Range: " . ($isToday ? "YES" : "NO") . "\n\n";
}

echo "\n\nDirect DB Query:\n";
$dbSchedules = \DB::table('medication_schedules')
    ->where('user_id', 3)
    ->where('is_active', 1)
    ->get();

foreach($dbSchedules as $s) {
    echo "ID: {$s->id}, Start: {$s->start_date}, End: {$s->end_date}\n";
}

echo "\n\nMedication Logs for User 3 Today:\n";
$logs = MedicationLog::where('user_id', 3)
    ->whereDate('created_at', $today)
    ->get();
echo "Count: " . $logs->count() . "\n";
foreach($logs as $log) {
    echo "Schedule ID: {$log->schedule_id}, Status: {$log->status}\n";
}

echo "</pre>";
