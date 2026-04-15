<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MedicationSchedule;
use Carbon\Carbon;


class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Ambil data user yang sedang login
        $user = $request->user();
        $now = now();

        // Ambil jadwal obat hari ini yang aktif dengan tracking kepatuhan
        $todaySchedules = \App\Models\MedicationSchedule::with(['medicine', 'logs' => function($q) {
                // Ambil log yang di-update atau dibuat hari ini (untuk tracking hari ini)
                $q->where(function($query) {
                    $query->whereDate('updated_at', today())
                          ->orWhereDate('taken_at', today());
                });
            }])
            ->where('user_id', auth()->id())  // filter utk user yang login
            ->where('is_active', true)        // cuma jadwal yang aktif
            ->whereDate('start_date', '<=', today())  // Jadwal sudah dimulai
            ->where(function($q){
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', today());
            })
            ->orderBy('time')
            ->get();

        // Ambil obat yang sudah melewati waktu minumnya (untuk reminder di dashboard)
        $dueMedications = $todaySchedules->filter(function($schedule) use ($now) {
            [$hour, $minute] = explode(':', $schedule->time);
            $scheduledTime = Carbon::createFromTime($hour, $minute);
            
            // Hanya tampilkan jika waktu sekarang > waktu jadwal (sudah melewati)
            return $now->gt($scheduledTime);
        })
        ->filter(function($schedule) {
            // Filter hanya obat yang belum diminum
            $log = $schedule->logs->first();
            return !$log || $log->status !== 'taken';
        })
        ->values();

        // Preview jadwal obat besok (max 3 jadwal)
        $tomorrow = today()->addDay();
        $tomorrowSchedules = \App\Models\MedicationSchedule::with(['medicine'])
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $tomorrow)
            ->where(function($q) use ($tomorrow){
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $tomorrow);
            })
            ->orderBy('time')
            ->limit(3)
            ->get();

        // Hitung compliance: (jumlah diminum / total jadwal) * 100
        $totalToday = $todaySchedules->count();
        $takenToday = $todaySchedules->filter(function($schedule) {
            return $schedule->logs->first()?->status === 'taken';
        })->count();
        $complianceToday = $totalToday > 0 ? round(($takenToday / $totalToday) * 100) : 0;

        // Kirim data ke view dashboard
        return view('app.dashboard', [
            'schedules' => $todaySchedules,
            'dueMedications' => $dueMedications,
            'tomorrowSchedules' => $tomorrowSchedules,
            'complianceToday' => $complianceToday,
            'takenToday' => $takenToday,
            'totalToday' => $totalToday,
        ]);
    }

    // Tampilkan jadwal obat 30 hari ke depan, dikelompokkan per tanggal
    public function upcomingSchedules(Request $request)
    {
        $userId = auth()->id();

        // Tentukan range tanggal: hari ini sampai 30 hari ke depan
        $startDate = now()->startOfDay();
        $endDate = now()->addDays(30)->endOfDay();

        // Ambil jadwal aktif untuk user dalam range 30 hari
        $schedules = MedicationSchedule::with(['medicine', 'logs'])
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $endDate)
            ->where(function($q) use ($startDate) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $startDate->toDateString());
            })
            ->get();

        // Inisialisasi array untuk menyimpan jadwal per tanggal (30 hari)
        $schedulesByDate = [];
        for ($i = 0; $i < 30; $i++) {
            $date = now()->addDays($i)->toDateString();
            $schedulesByDate[$date] = collect();
        }

        // Iterasi setiap jadwal dan tentukan tanggal berlakunya dalam 30 hari
        foreach ($schedules as $schedule) {
            // Tentukan tanggal mulai: hari ini jika jadwal sudah mulai, atau start_date jadwal
            $startDate = Carbon::parse($schedule->start_date);
            $iterationStart = $startDate->lt(now()) ? now()->startOfDay() : $startDate->startOfDay();
            // Tentukan tanggal akhir: end_date jadwal atau 30 hari, mana yang lebih awal
            $limit30Days = now()->addDays(30)->endOfDay();
            if ($schedule->end_date) {
                $scheduleEnd = Carbon::parse($schedule->end_date);
                $iterationEnd = $scheduleEnd->gt($limit30Days) ? $limit30Days : $scheduleEnd->endOfDay();
            } else {
                $iterationEnd = $limit30Days;
            }

            // Loop setiap hari dalam range jadwal, tambahkan ke array per tanggal
            $currentDate = $iterationStart->copy()->startOfDay();
            while ($currentDate->lte($iterationEnd)) {
                $dateStr = $currentDate->toDateString();
                if (isset($schedulesByDate[$dateStr])) {
                    $schedulesByDate[$dateStr]->push($schedule);
                }
                $currentDate->addDay();
            }
        }

        // Hapus tanggal yang tidak ada jadwal
        $schedulesByDate = array_filter($schedulesByDate, function($schedules) {
            return $schedules->count() > 0;
        });

        // Urutkan berdasarkan tanggal
        ksort($schedulesByDate);

        return view('app.schedules.upcoming', [
            'schedulesByDate' => $schedulesByDate,
        ]);
    }
}
