<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ClinicPatient;

class SyncClinicPatientStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-clinic-patient-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronkan status semua pasien poliklinik berdasarkan jadwal minum obat mereka';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai sinkronisasi status pasien...');
        
        $patients = ClinicPatient::all();
        $updated = 0;
        $unchanged = 0;

        foreach ($patients as $patient) {
            $oldStatus = $patient->status;
            $patient->syncStatusWithSchedule();
            
            if ($patient->status !== $oldStatus) {
                $updated++;
                $statusLabel = $patient->status === 'aktif' ? 'Aktif' : 'Tidak Aktif';
                $this->line("  ✓ {$patient->name}: $oldStatus → {$statusLabel}");
            } else {
                $unchanged++;
            }
        }

        $this->info("Selesai!");
        $this->info("📊 Hasil: $updated pasien diupdate status-nya, $unchanged pasien tidak berubah");

        return Command::SUCCESS;
    }
}
