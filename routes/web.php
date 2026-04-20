<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedicationLogController;
use App\Http\Controllers\UserMedicineController;
use App\Http\Controllers\UserMedicationScheduleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MedicationHistoryController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\Admin\AdminMedicationScheduleController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\RiwayatController;
use App\Http\Controllers\Admin\ObatController;
use App\Http\Controllers\Admin\RekamMedisController;
use App\Http\Controllers\Admin\ClinicPatientController;

/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| USER AREA (auth:web)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return redirect()->route('app.dashboard');
    })->name('dashboard');

    Route::prefix('app')->name('app.')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::post('/schedules/{schedule}/take', [MedicationLogController::class, 'take'])
            ->name('schedules.take');

        // Medications list
        Route::get('/medications', [UserMedicineController::class, 'index'])
            ->name('medications.index');

        // User Medicines - Add/Edit/Delete
        Route::get('/medications/create', [UserMedicineController::class, 'create'])
            ->name('medicines.create');
        Route::post('/medications', [UserMedicineController::class, 'store'])
            ->name('medicines.store');
        Route::get('/medications/my', [UserMedicineController::class, 'myMedicines'])
            ->name('medicines.my');
        Route::get('/medications/{medicine}/edit', [UserMedicineController::class, 'edit'])
            ->name('medicines.edit');
        Route::put('/medications/{medicine}', [UserMedicineController::class, 'update'])
            ->name('medicines.update');
        Route::delete('/medications/{medicine}', [UserMedicineController::class, 'destroy'])
            ->name('medicines.destroy');

        // User Medication Schedules
        Route::get('/schedules', [UserMedicationScheduleController::class, 'index'])
            ->name('schedules.index');
        Route::get('/schedules/create', [UserMedicationScheduleController::class, 'create'])
            ->name('schedules.create');
        Route::get('/schedules/upcoming', [DashboardController::class, 'upcomingSchedules'])
            ->name('schedules.upcoming');
        Route::post('/schedules', [UserMedicationScheduleController::class, 'store'])
            ->name('schedules.store');
        Route::get('/schedules/{schedule}/edit', [UserMedicationScheduleController::class, 'edit'])
            ->name('schedules.edit');
        Route::put('/schedules/{schedule}', [UserMedicationScheduleController::class, 'update'])
            ->name('schedules.update');
        Route::delete('/schedules/{schedule}', [UserMedicationScheduleController::class, 'destroy'])
            ->name('schedules.destroy');

        // History list
        Route::get('/history', [MedicationHistoryController::class, 'index'])
            ->name('history.index');
        
        // DEBUG: Test history rendering
        Route::get('/history-test', function () {
            try {
                return view('app.history.test', [
                    'stats' => ['test' => true],
                    'logs' => collect(),
                    'medicines' => collect(),
                    'dailyCompliance' => [],
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ], 500);
            }
        })->middleware('auth');
        
        // DEBUG: Test history rendering with data
        Route::get('/history-debug', function () {
            try {
                $stats = [
                    'overall_compliance' => 75,
                    'total_days' => 30,
                    'perfect_days' => 20,
                    'zero_days' => 5,
                ];
                
                $dailyCompliance = [
                    ['date' => '2026-03-01', 'count' => 2],
                    ['date' => '2026-03-02', 'count' => 1],
                    ['date' => '2026-03-03', 'count' => 3],
                ];
                
                return view('app.history.index', [
                    'logs' => collect(),
                    'stats' => $stats,
                    'dailyCompliance' => $dailyCompliance,
                    'medicines' => collect(),
                    'fromDate' => now()->subMonth()->toDateString(),
                    'toDate' => now()->toDateString(),
                    'selectedMedicineId' => null,
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ], 500);
            }
        })->middleware('auth');
        
        Route::get('/history/export', [MedicationHistoryController::class, 'exportCsv'])
            ->name('history.export');

        // Compliance statistics
        Route::get('/compliance', function() {
            return view('app.compliance');
        })->name('compliance.show');

        // Profile
        Route::get('/profile', function() {
            return view('app.profile.show');
        })->name('profile.show');

        // Settings
        Route::get('/settings', function() {
            return view('app.settings');
        })->name('settings');
    });
    
    /*
    |--------------------------------------------------------------------------
    | API ROUTES FOR OFFLINE SYNC (auth:web)
    |--------------------------------------------------------------------------
    */
    Route::prefix('api')->name('api.')->group(function () {
        // Sync offline medication logs
        Route::post('/sync-medication-logs', [MedicationLogController::class, 'syncOfflineLogs'])
            ->name('sync-medication-logs');
        
        // Get pending sync count
        Route::get('/sync-status', [MedicationLogController::class, 'syncStatus'])
            ->name('sync-status');

        // NOTIFICATION ROUTES
        Route::post('/notification-times', [NotificationController::class, 'getNotificationTimes'])
            ->name('notification-times');
        
        Route::post('/notification-sent', [NotificationController::class, 'markNotificationSent'])
            ->name('notification-sent');
        
        Route::get('/notification-preferences', [NotificationController::class, 'getPreferences'])
            ->name('notification-preferences');
        
        Route::post('/notification-preferences', [NotificationController::class, 'savePreferences'])
            ->name('notification-preferences.save');
        
        Route::get('/should-notify', [NotificationController::class, 'shouldNotify'])
            ->name('should-notify');
        
        Route::post('/snooze-notification', [NotificationController::class, 'snoozeNotification'])
            ->name('snooze-notification');
        
        Route::post('/dismiss-notification', [NotificationController::class, 'dismissNotification'])
            ->name('dismiss-notification');
        
        // SECOND REMINDER ROUTES
        Route::post('/second-reminders', [NotificationController::class, 'getSecondReminders'])
            ->name('second-reminders');
        
        Route::post('/second-reminder-sent', [NotificationController::class, 'markSecondReminderSent'])
            ->name('second-reminder-sent');
        
        // DASHBOARD REMINDER ROUTES
        Route::get('/due-medications', [NotificationController::class, 'getDueMedications'])
            ->name('due-medications');
        
        Route::post('/snooze-reminder-dashboard', [NotificationController::class, 'snoozeReminderDashboard'])
            ->name('snooze-reminder-dashboard');
        
        Route::get('/pending-reminders', [NotificationController::class, 'getPendingReminders'])
            ->name('pending-reminders');
        
        // MEDICATION ACTION ROUTES (for notification modal)
        Route::get('/medication-schedule/{schedule}', [MedicationLogController::class, 'getScheduleDetails'])
            ->name('medication-schedule');
        
        Route::post('/medication-taken', [MedicationLogController::class, 'medicationTaken'])
            ->name('medication-taken');
        
        Route::post('/medication-snooze', [MedicationLogController::class, 'medicationSnooze'])
            ->name('medication-snooze');
        
        // HISTORY & STATISTICS ROUTES
        Route::get('/history', [MedicationHistoryController::class, 'apiHistory'])
            ->name('history');
        
        Route::get('/statistics/summary', [MedicationHistoryController::class, 'summary'])
            ->name('statistics.summary');
        
        Route::get('/statistics/weekly-stats', [MedicationHistoryController::class, 'weeklyStats'])
            ->name('statistics.weekly-stats');
    });
});

