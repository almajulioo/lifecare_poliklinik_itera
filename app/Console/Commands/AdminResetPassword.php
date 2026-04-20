<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class AdminResetPassword extends Command
{
    /**
     * Nama dan deskripsi command
     */
    protected $signature = 'admin:reset-password {email} {password?}';
    protected $description = 'Reset password admin atau generate password baru';

    /**
     * Jalankan command
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        // Cari admin berdasarkan email
        $admin = Admin::where('email', $email)->first();

        if (!$admin) {
            $this->error("Admin dengan email '{$email}' tidak ditemukan.");
            return 1;
        }

        // Jika password tidak diberikan, generate random password
        if (!$password) {
            $password = \Illuminate\Support\Str::random(12);
            $this->info("Password baru (otomatis): {$password}");
        }

        // Update password
        $admin->update([
            'password' => Hash::make($password),
        ]);

        $this->info("Password admin '{$email}' berhasil direset.");
        $this->line("Email: {$email}");
        $this->line("Password: {$password}");

        return 0;
    }
}
