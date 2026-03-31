<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\MedicationSchedule;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NotificationLog>
 */
class NotificationLogFactory extends Factory
{
    public function definition(): array
    {
        $this->faker = \Faker\Factory::create('id_ID');

        $scheduledTime = $this->faker->dateTimeBetween('-7 days', 'now');
        $status = $this->faker->randomElement(['sent', 'snoozed', 'dismissed', 'pending']);
        
        // Create Carbon instance if needed
        $scheduledTimeCopy = \Carbon\Carbon::instance($scheduledTime);
        
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'medication_schedule_id' => MedicationSchedule::inRandomOrder()->first()->id ?? MedicationSchedule::factory(),
            'scheduled_time' => $scheduledTime,
            'sent_at' => $status !== 'pending' 
                ? $scheduledTimeCopy->addMinutes(rand(1, 60))
                : null,
            'status' => $status,
            'snooze_minutes' => $status === 'snoozed'
                ? $this->faker->randomElement([5, 10, 15, 30, 60])
                : null,
            'notification_type' => $this->faker->randomElement(['browser', 'email', 'sms']),
            'device_info' => json_encode([
                'user_agent' => $this->faker->userAgent(),
                'device' => $this->faker->randomElement(['mobile', 'desktop', 'tablet']),
                'os' => $this->faker->randomElement(['Android', 'iOS', 'Windows', 'MacOS', 'Linux']),
            ]),
        ];
    }

    /**
     * Create a sent notification
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'sent_at' => now(),
            'snooze_minutes' => null,
        ]);
    }

    /**
     * Create a snoozed notification
     */
    public function snoozed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'snoozed',
            'snooze_minutes' => $this->faker->randomElement([5, 10, 15, 30]),
        ]);
    }

    /**
     * Create a dismissed notification
     */
    public function dismissed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'dismissed',
            'snooze_minutes' => null,
        ]);
    }

    /**
     * Create a failed/pending notification
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'sent_at' => null,
            'snooze_minutes' => null,
        ]);
    }
}
