<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClinicPatient>
 */
class ClinicPatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'identity_number' => $this->faker->unique()->numerify('################'),
            'category' => $this->faker->randomElement(['outpatient', 'inpatient']),
            'status' => $this->faker->randomElement(['active', 'inactive', 'archived']),
        ];
    }

    /**
     * Indicate that the clinic patient is linked to a user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the clinic patient is active
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }
}