/*
|--------------------------------------------------------------------------
| DEBUG ROUTES (untuk testing)
|--------------------------------------------------------------------------
*/
Route::get('/debug/compliance-stats', function() {
    // Hardcode user 3 for testing
    $userId = 3;
    $today = \Carbon\Carbon::parse('2026-03-25')->startOfDay();
    
    // Get schedules
    $schedules = \App\Models\MedicationSchedule::where('user_id', $userId)
        ->where('is_active', true)
        ->whereDate('start_date', '<=', $today)
        ->where(function ($q) use ($today) {
            $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
        })
        ->with(['logs' => function($q) use ($today) {
            $q->whereDate('created_at', '>=', $today)
              ->whereDate('created_at', '<=', $today);
        }])
        ->get();
    
    // Mimic calculateStats logic
    $period = \Carbon\CarbonPeriod::create($today, $today);
    $totalExpected = 0;
    $totalTaken = 0;
    
    foreach ($period as $date) {
        foreach ($schedules as $schedule) {
            if ($schedule->start_date->lte($date) && 
                ($schedule->end_date === null || $schedule->end_date->gte($date))) {
                $totalExpected++;
                
                $log = $schedule->logs
                    ->where('status', 'taken')
                    ->first(function($log) use ($date) {
                        return $log->created_at->toDateString() === $date->toDateString();
                    });
                    
                if ($log) {
                    $totalTaken++;
                }
            }
        }
    }
    
    return response()->json([
        'user_id' => $userId,
        'today' => $today->toDateString(),
        'schedules_found' => $schedules->count(),
        'total_expected' => $totalExpected,
        'total_taken' => $totalTaken,
        'compliance' => $totalExpected > 0 ? round(($totalTaken / $totalExpected) * 100) : 0,
        'schedules' => $schedules->map(function($s) {
            return [
                'id' => $s->id,
                'medicine' => $s->medicine->name,
                'start_date' => $s->start_date->toDateString(),
                'end_date' => $s->end_date?->toDateString(),
                'is_active' => $s->is_active,
                'time' => $s->time,
                'logs_count' => $s->logs->count(),
                'logs' => $s->logs->map(fn($l) => [
                    'status' => $l->status, 
                    'created_at' => $l->created_at->toDateString()
                ])->toArray(),
            ];
        })->toArray(),
    ]);
});

