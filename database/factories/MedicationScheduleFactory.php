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
    public function definition(): array
    {
        $this->faker = \Faker\Factory::create('id_ID');

        $startDate = $this->faker->dateTimeBetween('-3 months', 'now');
        $durationDays = $this->faker->numberBetween(5, 90);
        
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'medicine_id' => Medicine::inRandomOrder()->first()->id ?? Medicine::factory(),
            'start_date' => $startDate,
            'end_date' => $this->faker->dateTimeBetween($startDate, '+3 months'),
            'time' => $this->faker->randomElement([
                '08:00',
                '12:00',
                '18:00',
                '08:00,14:00',
                '08:00,14:00,20:00',
                '07:00,19:00',
            ]),
            'frequency' => $this->faker->randomElement(['sekali sehari', 'dua kali sehari', 'tiga kali sehari', 'sesuai kebutuhan']),
            'duration_days' => $durationDays,
            'source' => $this->faker->randomElement(['resep', 'mandiri']),
            'source_type' => 'ADMIN',
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
        ];
    }

    /**
     * Create an active schedule
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create an inactive schedule
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a schedule for today
     */
    public function forToday(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->startOfDay(),
            'end_date' => now()->addDays(30),
        ]);
    }
}
