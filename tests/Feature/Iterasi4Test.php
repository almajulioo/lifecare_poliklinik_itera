<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Medicine;
use App\Models\MedicationSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

class Iterasi4Test extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin dapat mengupdate jadwal konsumsi obat
     */
    public function test_admin_can_update_schedule()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $medicine = Medicine::factory()->create();

        $schedule = MedicationSchedule::factory()->create([
            'user_id' => $user->id,
            'medicine_id' => $medicine->id,
        ]);

        $response = $this->actingAs($admin, 'admin')->put('/admin/schedules/' . $schedule->id, [
            'user_id' => $user->id,
            'medicine_id' => $medicine->id,
            'start_date' => now()->format('Y-m-d'),
            'time' => '08:00',
            'source' => 'resep',
        ]);

        $response->assertStatus(302); // redirect success

        $this->assertDatabaseHas('medication_schedules', [
            'id' => $schedule->id,
            'time' => '08:00',
            'user_id' => $user->id,
            'medicine_id' => $medicine->id,
        ]);
    }

    /**
     * Test admin dapat menghapus jadwal konsumsi obat
     */
    public function test_admin_can_delete_schedule()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $medicine = Medicine::factory()->create();

        $schedule = MedicationSchedule::factory()->create([
            'user_id' => $user->id,
            'medicine_id' => $medicine->id,
        ]);

        $response = $this->actingAs($admin, 'admin')->delete('/admin/schedules/' . $schedule->id);

        $response->assertStatus(302);
        $this->assertDatabaseMissing('medication_schedules', [
            'id' => $schedule->id,
        ]);
    }

    /**
     * Test pasien dapat melihat jadwal konsumsi obat
     */
    public function test_user_can_view_schedule()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/app/schedules');

        $response->assertStatus(200);
    }
}