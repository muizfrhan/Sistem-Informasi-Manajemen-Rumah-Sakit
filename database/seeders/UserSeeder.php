<?php

namespace Database\Seeders;

use App\Enums\RoleCode;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['superadmin', 'Rina Kusuma', RoleCode::SUPER_ADMIN],
            ['receptionist', 'Siti Rahayu', RoleCode::RECEPTIONIST],
            ['dokter', 'dr. Budi Santoso, Sp.B', RoleCode::DOCTOR],
            ['perawat', 'Andi Wijaya', RoleCode::NURSE],
            ['apoteker', 'Dewi Lestari, S.Farm', RoleCode::PHARMACIST],
            ['kasir', 'Budianto Hartono', RoleCode::CASHIER],
            ['manajemen', 'Hj. Kartika Sari', RoleCode::MANAGER],
        ];

        $roleIds = Role::pluck('id', 'code');

        foreach ($users as [$username, $name, $roleCode]) {
            User::updateOrCreate(
                ['username' => $username],
                [
                    'role_id' => $roleIds[$roleCode->value],
                    'name' => $name,
                    'email' => $username.'@simrs-rme.test',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
        }
    }
}
