<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\RiwayatController;
use App\Http\Controllers\Admin\ObatController;
use App\Http\Controllers\Admin\RekamMedisController;
use App\Http\Controllers\Admin\AdminMedicationScheduleController;
use App\Http\Controllers\Admin\ClinicPatientController;

// Admin Authentication Routes
Route::middleware('guest:admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::get('/register', [AdminAuthController::class, 'showRegister'])->name('admin.register');
    Route::post('/register', [AdminAuthController::class, 'register']);
});

// Protected Admin Routes
Route::middleware('auth:admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Pengguna Management
    Route::resource('pengguna', PenggunaController::class, ['as' => 'admin']);
    
    // Riwayat Pengingat
    Route::get('/riwayat', [RiwayatController::class, 'index'])->name('admin.riwayat.index');
    
    // Obat Management
    Route::resource('obat', ObatController::class, ['as' => 'admin']);
    
    // Rekam Medis - explicit routes to avoid singularization
    Route::get('/rekam-medis', [RekamMedisController::class, 'index'])->name('admin.rekam-medis.index');
    Route::get('/rekam-medis/{user}', [RekamMedisController::class, 'show'])->name('admin.rekam-medis.show');
    Route::get('/rekam-medis/{user}/edit', [RekamMedisController::class, 'edit'])->name('admin.rekam-medis.edit');
    Route::put('/rekam-medis/{user}', [RekamMedisController::class, 'update'])->name('admin.rekam-medis.update');
    
    // Medication Schedules
    Route::get('/schedules', [AdminMedicationScheduleController::class, 'index'])->name('admin.schedules.index');
    Route::get('/schedules/create', [AdminMedicationScheduleController::class, 'create'])->name('admin.schedules.create');
    Route::post('/schedules', [AdminMedicationScheduleController::class, 'store'])->name('admin.schedules.store');
    Route::get('/schedules/{schedule}/edit', [AdminMedicationScheduleController::class, 'edit'])->name('admin.schedules.edit');
    Route::put('/schedules/{schedule}', [AdminMedicationScheduleController::class, 'update'])->name('admin.schedules.update');
    Route::delete('/schedules/{schedule}', [AdminMedicationScheduleController::class, 'destroy'])->name('admin.schedules.destroy');
    
    // Clinic Patient Management (Manajemen Pasien Poliklinik)
    Route::get('/clinic-patients/report/pdf', [ClinicPatientController::class, 'reportPdf'])->name('admin.clinic-patients.report-pdf');
    Route::get('/clinic-patients/download/pdf', [ClinicPatientController::class, 'downloadPdf'])->name('admin.clinic-patients.download-pdf');
    Route::resource('clinic-patients', ClinicPatientController::class, ['as' => 'admin']);
    
    // Logout
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});

// Admin pages accessible only to authenticated admins
Route::middleware('auth:admin')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });
});
