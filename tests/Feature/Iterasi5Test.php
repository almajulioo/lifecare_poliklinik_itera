<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Medicine;
use App\Models\MedicationSchedule;
use App\Models\MedicationLog;
use App\Models\NotificationLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

class Iterasi5Test extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function pasien_mendapatkan_notifikasi_obat()
    {
        $user = User::factory()->create();

        $medicine = Medicine::factory()->create();

        $schedule = MedicationSchedule::factory()->create([
            'user_id' => $user->id,
            'medicine_id' => $medicine->id,
            'time' => now()->subMinutes(10)->format('H:i'), // sudah lewat
            'is_active' => true,
            'start_date' => today(),
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/due-medications');

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    /** @test */
    public function pasien_konfirmasi_minum_obat()
    {
        $user = User::factory()->create();

        $medicine = Medicine::factory()->create();

        $schedule = MedicationSchedule::factory()->create([
            'user_id' => $user->id,
            'medicine_id' => $medicine->id,
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/medication-taken', [
            'medication_schedule_id' => $schedule->id,
        ]);

        $this->assertDatabaseHas('medication_logs', [
            'user_id' => $user->id,
            'medication_schedule_id' => $schedule->id,
            'status' => 'taken'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    /** @test */
    public function sistem_memberikan_pengingat_kedua_jika_belum_konfirmasi()
    {
        $user = User::factory()->create();

        $medicine = Medicine::factory()->create();

        $schedule = MedicationSchedule::factory()->create([
            'user_id' => $user->id,
            'medicine_id' => $medicine->id,
        ]);

        // Simulasi log belum diminum
        MedicationLog::create([
            'user_id' => $user->id,
            'medication_schedule_id' => $schedule->id,
            'status' => 'pending',
        ]);

        NotificationLog::create([
            'user_id' => $user->id,
            'medication_schedule_id' => $schedule->id,
            'scheduled_time' => now()->toDateTimeString(),
            'sent_at' => now()->subMinutes(30),
            'status' => 'sent',
            'notification_type' => 'browser',
            'reminder_number' => 1,
            'second_reminder_at' => now()->subMinute(),
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/second-reminders');

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }
}