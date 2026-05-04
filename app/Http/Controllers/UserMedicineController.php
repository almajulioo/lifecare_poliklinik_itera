<?php

// Kontrol untuk mengelola daftar obat user (obat pribadi + obat dari jadwal)
namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;

class UserMedicineController extends Controller
{
    // Tampilkan daftar obat user (obat pribadi + obat dari jadwal)
    public function index()
    {
        $userId = auth()->id();
        
        // Ambil obat pribadi yang dibuat user
        $ownMedicines = Medicine::userMedicines($userId)->get();
        
        // Ambil obat dari jadwal yang sudah dibuat admin
        $scheduledMedicines = Medicine::whereIn('id', function($query) use ($userId) {
            $query->select('medicine_id')
                  ->from('medication_schedules')
                  ->where('user_id', $userId);
        })->get();
        
        // Gabung dan hapus duplikat
        $allMedicines = $ownMedicines->merge($scheduledMedicines)->unique('id');
        
        return view('app.medications.index', compact('allMedicines'));
    }

    // Tampilkan form untuk tambah obat
    public function create()
    {
        return view('app.medicines.create');
    }

    // Simpan obat baru ke database
    public function store(Request $request)
    {
        $this->authorize('create', Medicine::class);

        $userId = auth()->id();

        // Validasi input obat
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dose' => 'required|string|max:100',
            'unit' => 'required|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        // Cek apakah obat dengan nama yang sama sudah ada
        $existingMedicine = Medicine::where('user_id', $userId)
            ->whereRaw('LOWER(name) = ?', [strtolower($validated['name'])])
            ->first();

        if ($existingMedicine) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['name' => 'Obat sudah ada. Gunakan nama yang berbeda.']);
        }

        // Tambah user_id dan tipe sumber
        $validated['user_id'] = $userId;
        $validated['source_type'] = 'PATIENT';

        $medicine = Medicine::create($validated);

        return redirect()
            ->route('app.medications.index')
            ->with('success', 'Obat berhasil ditambahkan: ' . $medicine->name);
    }

    // Tampilkan daftar obat pribadi user dengan pagination
    public function myMedicines()
    {
        $medicines = Medicine::where('user_id', auth()->id())->paginate(10);
        
        return view('app.medicines.my-medicines', compact('medicines'));
    }

    // Tampilkan form edit obat
    public function edit(Medicine $medicine)
    {
        $this->authorize('update', $medicine);
        return view('app.medicines.edit', compact('medicine'));
    }

    // Perbarui data obat
    public function update(Request $request, Medicine $medicine)
    {
        $this->authorize('update', $medicine);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dose' => 'required|string|max:100',
            'unit' => 'required|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $medicine->update($validated);

        return redirect()
            ->route('app.medications.index')
            ->with('success', 'Obat berhasil diperbarui: ' . $medicine->name);
    }

    // Hapus obat dari database
    public function destroy(Medicine $medicine)
    {
        $this->authorize('delete', $medicine);

        // Simpan nama obat sebelum dihapus
        $name = $medicine->name;
        $medicine->delete();

        return redirect()
            ->route('app.medications.index')
            ->with('success', 'Obat berhasil dihapus: ' . $name);
    }
}