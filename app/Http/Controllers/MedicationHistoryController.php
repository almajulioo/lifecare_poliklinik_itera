<?php

namespace App\Http\Controllers;

use App\Models\MedicationLog;
use App\Models\MedicationSchedule;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class MedicationHistoryController extends Controller
{
    /**
     * Get medication history with filters
     */
    public function index(Request $request)
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

        // Get medication logs with medicine details
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

        // Get all schedules for user in period
        $schedules = MedicationSchedule::where('user_id', $userId)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $toDate)
            ->where(function ($q) use ($toDate) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $toDate);
            })
            ->with(['logs' => function($q) use ($fromDate, $toDate) {
                $q->whereDate('created_at', '>=', $fromDate)
                  ->whereDate('created_at', '<=', $toDate);
            }])
            ->get();

        // Count total expected vs taken per day
        $totalExpected = 0;
        $totalTaken = 0;
        $dayBreakdown = [];

        foreach ($period as $date) {
            $dayCount = 0;
            $dayTaken = 0;

            foreach ($schedules as $schedule) {
                // Check if schedule is active on this date
                if ($schedule->start_date->lte($date) && 
                    ($schedule->end_date === null || $schedule->end_date->gte($date))) {
                    $dayCount++;

                    // Check if taken - using Carbon comparison for collection
                    $log = $schedule->logs
                        ->where('status', 'taken')
                        ->first(function($log) use ($date) {
                            return $log->created_at->toDateString() === $date->toDateString();
                        });

                    if ($log) {
                        $dayTaken++;
                    }
                }
            }

            if ($dayCount > 0) {
                $totalExpected += $dayCount;
                $totalTaken += $dayTaken;
                
                $dayBreakdown[$date->toDateString()] = [
                    'date' => $date->toDateString(),
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
            'perfect_days' => collect($dayBreakdown)->filter(fn($d) => $d['compliance'] === 100)->count(),
            'zero_days' => collect($dayBreakdown)->filter(fn($d) => $d['taken'] === 0)->count(),
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
