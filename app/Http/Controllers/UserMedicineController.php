<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;

class UserMedicineController extends Controller
{
    /**
     * Show list of medicines (admin + user medicines)
     */
    public function index()
    {
        $adminMedicines = Medicine::adminMedicines()->paginate(5);
        $userMedicines = Medicine::userMedicines(auth()->id())->get();

        return view('app.medications.index', compact('adminMedicines', 'userMedicines'));
    }

    /**
     * Show create form untuk tambah obat user
     */
    public function create()
    {
        return view('app.medicines.create');
    }

    /**
     * Store medicine untuk user
     */
    public function store(Request $request)
    {
        $this->authorize('create', Medicine::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dose' => 'required|string|max:100',
            'unit' => 'required|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        // Add user_id dan source_type untuk medicine PATIENT
        $validated['user_id'] = auth()->id();
        $validated['source_type'] = 'PATIENT';

        $medicine = Medicine::create($validated);

        return redirect()
            ->route('app.medications.index')
            ->with('success', 'Obat berhasil ditambahkan: ' . $medicine->name);
    }

    /**
     * Get user medicines
     */
    public function myMedicines()
    {
        $medicines = Medicine::where('user_id', auth()->id())->paginate(10);
        
        return view('app.medicines.my-medicines', compact('medicines'));
    }

    /**
     * Edit user medicine
     */
    public function edit(Medicine $medicine)
    {
        $this->authorize('update', $medicine);
        return view('app.medicines.edit', compact('medicine'));
    }

    /**
     * Update user medicine
     */
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
            ->route('app.medicines.index')
            ->with('success', 'Obat berhasil diperbarui: ' . $medicine->name);
    }

    /**
     * Delete user medicine
     */
    public function destroy(Medicine $medicine)
    {
        $this->authorize('delete', $medicine);

        $name = $medicine->name;
        $medicine->delete();

        return redirect()
            ->route('app.medications.index')
            ->with('success', 'Obat berhasil dihapus: ' . $name);
    }
}
