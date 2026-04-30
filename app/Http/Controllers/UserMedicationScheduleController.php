<?php

// Kontrol untuk mengelola jadwal obat user
namespace App\Http\Controllers;

use App\Models\MedicationSchedule;
use App\Models\Medicine;
use App\Notifications\MedicationReminderNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class UserMedicationScheduleController extends Controller
{
    // Tampilkan daftar jadwal obat user
    public function index()
    {
        try {
            $user = Auth::user();
            $schedules = MedicationSchedule::where('user_id', $user->id)
                ->with('medicine')
                ->orderBy('created_at', 'desc')
                ->get();

            return view('app.schedules.index', compact('schedules'));
        } catch (\Exception $e) {
            return redirect()->route('app.dashboard')
                ->with('error', 'Gagal memuat jadwal: ' . $e->getMessage());
        }
    }

    // Tampilkan form buat jadwal obat baru
    public function create()
    {
        try {
            $user = Auth::user();
            
            // Ambil obat milik user untuk dipilih
            $medicines = Medicine::where('user_id', $user->id)
                ->orderBy('name', 'asc')
                ->get();

            return view('app.schedules.create', compact('medicines'));
        } catch (\Exception $e) {
            return redirect()->route('app.schedules.index')
                ->with('error', 'Gagal membuka form: ' . $e->getMessage());
        }
    }

    // Simpan jadwal obat baru
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            // Validasi input jadwal obat
            $validated = $request->validate([
                'medicine_id' => ['required', 'exists:medicines,id'],
                'start_date' => ['required', 'date', 'date_format:Y-m-d'],
                'end_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
                'times' => ['required', 'array', 'min:1'],
                'times.*' => ['required', 'date_format:H:i'],
                'frequency' => ['nullable', 'string', 'max:50'],
                'duration_days' => ['nullable', 'integer', 'min:1', 'max:365'],
                'source' => ['nullable', 'string', 'in:mandiri'],
            ]);

            // Atur user_id dan tipe sumber
            $validated['user_id'] = $user->id;
            $validated['source_type'] = 'PATIENT';
            $validated['is_active'] = true;

            // Ambil array waktu minum obat
            $times = $validated['times'];
            unset($validated['times']);
            unset($validated['source']);

            // Buat schedule terpisah untuk setiap waktu minum
            foreach ($times as $time) {
                $scheduleData = $validated;
                $scheduleData['time'] = $time;
                
                MedicationSchedule::create($scheduleData);
            }

            // Ambil info obat untuk notifikasi
            $medicine = Medicine::find($validated['medicine_id']);

            // Kirim konfirmasi via Push Notification (OneSignal)
            try {
                $firstTime = $times[0] ?? '00:00';
                $medicineDose = $medicine->dose . ' ' . ($medicine->unit ?? '');
                
                Notification::send($user, new MedicationReminderNotification(
                    $medicine->name,
                    $medicineDose,
                    $firstTime,
                    0, // ID 0 karena ini hanya konfirmasi
                    'confirmation' // Tipe baru untuk konfirmasi
                ));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal mengirim konfirmasi OneSignal: ' . $e->getMessage());
            }

            return redirect()->route('app.schedules.index')
                ->with('success', "Jadwal '{$medicine->name}' berhasil dibuat untuk " . count($times) . " waktu minum.");

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

    // Tampilkan form edit jadwal obat
    public function edit(MedicationSchedule $schedule)
    {
        try {
            $user = Auth::user();
            
            // Validasi user hanya bisa edit jadwal miliknya
            if ($schedule->user_id !== $user->id) {
                return redirect()->route('app.schedules.index')
                    ->with('error', 'Anda tidak berhak mengakses jadwal ini.');
            }

            // Ambil obat milik user dan obat dari admin
            $medicines = Medicine::where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('source_type', 'ADMIN');
                })
                ->orderBy('name', 'asc')
                ->get();

            return view('app.schedules.edit', compact('schedule', 'medicines'));
        } catch (\Exception $e) {
            return redirect()->route('app.schedules.index')
                ->with('error', 'Gagal membuka form: ' . $e->getMessage());
        }
    }

    // Perbarui jadwal obat
    public function update(Request $request, MedicationSchedule $schedule)
    {
        try {
            $user = Auth::user();

            // Validasi user hanya bisa update jadwal miliknya
            if ($schedule->user_id !== $user->id) {
                return redirect()->route('app.schedules.index')
                    ->with('error', 'Anda tidak berhak mengupdate jadwal ini.');
            }

            // Validasi input form update
            $validated = $request->validate([
                'medicine_id' => ['required', 'exists:medicines,id'],
                'start_date' => ['required', 'date', 'date_format:Y-m-d'],
                'end_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
                'time' => ['required', 'date_format:H:i'],
                'frequency' => ['nullable', 'string', 'max:50'],
                'duration_days' => ['nullable', 'integer', 'min:1', 'max:365'],
                'is_active' => ['nullable', 'boolean'],
            ]);

            // Atur status aktif
            $validated['is_active'] = $request->boolean('is_active', $schedule->is_active);

            // Update data jadwal
            $schedule->update($validated);
            $medicine = Medicine::find($validated['medicine_id']);

            return redirect()->route('app.schedules.index')
                ->with('success', "Jadwal obat '{$medicine->name}' berhasil diperbarui.");

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

    // Hapus jadwal obat
    public function destroy(MedicationSchedule $schedule)
    {
        try {
            $user = Auth::user();

            // Validasi user hanya bisa hapus jadwal miliknya
            if ($schedule->user_id !== $user->id) {
                return redirect()->route('app.schedules.index')
                    ->with('error', 'Anda tidak berhak menghapus jadwal ini.');
            }

            // Simpan nama obat sebelum jadwal dihapus
            $medicineName = $schedule->medicine->name;
            $schedule->delete();

            return redirect()->route('app.schedules.index')
                ->with('success', "Jadwal obat '{$medicineName}' berhasil dihapus.");

        } catch (\Exception $e) {
            return redirect()->route('app.schedules.index')
                ->with('error', 'Gagal menghapus jadwal: ' . $e->getMessage());
        }
    }
}
