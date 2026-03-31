<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClinicPatient>
 */
class ClinicPatientFactory extends Factory
{
    public function definition(): array
    {
        $this->faker = \Faker\Factory::create('id_ID');

        $category = $this->faker->randomElement(['mahasiswa', 'pegawai', 'umum']);
        
        // Generate NIM untuk mahasiswa dengan format: 1(2 digit tahun)(2 digit 01-99)(4 digit random)
        // Contoh: 122140162 = 1 + 22 + 14 + 0162
        $identityNumber = null;
        if ($category === 'mahasiswa') {
            $year = now()->year;
            $yearDigits = str_pad($year % 100, 2, '0', STR_PAD_LEFT); // 2 digit tahun (22 untuk 2022)
            $sequenceNumber = str_pad($this->faker->numberBetween(1, 99), 2, '0', STR_PAD_LEFT); // 01-99
            $randomDigits = str_pad($this->faker->numberBetween(0, 9999), 4, '0', STR_PAD_LEFT); // 0000-9999
            $identityNumber = '1' . $yearDigits . $sequenceNumber . $randomDigits;
        }

        return [
            'user_id' => $this->faker->boolean(70) ? User::inRandomOrder()->first()?->id : null, // 70% linked to app user
            'name' => $this->faker->name(),
            'identity_number' => $identityNumber,
            'category' => $category,
            'phone' => $this->faker->regexify('08[1-9]\d{7,8}'),
            'email' => $this->faker->bothify('?##-????-##@patient.id'),
            'status' => $this->faker->randomElement(['aktif', 'tidak_aktif']),
        ];
    }

    /**
     * Create an active clinic patient
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'aktif',
        ]);
    }

    /**
     * Create an inactive clinic patient
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'tidak_aktif',
        ]);
    }

    /**
     * Create a clinic patient linked to an app user
     */
    public function withAppUser(User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user?->id ?? User::factory(),
        ]);
    }

    /**
     * Create a clinic patient without app user
     */
    public function withoutAppUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }
}
