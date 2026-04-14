<?php

// Kontrol untuk mengelola sesi autentikasi (login/logout)
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    // Tampilkan form login
    public function create(): View
    {
        return view('auth.login');
    }

    // Proses login - autentikasi user
    public function store(LoginRequest $request): RedirectResponse
    {
        // Validasi dan autentikasi kredensial
        $request->authenticate();

        // Regenerate session setelah login
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    // Logout - hapus sesi autentikasi
    public function destroy(Request $request): RedirectResponse
    {
        // Logout user
        Auth::guard('web')->logout();

        // Invalidate session
        $request->session()->invalidate();

        // Regenerate CSRF token
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
