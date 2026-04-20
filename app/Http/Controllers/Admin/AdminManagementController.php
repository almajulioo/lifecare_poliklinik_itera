<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminManagementController extends Controller
{
    /**
     * Tampilkan daftar semua admin
     */
    public function index()
    {
        $admins = Admin::paginate(10);
        return view('admin.management.index', compact('admins'));
    }

    /**
     * Tampilkan form create admin
     */
    public function create()
    {
        return view('admin.management.create');
    }

    /**
     * Simpan admin baru ke database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.unique' => 'Email admin sudah terdaftar.',
        ]);

        Admin::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.management.index')
            ->with('success', "Admin '{$validated['email']}' berhasil ditambahkan.");
    }

    /**
     * Hapus admin
     */
    public function destroy(Admin $admin)
    {
        // Cegah penghapusan admin terakhir
        if (Admin::count() <= 1) {
            return back()->with('error', 'Tidak bisa menghapus admin terakhir. Setidaknya harus ada 1 admin.');
        }

        $email = $admin->email;
        $admin->delete();

        return redirect()->route('admin.management.index')
            ->with('success', "Admin '{$email}' berhasil dihapus.");
    }
}
