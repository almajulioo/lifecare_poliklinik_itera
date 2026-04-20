<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class AdminCreate extends Command
{
    /**
     * Nama dan deskripsi command
     */
    protected $signature = 'admin:create {email} {password?}';
    protected $description = 'Buat admin baru atau generate password random';

    /**
     * Jalankan command
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        // Validasi email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("Format email tidak valid: '{$email}'");
            return 1;
        }

        // Cek apakah admin sudah ada
        $existingAdmin = Admin::where('email', $email)->first();

        if ($existingAdmin) {
            $this->error("Admin dengan email '{$email}' sudah ada.");
            return 1;
        }

        // Jika password tidak diberikan, generate random password
        if (!$password) {
            $password = \Illuminate\Support\Str::random(12);
            $this->info("Password baru (otomatis): {$password}");
        }

        // Buat admin baru
        $admin = Admin::create([
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Admin baru berhasil dibuat!");
        $this->line("Email: {$email}");
        $this->line("Password: {$password}");
        $this->line("Admin ID: {$admin->id}");

        return 0;
    }
}
