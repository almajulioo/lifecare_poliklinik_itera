<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicationLog;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    /**
     * Display a listing of medication logs.
     */
    public function index(Request $request)
    {
        $query = MedicationLog::with(['schedule.medicine', 'schedule.user'])->latest();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('schedule.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%");
            })
            ->orWhereHas('schedule.medicine', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(15);

        // Calculate statistics
        $stats = [
            'today' => MedicationLog::whereDate('created_at', today())->count(),
            'confirmed' => MedicationLog::where('status', 'taken')->count(),
            'pending' => MedicationLog::where('status', 'pending')->count(),
            'missed' => MedicationLog::where('status', 'missed')->count(),
            'delayed' => MedicationLog::where('status', 'delayed')->count(),
        ];

        return view('admin.riwayat.index', [
            'logs' => $logs,
            'stats' => $stats,
            'search' => $request->search,
            'status' => $request->status ?? 'all',
        ]);
    }
}
