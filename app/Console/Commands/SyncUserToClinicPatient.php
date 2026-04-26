<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\ClinicPatient;
use Illuminate\Console\Command;

class SyncUserToClinicPatient extends Command
{
    /**
     * Nama dan deskripsi dari command
     */
    protected $signature = 'app:sync-user-to-clinic-patient';

    protected $description = 'Sinkronisasi semua User yang belum punya ClinicPatient - membuat ClinicPatient entry untuk mereka';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai sinkronisasi User ke ClinicPatient...');

        // Cari semua User yang tidak punya ClinicPatient
        $usersWithoutClinicPatient = User::whereDoesntHave('clinicPatient')->get();

        if ($usersWithoutClinicPatient->isEmpty()) {
            $this->info('✓ Semua User sudah punya ClinicPatient!');
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($usersWithoutClinicPatient as $user) {
            // Buat ClinicPatient untuk user ini
            try {
                ClinicPatient::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'identity_number' => $user->nim ?? null,
                    'category' => $user->role_user, // mahasiswa atau pegawai
                    'email' => $user->email,
                    'status' => 'aktif',
                ]);
                $count++;
                $this->line("  ✓ {$user->name} ({$user->email})");
            } catch (\Exception $e) {
                $this->error("  ✗ Gagal membuat ClinicPatient untuk {$user->name}: {$e->getMessage()}");
            }
        }

        $this->info("\n✓ Sinkronisasi selesai! {$count} ClinicPatient entry berhasil dibuat.");
        return self::SUCCESS;
    }
}
