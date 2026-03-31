<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create multiple realistic Indonesian app users
        User::factory(50)->create();

        // Create specific users for testing
        // Format NIM mahasiswa: 1(2 digit tahun)(2 digit urutan)(4 digit random)
        User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'nim' => '122010001',
            'prodi' => 'Teknik Informatika',
            'role_user' => 'mahasiswa',
        ]);

        User::factory()->create([
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@example.com',
            'nim' => '122020002',
            'prodi' => 'Sistem Informasi',
            'role_user' => 'mahasiswa',
        ]);

        User::factory()->create([
            'name' => 'Ahmad Wijaya',
            'email' => 'ahmad@example.com',
            'nim' => '122030003',
            'prodi' => 'Teknik Elektro',
            'role_user' => 'mahasiswa',
        ]);
    }
}
