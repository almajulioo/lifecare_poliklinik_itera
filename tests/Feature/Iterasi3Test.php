<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Medicine;
use App\Models\MedicationSchedule;
use App\Models\MedicationLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

class Iterasi3Test extends TestCase
{
    use RefreshDatabase;

    public function test_pasien_melihat_daftar_obat()
    {
        $pasien = User::factory()->create([
            'role_user' => 'mahasiswa',
        ]);

        $medicine = Medicine::create([
            'user_id' => $pasien->id,
            'name' => 'Paracetamol',
            'dose' => '500 mg',
            'unit' => 'tablet',
            'source_type' => 'PATIENT',
        ]);

        $response = $this->actingAs($pasien)
            ->get(route('app.medications.index'));

        $response->assertStatus(200);
        $response->assertSee('Paracetamol');
    }

    public function test_admin_melihat_riwayat_konsumsi_pasien()
    {
        $admin = Admin::factory()->create();

        $pasien = User::factory()->create([
            'role_user' => 'mahasiswa',
        ]);

        $medicine = Medicine::create([
            'name' => 'Amoxicillin',
            'dose' => '500 mg',
            'unit' => 'kapsul',
            'source_type' => 'ADMIN',
        ]);

        $schedule = MedicationSchedule::create([
            'user_id' => $pasien->id,
            'medicine_id' => $medicine->id,
            'start_date' => now()->format('Y-m-d'),
            'time' => '08:00',
            'frequency' => '1x sehari',
            'source' => 'resep',
            'source_type' => 'ADMIN',
            'is_active' => true,
        ]);

        MedicationLog::create([
            'user_id' => $pasien->id,
            'medication_schedule_id' => $schedule->id,
            'status' => 'taken',
            'taken_at' => now(),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.riwayat.index'));

        $response->assertStatus(200);
        $response->assertSee('Amoxicillin');
    }

    public function test_pasien_melihat_riwayat_pribadi()
    {
        $pasien = User::factory()->create([
            'role_user' => 'mahasiswa',
        ]);

        $medicine = Medicine::create([
            'user_id' => $pasien->id,
            'name' => 'Vitamin C',
            'dose' => '500 mg',
            'unit' => 'tablet',
            'source_type' => 'PATIENT',
        ]);

        $schedule = MedicationSchedule::create([
            'user_id' => $pasien->id,
            'medicine_id' => $medicine->id,
            'start_date' => now()->format('Y-m-d'),
            'time' => '09:00',
            'frequency' => '1x sehari',
            'source' => 'mandiri',
            'source_type' => 'PATIENT',
            'is_active' => true,
        ]);

        MedicationLog::create([
            'user_id' => $pasien->id,
            'medication_schedule_id' => $schedule->id,
            'status' => 'taken',
            'taken_at' => now(),
        ]);

        $response = $this->actingAs($pasien)
            ->get(route('app.history.index'));

        $response->assertStatus(200);
        $response->assertSee('Vitamin C');
    }
}