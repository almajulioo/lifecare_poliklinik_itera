<?php

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
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'role_user' => ['required', 'in:mahasiswa,pegawai'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'nim' => [$request->role_user === 'mahasiswa' ? 'required' : 'nullable'],
            'prodi' => [$request->role_user === 'mahasiswa' ? 'required' : 'nullable'],
        ]);

        $user = User::create([
            'role_user' => $request->role_user,
            'name' => $request->name,
            'email' => $request->email,
            'nim' => $request->role_user === 'mahasiswa' ? $request->nim : null,
            'prodi' => $request->role_user === 'mahasiswa' ? $request->prodi : null,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
