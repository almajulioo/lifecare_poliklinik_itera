<?php

// Kontrol untuk konfirmasi password sebelum aksi sensitif
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    // Tampilkan form konfirmasi password
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    // Verifikasi password untuk aksi sensitif
    public function store(Request $request): RedirectResponse
    {
        // Validasi password yang diinput
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        // Set session password confirmation time
        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
