<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicationSchedule;
use App\Models\Medicine;
use App\Models\User;
use Illuminate\Http\Request;

class AdminMedicationScheduleController extends Controller
{
    /**
     * Menampilkan daftar jadwal obat dengan pagination
     * 
     * @return View
     */
    public function index()
    {
        try {
            $query = MedicationSchedule::with(['user', 'medicine'])
                ->orderBy('created_at', 'desc');

            // Apply search filter if search term provided
            $search = request('search');
            if ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('id', 'like', "%{$search}%");
                });
            }

            $schedules = $query->paginate(10);

            return view('admin.schedules.index', compact('schedules'));
        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Gagal memuat jadwal obat: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan form untuk membuat jadwal obat baru
     * 
     * @return View
     */
    public function create()
    {
        try {
            $users = User::orderBy('name', 'asc')->get();
            $medicines = Medicine::adminMedicines()->orderBy('name', 'asc')->get();

            if ($users->isEmpty() || $medicines->isEmpty()) {
                return back()->with('error', 'User atau obat belum ada. Silakan tambahkan data terlebih dahulu.');
            }

            return view('admin.schedules.create', compact('users', 'medicines'));
        } catch (\Exception $e) {
            return redirect()->route('admin.schedules.index')
                ->with('error', 'Gagal membuka form: ' . $e->getMessage());
        }
    }

    /**
     * Menyimpan jadwal obat baru ke database
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'user_id' => ['required', 'exists:users,id'],
                'medicine_id' => ['required', 'exists:medicines,id'],
                'start_date' => ['required', 'date', 'date_format:Y-m-d'],
                'end_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
                'times' => ['required', 'array'],
                'times.*' => ['required', 'date_format:H:i'],
                'frequency' => ['nullable', 'string', 'max:50'],
                'duration_days' => ['nullable', 'integer', 'min:1', 'max:365'],
                'source' => ['required', 'in:resep,mandiri'],
                'is_active' => ['nullable', 'boolean'],
            ]);

            // Set is_active ke boolean
            $validated['is_active'] = $request->boolean('is_active', true);
            
            // Set source_type to ADMIN for all schedules created by admin
            $validated['source_type'] = 'ADMIN';

            // Ambil times array
            $times = $validated['times'];
            unset($validated['times']);

            // Buat schedule untuk SETIAP waktu yang berbeda
            // Ini memudahkan sistem tracking per waktu
            foreach ($times as $time) {
                $scheduleData = $validated;
                $scheduleData['time'] = $time; // Simpan waktu dalam format H:i yang valid
                
                MedicationSchedule::create($scheduleData);
            }

            // Return dengan pesan sukses
            $userSchedules = count($times);
            $user = User::find($validated['user_id']);
            $medicine = Medicine::find($validated['medicine_id']);

            return redirect()->route('admin.schedules.index')
                ->with('success', "Jadwal obat berhasil dibuat untuk {$user->name} ({$userSchedules}x sehari - {$medicine->name}).");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Gagal membuat jadwal: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified medication schedule.
     */
    public function edit(MedicationSchedule $schedule)
    {
        try {
            $users = User::orderBy('name', 'asc')->get();
            $medicines = Medicine::adminMedicines()->orderBy('name', 'asc')->get();

            return view('admin.schedules.edit', compact('schedule', 'users', 'medicines'));
        } catch (\Exception $e) {
            return redirect()->route('admin.schedules.index')
                ->with('error', 'Gagal membuka form edit: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified medication schedule in storage.
     */
    public function update(Request $request, MedicationSchedule $schedule)
    {
        try {
            $validated = $request->validate([
                'user_id' => ['required', 'exists:users,id'],
                'medicine_id' => ['required', 'exists:medicines,id'],
                'start_date' => ['required', 'date', 'date_format:Y-m-d'],
                'end_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
                'time' => ['required', 'date_format:H:i'],
                'frequency' => ['nullable', 'string', 'max:50'],
                'duration_days' => ['nullable', 'integer', 'min:1', 'max:365'],
                'source' => ['required', 'in:resep,mandiri'],
                'is_active' => ['nullable', 'boolean'],
            ]);

            $validated['is_active'] = $request->boolean('is_active', true);

            $schedule->update($validated);

            return redirect()->route('admin.schedules.index')
                ->with('success', 'Jadwal obat berhasil diperbarui');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Gagal memperbarui jadwal: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified medication schedule from storage.
     */
    public function destroy(MedicationSchedule $schedule)
    {
        try {
            $user = $schedule->user;
            $medicine = $schedule->medicine;
            
            $schedule->delete();

            return redirect()->route('admin.schedules.index')
                ->with('success', "Jadwal obat untuk {$user->name} ({$medicine->name}) berhasil dihapus");
        } catch (\Exception $e) {
            return redirect()->route('admin.schedules.index')
                ->with('error', 'Gagal menghapus jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Cek apakah user dan medicine ada
     */
    private function checkRequiredData(): ?string
    {
        if (User::count() === 0) {
            return 'Belum ada data User. Silakan tambahkan user terlebih dahulu.';
        }
        if (Medicine::count() === 0) {
            return 'Belum ada data Obat. Silakan tambahkan obat terlebih dahulu.';
        }
        return null;
    }
}