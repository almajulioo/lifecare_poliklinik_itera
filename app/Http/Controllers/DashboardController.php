<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MedicationSchedule;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // TODAY'S SCHEDULES with compliance tracking
        $todaySchedules = \App\Models\MedicationSchedule::with(['medicine', 'logs' => function($q) {
                $q->whereDate('created_at', today());
            }])
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->whereDate('start_date', '<=', today())
            ->where(function($q){
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', today());
            })
            ->orderBy('time')
            ->get();

        // TOMORROW'S SCHEDULES (preview)
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

        // COMPLIANCE CALCULATION FOR TODAY
        $totalToday = $todaySchedules->count();
        $takenToday = $todaySchedules->filter(function($schedule) {
            return $schedule->logs->first()?->status === 'taken';
        })->count();

        $complianceToday = $totalToday > 0 
            ? round(($takenToday / $totalToday) * 100) 
            : 0;

        // No caching - always fresh data for better UX
        return view('app.dashboard', [
            'schedules' => $todaySchedules,
            'tomorrowSchedules' => $tomorrowSchedules,
            'complianceToday' => $complianceToday,
            'takenToday' => $takenToday,
            'totalToday' => $totalToday,
        ]);
    }

    /**
     * Get upcoming medication schedules for 30 days
     */
    public function upcomingSchedules(Request $request)
    {
        $userId = auth()->id();

        // Get schedules for next 30 days
        $startDate = now()->startOfDay();
        $endDate = now()->addDays(30)->endOfDay();

        $schedules = MedicationSchedule::with(['medicine', 'logs'])
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $endDate)
            ->where(function($q) use ($startDate) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $startDate->toDateString());
            })
            ->get();

        // Group schedules by date
        $schedulesByDate = [];
        
        for ($i = 0; $i < 30; $i++) {
            $date = now()->addDays($i)->toDateString();
            $schedulesByDate[$date] = collect();
        }

        foreach ($schedules as $schedule) {
            // Start date: use max of schedule start_date or today
            // This handles schedules that started in the past
            $startDate = Carbon::parse($schedule->start_date);
            $iterationStart = $startDate->lt(now()) ? now()->startOfDay() : $startDate->startOfDay();
            
            // End date: use min of schedule end_date or 30 days from now
            $limit30Days = now()->addDays(30)->endOfDay();
            if ($schedule->end_date) {
                $scheduleEnd = Carbon::parse($schedule->end_date);
                $iterationEnd = $scheduleEnd->gt($limit30Days) ? $limit30Days : $scheduleEnd->endOfDay();
            } else {
                $iterationEnd = $limit30Days;
            }

            // Iterate through all days in the range
            $currentDate = $iterationStart->copy()->startOfDay();
            while ($currentDate->lte($iterationEnd)) {
                $dateStr = $currentDate->toDateString();
                
                if (isset($schedulesByDate[$dateStr])) {
                    $schedulesByDate[$dateStr]->push($schedule);
                }

                $currentDate->addDay();
            }
        }

        // Remove empty dates
        $schedulesByDate = array_filter($schedulesByDate, function($schedules) {
            return $schedules->count() > 0;
        });

        // Sort by date
        ksort($schedulesByDate);

        return view('app.schedules.upcoming', [
            'schedulesByDate' => $schedulesByDate,
        ]);
    }
}
