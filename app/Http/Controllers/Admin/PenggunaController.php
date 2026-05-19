<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClinicPatient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PenggunaController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%");
        }

        // Status filter
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Sort
        $sort = $request->sort ?? 'latest';
        if ($sort == 'latest') {
            $query->latest();
        } elseif ($sort == 'oldest') {
            $query->oldest();
        }

        $users = $query->paginate(15);

        return view('admin.pengguna.index', [
            'users' => $users,
            'search' => $request->search,
            'status' => $request->status ?? 'all',
            'sort' => $sort,
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.pengguna.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'role_user' => 'required|string|in:mahasiswa,pegawai',
            'password' => 'required|min:8|confirmed',
        ];

        // NIM is required only for mahasiswa
        if ($request->input('role_user') == 'mahasiswa') {
            $rules['nim'] = 'required|string|unique:users';
            $rules['prodi'] = 'nullable|string';
        } else {
            // Pegawai doesn't need NIM, but may have other ID
            $rules['nim'] = 'nullable|string|unique:users';
            $rules['prodi'] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        // Hash the password before saving
        $validated['password'] = Hash::make($validated['password']);

        // Create user
        $user = User::create($validated);

        // Otomatis buat ClinicPatient entry untuk user baru
        ClinicPatient::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'identity_number' => $user->nim ?? null,
            'category' => $user->role_user, // mahasiswa atau pegawai
            'email' => $user->email,
            'status' => 'aktif',
        ]);

        return redirect()->route('admin.pengguna.index')->with('success', 'Pengguna berhasil ditambahkan dan otomatis terdaftar di ClinicPatient');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.pengguna.edit', ['user' => $user]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_user' => 'required|string|in:mahasiswa,pegawai',
            'prodi' => 'nullable|string',
        ];

        // NIM is required only for mahasiswa
        if ($request->input('role_user') == 'mahasiswa') {
            $rules['nim'] = 'required|string|unique:users,nim,' . $user->id;
        } else {
            // Pegawai doesn't need NIM
            $rules['nim'] = 'nullable|string|unique:users,nim,' . $user->id;
        }

        $validated = $request->validate($rules);

        $user->update($validated);

        // Update ClinicPatient yang terkait untuk konsistensi data
        if ($user->clinicPatient) {
            $user->clinicPatient->update([
                'name' => $validated['name'],
                'identity_number' => $validated['nim'] ?? null,
                'category' => $validated['role_user'],
                'email' => $user->email,
            ]);
        }

        return redirect()->route('admin.pengguna.index')->with('success', 'Pengguna berhasil diperbarui');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Hapus ClinicPatient yang terkait jika ada
        if ($user->clinicPatient) {
            $user->clinicPatient->delete();
        }

        $user->delete();

        return redirect()->route('admin.pengguna.index')->with('success', 'Pengguna dan data pasien berhasil dihapus');
    }
}
