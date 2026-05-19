<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicPatient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

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
        if ($request->filled('category') && $request->category != 'all') {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Filter by app user status
        if ($request->filled('app_user') && $request->app_user != 'all') {
            if ($request->app_user == 'using') {
                $query->whereNotNull('user_id');
            } else {
                $query->whereNull('user_id');
            }
        }

        // Sort
        $sort = $request->sort ?? 'latest';
        if ($sort == 'latest') {
            $query->latest();
        } elseif ($sort == 'oldest') {
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
        return view('admin.clinic-patients.create');
    }

    /**
     * Get app user data for automatic form population via AJAX
     */
    public function getAppUserData($userId)
    {
        try {
            $user = User::find($userId);
            
            if (!$user) {
                return response()->json(['error' => 'User tidak ditemukan'], 404);
            }

            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'nim' => $user->nim ?? '',
                'prodi' => $user->prodi ?? '',
                'medical_conditions' => $user->medical_conditions ?? [],
                'notes' => $user->notes ?? '',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created clinic patient in storage.
     */
    public function store(Request $request)
    {
        // Build unique rule for user_id - only check if not null to allow multiple null values
        // Simplified: don't enforce unique since multiple clinic_patients can link to same user
        $userIdRules = [];
        if ($request->filled('user_id')) {
            $userIdRules = ['exists:users,id'];
        }
        
        $rules = [
            'name' => 'required|string|max:255',
            'identity_number' => ['nullable', 'string', 'max:255', Rule::unique('clinic_patients', 'identity_number')->whereNotNull('identity_number')],
            'category' => 'required|in:mahasiswa,pegawai,umum',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:clinic_patients,email',
            'status' => 'required|in:aktif,tidak_aktif',
            'age' => 'nullable|integer|min:1|max:150',
            'gender' => 'nullable|in:laki-laki,perempuan',
            'medical_conditions' => 'nullable|array',
            'medical_conditions.*' => 'string|max:255',
            'notes' => 'nullable|string',
            'create_user_account' => 'nullable|boolean',
            'user_id' => array_merge(['nullable'], $userIdRules),
        ];

        // Jika create_user_account dicentang, password wajib diisi
        if ($request->filled('create_user_account')) {
            $rules['prodi'] = 'nullable|string|max:255';
            $rules['password'] = 'required|min:8|confirmed';
        }

        $validated = $request->validate($rules);

        // Separate clinic patient data from user data
        $clinicPatientData = $validated;
        $medicalConditions = $validated['medical_conditions'] ?? null;
        $notes = $validated['notes'] ?? null;
        $createUserAccount = $validated['create_user_account'] ?? false;
        $password = $validated['password'] ?? null;
        $prodi = $validated['prodi'] ?? null;
        $age = $validated['age'] ?? null;
        $gender = $validated['gender'] ?? null;
        
        unset($clinicPatientData['medical_conditions']);
        unset($clinicPatientData['notes']);
        unset($clinicPatientData['create_user_account']);
        unset($clinicPatientData['password']);
        unset($clinicPatientData['prodi']);
        unset($clinicPatientData['age']);
        unset($clinicPatientData['gender']);

        // Jika create_user_account, buat User terlebih dahulu
        if ($createUserAccount) {
            // role_user = category (mahasiswa atau pegawai)
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role_user' => $validated['category'],
                'nim' => $validated['identity_number'] ?? null,
                'prodi' => $prodi,
                'age' => $age,
                'gender' => $gender,
                'phone' => $validated['phone'],
                'password' => Hash::make($password),
                'timezone' => 'Asia/Jakarta',
            ]);

            // Link user ke clinic patient
            $clinicPatientData['user_id'] = $user->id;

            // Update medical conditions dan notes di User
            if ($medicalConditions) {
                $user->update([
                    'medical_conditions' => array_filter($medicalConditions, function($val) {
                        return !empty(trim($val));
                    }),
                ]);
            }
            if ($notes) {
                $user->update(['notes' => $notes]);
            }
        }

        // Create clinic patient
        $patient = ClinicPatient::create($clinicPatientData);

        return redirect()->route('admin.clinic-patients.index')
                        ->with('success', 'Pasien berhasil ditambahkan' . ($createUserAccount ? ' dan akun aplikasi berhasil dibuat' : ''));
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

        // Prepare medical conditions and notes data
        // If user is linked, get from user; otherwise get from notes field
        $medicalConditions = [];
        if ($clinicPatient->user && $clinicPatient->user->medical_conditions) {
            $medicalConditions = $clinicPatient->user->medical_conditions;
        }

        $notes = '';
        if ($clinicPatient->user && $clinicPatient->user->notes) {
            $notes = $clinicPatient->user->notes;
        }

        // Prepare prodi data
        $prodi = '';
        if ($clinicPatient->user && $clinicPatient->user->prodi) {
            $prodi = $clinicPatient->user->prodi;
        }

        // Prepare age and gender data
        $age = $clinicPatient->user?->age ?? null;
        $gender = $clinicPatient->user?->gender ?? null;

        return view('admin.clinic-patients.edit', [
            'patient' => $clinicPatient,
            'availableUsers' => $availableUsers,
            'medicalConditions' => $medicalConditions,
            'notes' => $notes,
            'prodi' => $prodi,
            'age' => $age,
            'gender' => $gender,
        ]);
    }

    /**
     * Update the specified clinic patient in storage.
     */
    public function update(Request $request, ClinicPatient $clinicPatient)
    {
        // Build unique rule for user_id - only check if not null to allow multiple null values
        // Simplified: don't enforce unique since multiple clinic_patients can link to same user
        $userIdRules = [];
        if ($request->filled('user_id')) {
            // Only validate that user exists
            $userIdRules = ['exists:users,id'];
        }
        
        $validated = $request->validate([
            'user_id' => array_merge(['nullable'], $userIdRules),
            'name' => 'required|string|max:255',
            'identity_number' => ['nullable', 'string', 'max:255', Rule::unique('clinic_patients', 'identity_number')->ignore($clinicPatient->id)->whereNotNull('identity_number')],
            'category' => 'required|in:mahasiswa,pegawai,umum',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:aktif,tidak_aktif',
            'age' => 'nullable|integer|min:1|max:150',
            'gender' => 'nullable|in:laki-laki,perempuan',
            'medical_conditions' => 'nullable|array',
            'medical_conditions.*' => 'string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Separate clinic patient data from user data
        $clinicPatientData = $validated;
        $medicalConditions = $validated['medical_conditions'] ?? null;
        $notes = $validated['notes'] ?? null;
        $age = $validated['age'] ?? null;
        $gender = $validated['gender'] ?? null;
        
        unset($clinicPatientData['medical_conditions']);
        unset($clinicPatientData['notes']);
        unset($clinicPatientData['age']);
        unset($clinicPatientData['gender']);

        // IMPORTANT: Override status dengan automatic status berdasarkan jadwal minum obat
        // Status tidak bisa diubah manual, selalu ditentukan oleh sistem
        $clinicPatientData['status'] = $clinicPatient->getAutomaticStatus();

        // Update clinic patient
        $clinicPatient->update($clinicPatientData);

        // If user is selected, sync data with user account
        if ($clinicPatient->user_id) {
            $user = User::find($clinicPatient->user_id);
            $userData = [];
            
            // Sync NIM if provided
            if ($validated['identity_number'] && $user->nim != $validated['identity_number']) {
                $userData['nim'] = $validated['identity_number'];
            }
            
            // Sync email if changed
            if ($validated['email'] && $user->email != $validated['email']) {
                $userData['email'] = $validated['email'];
            }
            
            // Sync phone if provided
            if ($validated['phone']) {
                $userData['phone'] = $validated['phone'];
            }
            
            // Sync age if provided
            if ($age != null) {
                $userData['age'] = $age;
            }
            
            // Sync gender if provided
            if ($gender != null) {
                $userData['gender'] = $gender;
            }
            
            // Sync medical conditions
            if ($medicalConditions) {
                $userData['medical_conditions'] = array_filter($medicalConditions, function($val) {
                    return !empty(trim($val));
                });
            }
            
            // Sync notes
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
                          return $patient->category == 'mahasiswa' && 
                              $patient->created_at->format('Y-m-d') == $date;
                })->count();

                $pegawaiCount = $patients->filter(function ($patient) use ($date) {
                          return $patient->category == 'pegawai' && 
                              $patient->created_at->format('Y-m-d') == $date;
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
        // Load user relation untuk menghapus data terkait
        $clinicPatient->load('user');
        $userId = $clinicPatient->user_id;
        $userEmail = $clinicPatient->user?->email ?? null;
        
        // Hapus clinic patient terlebih dahulu (untuk release foreign key constraint)
        $clinicPatient->delete();
        Log::info("ClinicPatient deleted: {$clinicPatient->id}");
        
        // Hapus semua data user jika ada user linked
        if ($userId) {
            try {
                $user = User::findOrFail($userId);
                Log::info("Found user {$userId} with email {$user->email}");
                
                try {
                    // Hapus semua medication schedules user
                    $user->medicationSchedules()->delete();
                    Log::info("Medication schedules deleted for user {$userId}");
                } catch (\Exception $e) {
                    Log::warning("Error deleting medication schedules for user {$userId}: {$e->getMessage()}");
                }
                
                try {
                    // Hapus semua medication logs user
                    $user->medicationLogs()->delete();
                    Log::info("Medication logs deleted for user {$userId}");
                } catch (\Exception $e) {
                    Log::warning("Error deleting medication logs for user {$userId}: {$e->getMessage()}");
                }
                
                try {
                    // Hapus semua notifications user (jika table ada)
                    if (Schema::hasTable('notifications')) {
                        $user->notifications()->delete();
                        Log::info("Notifications deleted for user {$userId}");
                    }
                } catch (\Exception $e) {
                    Log::warning("Error deleting notifications for user {$userId}: {$e->getMessage()}");
                }
                
                // Hapus user account dengan forceDelete
                $deleted = $user->forceDelete();
                Log::info("User {$userId} deleted with forceDelete. Result: {$deleted}");
            } catch (\Exception $e) {
                Log::error("Error deleting user {$userId}: {$e->getMessage()} | " . get_class($e));
                // Continue anyway
            }
        }

        return redirect()->route('admin.clinic-patients.index')
                        ->with('success', 'Pasien dan semua data terkait berhasil dihapus. Pasien dapat mendaftar ulang.');
    }
}
