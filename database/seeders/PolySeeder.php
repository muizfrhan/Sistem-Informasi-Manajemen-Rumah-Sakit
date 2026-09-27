<?php

namespace Database\Seeders;

use App\Models\Poly;
use Illuminate\Database\Seeder;

class PolySeeder extends Seeder
{
    public function run(): void
    {
        $polies = [
            ['P001', 'Poli Umum', 'Layanan dokter umum untuk pasien umum dan wali.', 'A'],
            ['P002', 'Poli Anak', 'Layanan kesehatan anak dan balita.', 'B'],
            ['P003', 'Poli Kandungan', 'Layanan obstetri dan ginekologi.', 'C'],
            ['P004', 'Poli Penyakit Dalam', 'Layanan penyakit dalam dan metabolik.', 'D'],
            ['P005', 'Poli Gigi', 'Layanan kesehatan gigi dan mulut.', 'E'],
            ['P006', 'Imergensi / Gawat Darurat', 'Layanan gawat darurat 24 jam.', 'G'],
        ];

        foreach ($polies as [$code, $name, $description, $prefix]) {
            Poly::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'description' => $description,
                    'queue_prefix' => $prefix,
                    'is_active' => true,
                ]
            );
        }
    }
}
