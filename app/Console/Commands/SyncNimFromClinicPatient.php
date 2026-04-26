<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\ClinicPatient;
use Illuminate\Console\Command;

class SyncNimFromClinicPatient extends Command
{
    /**
     * Nama dan deskripsi dari command
     */
    protected $signature = 'app:sync-nim-from-clinic-patient';

    protected $description = 'Sinkronisasi NIM dari ClinicPatient ke User jika User.nim kosong';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai sinkronisasi NIM dari ClinicPatient...');

        // Cari semua User yang punya ClinicPatient tapi NIM kosong
        $users = User::where('nim', null)
                    ->whereHas('clinicPatient', function ($query) {
                        $query->whereNotNull('identity_number');
                    })
                    ->get();

        if ($users->isEmpty()) {
            $this->info('✓ Semua User sudah punya NIM atau tidak ada ClinicPatient dengan identity_number!');
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($users as $user) {
            $clinicPatient = $user->clinicPatient;
            
            if ($clinicPatient && $clinicPatient->identity_number) {
                try {
                    $user->update(['nim' => $clinicPatient->identity_number]);
                    $count++;
                    $this->line("  ✓ {$user->name} - NIM: {$clinicPatient->identity_number}");
                } catch (\Exception $e) {
                    $this->error("  ✗ Gagal update {$user->name}: {$e->getMessage()}");
                }
            }
        }

        $this->info("\n✓ Sinkronisasi selesai! {$count} User NIM berhasil diisi.");
        return self::SUCCESS;
    }
}
