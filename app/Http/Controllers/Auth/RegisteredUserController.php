<?php

// Kontrol untuk mengelola registrasi user baru
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    // Tampilkan form registrasi
    public function create(): View
    {
        return view('auth.register');
    }

    // Proses registrasi - buat user baru
    public function store(Request $request): RedirectResponse
    {
        // Validasi input registrasi termasuk role (mahasiswa/pegawai)
        $request->validate([
            'role_user' => ['nullable', 'in:mahasiswa,pegawai'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'nim' => ['nullable', 'string', 'max:255'],
            'prodi' => ['nullable', 'string', 'max:255'],
        ]);

        // Buat user baru dengan data yang tervalidasi
        $user = User::create([
            'role_user' => $request->role_user ?? 'mahasiswa',
            'name' => $request->name,
            'email' => $request->email,
            'nim' => $request->nim,
            'prodi' => $request->prodi,
            'password' => Hash::make($request->password),
        ]);

        // Fire registered event untuk email verification
        event(new Registered($user));

        // Login otomatis setelah registrasi
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
