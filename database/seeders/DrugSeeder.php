<?php

namespace Database\Seeders;

use App\Models\Drug;
use Illuminate\Database\Seeder;

class DrugSeeder extends Seeder
{
    public function run(): void
    {
        $drugs = [
            ['OB001', 'Amoxicillin', 'Amoxicillin', 'Kapsul', '500 mg', 'Kapsul', 8500, 5000, 500],
            ['OB002', 'Paracetamol', 'Paracetamol', 'Tablet', '500 mg', 'Tablet', 500, 10000, 1000],
            ['OB003', 'Ibuprofen', 'Ibuprofen', 'Tablet', '200 mg', 'Tablet', 750, 8000, 800],
            ['OB004', 'Metronidazole', 'Metronidazole', 'Tablet', '500 mg', 'Tablet', 1100, 4000, 400],
            ['OB005', 'Cetirizine', 'Cetirizine', 'Tablet', '10 mg', 'Tablet', 900, 6000, 600],
            ['OB006', 'Ambroxol Syrup', 'Ambroxol', 'Sirup', '100 mg/5 ml', 'Botol', 18000, 800, 100],
            ['OB007', 'Metformin HCl', 'Metformin', 'Tablet', '500 mg', 'Tablet', 1400, 5000, 500],
            ['OB008', 'Simvastatin', 'Simvastatin', 'Tablet', '20 mg', 'Tablet', 2200, 3500, 350],
            ['OB009', 'Amlodipine', 'Amlodipine', 'Tablet', '10 mg', 'Tablet', 2600, 3000, 300],
            ['OB010', 'Captopril', 'Captopril', 'Tablet', '25 mg', 'Tablet', 1200, 2500, 250],
            ['OB011', 'Salbutamol Inhaler', 'Salbutamol', 'Inhaler', '100 mcg', 'Buah', 95000, 200, 50],
            ['OB012', 'Amoxicillin Sirup', 'Amoxicillin', 'Sirup', '250 mg/5 ml', 'Botol', 24000, 600, 100],
            ['OB013', 'ORS (Oral Rehydration Salt)', 'ORS', 'Ramuan', ' sachet', 'Sachet', 3500, 4000, 500],
            ['OB014', 'Vitamin C', 'Ascorbic Acid', 'Tablet', '500 mg', 'Tablet', 1000, 7000, 700],
            ['OB015', 'Calcium Carbonate', 'Calcium Carbonate', 'Tablet', '500 mg', 'Tablet', 1300, 4500, 450],

            // Daftar di bawah sengaja dibuat menipis / mendekati habis agar
            // indikator "Stok Menipis" dan "Stok Habis" pada modul farmasi
            // dapat diuji dengan data yang nyata, bukan angka dummy.
            ['OB016', 'Salbutamol Tablet', 'Salbutamol', 'Tablet', '2 mg', 'Tablet', 4500, 18, 50],
            ['OB017', 'Metoclopramide Injeksi', 'Metoclopramide', 'Injeksi', '5 mg/ml', 'Vial', 28000, 0, 10],
        ];

        foreach ($drugs as [$code, $name, $generic, $form, $strength, $unit, $price, $stock, $minStock]) {
            Drug::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'generic_name' => $generic,
                    'form' => $form,
                    'strength' => $strength,
                    'unit' => $unit,
                    'price' => $price,
                    'stock' => $stock,
                    'min_stock' => $minStock,
                    'requires_prescription' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}
