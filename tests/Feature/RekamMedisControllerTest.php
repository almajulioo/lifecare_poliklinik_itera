<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Medicine;
use App\Models\MedicationSchedule;
use App\Models\MedicationLog;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RekamMedisControllerTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin
        $this->admin = Admin::create([
            'email' => 'admin@test.local',
            'password' => bcrypt('password123'),
        ]);
    }

    /** @test */
    public function rekam_medis_index_page_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.rekam-medis.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function rekam_medis_update_works()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role_user' => 'mahasiswa',
            'nim' => '12345678',
            'medical_conditions' => ['Diabetes'],
            'notes' => 'Original notes',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.rekam-medis.update', $user), [
                'medical_conditions' => ['Diabetes', 'Asma'],
                'notes' => 'Updated notes',
            ]);

        $response->assertRedirect(route('admin.rekam-medis.index'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertContains('Asma', $user->medical_conditions);
        $this->assertEquals('Updated notes', $user->notes);
    }

    /** @test */
    public function rekam_medis_edit_page_renders()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role_user' => 'mahasiswa',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.rekam-medis.edit', $user));

        $response->assertStatus(200);
        $response->assertSee('Edit Rekam Medis');
    }

    /** @test */
    public function rekam_medis_view_handles_medication_schedules_correctly()
    {
        // This test verifies that the view uses $schedule->medicine->dose (not $schedule->dosage)
        $user = User::create([
            'name' => 'Patient A',
            'email' => 'patient@test.com',
            'password' => bcrypt('password'),
            'role_user' => 'mahasiswa',
        ]);

        $medicine = Medicine::create([
            'name' => 'TestMedicine',
            'dose' => '500mg',
            'unit' => 'tablet',
            'source_type' => 'ADMIN',
        ]);

        $schedule = MedicationSchedule::create([
            'user_id' => $user->id,
            'medicine_id' => $medicine->id,
            'start_date' => now(),
            'time' => '08:00',
            'frequency' => '1x sehari',
            'source_type' => 'ADMIN',
            'is_active' => true,
        ]);

        // Just load the page - if it errors on dosage field, it will show 500 error
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.rekam-medis.index', ['user_id' => $user->id]));

        $response->assertStatus(200);
        $response->assertSee('TestMedicine');
        $response->assertSee('500mg');
    }

    /** @test */
    public function rekam_medis_view_handles_medication_logs_correctly()
    {
        // This test verifies that the view uses $log->status (not $log->is_taken)
        $user = User::create([
            'name' => 'Patient B',
            'email' => 'patient2@test.com',
            'password' => bcrypt('password'),
            'role_user' => 'mahasiswa',
        ]);

        $medicine = Medicine::create([
            'name' => 'TestMedicine2',
            'dose' => '250mg',
            'unit' => 'tablet',
            'source_type' => 'ADMIN',
        ]);

        $schedule = MedicationSchedule::create([
            'user_id' => $user->id,
            'medicine_id' => $medicine->id,
            'start_date' => now(),
            'time' => '09:00',
            'frequency' => '2x sehari',
            'source_type' => 'ADMIN',
            'is_active' => true,
        ]);

        MedicationLog::create([
            'user_id' => $user->id,
            'medication_schedule_id' => $schedule->id,
            'status' => 'taken',
        ]);

        MedicationLog::create([
            'user_id' => $user->id,
            'medication_schedule_id' => $schedule->id,
            'status' => 'missed',
        ]);

        // Just load the page - if it errors on is_taken field, it will show 500 error
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.rekam-medis.index', ['user_id' => $user->id]));

        $response->assertStatus(200);
    }

    /** @test */
    public function create_route_does_not_exist()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/rekam-medis/create');

        $response->assertStatus(404);
    }
}
