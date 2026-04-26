<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\MedicationSchedule;
use App\Observers\MedicationScheduleObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observer untuk auto-sync status pasien berdasarkan jadwal minum obat
        MedicationSchedule::observe(MedicationScheduleObserver::class);
    }
}
