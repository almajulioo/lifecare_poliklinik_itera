<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // ============ MAHASISWA ============
            [
                'role_user' => 'mahasiswa',
                'name' => 'Farrel Alghifari',
                'email' => 'farrel.122140068@student.itera.ac.id',
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
                'email' => 'budi.122010001@student.itera.ac.id',
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
                'email' => 'siti.122020002@student.itera.ac.id',
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
                'email' => 'ahmad.122030003@student.itera.ac.id',
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
                'email' => 'rina.122040004@student.itera.ac.id',
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
                'email' => 'doni.122050005@student.itera.ac.id',
                'password' => Hash::make('password123'),
                'nim' => '122050005',
                'prodi' => 'Teknik Sipil',
                'notification_preferences' => json_encode(['email' => true, 'browser' => true]),
                'timezone' => 'Asia/Jakarta',
                'medical_conditions' => json_encode(['heart_disease']),
            ],
            [
                'role_user' => 'mahasiswa',
                'name' => 'Eka Putri Handini',
                'email' => 'eka.122060006@student.itera.ac.id',
                'password' => Hash::make('password123'),
                'nim' => '122060006',
                'prodi' => 'Teknik Industri',
                'notification_preferences' => json_encode(['email' => true, 'browser' => true]),
                'timezone' => 'Asia/Jakarta',
                'medical_conditions' => json_encode(['allergy']),
            ],
            [
                'role_user' => 'mahasiswa',
                'name' => 'Rizki Pratama',
                'email' => 'rizki.122070007@student.itera.ac.id',
                'password' => Hash::make('password123'),
                'nim' => '122070007',
                'prodi' => 'Teknik Kimia',
                'notification_preferences' => json_encode(['email' => true, 'browser' => true]),
                'timezone' => 'Asia/Jakarta',
                'medical_conditions' => json_encode([]),
            ],
            [
                'role_user' => 'mahasiswa',
                'name' => 'Dewi Lestari',
                'email' => 'dewi.122080008@student.itera.ac.id',
                'password' => Hash::make('password123'),
                'nim' => '122080008',
                'prodi' => 'Teknik Bioproses',
                'notification_preferences' => json_encode(['email' => true, 'browser' => true]),
                'timezone' => 'Asia/Jakarta',
                'medical_conditions' => json_encode(['gastritis']),
            ],
            [
                'role_user' => 'mahasiswa',
                'name' => 'Muhammad Hanif',
                'email' => 'muhammad.122090009@student.itera.ac.id',
                'password' => Hash::make('password123'),
                'nim' => '122090009',
                'prodi' => 'Sistem Informasi',
                'notification_preferences' => json_encode(['email' => true, 'browser' => true]),
                'timezone' => 'Asia/Jakarta',
                'medical_conditions' => json_encode([]),
            ],

            // ============ PEGAWAI ============
            [
                'role_user' => 'pegawai',
                'name' => 'Dr. Bambang Sukarna',
                'email' => 'bambang.sukarna@itera.ac.id',
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
                'email' => 'sartini.kesehatan@itera.ac.id',
                'password' => Hash::make('password123'),
                'nim' => null,
                'prodi' => null,
                'notification_preferences' => json_encode(['email' => true, 'browser' => true]),
                'timezone' => 'Asia/Jakarta',
                'medical_conditions' => json_encode([]),
            ],
            [
                'role_user' => 'pegawai',
                'name' => 'Dr. Adi Permana',
                'email' => 'adi.permana@itera.ac.id',
                'password' => Hash::make('password123'),
                'nim' => null,
                'prodi' => null,
                'notification_preferences' => json_encode(['email' => true, 'browser' => true]),
                'timezone' => 'Asia/Jakarta',
                'medical_conditions' => json_encode([]),
            ],
            [
                'role_user' => 'pegawai',
                'name' => 'Ns. Rini Handayani',
                'email' => 'rini.handayani@itera.ac.id',
                'password' => Hash::make('password123'),
                'nim' => null,
                'prodi' => null,
                'notification_preferences' => json_encode(['email' => true, 'browser' => true]),
                'timezone' => 'Asia/Jakarta',
                'medical_conditions' => json_encode([]),
            ],
            [
                'role_user' => 'pegawai',
                'name' => 'Bapak Suryanto Farmasis',
                'email' => 'suryanto.farmasis@itera.ac.id',
                'password' => Hash::make('password123'),
                'nim' => null,
                'prodi' => null,
                'notification_preferences' => json_encode(['email' => true, 'browser' => true]),
                'timezone' => 'Asia/Jakarta',
                'medical_conditions' => json_encode([]),
            ],
            [
                'role_user' => 'pegawai',
                'name' => 'Ibu Endang Laboratorium',
                'email' => 'endang.laboratorium@itera.ac.id',
                'password' => Hash::make('password123'),
                'nim' => null,
                'prodi' => null,
                'notification_preferences' => json_encode(['email' => true, 'browser' => true]),
                'timezone' => 'Asia/Jakarta',
                'medical_conditions' => json_encode([]),
            ],
            [
                'role_user' => 'pegawai',
                'name' => 'Bapak Hendra Admin',
                'email' => 'hendra.admin@itera.ac.id',
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
