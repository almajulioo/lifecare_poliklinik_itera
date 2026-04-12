<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedicationSchedule;
use App\Models\User;
use App\Models\Medicine;

class MedicationScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = [
            // Jadwal untuk Budi Santoso (mahasiswa dengan diabetes)
            [
                'user_email' => 'budi@example.com',
                'medicine_name' => 'Metformin',
                'start_date' => now()->toDateString(),
                'end_date' => null,
                'time' => '08:00',
                'frequency' => '2x sehari',
                'duration_days' => 365,
                'source' => 'resep',
                'source_type' => 'ADMIN',
                'is_active' => true,
                'notes' => 'Jadwal rutin untuk kontrol diabetes',
            ],
            // Jadwal untuk Siti Nurhaliza (mahasiswa dengan hipertensi)
            [
                'user_email' => 'siti@example.com',
                'medicine_name' => 'Amlodipine',
                'start_date' => now()->toDateString(),
                'end_date' => null,
                'time' => '07:00',
                'frequency' => '1x sehari',
                'duration_days' => 365,
                'source' => 'resep',
                'source_type' => 'ADMIN',
                'is_active' => true,
                'notes' => 'Obat tekanan darah tinggi',
            ],
            // Jadwal untuk Ahmad Wijaya (mahasiswa)
            [
                'user_email' => 'ahmad@example.com',
                'medicine_name' => 'Vitamin C',
                'start_date' => now()->toDateString(),
                'end_date' => null,
                'time' => '09:00',
                'frequency' => '1x sehari',
                'duration_days' => 30,
                'source' => 'mandiri',
                'source_type' => 'PATIENT',
                'is_active' => true,
                'notes' => 'Suplemen untuk imunitas',
            ],
            // Jadwal untuk Rina Wijaya (mahasiswa dengan asma)
            [
                'user_email' => 'rina@example.com',
                'medicine_name' => 'Salbutamol',
                'start_date' => now()->toDateString(),
                'end_date' => null,
                'time' => '06:00',
                'frequency' => '2x sehari',
                'duration_days' => 180,
                'source' => 'resep',
                'source_type' => 'ADMIN',
                'is_active' => true,
                'notes' => 'Inhalasi asma pagi dan malam',
            ],
            // Jadwal untuk Doni Setiawan (mahasiswa dengan penyakit jantung)
            [
                'user_email' => 'doni@example.com',
                'medicine_name' => 'Aspirin',
                'start_date' => now()->toDateString(),
                'end_date' => null,
                'time' => '08:00',
                'frequency' => '1x sehari',
                'duration_days' => 365,
                'source' => 'resep',
                'source_type' => 'ADMIN',
                'is_active' => true,
                'notes' => 'Pencegahan trombosis',
            ],
            // Jadwal untuk Dr. Bambang (pegawai)
            [
                'user_email' => 'bambang@example.com',
                'medicine_name' => 'Omeprazole',
                'start_date' => now()->toDateString(),
                'end_date' => null,
                'time' => '07:30',
                'frequency' => '1x sehari',
                'duration_days' => 90,
                'source' => 'resep',
                'source_type' => 'ADMIN',
                'is_active' => true,
                'notes' => 'Pengobatan asam lambung',
            ],
            // Jadwal untuk Ibu Sartini (pegawai)
            [
                'user_email' => 'sartini@example.com',
                'medicine_name' => 'Vitamin B12',
                'start_date' => now()->toDateString(),
                'end_date' => null,
                'time' => '10:00',
                'frequency' => '3x seminggu',
                'duration_days' => 30,
                'source' => 'mandiri',
                'source_type' => 'PATIENT',
                'is_active' => true,
                'notes' => 'Suplemen energi',
            ],
        ];

        $users = User::all()->keyBy('email');
        $medicines = Medicine::all()->keyBy('name');

        foreach ($schedules as $schedule) {
            $userId = $users[$schedule['user_email']]->id ?? null;
            $medicineId = $medicines[$schedule['medicine_name']]->id ?? null;

            if ($userId && $medicineId) {
                MedicationSchedule::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'medicine_id' => $medicineId,
                        'start_date' => $schedule['start_date'],
                    ],
                    [
                        'end_date' => $schedule['end_date'],
                        'time' => $schedule['time'],
                        'frequency' => $schedule['frequency'],
                        'duration_days' => $schedule['duration_days'],
                        'source' => $schedule['source'],
                        'source_type' => $schedule['source_type'],
                        'is_active' => $schedule['is_active'],
                    ]
                );
            }
        }
    }
}
