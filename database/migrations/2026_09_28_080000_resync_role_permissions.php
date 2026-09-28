<?php

use App\Enums\Permission;
use App\Enums\RoleCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menyelaraskan ulang `permissions` dan `permission_role` dengan enum terbaru.
 *
 * Migrasi 2026_09_27_001200 sudah melakukan hal yang sama, tetapi enum
 * Permission/RoleCode dikembangkan setelah migrasi itu tercatat berjalan.
 * Migrasi yang sudah dijalankan tidak pernah dieksekusi ulang, sehingga
 * instalasi yang sudah ada tertinggal izin — akibatnya peran seperti Perawat
 * dan Kasir kehilangan akses ke modul yang memang diberikan oleh enum, dan
 * halaman tersebut membalas 403.
 *
 * Migrasi ini dijaga idempoten, jadi aman dijalankan berkali-kali.
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
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $permissionIds = DB::table('permissions')->pluck('id', 'code');

        foreach (DB::table('roles')->pluck('id', 'code') as $roleId => $roleCode) {
            $role = RoleCode::tryFrom($roleCode);

            if ($role === null) {
                continue;
            }

            $wanted = collect($role->permissions())
                ->map(fn (Permission $p) => $permissionIds[$p->value] ?? null)
                ->filter()
                ->all();

            $current = DB::table('permission_role')
                ->where('role_id', $roleId)
                ->pluck('permission_id')
                ->all();

            // Lewati bila sudah sama, supaya tidak menulis tanpa perlu.
            if ($current === $wanted) {
                continue;
            }

            DB::table('permission_role')->where('role_id', $roleId)->delete();

            foreach ($wanted as $permissionId) {
                DB::table('permission_role')->insertOrIgnore([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Sinkronisasi tidak punya arah balik yang bermakna: definisi
        // kebenaran tersimpan di enum, bukan di migrasi. `up()` dijalankan ulang
        // untuk mengembalikan keadaan yang konsisten.
        $this->up();
    }
};
