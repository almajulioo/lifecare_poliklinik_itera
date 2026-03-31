<?php

require 'bootstrap/app.php';
$app = require_once 'bootstrap/app.php';

$container = $app->make(Illuminate\Contracts\Foundation\Application::class);
$db = $container->make('db');

// Check schedules
echo "=== SCHEDULES USER 3 ===\n";
$schedules = $db->table('medication_schedules')->where('user_id', 3)->get();
foreach($schedules as $s) {
    echo "ID: {$s->id}, Start: {$s->start_date}, End: {$s->end_date}, Active: {$s->is_active}\n";
}

// Check logs today
echo "\n=== LOGS USER 3 TODAY ===\n";
$today = date('Y-m-d');
$logs = $db->table('medication_logs')->where('user_id', 3)->whereDate('created_at', $today)->get();
echo "Total logs today: " . count($logs) . "\n";
foreach($logs as $l) {
    echo "Schedule: {$l->schedule_id}, Status: {$l->status}\n";
}

// Check schedule 5 details
echo "\n=== SCHEDULE 5 DETAILS ===\n";
$schedule = $db->table('medication_schedules')->find(5);
if ($schedule) {
    echo "ID: {$schedule->id}\n";
    echo "Start: {$schedule->start_date}\n";
    echo "End: {$schedule->end_date}\n";
    echo "Active: {$schedule->is_active}\n";
    echo "Today: {$today}\n";
    echo "Is today between start and end? " . (($today >= $schedule->start_date && ($schedule->end_date === null || $today <= $schedule->end_date)) ? "YES" : "NO") . "\n";
}
