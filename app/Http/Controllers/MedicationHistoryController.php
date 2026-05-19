<?php

// Kontrol untuk menampilkan riwayat dan statistik pengambilan obat
namespace App\Http\Controllers;

use App\Models\MedicationLog;
use App\Models\MedicationSchedule;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class MedicationHistoryController extends Controller
{
    // Tampilkan riwayat obat dengan filter (tanggal dan obat)
    public function index(Request $request)
    {
        // Validasi input filter
        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'medicine_id' => 'nullable|exists:medicines,id',
        ]);

        $userId = auth()->id();
        // Tentukan range tanggal: default 1 bulan ke belakang
        $fromDate = !empty($validated['from_date']) ? Carbon::parse($validated['from_date']) : now()->subMonth();
        $toDate = !empty($validated['to_date']) ? Carbon::parse($validated['to_date']) : now();
        $medicineId = $validated['medicine_id'] ?? null;

        // Ambil log obat dengan relasi dan filter
        $logs = MedicationLog::with(['medicationSchedule.medicine', 'user'])
            ->where('user_id', $userId)
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->when($medicineId, function ($q) use ($medicineId) {
                return $q->whereHas('medicationSchedule', function ($q) use ($medicineId) {
                    return $q->where('medicine_id', $medicineId);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Calculate statistics
        $stats = $this->calculateStats($userId, $fromDate, $toDate);

        // Get daily compliance data for chart
        $dailyCompliance = $this->getDailyCompliance($userId, $fromDate, $toDate);

        // Get available medicines for filter
        $medicines = MedicationSchedule::where('user_id', $userId)
            ->where('is_active', true)
            ->with('medicine')
            ->distinct()
            ->get()
            ->pluck('medicine')
            ->unique('id');

        return view('app.history.index', [
            'logs' => $logs,
            'stats' => $stats,
            'dailyCompliance' => $dailyCompliance,
            'medicines' => $medicines,
            'fromDate' => $fromDate->toDateString(),
            'toDate' => $toDate->toDateString(),
            'selectedMedicineId' => $medicineId,
        ]);
    }

    /**
     * Get API data for history (JSON response)
     */
    public function apiHistory(Request $request)
    {
        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'medicine_id' => 'nullable|exists:medicines,id',
        ]);

        $userId = auth()->id();
        $fromDate = !empty($validated['from_date']) ? Carbon::parse($validated['from_date']) : now()->subMonth();
        $toDate = !empty($validated['to_date']) ? Carbon::parse($validated['to_date']) : now();
        $medicineId = $validated['medicine_id'] ?? null;

        $logs = MedicationLog::with(['medicationSchedule.medicine'])
            ->where('user_id', $userId)
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->when($medicineId, function ($q) use ($medicineId) {
                return $q->whereHas('medicationSchedule', function ($q) use ($medicineId) {
                    return $q->where('medicine_id', $medicineId);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'medicine_name' => $log->medicationSchedule?->medicine->name ?? 'Unknown',
                    'status' => $log->status,
                    'taken_at' => $log->taken_at?->format('Y-m-d H:i') ?? $log->created_at->format('Y-m-d H:i'),
                    'note' => $log->note,
                    'offline_synced' => $log->offline_synced,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $logs->count(),
            'logs' => $logs,
            'fromDate' => $fromDate->toDateString(),
            'toDate' => $toDate->toDateString(),
        ]);
    }

    /**
     * Calculate compliance statistics for period
     */
    private function calculateStats($userId, $fromDate, $toDate)
    {
        $period = CarbonPeriod::create($fromDate, $toDate);

        // PERBAIKAN 1: Filter Query Database
        $schedules = MedicationSchedule::where('user_id', $userId)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $toDate)
            ->where(function ($q) use ($fromDate) { // Ubah $toDate menjadi $fromDate di sini
                $q->whereNull('end_date')
                ->orWhereDate('end_date', '>=', $fromDate);
            })
            ->with(['logs' => function($q) use ($fromDate, $toDate) {
                $q->whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate);
            }])
            ->get();

        $totalExpected = 0;
        $totalTaken = 0;
        $dayBreakdown = [];

        foreach ($period as $date) {
            $dayCount = 0;
            $dayTaken = 0;
            
            // Ambil format YYYY-MM-DD agar perbandingan tanggal kebal terhadap masalah zona waktu/jam
            $dateString = $date->toDateString();

            foreach ($schedules as $schedule) {
                // PERBAIKAN 2: Ubah Carbon object menjadi toDateString() sebelum dibandingan
                // Pastikan 'start_date' dan 'end_date' sudah dimasukkan ke array $casts di model MedicationSchedule!
                $startDateString = Carbon::parse($schedule->start_date)->toDateString();
                $endDateString = $schedule->end_date ? Carbon::parse($schedule->end_date)->toDateString() : null;

                // Cek apakah jadwal aktif di tanggal ini
                if ($startDateString <= $dateString && ($endDateString === null || $endDateString >= $dateString)) {
                    $dayCount++;

                    // Cek apakah obat diminum di tanggal ini
                    $isTaken = $schedule->logs
                        ->where('status', 'taken')
                        ->contains(function($log) use ($dateString) {
                            return Carbon::parse($log->created_at)->toDateString() === $dateString;
                        });

                    if ($isTaken) {
                        $dayTaken++;
                    }
                }
            }

            // Catat hari ini ke dalam breakdown HANYA JIKA ada jadwal
            if ($dayCount > 0) {
                $totalExpected += $dayCount;
                $totalTaken += $dayTaken;
                
                $dayBreakdown[$dateString] = [
                    'date' => $dateString,
                    'expected' => $dayCount,
                    'taken' => $dayTaken,
                    'compliance' => round(($dayTaken / $dayCount) * 100),
                ];
            }
        }

        $overallCompliance = $totalExpected > 0 
            ? round(($totalTaken / $totalExpected) * 100) 
            : 0;

        return [
            'period_start' => $fromDate->toDateString(),
            'period_end' => $toDate->toDateString(),
            'total_days' => count($dayBreakdown),
            'total_expected' => $totalExpected,
            'total_taken' => $totalTaken,
            'overall_compliance' => $overallCompliance,
            'perfect_days' => collect($dayBreakdown)->filter(fn($d) => $d['compliance'] == 100)->count(),
            'zero_days' => collect($dayBreakdown)->filter(fn($d) => $d['taken'] == 0)->count(),
            'day_breakdown' => $dayBreakdown,
        ];
    }
    /**
     * Get daily compliance for chart
     */
    private function getDailyCompliance($userId, $fromDate, $toDate)
    {
        $logs = MedicationLog::where('user_id', $userId)
            ->where('status', 'taken')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->get()
            ->groupBy(function ($log) {
                return $log->created_at->toDateString();
            })
            ->map(fn($group) => $group->count());

        // Fill in missing dates with 0
        $period = CarbonPeriod::create($fromDate, $toDate);
        $dailyData = [];

        foreach ($period as $date) {
            $key = $date->toDateString();
            $dailyData[] = [
                'date' => $key,
                'count' => $logs->get($key, 0),
            ];
        }

        return $dailyData;
    }

    /**
     * Get compliance overview by week
     */
    public function weeklyStats(Request $request)
    {
        $userId = auth()->id();
        $weeks = 12; // Last 12 weeks

        $stats = [];
        for ($i = $weeks - 1; $i >= 0; $i--) {
            $start = now()->subWeeks($i)->startOfWeek();
            $end = $start->copy()->endOfWeek();

            $stat = $this->calculateStats($userId, $start, $end);
            $stats[] = [
                'week' => $start->format('M d'),
                'compliance' => $stat['overall_compliance'],
                'taken' => $stat['total_taken'],
                'expected' => $stat['total_expected'],
            ];
        }

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Export history as CSV
     */
    public function exportCsv(Request $request)
    {
        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
        ]);

        $userId = auth()->id();
        $fromDate = !empty($validated['from_date']) ? Carbon::parse($validated['from_date']) : now()->subMonth();
        $toDate = !empty($validated['to_date']) ? Carbon::parse($validated['to_date']) : now();

        $logs = MedicationLog::with(['medicationSchedule.medicine'])
            ->where('user_id', $userId)
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = "medication-history-{$fromDate->toDateString()}-to-{$toDate->toDateString()}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, ['Tanggal', 'Obat', 'Status', 'Waktu Minum', 'Catatan', 'Sinkronisasi Offline']);

            // Data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d'),
                    $log->medicationSchedule?->medicine->name ?? 'Unknown',
                    ucfirst($log->status),
                    $log->taken_at?->format('H:i') ?? '-',
                    $log->note ?? '-',
                    $log->offline_synced ? 'Ya' : 'Tidak',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get compliance summary
     */
    public function summary(Request $request)
    {
        $userId = auth()->id();

        // Today
        $todayStats = $this->calculateStats($userId, now(), now());

        // This week
        $weekStats = $this->calculateStats($userId, now()->startOfWeek(), now()->endOfWeek());

        // This month
        $monthStats = $this->calculateStats($userId, now()->startOfMonth(), now()->endOfMonth());

        return response()->json([
            'success' => true,
            'today' => [
                'expected' => $todayStats['total_expected'],
                'taken' => $todayStats['total_taken'],
                'compliance' => $todayStats['overall_compliance'],
            ],
            'week' => [
                'expected' => $weekStats['total_expected'],
                'taken' => $weekStats['total_taken'],
                'compliance' => $weekStats['overall_compliance'],
                'perfect_days' => $weekStats['perfect_days'],
            ],
            'month' => [
                'expected' => $monthStats['total_expected'],
                'taken' => $monthStats['total_taken'],
                'compliance' => $monthStats['overall_compliance'],
            ],
        ]);
    }
}
