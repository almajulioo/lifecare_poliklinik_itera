<?php

// Kontrol untuk mengelola profil user
namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    // Tampilkan form edit profil user
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    // Perbarui informasi profil user
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Update data profil dengan input yang sudah divalidasi
        $request->user()->fill($request->validated());

        // Jika email berubah, hapus email_verified_at
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    // Hapus akun user
    public function destroy(Request $request): RedirectResponse
    {
        // Validasi password untuk konfirmasi penghapusan
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Logout user
        Auth::logout();

        // Hapus data user dari database
        $user->delete();

        // Invalidasi session dan regenerate token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
