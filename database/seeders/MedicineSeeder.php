<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medicine;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        // Data obat untuk poliklinik universitas - Obat yang sering digunakan
        $medicines = [
            // ============ ANALGESIK & ANTIPIRETIK ============
            ['name' => 'Paracetamol', 'dose' => '500', 'unit' => 'mg', 'notes' => 'Penurun demam dan penghilang rasa sakit - Obat lini pertama', 'source_type' => 'ADMIN'],
            ['name' => 'Ibuprofen', 'dose' => '400', 'unit' => 'mg', 'notes' => 'Anti-inflamasi dan penghilang rasa sakit untuk nyeri ringan-sedang', 'source_type' => 'ADMIN'],
            ['name' => 'Diclofenac', 'dose' => '50', 'unit' => 'mg', 'notes' => 'NSAID untuk nyeri dan peradangan yang lebih kuat', 'source_type' => 'ADMIN'],
            ['name' => 'Meloxicam', 'dose' => '15', 'unit' => 'mg', 'notes' => 'NSAID dengan efek samping GI lebih rendah', 'source_type' => 'ADMIN'],
            
            // ============ ANTIBIOTIK ============
            ['name' => 'Amoxicillin', 'dose' => '500', 'unit' => 'mg', 'notes' => 'Antibiotik beta-laktam untuk infeksi bakteri ringan-sedang', 'source_type' => 'ADMIN'],
            ['name' => 'Cefadroxil', 'dose' => '500', 'unit' => 'mg', 'notes' => 'Sefalosporin generasi pertama untuk infeksi kulit dan saluran kemih', 'source_type' => 'ADMIN'],
            ['name' => 'Ciprofloxacin', 'dose' => '500', 'unit' => 'mg', 'notes' => 'Fluoroquinolone untuk infeksi saluran kemih dan pencernaan', 'source_type' => 'ADMIN'],
            ['name' => 'Azithromycin', 'dose' => '500', 'unit' => 'mg', 'notes' => 'Macrolide untuk infeksi pernafasan dan kulit', 'source_type' => 'ADMIN'],
            ['name' => 'Clindamycin', 'dose' => '300', 'unit' => 'mg', 'notes' => 'Antibiotik untuk infeksi anaerob dan kulit', 'source_type' => 'ADMIN'],
            
            // ============ ANTIVIRUS & ANTIPIRETIK ============
            ['name' => 'Oseltamivir', 'dose' => '75', 'unit' => 'mg', 'notes' => 'Antivirus untuk influenza jika diberikan dalam 48 jam', 'source_type' => 'ADMIN'],
            
            // ============ OBAT LAMBUNG & PENCERNAAN ============
            ['name' => 'Omeprazole', 'dose' => '20', 'unit' => 'mg', 'notes' => 'Proton pump inhibitor untuk GERD dan tukak lambung', 'source_type' => 'ADMIN'],
            ['name' => 'Ranitidine', 'dose' => '150', 'unit' => 'mg', 'notes' => 'H2 blocker untuk asam lambung dan gastritis', 'source_type' => 'ADMIN'],
            ['name' => 'Antasida (Al Mg OH)', 'dose' => '500', 'unit' => 'mg', 'notes' => 'Antasida untuk penghilang cepat gejala maag', 'source_type' => 'ADMIN'],
            ['name' => 'Metoclopramide', 'dose' => '10', 'unit' => 'mg', 'notes' => 'Pro-kinetik untuk mual dan gangguan pencernaan', 'source_type' => 'ADMIN'],
            ['name' => 'Loperamide', 'dose' => '2', 'unit' => 'mg', 'notes' => 'Antidiarhe untuk diare non-spesifik', 'source_type' => 'ADMIN'],
            ['name' => 'Bismuth Subsalicylate', 'dose' => '525', 'unit' => 'mg', 'notes' => 'Obat diare dengan efek antimikroba', 'source_type' => 'ADMIN'],
            
            // ============ ANTIHISTAMIN & ALERGI ============
            ['name' => 'Loratadine', 'dose' => '10', 'unit' => 'mg', 'notes' => 'Antihistamin generasi kedua untuk alergi tanpa mengantuk', 'source_type' => 'ADMIN'],
            ['name' => 'Cetirizine', 'dose' => '10', 'unit' => 'mg', 'notes' => 'Antihistamin untuk gejala alergi ringan-sedang', 'source_type' => 'ADMIN'],
            ['name' => 'Chlorpheniramine', 'dose' => '4', 'unit' => 'mg', 'notes' => 'Antihistamin generasi pertama dengan efek sedasi', 'source_type' => 'ADMIN'],
            ['name' => 'Diphenhydramine', 'dose' => '25', 'unit' => 'mg', 'notes' => 'Antihistamin untuk alergi akut dan gangguan tidur', 'source_type' => 'ADMIN'],
            
            // ============ OBAT PERNAPASAN ============
            ['name' => 'Salbutamol', 'dose' => '100', 'unit' => 'mcg', 'notes' => 'Bronkodilator untuk asma dan PPOK - Rescue inhaler', 'source_type' => 'ADMIN'],
            ['name' => 'Ipratropium Bromide', 'dose' => '20', 'unit' => 'mcg', 'notes' => 'Anticholinergic untuk asma dan PPOK', 'source_type' => 'ADMIN'],
            ['name' => 'Ambroxol', 'dose' => '30', 'unit' => 'mg', 'notes' => 'Ekspektoran untuk batuk produktif', 'source_type' => 'ADMIN'],
            ['name' => 'Dextromethorphan', 'dose' => '15', 'unit' => 'mg', 'notes' => 'Antitusif untuk batuk kering', 'source_type' => 'ADMIN'],
            ['name' => 'Guaifenesin', 'dose' => '200', 'unit' => 'mg', 'notes' => 'Ekspektoran untuk membantu mengeluarkan lendir', 'source_type' => 'ADMIN'],
            
            // ============ OBAT KARDIOVASKULAR & HIPERTENSI ============
            ['name' => 'Amlodipine', 'dose' => '5', 'unit' => 'mg', 'notes' => 'Calcium channel blocker untuk hipertensi', 'source_type' => 'ADMIN'],
            ['name' => 'Lisinopril', 'dose' => '10', 'unit' => 'mg', 'notes' => 'ACE inhibitor untuk hipertensi dan gagal jantung', 'source_type' => 'ADMIN'],
            ['name' => 'Atenolol', 'dose' => '50', 'unit' => 'mg', 'notes' => 'Beta-blocker untuk hipertensi dan palpitasi jantung', 'source_type' => 'ADMIN'],
            ['name' => 'Valsartan', 'dose' => '80', 'unit' => 'mg', 'notes' => 'ARB untuk hipertensi', 'source_type' => 'ADMIN'],
            
            // ============ STATIN & LIPID-LOWERING ============
            ['name' => 'Atorvastatin', 'dose' => '10', 'unit' => 'mg', 'notes' => 'Statin untuk penurun kolesterol LDL', 'source_type' => 'ADMIN'],
            ['name' => 'Simvastatin', 'dose' => '20', 'unit' => 'mg', 'notes' => 'Statin untuk hiperkolesterolemia', 'source_type' => 'ADMIN'],
            
            // ============ OBAT JANTUNG ============
            ['name' => 'Aspirin', 'dose' => '100', 'unit' => 'mg', 'notes' => 'Antiplatelet untuk pencegahan penyakit jantung', 'source_type' => 'ADMIN'],
            ['name' => 'Clopidogrel', 'dose' => '75', 'unit' => 'mg', 'notes' => 'Antiplatelet untuk pencegahan trombosis', 'source_type' => 'ADMIN'],
            
            // ============ OBAT DIABETES ============
            ['name' => 'Metformin', 'dose' => '500', 'unit' => 'mg', 'notes' => 'Biguanid untuk diabetes tipe 2 - lini pertama', 'source_type' => 'ADMIN'],
            ['name' => 'Glibenclamide', 'dose' => '5', 'unit' => 'mg', 'notes' => 'Sulfonylurea untuk diabetes tipe 2', 'source_type' => 'ADMIN'],
            ['name' => 'Pioglitazone', 'dose' => '15', 'unit' => 'mg', 'notes' => 'Thiazolidinedione untuk sensitivitas insulin', 'source_type' => 'ADMIN'],
            
            // ============ HORMON TIROID ============
            ['name' => 'Levothyroxine', 'dose' => '100', 'unit' => 'mcg', 'notes' => 'Hormon tiroid sintetis untuk hipotiroidisme', 'source_type' => 'ADMIN'],
            
            // ============ VITAMIN & SUPLEMEN ============
            ['name' => 'Vitamin C', 'dose' => '1000', 'unit' => 'mg', 'notes' => 'Antioksidan untuk imunitas dan kesehatan umum', 'source_type' => 'ADMIN'],
            ['name' => 'Vitamin D3', 'dose' => '1000', 'unit' => 'IU', 'notes' => 'Vitamin D untuk kesehatan tulang dan imunitas', 'source_type' => 'ADMIN'],
            ['name' => 'Vitamin B12', 'dose' => '1000', 'unit' => 'mcg', 'notes' => 'Vitamin B12 untuk energi dan fungsi saraf', 'source_type' => 'ADMIN'],
            ['name' => 'Multivitamin', 'dose' => '1', 'unit' => 'tablet', 'notes' => 'Suplemen multivitamin kompleks harian', 'source_type' => 'ADMIN'],
            ['name' => 'Vitamin B Complex', 'dose' => '1', 'unit' => 'tablet', 'notes' => 'Vitamin B kompleks untuk metabolisme dan energi', 'source_type' => 'ADMIN'],
            
            // ============ MINERAL ============
            ['name' => 'Kalsium Karbonat', 'dose' => '500', 'unit' => 'mg', 'notes' => 'Suplemen kalsium untuk kesehatan tulang', 'source_type' => 'ADMIN'],
            ['name' => 'Magnesium', 'dose' => '200', 'unit' => 'mg', 'notes' => 'Magnesium untuk relaksasi otot dan tidur', 'source_type' => 'ADMIN'],
            ['name' => 'Zinc', 'dose' => '20', 'unit' => 'mg', 'notes' => 'Suplemen zinc untuk imunitas', 'source_type' => 'ADMIN'],
            ['name' => 'Besi (Fe)', 'dose' => '325', 'unit' => 'mg', 'notes' => 'Suplemen besi untuk anemia', 'source_type' => 'ADMIN'],
            
            // ============ OBAT INFEKSI JAMUR ============
            ['name' => 'Ketoconazole', 'dose' => '200', 'unit' => 'mg', 'notes' => 'Antifungal untuk infeksi jamur sistemik', 'source_type' => 'ADMIN'],
            ['name' => 'Fluconazole', 'dose' => '150', 'unit' => 'mg', 'notes' => 'Antifungal untuk kandidiasis dan infeksi jamur lainnya', 'source_type' => 'ADMIN'],
            ['name' => 'Miconazole', 'dose' => '2', 'unit' => '%', 'notes' => 'Antifungal topikal untuk infeksi kulit dan mukosa', 'source_type' => 'ADMIN'],
            
            // ============ OBAT ANTIPARASIT ============
            ['name' => 'Mebendazole', 'dose' => '500', 'unit' => 'mg', 'notes' => 'Antiparasit untuk cacing usus', 'source_type' => 'ADMIN'],
            ['name' => 'Albendazole', 'dose' => '400', 'unit' => 'mg', 'notes' => 'Antiparasit broad-spectrum untuk berbagai cacing', 'source_type' => 'ADMIN'],
            
            // ============ OBAT INFEKSI BAKTERI SPESIFIK ============
            ['name' => 'Metronidazole', 'dose' => '500', 'unit' => 'mg', 'notes' => 'Antiprotozoal dan antibiotik untuk infeksi anaerob', 'source_type' => 'ADMIN'],
            ['name' => 'Trimethoprim-Sulfamethoxazole', 'dose' => '480', 'unit' => 'mg', 'notes' => 'Antibiotik kombinasi untuk UTI dan infeksi lainnya', 'source_type' => 'ADMIN'],
            
            // ============ OBAT DEMAM BERDARAH ============
            ['name' => 'Platelet Concentrate', 'dose' => '1', 'unit' => 'unit', 'notes' => 'Transfusi platelet untuk DBD dengan perdarahan masif', 'source_type' => 'ADMIN'],
            
            // ============ OBAT ANTIINFLAMASI & ANTIPIRETIK KOMBINASI ============
            ['name' => 'Paracetamol + Ibuprofen', 'dose' => 'var', 'unit' => 'tablet', 'notes' => 'Kombinasi untuk nyeri dan demam yang lebih efektif', 'source_type' => 'ADMIN'],
            
            // ============ OBAT MENSTRUASI & KEJANG ============
            ['name' => 'Mefenamic Acid', 'dose' => '500', 'unit' => 'mg', 'notes' => 'NSAID untuk dismenore dan nyeri haid', 'source_type' => 'ADMIN'],
            
            // ============ OBAT ANTIMUAL & ANTIVERTIGO ============
            ['name' => 'Dramamine', 'dose' => '50', 'unit' => 'mg', 'notes' => 'Antihistamin untuk mual dan vertigo', 'source_type' => 'ADMIN'],
            ['name' => 'Scopolamine', 'dose' => '0.3', 'unit' => 'mg', 'notes' => 'Anticholinergic untuk mual dan motion sickness', 'source_type' => 'ADMIN'],
            
            // ============ OBAT KULIT LOKAL ============
            ['name' => 'Hydrocortisone Cream', 'dose' => '1', 'unit' => '%', 'notes' => 'Kortikosteroid topikal untuk dermatitis ringan', 'source_type' => 'ADMIN'],
            ['name' => 'Clotrimazole Cream', 'dose' => '1', 'unit' => '%', 'notes' => 'Antifungal topikal untuk panu dan infeksi jamur kulit', 'source_type' => 'ADMIN'],
            ['name' => 'Neomycin + Bacitracin', 'dose' => 'var', 'unit' => 'tube', 'notes' => 'Antibiotik topikal untuk luka minor dan infeksi kulit', 'source_type' => 'ADMIN'],
            ['name' => 'Benzoyl Peroxide', 'dose' => '2.5', 'unit' => '%', 'notes' => 'Obat jerawat antibakteri', 'source_type' => 'ADMIN'],
            
            // ============ OBAT STRESS & TIDUR ============
            ['name' => 'Diazepam', 'dose' => '5', 'unit' => 'mg', 'notes' => 'Benzodiazepin untuk kecemasan akut dan insomnia', 'source_type' => 'ADMIN'],
            
            // ============ INJEKSI & VAKSIN UMUM ============
            ['name' => 'Tetanus Toxoid', 'dose' => '0.5', 'unit' => 'mL', 'notes' => 'Vaksin untuk pencegahan tetanus', 'source_type' => 'ADMIN'],
            ['name' => 'BCG Vaccine', 'dose' => '0.05', 'unit' => 'mL', 'notes' => 'Vaksin untuk pencegahan tuberkulosis', 'source_type' => 'ADMIN'],
            ['name' => 'Hepatitis B Vaccine', 'dose' => '1', 'unit' => 'mL', 'notes' => 'Vaksin untuk pencegahan hepatitis B', 'source_type' => 'ADMIN'],
            ['name' => 'Influenza Vaccine', 'dose' => '0.5', 'unit' => 'mL', 'notes' => 'Vaksin tahunan untuk pencegahan influenza', 'source_type' => 'ADMIN'],
            
            // ============ OBAT INJEKSI LAINNYA ============
            ['name' => 'Ampicillin Injection', 'dose' => '500', 'unit' => 'mg', 'notes' => 'Antibiotik injeksi untuk infeksi berat', 'source_type' => 'ADMIN'],
            ['name' => 'Dexamethasone Injection', 'dose' => '5', 'unit' => 'mg', 'notes' => 'Kortikosteroid injeksi untuk inflamasi akut', 'source_type' => 'ADMIN'],
            ['name' => 'Vitamin B1 Injection', 'dose' => '100', 'unit' => 'mg', 'notes' => 'Tiamin injeksi untuk defisiensi vitamin', 'source_type' => 'ADMIN'],
            
            // ============ CAIRAN REHIDRASI ============
            ['name' => 'Oralit (Garam Rehidrasi Oral)', 'dose' => 'var', 'unit' => 'sachet', 'notes' => 'Cairan rehidrasi untuk diare dan dehidrasi', 'source_type' => 'ADMIN'],
            ['name' => 'Infus Saline Normal 0.9%', 'dose' => '500', 'unit' => 'mL', 'notes' => 'Cairan infus standar untuk rehidrasi', 'source_type' => 'ADMIN'],
            
            // ============ OBAT LAINNYA YANG SERING DIGUNAKAN ============
            ['name' => 'Obat Tetes Mata Antihistamin', 'dose' => 'var', 'unit' => 'bottle', 'notes' => 'Untuk konjunktivitis alergi', 'source_type' => 'ADMIN'],
            ['name' => 'Obat Tetes Telinga', 'dose' => 'var', 'unit' => 'bottle', 'notes' => 'Untuk otitis eksternal dan serumen', 'source_type' => 'ADMIN'],
            ['name' => 'Gentian Violet', 'dose' => '1', 'unit' => '%', 'notes' => 'Antiseptik topikal untuk luka dan infeksi kulit ringan', 'source_type' => 'ADMIN'],
            ['name' => 'Kalium Permanganat', 'dose' => 'var', 'unit' => 'tablet', 'notes' => 'Antiseptik untuk rendam pada dermatitis', 'source_type' => 'ADMIN'],
        ];

        foreach ($medicines as $medicine) {
            Medicine::updateOrCreate(
                ['name' => $medicine['name']],
                $medicine
            );
        }
    }
}