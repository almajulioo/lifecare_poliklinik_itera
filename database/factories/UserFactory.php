<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        // Set locale to Indonesian
        $this->faker = \Faker\Factory::create('id_ID');

        $prodis = [
            'Teknik Informatika',
            'Teknik Elektro',
            'Teknik Mesin',
            'Ilmu Komputer',
            'Sistem Informasi',
            'Manajemen Informatika',
            'Biologi',
            'Kimia',
            'Fisika',
            'Matematika',
        ];

        $role = $this->faker->randomElement(['mahasiswa', 'pegawai']);
        
        // Generate NIM berdasarkan role
        $nim = null;
        if ($role === 'mahasiswa') {
            // Format mahasiswa: 1(2 digit tahun)(2 digit 01-99)(4 digit random)
            // Contoh: 122140162
            $year = now()->year;
            $yearDigits = str_pad($year % 100, 2, '0', STR_PAD_LEFT); // 2 digit tahun (22 untuk 2022)
            $sequenceNumber = str_pad($this->faker->numberBetween(1, 99), 2, '0', STR_PAD_LEFT); // 01-99
            $randomDigits = str_pad($this->faker->numberBetween(0, 9999), 4, '0', STR_PAD_LEFT); // 0000-9999
            $nim = '1' . $yearDigits . $sequenceNumber . $randomDigits;
        }

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'nim' => $nim,
            'prodi' => $this->faker->randomElement($prodis),
            'role_user' => $role,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password123'),
            'notification_preferences' => [
                'enable_reminders' => true,
                'reminder_time_before' => 15,
                'do_not_disturb_start' => '22:00',
                'do_not_disturb_end' => '07:00',
            ],
            'timezone' => 'Asia/Jakarta',
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create an admin user
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_user' => 'pegawai',
        ]);
    }
}
