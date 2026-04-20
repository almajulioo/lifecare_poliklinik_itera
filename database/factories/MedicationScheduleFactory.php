<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Medicine;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MedicationSchedule>
 */
class MedicationScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'medicine_id' => Medicine::factory(),
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'time' => $this->faker->time('H:i'),
            'frequency' => $this->faker->randomElement(['daily', 'twice_daily', 'three_times_daily']),
            'source_type' => $this->faker->randomElement(['ADMIN', 'PATIENT']),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the schedule is for a specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the schedule is admin-managed
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => 'ADMIN',
        ]);
    }

    /**
     * Indicate that the schedule is inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
