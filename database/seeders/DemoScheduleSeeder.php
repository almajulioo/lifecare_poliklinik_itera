<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Medicine;
use App\Models\MedicationSchedule;
use Illuminate\Support\Facades\Hash;

class DemoScheduleSeeder extends Seeder
{
    public function run(): void
    {
        // user demo
        $user = User::updateOrCreate(
            ['email' => 'user@lifecare.test'],
            [
                'role_user' => 'pegawai',
                'name' => 'Test 123',
                'password' => Hash::make('password123'),
                'nim' => null,
                'prodi' => null,
            ]
        );

        $medicine = Medicine::first();

        if (!$medicine) return;

        // jadwal demo: hari ini jam 08:00
        MedicationSchedule::create([
            'user_id' => $user->id,
            'medicine_id' => $medicine->id,
            'start_date' => now()->toDateString(),
            'end_date' => null,
            'time' => '08:00:00',
            'frequency' => '1x sehari',
            'duration_days' => 7,
            'source' => 'resep',
            'is_active' => true,
        ]);
    }
}