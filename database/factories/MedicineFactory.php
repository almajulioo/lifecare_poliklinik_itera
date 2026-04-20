<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Medicine>
 */
class MedicineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word() . ' Medicine',
            'dose' => $this->faker->numerify('##'),
            'unit' => $this->faker->randomElement(['mg', 'ml', 'tablet', 'capsule']),
            'notes' => $this->faker->sentence(),
            'source_type' => $this->faker->randomElement(['ADMIN', 'PATIENT']),
            'user_id' => null,
        ];
    }

    /**
     * Indicate that the medicine belongs to a patient
     */
    public function forPatient(User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => 'PATIENT',
            'user_id' => $user?->id ?? User::factory(),
        ]);
    }

    /**
     * Indicate that the medicine is admin-managed
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => 'ADMIN',
            'user_id' => null,
        ]);
    }
}
