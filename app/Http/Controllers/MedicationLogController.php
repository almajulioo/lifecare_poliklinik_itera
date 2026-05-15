<?php

// Kontrol untuk mencatat log pengambilan obat user
namespace App\Http\Controllers;

use App\Models\MedicationLog;
use App\Models\MedicationSchedule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MedicationLogController extends Controller
{

    public function later(Request $request, MedicationSchedule $schedule)
    {
        $schedule->update([
            'time' => Carbon::now()->addMinutes(5)->format('H:i'),
        ]);

        return back()->with('success', 'Menunda minum obat selama lima menit.');

    }


    // Catat obat yang sudah diminum (online & offline)
    public function take(Request $request, MedicationSchedule $schedule)
    {
        $this->authorize('confirmIntake', $schedule);
    
        // Cek apakah request offline atau online
        $offline = $request->input('offline', false);

        if ($offline) {
            // Response untuk offline - client akan queue ini
            return response()->json([
                'success' => true,
                'offline' => true,
                'message' => 'Dikonfirmasi (offline - akan disinkronkan)',
                'offlineId' => $request->input('offlineId'),
                'timestamp' => now()->toIso8601String(),
            ]);
        }

        // Online flow: simpan log ke database
        MedicationLog::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'medication_schedule_id' => $schedule->id,
            ],
            [
                'status' => 'taken',
                'taken_at' => now(),
                'note' => $request->input('note'),
            ]
        );

        return back()->with('success', 'Mantap! Kamu sudah konfirmasi minum obat.');
    }

    // Sinkronisasi log obat offline
    public function syncOfflineLogs(Request $request)
    {
        // Validasi data log yang dikirim
        $validated = $request->validate([
            'logs' => 'required|array',
            'logs.*.offlineId' => 'required|string',
            'logs.*.scheduleId' => 'required|integer|exists:medication_schedules,id',
            'logs.*.status' => 'required|in:taken,pending,missed',
            'logs.*.taken_at' => 'required|date_format:Y-m-d\TH:i:sZ',
            'logs.*.note' => 'nullable|string|max:500',
        ]);

        $userId = auth()->id();
        $syncedIds = [];
        $conflictIds = [];
        $errors = [];

        foreach ($validated['logs'] ?? [] as $logData) {
            try {
                // Verifikasi jadwal milik user
                $schedule = MedicationSchedule::findOrFail($logData['scheduleId']);
                
                $this->authorize('confirmIntake', $schedule);

                // Deteksi konflik: obat sudah diminum dalam 2 jam terakhir
                $existingLog = MedicationLog::where('user_id', $userId)
                    ->where('medication_schedule_id', $schedule->id)
                    ->where('status', 'taken')
                    ->where('created_at', '>=', now()->subHours(2))
                    ->first();

                if ($existingLog) {
                    // Ada konflik - obat sudah dikonfirmasi
                    $conflictIds[] = $logData['offlineId'];
                    
                    \Log::info('Medication conflict detected', [
                        'offlineId' => $logData['offlineId'],
                        'scheduleId' => $schedule->id,
                        'existingLogId' => $existingLog->id,
                        'existingTakenAt' => $existingLog->taken_at,
                    ]);
                    continue;
                }

                // Buat atau update log obat yang diminum
                $log = MedicationLog::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'medication_schedule_id' => $schedule->id,
                    ],
                    [
                        'status' => 'taken',
                        'taken_at' => $logData['taken_at'],
                        'note' => $logData['note'] ?? null,
                        'offline_synced' => true,
                        'offline_synced_at' => now(),
                    ]
                );

                $syncedIds[] = $logData['offlineId'];
                
                \Log::info('Offline log synced', [
                    'offlineId' => $logData['offlineId'],
                    'logId' => $log->id,
                    'scheduleId' => $schedule->id,
                ]);
                
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $errors[$logData['offlineId']] = 'Jadwal obat tidak ditemukan';
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $errors[$logData['offlineId']] = 'Akses ditolak: Jadwal tidak milik Anda';
            } catch (\Exception $e) {
                \Log::error('Offline sync error', [
                    'offlineId' => $logData['offlineId'],
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                $errors[$logData['offlineId']] = 'Kesalahan server: ' . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'synced' => $syncedIds,
            'conflicts' => $conflictIds,
            'syncedCount' => count($syncedIds),
            'conflictCount' => count($conflictIds),
            'errorCount' => count($errors),
            'errors' => $errors,
            'timestamp' => now()->toIso8601String(),
            'message' => vsprintf(
                'Sync complete: %d berhasil, %d konflik, %d error',
                [count($syncedIds), count($conflictIds), count($errors)]
            ),
        ]);
    }

    // Ambil status sinkronisasi: berapa banyak log yang pending
    public function syncStatus(Request $request)
    {
        $userId = auth()->id();
        
        // Hitung log pending
        $pendingCount = MedicationLog::where('user_id', $userId)
            ->where('status', 'pending')
            ->count();

        // Hitung total log hari ini
        $todayCount = MedicationLog::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->count();

        // Hitung log yang diminum hari ini
        $takenCount = MedicationLog::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->where('status', 'taken')
            ->count();

        return response()->json([
            'pending' => $pendingCount,
            'today_total' => $todayCount,
            'today_taken' => $takenCount,
            'compliance_today' => $todayCount > 0 ? round(($takenCount / $todayCount) * 100) : 0,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    // Ambil detail jadwal untuk modal notifikasi
    public function getScheduleDetails(MedicationSchedule $schedule)
    {
        $this->authorize('view', $schedule);

        return response()->json([
            'id' => $schedule->id,
            'medicine_name' => $schedule->medicine->name,
            'medicine_dose' => $schedule->medicine->dose,
            'medicine_unit' => $schedule->medicine->unit,
            'time' => $schedule->time,
            'date' => $schedule->start_date,
        ]);
    }

    // API: Catat obat yang sudah diminum dari modal notifikasi
    public function medicationTaken(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'medication_schedule_id' => 'required|exists:medication_schedules,id',
            'taken_at' => 'nullable|date_format:Y-m-d\TH:i:s.000\Z',
            'source' => 'nullable|string',
        ]);

        $schedule = MedicationSchedule::find($validated['medication_schedule_id']);
        $this->authorize('confirmIntake', $schedule);

        // Buat atau update log
        $log = MedicationLog::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'medication_schedule_id' => $validated['medication_schedule_id'],
            ],
            [
                'status' => 'taken',
                'taken_at' => now(),
                'note' => 'Recorded from ' . ($validated['source'] ?? 'app'),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Medication recorded successfully',
            'log_id' => $log->id,
        ]);
    }

    // API: Snooze/tunda notifikasi obat
    public function medicationSnooze(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'medication_schedule_id' => 'required|exists:medication_schedules,id',
            'snooze_minutes' => 'nullable|integer|min:5|max:120',
        ]);

        $schedule = MedicationSchedule::find($validated['medication_schedule_id']);
        $this->authorize('view', $schedule);

        $snoozeMinutes = $validated['snooze_minutes'] ?? 15;

        // Simpan snooze ke cache, skip notifikasi untuk durasi tersebut
        $cacheKey = "medication_snoozed_{$schedule->id}";
        \Cache::put($cacheKey, true, now()->addMinutes($snoozeMinutes));

        return response()->json([
            'success' => true,
            'message' => "Reminder snoozed for {$snoozeMinutes} minutes",
            'snoozed_until' => now()->addMinutes($snoozeMinutes)->toIso8601String(),
        ]);
    }
}