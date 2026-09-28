<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            PolySeeder::class,
            DoctorSeeder::class,
            Icd10Seeder::class,
            DrugSeeder::class,
            PatientSeeder::class,
            FacilitySeeder::class,
            RegistrationSeeder::class,
            ClinicalSeeder::class,
            BillingSeeder::class,
        ]);
    }
}
