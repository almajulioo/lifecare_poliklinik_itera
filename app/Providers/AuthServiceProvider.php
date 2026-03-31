<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Medicine;
use App\Models\MedicationSchedule;
use App\Models\Admin;
use App\Policies\MedicinePolicy;
use App\Policies\MedicationSchedulePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Medicine::class => MedicinePolicy::class,
        MedicationSchedule::class => MedicationSchedulePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
