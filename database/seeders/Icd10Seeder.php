<?php

namespace Database\Seeders;

use App\Models\Icd10Code;
use Illuminate\Database\Seeder;

class Icd10Seeder extends Seeder
{
    public function run(): void
    {
        $codes = [
            ['A00', 'Cholera', 'I'],
            ['B20', 'HIV disease', 'I'],
            ['D50', 'Iron deficiency anaemia', 'III'],
            ['E11', 'Type 2 diabetes mellitus', 'IV'],
            ['E78', 'Disorders of lipoprotein metabolism', 'IV'],
            ['F32', 'Depressive episode', 'V'],
            ['F41', 'Anxiety disorder', 'V'],
            ['G43', 'Migraine', 'VI'],
            ['G54', 'Brachial plexus disorders', 'VI'],
            ['I10', 'Essential hypertension', 'IX'],
            ['I21', 'Acute myocardial infarction', 'IX'],
            ['I50', 'Heart failure', 'IX'],
            ['J00', 'Acute nasopharyngitis', 'X'],
            ['J06', 'Acute upper respiratory infection', 'X'],
            ['J18', 'Pneumonia', 'X'],
            ['J45', 'Asthma', 'X'],
            ['K29', 'Gastritis and duodenitis', 'XI'],
            ['K52', 'Other noninfective gastroenteritis', 'XI'],
            ['L20', 'Atopic dermatitis', 'XII'],
            ['L40', 'Psoriasis', 'XII'],
            ['M54', 'Low back pain', 'XIII'],
            ['M79', 'Soft tissue disorders', 'XIII'],
            ['N39', 'Urinary tract infection', 'XIV'],
            ['N63', 'Unspecified breast mass', 'XIV'],
            ['O80', 'Single spontaneous delivery', 'XV'],
            ['O82', 'Delivery by caesarean section', 'XV'],
            ['P07', 'Slow fetal growth', 'XVI'],
            ['R05', 'Cough', 'XVIII'],
            ['R10', 'Abdominal and lower-back pain', 'XVIII'],
            ['R50', 'Fever of unknown origin', 'XVIII'],
            ['R51', 'Headache', 'XVIII'],
            ['S06', 'Intracranial injury', 'XIX'],
            ['T14', 'Injury of unspecified body part', 'XIX'],
            ['Z00.0', 'General adult medical examination', 'XXI'],
            ['Z30.0', 'Contraceptive management', 'XXI'],
            ['Z51.1', 'Attention to antineoplastic chemotherapy', 'XXI'],
        ];

        foreach ($codes as [$code, $description, $category]) {
            Icd10Code::updateOrCreate(
                ['code' => $code],
                ['description' => $description, 'category' => $category, 'is_active' => true]
            );
        }
    }
}
