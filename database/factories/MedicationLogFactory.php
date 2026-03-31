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
    public function definition(): array
    {
        $this->faker = \Faker\Factory::create('id_ID');

        $status = $this->faker->randomElement(['taken', 'missed', 'pending']);
        $user = User::inRandomOrder()->first() ?? User::factory();
        
        return [
            'user_id' => $user->id,
            'medication_schedule_id' => MedicationSchedule::where('user_id', $user->id)->inRandomOrder()->first()->id ?? MedicationSchedule::factory(),
            'status' => $status,
            'taken_at' => $status === 'taken' 
                ? $this->faker->dateTimeBetween('-7 days', 'now')
                : null,
            'note' => $status === 'missed' 
                ? $this->faker->randomElement([
                    'Lupa minum obat',
                    'Sedang di luar rumah',
                    'Stok obat habis',
                    'Lupa bawa obat',
                    'Sakit perut',
                ])
                : ($status === 'taken'
                    ? $this->faker->randomElement([
                        'Diminum dengan baik',
                        'Diminum setelah makan',
                        'Diminum sesuai jadwal',
                        null,
                        null,
                    ])
                    : null),
            'offline_synced' => $this->faker->boolean(70),
            'offline_synced_at' => $this->faker->boolean(70) 
                ? $this->faker->dateTimeBetween('-7 days', 'now')
                : null,
            'sync_metadata' => null,
        ];
    }

    /**
     * Create a taken log
     */
    public function taken(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'taken',
            'taken_at' => now(),
            'note' => null,
        ]);
    }

    /**
     * Create a skipped log
     */
    public function skipped(): static
    {
        $this->faker = \Faker\Factory::create('id_ID');
        return $this->state(fn (array $attributes) => [
            'status' => 'missed',
            'taken_at' => null,
            'note' => $this->faker->randomElement([
                'Lupa minum obat',
                'Sedang di luar rumah',
                'Stok obat habis',
            ]),
        ]);
    }

    /**
     * Create a pending log
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'taken_at' => null,
            'note' => null,
        ]);
    }

    /**
     * Create today's logs
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => now(),
        ]);
    }
}
