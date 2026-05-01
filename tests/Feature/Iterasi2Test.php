<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use App\Models\Medicine;
use App\Models\MedicationSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Iterasi2Test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_menambahkan_data_obat()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.obat.store'), [
            'name' => 'Paracetamol',
            'dose' => '500 mg',
            'unit' => 'tablet',
            'notes' => 'Diminum setelah makan',
        ]);

        $response->assertRedirect(route('admin.obat.index'));

        $this->assertDatabaseHas('medicines', [
            'name' => 'Paracetamol',
            'dose' => '500 mg',
            'source_type' => 'ADMIN',
        ]);
    }

    public function test_admin_menambahkan_jadwal_konsumsi_obat()
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
            'user_id' => null,
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.schedules.store'), [
            'user_id' => $pasien->id,
            'medicine_id' => $medicine->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'times' => ['08:00', '20:00'],
            'frequency' => '2x sehari',
            'duration_days' => 3,
            'source' => 'resep',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.schedules.index'));

        $this->assertDatabaseHas('medication_schedules', [
            'user_id' => $pasien->id,
            'medicine_id' => $medicine->id,
            'time' => '08:00',
            'source_type' => 'ADMIN',
        ]);
    }

    public function test_pasien_menambahkan_obat_mandiri()
    {
        $pasien = User::factory()->create([
            'role_user' => 'mahasiswa',
        ]);

        $response = $this->actingAs($pasien)->post(route('app.medicines.store'), [
            'name' => 'Vitamin C',
            'dose' => '500 mg',
            'unit' => 'tablet',
            'notes' => 'Diminum pagi hari',
        ]);

        $response->assertRedirect(route('app.medications.index'));

        $this->assertDatabaseHas('medicines', [
            'name' => 'Vitamin C',
            'dose' => '500 mg',
            'source_type' => 'PATIENT',
            'user_id' => $pasien->id,
        ]);
    }
}