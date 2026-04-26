<?php

namespace App\Observers;

use App\Models\MedicationSchedule;
use App\Models\ClinicPatient;

class MedicationScheduleObserver
{
    /**
     * Handle the MedicationSchedule "created" event.
     * Saat jadwal obat baru dibuat, auto-sync status pasien
     */
    public function created(MedicationSchedule $schedule): void
    {
        $this->syncClinicPatientStatus($schedule);
    }

    /**
     * Handle the MedicationSchedule "updated" event.
     * Saat jadwal obat diubah, auto-sync status pasien
     */
    public function updated(MedicationSchedule $schedule): void
    {
        $this->syncClinicPatientStatus($schedule);
    }

    /**
     * Handle the MedicationSchedule "deleted" event.
     * Saat jadwal obat dihapus, auto-sync status pasien
     */
    public function deleted(MedicationSchedule $schedule): void
    {
        $this->syncClinicPatientStatus($schedule);
    }

    /**
     * Handle the MedicationSchedule "restored" event.
     */
    public function restored(MedicationSchedule $schedule): void
    {
        $this->syncClinicPatientStatus($schedule);
    }

    /**
     * Handle the MedicationSchedule "force deleted" event.
     */
    public function forceDeleted(MedicationSchedule $schedule): void
    {
        $this->syncClinicPatientStatus($schedule);
    }

    /**
     * Sync status clinic patient berdasarkan jadwal yang berubah
     */
    private function syncClinicPatientStatus(MedicationSchedule $schedule): void
    {
        // Cari clinic patient yang linked ke user ini
        $clinicPatient = ClinicPatient::where('user_id', $schedule->user_id)->first();

        if ($clinicPatient) {
            $clinicPatient->syncStatusWithSchedule();
        }
    }
}
