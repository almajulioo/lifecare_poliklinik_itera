<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medicine;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        // Create realistic Indonesian medicines
        $medicines = [
            // Analgesic & Antipyretic
            ['name' => 'Paracetamol', 'dose' => '500', 'unit' => 'mg', 'notes' => 'Penurun demam dan penghilang rasa sakit', 'source_type' => 'ADMIN'],
            ['name' => 'Ibuprofen', 'dose' => '400', 'unit' => 'mg', 'notes' => 'Anti-inflamasi dan penghilang rasa sakit', 'source_type' => 'ADMIN'],
            
            // Antibiotics
            ['name' => 'Amoxicillin', 'dose' => '500', 'unit' => 'mg', 'notes' => 'Antibiotik untuk infeksi bakteri', 'source_type' => 'ADMIN'],
            ['name' => 'Cefadroxil', 'dose' => '500', 'unit' => 'mg', 'notes' => 'Antibiotik sefalosporin', 'source_type' => 'ADMIN'],
            ['name' => 'Ciprofloxacin', 'dose' => '500', 'unit' => 'mg', 'notes' => 'Antibiotik fluoroquinolone', 'source_type' => 'ADMIN'],
            
            // Diabetes Medication
            ['name' => 'Metformin', 'dose' => '500', 'unit' => 'mg', 'notes' => 'Obat diabetes tipe 2', 'source_type' => 'ADMIN'],
            ['name' => 'Glibenclamide', 'dose' => '5', 'unit' => 'mg', 'notes' => 'Obat diabetes sulfonylurea', 'source_type' => 'ADMIN'],
            
            // Cholesterol & Cardiovascular
            ['name' => 'Atorvastatin', 'dose' => '10', 'unit' => 'mg', 'notes' => 'Penurun kolesterol', 'source_type' => 'ADMIN'],
            ['name' => 'Simvastatin', 'dose' => '20', 'unit' => 'mg', 'notes' => 'Statin untuk kolesterol', 'source_type' => 'ADMIN'],
            ['name' => 'Lisinopril', 'dose' => '10', 'unit' => 'mg', 'notes' => 'ACE inhibitor untuk hipertensi', 'source_type' => 'ADMIN'],
            ['name' => 'Amlodipine', 'dose' => '5', 'unit' => 'mg', 'notes' => 'Calcium channel blocker', 'source_type' => 'ADMIN'],
            ['name' => 'Valsartan', 'dose' => '80', 'unit' => 'mg', 'notes' => 'ARB untuk tekanan darah', 'source_type' => 'ADMIN'],
            
            // GI Medications
            ['name' => 'Omeprazole', 'dose' => '20', 'unit' => 'mg', 'notes' => 'Proton pump inhibitor untuk asam lambung', 'source_type' => 'ADMIN'],
            ['name' => 'Ranitidine', 'dose' => '150', 'unit' => 'mg', 'notes' => 'H2 blocker untuk asam lambung', 'source_type' => 'ADMIN'],
            ['name' => 'Famotidine', 'dose' => '20', 'unit' => 'mg', 'notes' => 'H2 receptor antagonist', 'source_type' => 'ADMIN'],
            
            // Vitamins & Supplements
            ['name' => 'Vitamin C', 'dose' => '1000', 'unit' => 'mg', 'notes' => 'Vitamin C untuk imunitas', 'source_type' => 'ADMIN'],
            ['name' => 'Vitamin D3', 'dose' => '1000', 'unit' => 'IU', 'notes' => 'Vitamin D untuk kesehatan tulang', 'source_type' => 'ADMIN'],
            ['name' => 'Vitamin B12', 'dose' => '1000', 'unit' => 'mcg', 'notes' => 'Vitamin B12 untuk energi', 'source_type' => 'ADMIN'],
            ['name' => 'Multivitamin', 'dose' => '1', 'unit' => 'tablet', 'notes' => 'Suplemen multivitamin harian', 'source_type' => 'ADMIN'],
            ['name' => 'Kalsium Karbonat', 'dose' => '500', 'unit' => 'mg', 'notes' => 'Kalsium untuk kesehatan tulang', 'source_type' => 'ADMIN'],
            ['name' => 'Magnesium', 'dose' => '200', 'unit' => 'mg', 'notes' => 'Magnesium untuk relaksasi otot', 'source_type' => 'ADMIN'],
            ['name' => 'Zinc', 'dose' => '20', 'unit' => 'mg', 'notes' => 'Zinc untuk imunitas', 'source_type' => 'ADMIN'],
            
            // Heart Protection
            ['name' => 'Aspirin', 'dose' => '100', 'unit' => 'mg', 'notes' => 'Aspirin untuk pencegahan jantung', 'source_type' => 'ADMIN'],
            ['name' => 'Clopidogrel', 'dose' => '75', 'unit' => 'mg', 'notes' => 'Antiplatelet untuk jantung', 'source_type' => 'ADMIN'],
            
            // Respiratory & Allergy
            ['name' => 'Loratadine', 'dose' => '10', 'unit' => 'mg', 'notes' => 'Antihistamin untuk alergi', 'source_type' => 'ADMIN'],
            ['name' => 'Cetirizine', 'dose' => '10', 'unit' => 'mg', 'notes' => 'Antihistamin generasi kedua', 'source_type' => 'ADMIN'],
            ['name' => 'Salbutamol', 'dose' => '100', 'unit' => 'mcg', 'notes' => 'Bronkodilator untuk asma', 'source_type' => 'ADMIN'],
            
            // Pain & Inflammation
            ['name' => 'Piroksikam', 'dose' => '20', 'unit' => 'mg', 'notes' => 'NSAID untuk peradangan', 'source_type' => 'ADMIN'],
            ['name' => 'Diclofenac', 'dose' => '50', 'unit' => 'mg', 'notes' => 'NSAID untuk nyeri dan peradangan', 'source_type' => 'ADMIN'],
            ['name' => 'Meloxicam', 'dose' => '15', 'unit' => 'mg', 'notes' => 'NSAID dengan efek samping lebih rendah', 'source_type' => 'ADMIN'],
            
            // Sexual Health
            ['name' => 'Sildenafil', 'dose' => '50', 'unit' => 'mg', 'notes' => 'Obat untuk disfungsi ereksi', 'source_type' => 'ADMIN'],
            
            // Thyroid
            ['name' => 'Levothyroxine', 'dose' => '100', 'unit' => 'mcg', 'notes' => 'Hormon tiroid sintetis', 'source_type' => 'ADMIN'],
            
            // Respiratory Infection
            ['name' => 'Ambroxol', 'dose' => '30', 'unit' => 'mg', 'notes' => 'Ekspektoran untuk batuk', 'source_type' => 'ADMIN'],
            ['name' => 'Dextromethorphan', 'dose' => '15', 'unit' => 'mg', 'notes' => 'Antitusif untuk batuk kering', 'source_type' => 'ADMIN'],
        ];

        foreach ($medicines as $medicine) {
            Medicine::updateOrCreate(
                ['name' => $medicine['name']],
                $medicine
            );
        }

        // Create additional randomized medicines using factory (50 more)
        Medicine::factory(50)->create();
    }
}