<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class RekamMedisController extends Controller
{
    /**
     * Display medical records for all users.
     */
    public function index(Request $request)
    {
        $query = User::with(['medicationSchedules.medicine', 'medicationLogs']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%");
        }

        $users = $query->paginate(15);

        // Get the selected user if provided
        $selectedUser = null;
        if ($request->filled('user_id')) {
            $selectedUser = User::with(['medicationSchedules.medicine', 'medicationLogs'])->find($request->user_id);
        }

        return view('admin.rekam-medis.index', [
            'users' => $users,
            'selectedUser' => $selectedUser,
            'search' => $request->search,
        ]);
    }

    /**
     * Show the form for editing medical records.
     */
    public function edit(User $user)
    {
        return view('admin.rekam-medis.edit', [
            'user' => $user->load(['medicationSchedules.medicine', 'medicationLogs'])
        ]);
    }

    /**
     * Update medical records.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'medical_conditions' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $user->update($validated);

        return redirect()->route('admin.rekam-medis.index')->with('success', 'Rekam medis berhasil diperbarui');
    }
}
