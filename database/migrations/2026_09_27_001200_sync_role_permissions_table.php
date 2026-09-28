<?php

use App\Enums\Permission;
use App\Enums\RoleCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menyelaraskan tabel `permissions` dan `permission_role` dengan definisi
 * enum terbaru. Migration ini murni penambahan (idempotent) sehingga aman
 * dijalankan pada instalasi lama yang sudah memiliki data.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach (Permission::cases() as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['code' => $permission->value],
                [
                    'name' => $permission->label(),
                    'group' => $permission->group(),
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $permissionIds = DB::table('permissions')->pluck('id', 'code');

        foreach (DB::table('roles')->pluck('id', 'code') as $roleId => $roleCode) {
            $role = RoleCode::tryFrom($roleCode);

            if ($role === null) {
                continue;
            }

            $ids = collect($role->permissions())
                ->map(fn (Permission $p) => $permissionIds[$p->value] ?? null)
                ->filter()
                ->all();

            // Sinkronkan penuh agar permission yang dicabut dari enum ikut hilang.
            DB::table('permission_role')->where('role_id', $roleId)->delete();

            foreach ($ids as $permissionId) {
                DB::table('permission_role')->insertOrIgnore([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('permission_role')
            ->whereIn('permission_id', DB::table('permissions')
                ->whereIn('code', [
                    'pharmacy.view',
                    'billing.view',
                    'inpatient.view',
                    'inpatient.manage',
                    'schedule.view',
                    'schedule.manage',
                ])
                ->pluck('id'))
            ->delete();

        DB::table('permissions')->whereIn('code', [
            'pharmacy.view',
            'billing.view',
            'inpatient.view',
            'inpatient.manage',
            'schedule.view',
            'schedule.manage',
        ])->delete();
    }
};
