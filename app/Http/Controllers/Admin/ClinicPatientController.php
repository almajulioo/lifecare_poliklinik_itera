<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicPatient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ClinicPatientController extends Controller
{
    /**
     * Display a listing of all clinic patients.
     */
    public function index(Request $request)
    {
        $query = ClinicPatient::with('user');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('identity_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
        }

        // Filter by month
        if ($request->filled('month')) {
            $month = $request->month; // Format: YYYY-MM
            $query->whereYear('created_at', substr($month, 0, 4))
                  ->whereMonth('created_at', substr($month, 5, 2));
        }

        // Filter by category
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by app user status
        if ($request->filled('app_user') && $request->app_user !== 'all') {
            if ($request->app_user === 'using') {
                $query->whereNotNull('user_id');
            } else {
                $query->whereNull('user_id');
            }
        }

        // Sort
        $sort = $request->sort ?? 'latest';
        if ($sort === 'latest') {
            $query->latest();
        } elseif ($sort === 'oldest') {
            $query->oldest();
        }

        $patients = $query->paginate(15);

        // Statistics
        $stats = [
            'total' => ClinicPatient::count(),
            'app_users' => ClinicPatient::whereNotNull('user_id')->count(),
            'non_app_users' => ClinicPatient::whereNull('user_id')->count(),
            'active_today' => ClinicPatient::where('status', 'aktif')->count(),
        ];

        return view('admin.clinic-patients.index', [
            'patients' => $patients,
            'stats' => $stats,
            'search' => $request->search,
            'month' => $request->month,
            'category' => $request->category ?? 'all',
            'status' => $request->status ?? 'all',
            'app_user' => $request->app_user ?? 'all',
            'sort' => $sort,
        ]);
    }

    /**
     * Show the form for creating a new clinic patient.
     */
    public function create()
    {
        // Get available users that don't have a clinic patient linked
        $availableUsers = User::whereDoesntHave('clinicPatient')->get();

        return view('admin.clinic-patients.create', [
            'availableUsers' => $availableUsers,
        ]);
    }

    /**
     * Store a newly created clinic patient in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id', Rule::unique('clinic_patients', 'user_id')->whereNotNull('user_id')],
            'name' => 'required|string|max:255',
            'identity_number' => ['nullable', 'string', 'max:255', Rule::unique('clinic_patients', 'identity_number')->whereNotNull('identity_number')],
            'category' => 'required|in:mahasiswa,pegawai,umum',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:aktif,tidak_aktif',
            'medical_conditions' => 'nullable|array',
            'medical_conditions.*' => 'string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Separate clinic patient data from user data
        $clinicPatientData = $validated;
        $medicalConditions = $validated['medical_conditions'] ?? null;
        $notes = $validated['notes'] ?? null;
        
        unset($clinicPatientData['medical_conditions']);
        unset($clinicPatientData['notes']);

        // Create clinic patient
        $patient = ClinicPatient::create($clinicPatientData);

        // If user is selected, update their medical conditions
        if ($validated['user_id']) {
            $user = User::find($validated['user_id']);
            $userData = [];
            
            if ($medicalConditions) {
                // Filter out empty values
                $userData['medical_conditions'] = array_filter($medicalConditions, function($val) {
                    return !empty(trim($val));
                });
            }
            
            if ($notes) {
                $userData['notes'] = $notes;
            }
            
            if (!empty($userData)) {
                $user->update($userData);
            }
        }

        return redirect()->route('admin.clinic-patients.index')
                        ->with('success', 'Pasien berhasil ditambahkan');
    }

    /**
     * Display the specified clinic patient.
     */
    public function show(ClinicPatient $clinicPatient)
    {
        $clinicPatient->load('user');
        return view('admin.clinic-patients.show', [
            'patient' => $clinicPatient,
        ]);
    }

    /**
     * Show the form for editing the specified clinic patient.
     */
    public function edit(ClinicPatient $clinicPatient)
    {
        $clinicPatient->load('user');
        
        // Get available users (including current user if linked)
        $availableUsers = User::where(function ($query) use ($clinicPatient) {
            $query->whereDoesntHave('clinicPatient')
                  ->orWhere('id', $clinicPatient->user_id);
        })->get();

        return view('admin.clinic-patients.edit', [
            'patient' => $clinicPatient,
            'availableUsers' => $availableUsers,
        ]);
    }

    /**
     * Update the specified clinic patient in storage.
     */
    public function update(Request $request, ClinicPatient $clinicPatient)
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id', Rule::unique('clinic_patients', 'user_id')->ignore($clinicPatient->id)->whereNotNull('user_id')],
            'name' => 'required|string|max:255',
            'identity_number' => ['nullable', 'string', 'max:255', Rule::unique('clinic_patients', 'identity_number')->ignore($clinicPatient->id)->whereNotNull('identity_number')],
            'category' => 'required|in:mahasiswa,pegawai,umum',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:aktif,tidak_aktif',
            'medical_conditions' => 'nullable|array',
            'medical_conditions.*' => 'string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Separate clinic patient data from user data
        $clinicPatientData = $validated;
        $medicalConditions = $validated['medical_conditions'] ?? null;
        $notes = $validated['notes'] ?? null;
        
        unset($clinicPatientData['medical_conditions']);
        unset($clinicPatientData['notes']);

        // Update clinic patient
        $clinicPatient->update($clinicPatientData);

        // If user is selected, update their medical conditions
        if ($validated['user_id']) {
            $user = User::find($validated['user_id']);
            $userData = [];
            
            if ($medicalConditions) {
                // Filter out empty values
                $userData['medical_conditions'] = array_filter($medicalConditions, function($val) {
                    return !empty(trim($val));
                });
            }
            
            if ($notes) {
                $userData['notes'] = $notes;
            }
            
            if (!empty($userData)) {
                $user->update($userData);
            }
        }

        return redirect()->route('admin.clinic-patients.index')
                        ->with('success', 'Pasien berhasil diperbarui');
    }

    /**
     * Generate clinic patient report PDF for preview in browser (inline)
     */
    public function reportPdf(Request $request)
    {
        try {
            // Get month from request or use current month
            $monthParam = $request->input('month');
            
            if ($monthParam) {
                list($year, $month) = explode('-', $monthParam);
            } else {
                $year = now()->year;
                $month = now()->month;
                $monthParam = sprintf('%04d-%02d', $year, $month);
            }

            // Get all clinic patients for this month
            $patients = ClinicPatient::whereYear('created_at', $year)
                                    ->whereMonth('created_at', $month)
                                    ->get();

            // Calculate statistics
            $mahasiswaCount = $patients->where('category', 'mahasiswa')->count();
            $pegawaiCount = $patients->where('category', 'pegawai')->count();
            $totalVisits = $mahasiswaCount + $pegawaiCount;

            // Calculate percentages
            $studentPercentage = $totalVisits > 0 ? round(($mahasiswaCount / $totalVisits) * 100) . '%' : '0%';
            $staffPercentage = $totalVisits > 0 ? round(($pegawaiCount / $totalVisits) * 100) . '%' : '0%';

            // Month name in Indonesian
            $monthNames = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember',
            ];

            $monthName = $monthNames[(int)$month];

            // Prepare data for view
            $data = [
                'year' => $year,
                'month' => $month,
                'monthName' => $monthName,
                'monthParam' => $monthParam,
                'displayMonth' => Carbon::createFromFormat('Y-m-d', "$year-$month-01")->locale('id')->translatedFormat('F Y'),
                'totalVisits' => $totalVisits,
                'studentCount' => $mahasiswaCount,
                'staffCount' => $pegawaiCount,
                'studentPercentage' => $studentPercentage,
                'staffPercentage' => $staffPercentage,
                'generatedAt' => now()->format('d F Y H:i'),
            ];

            // Return HTML view untuk preview (bukan PDF)
            return view('admin.clinic-patients.report-pdf', $data);
            
        } catch (\Exception $e) {
            // Log error dan return error response
            \Log::error('PDF Report Generation Error: ' . $e->getMessage(), [
                'exception' => $e,
                'month' => $monthParam ?? 'not set',
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Download clinic patient report PDF with forced download (attachment)
     */
    public function downloadPdf(Request $request)
    {
        try {
            // Get month from request or use current month
            $monthParam = $request->input('month');
            
            if ($monthParam) {
                list($year, $month) = explode('-', $monthParam);
            } else {
                $year = now()->year;
                $month = now()->month;
                $monthParam = sprintf('%04d-%02d', $year, $month);
            }

            // Generate array of all dates in the month
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            
            // Get all clinic patients for this month
            $patients = ClinicPatient::whereYear('created_at', $year)
                                    ->whereMonth('created_at', $month)
                                    ->get();

            // Build report data grouped by date
            $reportData = [];
            $grandTotal = [
                'mahasiswa' => 0,
                'pegawai' => 0,
                'total' => 0,
            ];

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $dateCarbon = Carbon::createFromFormat('Y-m-d', $date);

                // Count patients by category for this date
                $mahasiswaCount = $patients->filter(function ($patient) use ($date) {
                    return $patient->category === 'mahasiswa' && 
                           $patient->created_at->format('Y-m-d') === $date;
                })->count();

                $pegawaiCount = $patients->filter(function ($patient) use ($date) {
                    return $patient->category === 'pegawai' && 
                           $patient->created_at->format('Y-m-d') === $date;
                })->count();

                $dayTotal = $mahasiswaCount + $pegawaiCount;

                if ($dayTotal > 0) {
                    $reportData[] = [
                        'no' => count($reportData) + 1,
                        'date' => $dateCarbon,
                        'mahasiswa' => $mahasiswaCount,
                        'pegawai' => $pegawaiCount,
                        'total' => $dayTotal,
                    ];

                    $grandTotal['mahasiswa'] += $mahasiswaCount;
                    $grandTotal['pegawai'] += $pegawaiCount;
                    $grandTotal['total'] += $dayTotal;
                }
            }

            // Month name in Indonesian
            $monthNames = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember',
            ];

            $monthName = $monthNames[(int)$month];

            // Prepare data for view
            $data = [
                'year' => $year,
                'month' => $month,
                'monthName' => $monthName,
                'monthParam' => $monthParam,
                'reportData' => $reportData,
                'grandTotal' => $grandTotal,
                'generatedAt' => now()->format('d F Y H:i'),
                'displayMonth' => Carbon::createFromFormat('Y-m-d', "$year-$month-01")->locale('id')->translatedFormat('F Y'),
                'totalVisits' => $grandTotal['total'],
                'studentCount' => $grandTotal['mahasiswa'],
                'staffCount' => $grandTotal['pegawai'],
                'studentPercentage' => $grandTotal['total'] > 0 ? round(($grandTotal['mahasiswa'] / $grandTotal['total']) * 100) . '%' : '0%',
                'staffPercentage' => $grandTotal['total'] > 0 ? round(($grandTotal['pegawai'] / $grandTotal['total']) * 100) . '%' : '0%',
            ];

            // Generate PDF from Blade view
            $pdf = Pdf::loadView('admin.clinic-patients.report-pdf', $data);
            $pdf->setPaper('A4', 'portrait');
            
            $filename = 'laporan-kunjungan-poliklinik-' . $monthParam . '.pdf';
            
            // Use download() method untuk forced download
            // download() method automatically set proper Content-Type dan Content-Disposition: attachment headers
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            \Log::error('PDF Download Error', [
                'error' => $e->getMessage(),
                'month' => $monthParam ?? 'not set',
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Gagal download PDF: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified clinic patient from storage.
     */
    public function destroy(ClinicPatient $clinicPatient)
    {
        $clinicPatient->delete();

        return redirect()->route('admin.clinic-patients.index')
                        ->with('success', 'Pasien berhasil dihapus');
    }
}
