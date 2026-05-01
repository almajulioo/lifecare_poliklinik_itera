<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Medicine;
use App\Models\MedicationSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RBACAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $patient1;
    protected $patient2;
    protected $adminMedicine;
    protected $patientMedicine;
    protected $adminSchedule;

    public function setUp(): void
    {
        parent::setUp();

        // Create test users (patients)
        $this->patient1 = User::factory()->create([
            'name' => 'Patient 1',
            'email' => 'patient1@test.com',
            'role_user' => 'mahasiswa',
        ]);

        $this->patient2 = User::factory()->create([
            'name' => 'Patient 2',
            'email' => 'patient2@test.com',
            'role_user' => 'mahasiswa',
        ]);

        // Create test admin
        $this->admin = Admin::create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        // Create test medicines
        $this->adminMedicine = Medicine::create([
            'name' => 'Aspirin (ADMIN)',
            'dose' => '500mg',
            'unit' => 'tablet',
            'notes' => 'For headache relief',
            'source_type' => 'ADMIN',
            'user_id' => null,
        ]);

        $this->patientMedicine = Medicine::create([
            'name' => 'Vitamin C (PATIENT)',
            'dose' => '1000mg',
            'unit' => 'tablet',
            'notes' => 'Personal supplement',
            'source_type' => 'PATIENT',
            'user_id' => $this->patient1->id,
        ]);

        // Create test medication schedule
        $this->adminSchedule = MedicationSchedule::create([
            'user_id' => $this->patient1->id,
            'medicine_id' => $this->adminMedicine->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'time' => '08:00',
            'frequency' => 'Setiap hari',
            'duration_days' => 30,
            'source' => 'resep',
            'source_type' => 'ADMIN',
            'is_active' => true,
        ]);
    }

    // ==================== MEDICINE POLICY TESTS ====================

    /**
     * Test: ADMIN can view all medicines
     */
    public function test_admin_can_view_all_medicines()
    {
        $this->assertTrue($this->admin->can('view', $this->adminMedicine));
        $this->assertTrue($this->admin->can('view', $this->patientMedicine));
    }

    /**
     * Test: PATIENT can view ADMIN medicines
     */
    public function test_patient_can_view_admin_medicines()
    {
        $this->assertTrue($this->patient1->can('view', $this->adminMedicine));
    }

    /**
     * Test: PATIENT can view own medicines
     */
    public function test_patient_can_view_own_medicine()
    {
        $this->assertTrue($this->patient1->can('view', $this->patientMedicine));
    }

    /**
     * Test: PATIENT cannot view other patient's medicines
     */
    public function test_patient_cannot_view_other_patient_medicine()
    {
        $this->assertFalse($this->patient2->can('view', $this->patientMedicine));
    }

    /**
     * Test: Both ADMIN and PATIENT can create medicines
     * Admins create medicines with source_type ADMIN
     * Patients create medicines with source_type PATIENT
     */
    public function test_only_admin_can_create_medicine()
    {
        $this->assertTrue($this->admin->can('create', Medicine::class));
        $this->assertTrue($this->patient1->can('create', Medicine::class));
    }

    /**
     * Test: ADMIN can update any medicine
     */
    public function test_admin_can_update_any_medicine()
    {
        $this->assertTrue($this->admin->can('update', $this->adminMedicine));
        $this->assertTrue($this->admin->can('update', $this->patientMedicine));
    }

    /**
     * Test: PATIENT can update own PATIENT medicine
     */
    public function test_patient_can_update_own_patient_medicine()
    {
        $this->assertTrue($this->patient1->can('update', $this->patientMedicine));
    }

    /**
     * Test: PATIENT cannot update ADMIN medicine
     */
    public function test_patient_cannot_update_admin_medicine()
    {
        $this->assertFalse($this->patient1->can('update', $this->adminMedicine));
    }

    /**
     * Test: PATIENT cannot update other patient's medicine
     */
    public function test_patient_cannot_update_other_patient_medicine()
    {
        $otherPatientMedicine = Medicine::create([
            'name' => 'Other Patient Medicine',
            'dose' => '500mg',
            'unit' => 'tablet',
            'source_type' => 'PATIENT',
            'user_id' => $this->patient2->id,
        ]);

        $this->assertFalse($this->patient1->can('update', $otherPatientMedicine));
    }

    /**
     * Test: ADMIN can delete any medicine
     */
    public function test_admin_can_delete_any_medicine()
    {
        $this->assertTrue($this->admin->can('delete', $this->adminMedicine));
        $this->assertTrue($this->admin->can('delete', $this->patientMedicine));
    }

    /**
     * Test: PATIENT can delete own PATIENT medicine
     */
    public function test_patient_can_delete_own_patient_medicine()
    {
        $this->assertTrue($this->patient1->can('delete', $this->patientMedicine));
    }

    /**
     * Test: PATIENT cannot delete ADMIN medicine
     */
    public function test_patient_cannot_delete_admin_medicine()
    {
        $this->assertFalse($this->patient1->can('delete', $this->adminMedicine));
    }

    /**
     * Test: PATIENT cannot delete other patient's medicine
     */
    public function test_patient_cannot_delete_other_patient_medicine()
    {
        $otherPatientMedicine = Medicine::create([
            'name' => 'Other Patient Medicine',
            'dose' => '500mg',
            'unit' => 'tablet',
            'source_type' => 'PATIENT',
            'user_id' => $this->patient2->id,
        ]);

        $this->assertFalse($this->patient1->can('delete', $otherPatientMedicine));
    }

    // ==================== MEDICATION SCHEDULE POLICY TESTS ====================

    /**
     * Test: Only ADMIN can create medication schedules
     */
    public function test_only_admin_can_create_schedule()
    {
        $this->assertTrue($this->admin->can('create', MedicationSchedule::class));
        $this->assertFalse($this->patient1->can('create', MedicationSchedule::class));
        $this->assertFalse($this->patient2->can('create', MedicationSchedule::class));
    }

    /**
     * Test: ADMIN can view all schedules
     */
    public function test_admin_can_view_all_schedules()
    {
        $this->assertTrue($this->admin->can('view', $this->adminSchedule));
    }

    /**
     * Test: PATIENT can view own schedules
     */
    public function test_patient_can_view_own_schedules()
    {
        $this->assertTrue($this->patient1->can('view', $this->adminSchedule));
    }

    /**
     * Test: PATIENT cannot view other patient's schedules
     */
    public function test_patient_cannot_view_other_patient_schedules()
    {
        $this->assertFalse($this->patient2->can('view', $this->adminSchedule));
    }

    /**
     * Test: ADMIN can update any schedule
     */
    public function test_admin_can_update_any_schedule()
    {
        $this->assertTrue($this->admin->can('update', $this->adminSchedule));
    }

    /**
     * Test: PATIENT cannot update ADMIN schedule
     */
    public function test_patient_cannot_update_admin_schedule()
    {
        $this->assertFalse($this->patient1->can('update', $this->adminSchedule));
    }

    /**
     * Test: ADMIN can delete any schedule
     */
    public function test_admin_can_delete_any_schedule()
    {
        $this->assertTrue($this->admin->can('delete', $this->adminSchedule));
    }

    /**
     * Test: PATIENT cannot delete ADMIN schedule
     */
    public function test_patient_cannot_delete_admin_schedule()
    {
        $this->assertFalse($this->patient1->can('delete', $this->adminSchedule));
    }

    /**
     * Test: Both ADMIN and PATIENT can confirm intake
     */
    public function test_can_confirm_medication_intake()
    {
        // ADMIN can confirm for any patient
        $this->assertTrue($this->admin->can('confirmIntake', $this->adminSchedule));

        // PATIENT can confirm own medication
        $this->assertTrue($this->patient1->can('confirmIntake', $this->adminSchedule));

        // Another patient cannot confirm
        $this->assertFalse($this->patient2->can('confirmIntake', $this->adminSchedule));
    }

    /**
     * Test: Medicine has correct source_type
     */
    public function test_medicine_source_type_values()
    {
        $this->assertEquals('ADMIN', $this->adminMedicine->source_type);
        $this->assertEquals('PATIENT', $this->patientMedicine->source_type);
    }

    /**
     * Test: Schedule has correct source_type
     */
    public function test_schedule_source_type_values()
    {
        $this->assertEquals('ADMIN', $this->adminSchedule->source_type);
    }

    /**
     * Test: Model scopes work correctly
     */
    public function test_model_scopes()
    {
        $adminMedicines = Medicine::adminMedicines()->pluck('id')->toArray();
        $this->assertContains($this->adminMedicine->id, $adminMedicines);
        $this->assertNotContains($this->patientMedicine->id, $adminMedicines);

        $patientMedicines = Medicine::userMedicines($this->patient1->id)->pluck('id')->toArray();
        $this->assertContains($this->patientMedicine->id, $patientMedicines);
        $this->assertNotContains($this->adminMedicine->id, $patientMedicines);

        $adminSchedules = MedicationSchedule::adminSchedules()->pluck('id')->toArray();
        $this->assertContains($this->adminSchedule->id, $adminSchedules);
    }
}
