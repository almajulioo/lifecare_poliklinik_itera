<?php

// Kontrol untuk menampilkan halaman verifikasi email
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    // Cek status verifikasi email user
    public function __invoke(Request $request): RedirectResponse|View
    {
        // Jika email sudah terverifikasi, arahkan ke dashboard
        // Jika belum, tampilkan halaman verifikasi
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('dashboard', absolute: false))
                    : view('auth.verify-email');
    }
}
