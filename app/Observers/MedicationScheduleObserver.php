<?php

namespace App\Observers;

use App\Models\MedicationSchedule;
use App\Models\ClinicPatient;
use App\Services\OneSignalSyncService;

class MedicationScheduleObserver
{
    /**
     * Handle the MedicationSchedule "created" event.
     * Saat jadwal obat baru dibuat, auto-sync status pasien dan OneSignal
     */
    public function created(MedicationSchedule $schedule): void
    {
        $this->syncClinicPatientStatus($schedule);
        $this->syncOneSignal($schedule);
    }

    /**
     * Handle the MedicationSchedule "updated" event.
     * Saat jadwal obat diubah, auto-sync status pasien dan OneSignal
     */
    public function updated(MedicationSchedule $schedule): void
    {
        $this->syncClinicPatientStatus($schedule);
        // Cek apakah status is_active baru saja diubah, dan nilainya sekarang adalah false
        if ($schedule->wasChanged('is_active') && $schedule->is_active == false) {
            $this->syncOneSignalDelete($schedule); // Hapus/deactivate jadwal di OneSignal
         } else {
            $this->syncOneSignal($schedule); // Sync normal jika update kolom lain atau is_active diubah ke true
        }
    }

    /**
     * Handle the MedicationSchedule "deleted" event.
     * Saat jadwal obat dihapus, auto-sync status pasien dan OneSignal
     */
    public function deleted(MedicationSchedule $schedule): void
    {
        $this->syncClinicPatientStatus($schedule);
        $this->syncOneSignalDelete($schedule);
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

    /**
     * Sinkronisasi jadwal ke OneSignal (create/update)
     */
    private function syncOneSignal(MedicationSchedule $schedule): void
    {
        $service = new OneSignalSyncService();
        $service->syncScheduleToOneSignal($schedule);
    }

    /**
     * Deactivate jadwal dari OneSignal (delete)
     */
    private function syncOneSignalDelete(MedicationSchedule $schedule): void
    {
        $service = new OneSignalSyncService();
        $service->deactivateScheduleFromOneSignal($schedule);
    }
}
