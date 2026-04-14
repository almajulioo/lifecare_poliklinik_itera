<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GetPasswordResetLink extends Command
{
    /**
     * Nama dan deskripsi command
     */
    protected $signature = 'password:reset-link {email}';
    protected $description = 'Dapatkan link reset password untuk user tertentu';

    /**
     * Jalankan command
     */
    public function handle()
    {
        $email = $this->argument('email');

        // Cari user berdasarkan email
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User dengan email '{$email}' tidak ditemukan.");
            return 1;
        }

        // Ambil token dari password_reset_tokens
        $resetToken = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$resetToken) {
            $this->error("Tidak ada token reset password untuk email '{$email}'.");
            $this->info("Silakan request password reset di halaman forgot-password terlebih dahulu.");
            return 1;
        }

        // Generate reset URL
        $resetUrl = url(route('password.reset', [
            'token' => $resetToken->token,
            'email' => urlencode($email),
        ], false));

        // Tampilkan informasi
        $this->info("✅ Password Reset Link ditemukan:");
        $this->newLine();
        $this->line("📧 Email: <fg=cyan>{$email}</>");
        $this->line("🔐 Token: <fg=yellow>{$resetToken->token}</>");
        $this->line("⏰ Created: <fg=gray>{$resetToken->created_at}</>");
        $this->newLine();
        $this->info("🔗 Reset Link:");
        $this->line("<fg=blue;options=bold>{$resetUrl}</>");
        $this->newLine();
        $this->line("📋 Instruksi:");
        $this->line("1. Copy link di atas");
        $this->line("2. Buka di browser Anda");
        $this->line("3. Masukkan password baru");
        $this->line("4. Klik Reset Password");

        return 0;
    }
}
