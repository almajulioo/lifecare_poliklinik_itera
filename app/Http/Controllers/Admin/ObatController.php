<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use Illuminate\Http\Request;

class ObatController extends Controller
{
    /**
     * Display a listing of all medicines.
     */
    public function index(Request $request)
    {
        $query = Medicine::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
        }

        // Sort
        $sort = $request->sort ?? 'latest';
        if ($sort == 'latest') {
            $query->latest();
        } elseif ($sort == 'oldest') {
            $query->oldest();
        }

        $medicines = $query->paginate(15);

        return view('admin.obat.index', [
            'medicines' => $medicines,
            'search' => $request->search,
            'sort' => $sort,
        ]);
    }

    /**
     * Show the form for creating a new medicine.
     */
    public function create()
    {
        return view('admin.obat.create');
    }

    /**
     * Store a newly created medicine in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dose' => 'required|string|max:255',
            'unit' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        // Check if medicine with same name already exists
        $existingMedicine = Medicine::whereRaw('LOWER(name) = ?', [strtolower($validated['name'])])
            ->first();

        if ($existingMedicine) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['name' => 'Obat dengan nama ini sudah ada. Gunakan nama yang berbeda.']);
        }

        // Add source_type as ADMIN for medicines created by admin
        $validated['source_type'] = 'ADMIN';
        $validated['user_id'] = null; // Admin medicines don't belong to specific user

        Medicine::create($validated);

        return redirect()->route('admin.obat.index')->with('success', 'Obat berhasil ditambahkan');
    }

    /**
     * Show the form for editing the specified medicine.
     */
    public function edit($id)
    {
        $medicine = Medicine::findOrFail($id);
        return view('admin.obat.edit', ['medicine' => $medicine]);
    }

    /**
     * Update the specified medicine in storage.
     */
    public function update(Request $request, $id)
    {
        $medicine = Medicine::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dose' => 'required|string|max:255',
            'unit' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        // Check if another medicine with same name already exists (exclude current medicine)
        $existingMedicine = Medicine::whereRaw('LOWER(name) = ?', [strtolower($validated['name'])])
            ->where('id', '!=', $medicine->id)
            ->first();

        if ($existingMedicine) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['name' => 'Obat dengan nama ini sudah ada. Gunakan nama yang berbeda.']);
        }

        $medicine->update($validated);

        return redirect()->route('admin.obat.index')->with('success', 'Obat berhasil diperbarui');
    }

    /**
     * Remove the specified medicine from storage.
     */
    public function destroy($id)
    {
        $medicine = Medicine::findOrFail($id);
        $medicine->delete();

        return redirect()->route('admin.obat.index')->with('success', 'Obat berhasil dihapus');
    }
}
