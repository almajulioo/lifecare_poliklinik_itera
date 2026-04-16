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
        $query = User::with(['medicationSchedules.medicine', 'medicationLogs', 'clinicPatient']);

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
            $selectedUser = User::with(['medicationSchedules.medicine', 'medicationLogs', 'clinicPatient'])->find($request->user_id);
        }

        return view('admin.rekam-medis.index', [
            'users' => $users,
            'selectedUser' => $selectedUser,
            'search' => $request->search,
        ]);
    }

    /**
     * Display medical records for a specific user.
     */
    public function show(User $user)
    {
        $user->load(['medicationSchedules.medicine', 'medicationLogs', 'clinicPatient']);
        return view('admin.rekam-medis.show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing medical records.
     */
    public function edit(User $user)
    {
        return view('admin.rekam-medis.edit', [
            'user' => $user->load(['medicationSchedules.medicine', 'medicationLogs', 'clinicPatient'])
        ]);
    }

    /**
     * Update medical records.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'medical_conditions' => 'nullable|array',
            'medical_conditions.*' => 'string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Filter out empty medical conditions
        if ($validated['medical_conditions']) {
            $validated['medical_conditions'] = array_filter($validated['medical_conditions'], function($val) {
                return !empty(trim($val));
            });
        }

        $user->update($validated);

        return redirect()->route('admin.rekam-medis.index')->with('success', 'Rekam medis berhasil diperbarui');
    }
}

