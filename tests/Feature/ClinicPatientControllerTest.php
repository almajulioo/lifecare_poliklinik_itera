<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ClinicPatient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicPatientControllerTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test admin with raw create instead of factory
        $this->admin = Admin::create([
            'email' => 'admin@test.local',
            'password' => bcrypt('password123'),
        ]);
    }

    /** @test */
    public function clinic_patient_can_be_updated_with_null_user_id()
    {
        // Create multiple patients with null user_id
        $patient1 = ClinicPatient::create([
            'user_id' => null,
            'name' => 'Patient One',
            'identity_number' => '111111',
            'category' => 'mahasiswa',
            'status' => 'aktif',
        ]);

        $patient2 = ClinicPatient::create([
            'user_id' => null,
            'name' => 'Patient Two',
            'identity_number' => '222222',
            'category' => 'mahasiswa',
            'status' => 'aktif',
        ]);

        // Try updating patient1 without changing user_id (staying null)
        $response = $this->actingAs($this->admin, 'admin')->put(
            route('admin.clinic-patients.update', $patient1),
            [
                'name' => 'Patient One Updated',
                'identity_number' => '111111',
                'category' => 'pegawai',
                'phone' => '08123456789',
                'email' => 'patient1@test.local',
                'status' => 'aktif',
                'user_id' => null,
                'medical_conditions' => [],
                'notes' => null,
            ]
        );

        // Should succeed without validation error
        $response->assertRedirect(route('admin.clinic-patients.index'));
        $response->assertSessionHas('success', 'Pasien berhasil diperbarui');

        // Verify update
        $patient1->refresh();
        $this->assertEquals('Patient One Updated', $patient1->name);
        $this->assertEquals('pegawai', $patient1->category);
        $this->assertNull($patient1->user_id);
    }

    /** @test */
    public function clinic_patient_can_be_updated_with_unique_user_id()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'testuser@test.local',
            'password' => bcrypt('password'),
            'role_user' => 'mahasiswa',
        ]);

        $patient = ClinicPatient::create([
            'user_id' => null,
            'name' => 'Test Patient',
            'identity_number' => '999999',
            'category' => 'mahasiswa',
            'status' => 'aktif',
        ]);

        // Update with new user_id
        $response = $this->actingAs($this->admin, 'admin')->put(
            route('admin.clinic-patients.update', $patient),
            [
                'name' => 'Test Patient',
                'identity_number' => '999999',
                'category' => 'mahasiswa',
                'phone' => null,
                'email' => null,
                'status' => 'aktif',
                'user_id' => $user->id,
                'medical_conditions' => [],
                'notes' => null,
            ]
        );

        $response->assertRedirect(route('admin.clinic-patients.index'));
        
        $patient->refresh();
        $this->assertEquals($user->id, $patient->user_id);
    }

    /** @test */
    public function clinic_patient_store_with_null_identity_number()
    {
        $response = $this->actingAs($this->admin, 'admin')->post(
            route('admin.clinic-patients.store'),
            [
                'name' => 'New Patient',
                'identity_number' => null,
                'category' => 'umum',
                'phone' => null,
                'email' => null,
                'status' => 'aktif',
                'user_id' => null,
                'medical_conditions' => [],
                'notes' => null,
            ]
        );

        $response->assertRedirect(route('admin.clinic-patients.index'));
        
        $this->assertDatabaseHas('clinic_patients', [
            'name' => 'New Patient',
            'identity_number' => null,
            'user_id' => null,
        ]);
    }

    /** @test */
    public function duplicate_unique_identity_number_fails()
    {
        $patient1 = ClinicPatient::create([
            'user_id' => null,
            'name' => 'Patient One',
            'identity_number' => '555555',
            'category' => 'mahasiswa',
            'status' => 'aktif',
        ]);

        $patient2 = ClinicPatient::create([
            'user_id' => null,
            'name' => 'Patient Two',
            'identity_number' => null,
            'category' => 'mahasiswa',
            'status' => 'aktif',
        ]);

        // Try to update patient2 with patient1's identity number
        $response = $this->actingAs($this->admin, 'admin')->put(
            route('admin.clinic-patients.update', $patient2),
            [
                'name' => 'Patient Two',
                'identity_number' => '555555',
                'category' => 'mahasiswa',
                'status' => 'aktif',
                'user_id' => null,
                'medical_conditions' => [],
                'notes' => null,
            ]
        );

        // Should fail validation
        $response->assertSessionHasErrors('identity_number');
    }
}
