<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // Mahasiswa
            [
                'role_user' => 'mahasiswa',
                'name' => 'Farrel Alghifari',
                'email' => 'farrel.122140068@gmail.com',
                'password' => Hash::make('122140068'),
                'nim' => '122140068',
                'prodi' => 'Teknik Informatika',
                'notification_preferences' => json_encode(['email' => true, 'browser' => true]),
                'timezone' => 'Asia/Jakarta',
                'medical_conditions' => json_encode(['hypertension']),
            ],
            [
                'role_user' => 'mahasiswa',
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'password' => Hash::make('password123'),
                'nim' => '122010001',
                'prodi' => 'Teknik Informatika',
                'notification_preferences' => json_encode(['email' => true, 'browser' => true]),
                'timezone' => 'Asia/Jakarta',
                'medical_conditions' => json_encode(['diabetes']),
            ],
            [
                'role_user' => 'mahasiswa',
                'name' => 'Siti Nurhaliza',
                'email' => 'siti@example.com',
                'password' => Hash::make('password123'),
                'nim' => '122020002',
                'prodi' => 'Sistem Informasi',
                'notification_preferences' => json_encode(['email' => true, 'browser' => true]),
                'timezone' => 'Asia/Jakarta',
                'medical_conditions' => json_encode(['hypertension']),
            ],
            [
                'role_user' => 'mahasiswa',
                'name' => 'Ahmad Wijaya',
                'email' => 'ahmad@example.com',
                'password' => Hash::make('password123'),
                'nim' => '122030003',
                'prodi' => 'Teknik Elektro',
                'notification_preferences' => json_encode(['email' => false, 'browser' => true]),
                'timezone' => 'Asia/Jakarta',
                'medical_conditions' => json_encode([]),
            ],
            [
                'role_user' => 'mahasiswa',
                'name' => 'Rina Wijaya',
                'email' => 'rina@example.com',
                'password' => Hash::make('password123'),
                'nim' => '122040004',
                'prodi' => 'Teknik Mesin',
                'notification_preferences' => json_encode(['email' => true, 'browser' => false]),
                'timezone' => 'Asia/Jakarta',
                'medical_conditions' => json_encode(['asthma']),
            ],
            [
                'role_user' => 'mahasiswa',
                'name' => 'Doni Setiawan',
                'email' => 'doni@example.com',
                'password' => Hash::make('password123'),
                'nim' => '122050005',
                'prodi' => 'Teknik Sipil',
                'notification_preferences' => json_encode(['email' => true, 'browser' => true]),
                'timezone' => 'Asia/Jakarta',
                'medical_conditions' => json_encode(['heart_disease']),
            ],
            // Pegawai
            [
                'role_user' => 'pegawai',
                'name' => 'Dr. Bambang Sukarna',
                'email' => 'bambang@example.com',
                'password' => Hash::make('password123'),
                'nim' => null,
                'prodi' => null,
                'notification_preferences' => json_encode(['email' => true, 'browser' => true]),
                'timezone' => 'Asia/Jakarta',
                'medical_conditions' => json_encode([]),
            ],
            [
                'role_user' => 'pegawai',
                'name' => 'Ibu Sartini Kesehatan',
                'email' => 'sartini@example.com',
                'password' => Hash::make('password123'),
                'nim' => null,
                'prodi' => null,
                'notification_preferences' => json_encode(['email' => true, 'browser' => true]),
                'timezone' => 'Asia/Jakarta',
                'medical_conditions' => json_encode([]),
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
