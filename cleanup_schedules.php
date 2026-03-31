<?php
require 'bootstrap/app.php';

$app = require_once 'bootstrap/app.php';

$user = \App\Models\User::where('name', 'Asaveva Azzahra')->first();
if($user) {
    $count = \App\Models\MedicationSchedule::where('user_id', $user->id)->delete();
    echo "✅ Deleted $count schedules for Asaveva Azzahra\n";
} else {
    echo "❌ User not found\n";
}
