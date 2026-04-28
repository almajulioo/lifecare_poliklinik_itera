<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Medication Reminders Scheduling
|--------------------------------------------------------------------------
*/

// Send medication reminders every 5 minutes
Schedule::command('medication:send-reminders --check-interval=5')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Medication reminders command failed');
    });
