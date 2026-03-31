<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Medicine>
 */
class MedicineFactory extends Factory
{
    public function definition(): array
    {
        // Set locale to Indonesian
        $this->faker = \Faker\Factory::create('id_ID');

        $medicines = [
            ['name' => 'Paracetamol', 'dose' => '500', 'unit' => 'mg'],
            ['name' => 'Ibuprofen', 'dose' => '400', 'unit' => 'mg'],
            ['name' => 'Amoxicillin', 'dose' => '500', 'unit' => 'mg'],
            ['name' => 'Cefadroxil', 'dose' => '500', 'unit' => 'mg'],
            ['name' => 'Metformin', 'dose' => '500', 'unit' => 'mg'],
            ['name' => 'Atorvastatin', 'dose' => '10', 'unit' => 'mg'],
            ['name' => 'Lisinopril', 'dose' => '10', 'unit' => 'mg'],
            ['name' => 'Amlodipine', 'dose' => '5', 'unit' => 'mg'],
            ['name' => 'Omeprazole', 'dose' => '20', 'unit' => 'mg'],
            ['name' => 'Vitamin C', 'dose' => '1000', 'unit' => 'mg'],
            ['name' => 'Vitamin B12', 'dose' => '1000', 'unit' => 'mcg'],
            ['name' => 'Kalsium Karbonat', 'dose' => '500', 'unit' => 'mg'],
            ['name' => 'Zinc', 'dose' => '20', 'unit' => 'mg'],
            ['name' => 'Aspirin', 'dose' => '100', 'unit' => 'mg'],
            ['name' => 'Sildenafil', 'dose' => '50', 'unit' => 'mg'],
            ['name' => 'Loratadine', 'dose' => '10', 'unit' => 'mg'],
            ['name' => 'Cetirizine', 'dose' => '10', 'unit' => 'mg'],
            ['name' => 'Ranitidine', 'dose' => '150', 'unit' => 'mg'],
            ['name' => 'Piroksikam', 'dose' => '20', 'unit' => 'mg'],
            ['name' => 'Diclofenac', 'dose' => '50', 'unit' => 'mg'],
        ];

        $medicine = $this->faker->randomElement($medicines);

        return [
            'name' => $medicine['name'],
            'dose' => $medicine['dose'],
            'unit' => $medicine['unit'],
            'notes' => $this->faker->randomElement([
                'Diminum sesudah makan',
                'Diminum sebelum makan',
                'Diminum dengan air putih',
                'Diminum 3x sehari',
                'Diminum sesuai resep dokter',
                'Jangan dikonsumsi bersamaan dengan alkohol',
                'Dapat menyebabkan mengantuk',
                'Hati-hati jika menyetir',
            ]),
            'user_id' => null, // Admin medicines by default
            'source_type' => 'ADMIN',
        ];
    }

    /**
     * Create a medicine for a specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'source_type' => 'PATIENT',
        ]);
    }
}
