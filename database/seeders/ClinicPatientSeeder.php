<?php

namespace Database\Seeders;

use App\Models\ClinicPatient;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClinicPatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'identity_number' => '122010001',
                'category' => 'mahasiswa',
                'phone' => '081234567890',
                'status' => 'aktif',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti@example.com',
                'identity_number' => '122020002',
                'category' => 'mahasiswa',
                'phone' => '082345678901',
                'status' => 'aktif',
            ],
            [
                'name' => 'Ahmad Wijaya',
                'email' => 'ahmad@example.com',
                'identity_number' => '122030003',
                'category' => 'mahasiswa',
                'phone' => '083456789012',
                'status' => 'aktif',
            ],
            [
                'name' => 'Rina Wijaya',
                'email' => 'rina@example.com',
                'identity_number' => '122040004',
                'category' => 'mahasiswa',
                'phone' => '084567890123',
                'status' => 'aktif',
            ],
            [
                'name' => 'Doni Setiawan',
                'email' => 'doni@example.com',
                'identity_number' => '122050005',
                'category' => 'mahasiswa',
                'phone' => '085678901234',
                'status' => 'aktif',
            ],
            // Pasien dari pegawai
            [
                'name' => 'Dr. Bambang Sukarna',
                'email' => 'bambang@example.com',
                'identity_number' => '198001011',
                'category' => 'pegawai',
                'phone' => '086789012345',
                'status' => 'aktif',
            ],
            [
                'name' => 'Ibu Sartini Kesehatan',
                'email' => 'sartini@example.com',
                'identity_number' => '198502022',
                'category' => 'pegawai',
                'phone' => '087890123456',
                'status' => 'aktif',
            ],
            // Pasien umum tanpa user
            [
                'name' => 'Rudi Cahyono',
                'email' => 'rudi.cahyono@email.com',
                'identity_number' => '3201011990001001',
                'category' => 'umum',
                'phone' => '081111111111',
                'status' => 'aktif',
                'user_id' => null,
            ],
            [
                'name' => 'Sinta Dewi Kartini',
                'email' => 'sinta.dewi@email.com',
                'identity_number' => '3201011995002002',
                'category' => 'umum',
                'phone' => '081222222222',
                'status' => 'aktif',
                'user_id' => null,
            ],
            [
                'name' => 'Hendra Suryakan',
                'email' => 'hendra.suryakan@email.com',
                'identity_number' => '3201011988003003',
                'category' => 'umum',
                'phone' => '081333333333',
                'status' => 'tidak_aktif',
                'user_id' => null,
            ],
            [
                'name' => 'Lidia Aprilia',
                'email' => 'lidia.aprilia@email.com',
                'identity_number' => '3201011992004004',
                'category' => 'umum',
                'phone' => '081444444444',
                'status' => 'aktif',
                'user_id' => null,
            ],
            [
                'name' => 'Markus Budi',
                'email' => 'markus.budi@email.com',
                'identity_number' => '3201011985005005',
                'category' => 'umum',
                'phone' => '081555555555',
                'status' => 'aktif',
                'user_id' => null,
            ],
        ];

        // Get users yang ada
        $users = User::all()->keyBy('email');

        foreach ($patients as $patient) {
            // Cek apakah pasien ada di users
            if (isset($users[$patient['email']])) {
                $patient['user_id'] = $users[$patient['email']]->id;
            } else {
                $patient['user_id'] = null;
            }

            ClinicPatient::updateOrCreate(
                ['email' => $patient['email']],
                $patient
            );
        }
    }
}

