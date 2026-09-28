<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Services\PatientService;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        // Seeder harus aman dijalankan berulang: NIK bersifat unik, sehingga
        // pasien yang sudah terdaftar dilewati.
        if (Patient::query()->exists()) {
            return;
        }

        $patients = [
            ['Budi Santoso', '3201234567890001', Gender::MALE, '1990-05-14', 'Bandung', 'O', '081234567890', 'NONE', null, null, 'Jl. Merdeka No. 12, Bandung'],
            ['Siti Aminah', '3201234567890002', Gender::FEMALE, '1985-11-02', 'Bandung', 'A', '081234567891', 'BPJS', '0001234567890', 'BPJS Kesehatan', 'Jl. Sudirman No. 45, Bandung'],
            ['Ahmad Fauzi', '3201234567890003', Gender::MALE, '1978-02-28', 'Garut', 'B', '081234567892', 'ASURANSI', 'POL-887123', 'PT Asuransi Sejahtera', 'Jl. Raya Garut No. 8'],
            ['Dewi Lestari', '3201234567890004', Gender::FEMALE, '1995-07-19', 'Bandung', 'AB', '081234567893', 'BPJS', '0009876543210', 'BPJS Kesehatan', 'Jl. Cihampelas No. 21, Bandung'],
            ['Rahmat Hidayat', '3201234567890005', Gender::MALE, '1968-09-30', 'Sumedang', 'A', '081234567894', 'PERUSAHAAN', 'PLT-2024-001', 'PT Maju Bersama', 'Jl. Ahmad Yani No. 3, Sumedang'],
            ['Nurul Hidayah', '3201234567890006', Gender::FEMALE, '2001-03-08', 'Bandung', 'O', '081234567895', 'NONE', null, null, 'Jl. Dipatiukur No. 77, Bandung'],
            ['Agus Setiawan', '3201234567890007', Gender::MALE, '1982-12-25', 'Cimahi', 'B', '081234567896', 'BPJS', '0001122334455', 'BPJS Kesehatan', 'Jl. Cibabat No. 5, Cimahi'],
            ['Sri Wahyuni', '3201234567890008', Gender::FEMALE, '1972-06-11', 'Bandung', 'A', '081234567897', 'ASURANSI', 'PRL-556677', 'PT Asuransi Prima', 'Jl. Setiabudi No. 90, Bandung'],
        ];

        $service = app(PatientService::class);

        foreach ($patients as [$name, $nik, $gender, $birthDate, $birthPlace, $blood, $phone, $insurance, $insuranceNumber, $insuranceName, $address]) {
            $service->create([
                'name' => $name,
                'nik' => $nik,
                'gender' => $gender,
                'birth_date' => $birthDate,
                'birth_place' => $birthPlace,
                'blood_type' => $blood,
                'marital_status' => 'Kawin',
                'phone' => $phone,
                'address' => $address,
                'village' => 'Kel-example',
                'district' => 'Sumur Bandung',
                'city' => 'Bandung',
                'province' => 'Jawa Barat',
                'postal_code' => '40115',
                'insurance_type' => $insurance,
                'insurance_number' => $insuranceNumber,
                'insurance_name' => $insuranceName,
                'is_active' => true,
            ]);
        }
    }
}
