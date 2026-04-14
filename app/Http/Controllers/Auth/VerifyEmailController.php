<?php

// Kontrol untuk memverifikasi email user
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    // Tandai email user sebagai terverifikasi
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        // Jika sudah terverifikasi, arahkan ke dashboard
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        // Mark email sebagai verified
        if ($request->user()->markEmailAsVerified()) {
            // Fire verified event
            event(new Verified($request->user()));
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
