<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Medicine;
use App\Models\MedicationSchedule;
use App\Models\MedicationLog;
use App\Models\ClinicPatient;

class AdminDashboardController extends Controller
{
    // Tampilkan dashboard admin dengan statistik dan aktivitas terbaru
    public function index()
    {
        // Ambil statistik dari database
        $stats = [
            'total_patients' => User::count(),
            'active_schedules' => MedicationSchedule::where('is_active', true)->count(),
            'today_reminders' => MedicationLog::whereDate('created_at', today())->count(),
            'total_medicines' => Medicine::where('source_type', 'ADMIN')->count(),
            'clinic_patients' => ClinicPatient::count(),
        ];

        // Get recent medication logs (reminders) for activity feed
        $recentActivities = MedicationLog::with(['schedule.user', 'schedule.medicine'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($log) {
                return [
                    'type' => 'reminder',
                    'message' => $log->schedule->user->name . ' ' . ($log->status == 'taken' ? 'mengkonfirmasi' : 'melewatkan') . ' ' . $log->schedule->medicine->name,
                    'time' => $log->created_at,
                    'status' => $log->status,
                ];
            });

        // Get recent medicines added
        $recentMedicines = Medicine::where('source_type', 'ADMIN')
            ->latest()
            ->limit(3)
            ->get();

        // Get today's medication confirmations summary
        $todayConfirmed = MedicationLog::where('status', 'taken')
            ->whereDate('created_at', today())
            ->count();

        $todayTotal = MedicationSchedule::where('is_active', true)
            ->count();

        $confirmationRate = $todayTotal > 0 
            ? round(($todayConfirmed / $todayTotal) * 100) 
            : 0;

        // Recent medication schedules for quick access on dashboard
        $recentSchedules = MedicationSchedule::with(['user', 'medicine'])
            ->latest()
            ->limit(5)
            ->get();
        return view('admin.dashboard', compact(
            'stats',
            'recentActivities',
            'recentMedicines',
            'todayConfirmed',
            'todayTotal',
            'confirmationRate',
            'recentSchedules'
        ));
    }
}