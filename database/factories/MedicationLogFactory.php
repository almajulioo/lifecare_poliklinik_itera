<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\MedicationSchedule;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MedicationLog>
 */
class MedicationLogFactory extends Factory
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
            'medication_schedule_id' => MedicationSchedule::factory(),
            'taken_at' => null,
            'status' => $this->faker->randomElement(['pending', 'taken', 'missed']),
            'offline_synced' => false,
        ];
    }

    /**
     * Indicate that the log is for a specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the medication was taken
     */
    public function taken(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'taken',
            'taken_at' => now(),
        ]);
    }

    /**
     * Indicate that the medication was missed
     */
    public function missed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'missed',
        ]);
    }

    /**
     * Indicate that the log is pending
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
}
