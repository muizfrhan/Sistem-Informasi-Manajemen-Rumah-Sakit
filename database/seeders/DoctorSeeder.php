<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Poly;
use App\Models\User;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $doctorUser = User::where('username', 'dokter')->first();

        $doctors = [
            ['DR001', 'dr. Budi Santoso, Sp.B', '19780412 200512 1 008', 'Bedah Umum', 'P001'],
            ['DR002', 'dr. Dewi Anggraini, Sp.A', '19820905 201103 2 011', 'Anak', 'P002'],
            ['DR003', 'dr. Hendra Wijaya, Sp.OG', '19750618 200001 2 004', 'Kandungan & Kebidanikan', 'P003'],
            ['DR004', 'dr. Ratna Kusuma, Sp.PD', '19810330 200807 2 015', 'Penyakit Dalam', 'P004'],
            ['DR005', 'dr. Sari Melinda, Sp.PG', '19900122 201601 2 009', 'Gigi & Mulut', 'P005'],
            ['DR006', 'dr. Agung Prasetyo', '19880915 201208 1 006', 'Dokter Umum / Gawat Darurat', 'P006'],
            ['DR007', 'dr. Melati Puspita, Sp.A', '19870703 201105 2 007', 'Anak', 'P002'],
        ];

        $polyIds = Poly::pluck('id', 'code');

        foreach ($doctors as $index => [$code, $name, $nip, $specialty, $polyCode]) {
            Doctor::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'nip' => $nip,
                    'specialty' => $specialty,
                    'sip_number' => 'SIP-'.(2200 + $index).'/'.(1000 + $index).'/'.(1 + $index),
                    'phone' => '081'.mt_rand(100000000, 999999999),
                    'poly_id' => $polyIds[$polyCode],
                    'is_active' => true,
                ]
            );
        }

        if ($doctorUser) {
            Doctor::where('code', 'DR001')->update(['user_id' => $doctorUser->id]);
        }
    }
}
