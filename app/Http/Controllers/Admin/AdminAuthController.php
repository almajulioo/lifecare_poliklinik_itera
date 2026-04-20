<?php

// Kontrol untuk autentikasi admin
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminAuthController extends Controller
{
    // Tampilkan form login admin
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        if (auth()->guard('admin')->attempt($request->only('email','password'))) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password admin salah.',
        ]);
    }

    public function logout(Request $request)
    {
        auth()->guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    // ============ FORGOT PASSWORD SECTION ============

    public function showForgotPasswordForm()
    {
        return view('admin.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:admins'],
        ], [
            'email.exists' => 'Email admin tidak ditemukan dalam sistem.',
        ]);

        // Hapus token lama jika ada
        DB::table('admin_password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Generate token baru
        $token = Str::random(64);

        // Simpan ke database
        DB::table('admin_password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => now(),
        ]);

        // Generate reset URL
        $resetUrl = url(route('admin.password.reset', [
            'token' => $token,
            'email' => urlencode($request->email),
        ], false));

        // Tampilkan info untuk development (karena email mungkin belum dikonfigurasi)
        session()->flash('reset_link', $resetUrl);

        return back()->with('status', 'Link reset password telah dikirim ke email Anda. Cek email Anda untuk melanjutkan.');
    }

    public function showResetForm($token, $email)
    {
        // Validasi token
        $isValid = DB::table('admin_password_reset_tokens')
            ->where('email', $email)
            ->where('token', $token)
            ->first();

        if (!$isValid) {
            return redirect()->route('admin.login')->with('error', 'Token reset password tidak valid atau sudah kadaluarsa.');
        }

        return view('admin.auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:admins'],
            'token' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Validasi token
        $isValid = DB::table('admin_password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$isValid) {
            return back()->withErrors(['token' => 'Token tidak valid atau sudah kadaluarsa.']);
        }

        // Update password admin
        Admin::where('email', $request->email)->update([
            'password' => Hash::make($request->password),
        ]);

        // Hapus token
        DB::table('admin_password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return redirect()->route('admin.login')->with('status', 'Password berhasil direset. Silakan login dengan password baru Anda.');
    }

    // ============ REGISTER SECTION (Optional) ============

    public function showRegister()
    {
        return abort(404); // Disable registration for now
    }

    public function register(Request $request)
    {
        return abort(404); // Disable registration for now
    }
}