<?php

// Kontrol untuk kirim link reset password ke email user
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    // Tampilkan form lupa password
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    // Proses permintaan reset password - kirim link ke email
    public function store(Request $request): RedirectResponse
    {
        // Validasi email
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Kirim link reset password ke email user
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Arahkan berdasarkan status pengiriman
        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