// Redirect /admin to /admin/login or /admin/dashboard
Route::get('/admin', function () {
    if (auth('admin')->check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('admin.login');
});

Route::prefix('admin')->name('admin.')->group(function () {

    // ===== Admin Login (public) =====
    Route::get('/login', [AdminAuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AdminAuthController::class, 'login'])
        ->name('login.submit');

    // ===== Forgot Password Routes (public) =====
    Route::get('/forgot-password', [AdminAuthController::class, 'showForgotPasswordForm'])
        ->name('password.request');

    Route::post('/forgot-password', [AdminAuthController::class, 'sendResetLink'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [AdminAuthController::class, 'showResetForm'])
        ->name('password.reset');

    Route::post('/reset-password', [AdminAuthController::class, 'resetPassword'])
        ->name('password.update');

    // ===== Protected Admin Area =====
    Route::middleware('auth:admin')->group(function () {

        Route::post('/logout', [AdminAuthController::class, 'logout'])
            ->name('logout');

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        // Admin Management
        Route::get('/management', [AdminManagementController::class, 'index'])
            ->name('management.index');
        Route::get('/management/create', [AdminManagementController::class, 'create'])
            ->name('management.create');
        Route::post('/management', [AdminManagementController::class, 'store'])
            ->name('management.store');
        Route::delete('/management/{admin}', [AdminManagementController::class, 'destroy'])
            ->name('management.destroy');

        // Pengguna Management
        Route::resource('pengguna', PenggunaController::class);

        // Riwayat Pengingat
        Route::get('/riwayat', [RiwayatController::class, 'index'])
            ->name('riwayat.index');

        // Obat Management
        Route::resource('obat', ObatController::class);

        // Rekam Medis - explicit routes to avoid singularization
        Route::get('/rekam-medis', [RekamMedisController::class, 'index'])->name('rekam-medis.index');
        Route::get('/rekam-medis/{user}', [RekamMedisController::class, 'show'])->name('rekam-medis.show');
        Route::get('/rekam-medis/{user}/edit', [RekamMedisController::class, 'edit'])->name('rekam-medis.edit');
        Route::put('/rekam-medis/{user}', [RekamMedisController::class, 'update'])->name('rekam-medis.update');

        // Clinic Patient Management (Manajemen Pasien Poliklinik)
        Route::prefix('clinic-patients')->name('clinic-patients.')->group(function () {
            // Get app user data for automatic form population
            Route::get('app-user-data/{userId}', [ClinicPatientController::class, 'getAppUserData'])
                ->where('userId', '[0-9]+')
                ->name('app-user-data');
            
            // PDF Export routes (must be before show route)
            Route::get('report/pdf', [ClinicPatientController::class, 'reportPdf'])
                ->name('report-pdf');  // Preview in browser (inline)
            Route::get('download/pdf', [ClinicPatientController::class, 'downloadPdf'])
                ->name('download-pdf');  // Forced download (attachment)
        });
        
        Route::resource('clinic-patients', ClinicPatientController::class);

        // Medication Schedules
        Route::prefix('schedules')->name('schedules.')->group(function () {

            Route::get('/', [AdminMedicationScheduleController::class, 'index'])
                ->name('index');

            Route::get('/create', [AdminMedicationScheduleController::class, 'create'])
                ->name('create');

            Route::post('/', [AdminMedicationScheduleController::class, 'store'])
                ->name('store');

            Route::get('/{schedule}/edit', [AdminMedicationScheduleController::class, 'edit'])
                ->name('edit');

            Route::put('/{schedule}', [AdminMedicationScheduleController::class, 'update'])
                ->name('update');

            Route::delete('/{schedule}', [AdminMedicationScheduleController::class, 'destroy'])
                ->name('destroy');
        });

        Route::post('/logout', [AdminAuthController::class, 'logout'])
            ->name('logout');
    });
});

/*
|--------------------------------------------------------------------------
| PDF DEBUG ROUTES (No Authentication Required)
|--------------------------------------------------------------------------
*/
Route::get('/debug/pdf/test-basic', function() {
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHtml('<html><body style="font-family: Arial;"><h1>Test PDF - Basic</h1><p>If you see this in PDF format, it works!</p><p>Timestamp: ' . now() . '</p></body></html>');
    return $pdf->stream('test-basic.pdf');
})->name('debug.pdf.basic');

Route::get('/debug/pdf/test-download', function() {
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHtml('<html><body style="font-family: Arial;"><h1>Test PDF Download</h1><p>This PDF should download to your computer!</p><p>Timestamp: ' . now() . '</p></body></html>');
    return $pdf->download('test-download.pdf');
})->name('debug.pdf.download');

Route::get('/debug/pdf/clinic-report', function() {
    $month = request('month', now()->format('Y-m'));
    $displayMonth = \Carbon\Carbon::createFromFormat('Y-m', $month)->locale('id')->translatedFormat('F Y');
    
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            * { margin: 0; padding: 0; }
            body { 
                font-family: Arial, sans-serif; 
                padding: 30px; 
                background: white;
            }
            h1 { 
                text-align: center; 
                color: #333; 
                margin-bottom: 10px;
                font-size: 24px;
            }
            .meta {
                text-align: center;
                color: #666;
                margin-bottom: 30px;
                font-size: 12px;
            }
            table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-top: 20px;
            }
            th, td { 
                padding: 12px; 
                border: 1px solid #ddd; 
                text-align: left;
            }
            th { 
                background-color: #007bff; 
                color: white;
                font-weight: bold;
            }
            tr:nth-child(even) { 
                background-color: #f9f9f9; 
            }
            tr:last-child {
                background-color: #007bff;
                color: white;
                font-weight: bold;
            }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
        </style>
    </head>
    <body>
        <h1>📋 Laporan Pasien Poliklinik</h1>
        <div class="meta">
            <p><strong>Periode:</strong> ' . htmlspecialchars($displayMonth) . '</p>
            <p><strong>Dibuat pada:</strong> ' . now()->translatedFormat('d F Y H:i') . '</p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Kategori Pasien</th>
                    <th class="text-right">Jumlah Kunjungan</th>
                    <th class="text-right">Persentase</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>👨‍🎓 Mahasiswa</td>
                    <td class="text-right">42</td>
                    <td class="text-right">65%</td>
                </tr>
                <tr>
                    <td>👔 Pegawai</td>
                    <td class="text-right">23</td>
                    <td class="text-right">35%</td>
                </tr>
                <tr>
                    <td><strong>TOTAL</strong></td>
                    <td class="text-right"><strong>65</strong></td>
                    <td class="text-right"><strong>100%</strong></td>
                </tr>
            </tbody>
        </table>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 10px; color: #999;">
            <p>Dokumen ini dibuat secara otomatis oleh sistem LifeCare. Untuk informasi lebih lanjut, hubungi administrator poliklinik.</p>
        </div>
    </body>
    </html>';
    
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHtml($html);
    $pdf->setPaper('A4', 'portrait');
    return $pdf->stream('Laporan-Poliklinik-' . $month . '.pdf');
})->name('debug.pdf.clinic-report');

require __DIR__.'/auth.php';