<?php

namespace Tests\Feature;

use App\Enums\Permission;
use App\Enums\RoleCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Tabel permission_role harus selalu sama dengan enum RoleCode.
 *
 * Enum bisa diperluas kapan saja, dan migrasi yang sudah pernah dijalankan
 * tidak dieksekusi ulang. Kalau pivot tertinggal, peran tertentu diam-diam
 * kehilangan akses ke modul yang memang diberikan — halaman balas 403 tanpa
 * penjelasan. Test ini mencegah keadaan itu terjadi diam-diam.
 */
class RolePermissionSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_permissions_table_matches_enum(): void
    {
        $codes = DB::table('permissions')->pluck('code')->sort()->values()->all();
        $expected = collect(Permission::cases())
            ->map(fn (Permission $p) => $p->value)
            ->sort()
            ->values()
            ->all();

        $this->assertSame($expected, $codes, 'Tabel permissions tidak sama dengan enum Permission.');
    }

    public function test_every_role_permission_row_matches_enum(): void
    {
        $roles = DB::table('roles')->pluck('id', 'code');

        foreach (RoleCode::cases() as $role) {
            $roleId = $roles[$role->value] ?? null;
            $this->assertNotNull($roleId, "Peran {$role->value} belum ada di tabel roles.");

            $expected = collect($role->permissions())
                ->map(fn (Permission $p) => $p->value)
                ->sort()
                ->values()
                ->all();

            $actual = DB::table('permission_role')
                ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                ->where('permission_role.role_id', $roleId)
                ->pluck('permissions.code')
                ->sort()
                ->values()
                ->all();

            $missing = array_values(array_diff($expected, $actual));
            $extra = array_values(array_diff($actual, $expected));

            $this->assertSame([], $missing, "Peran {$role->label()} kehilangan izin: ".implode(', ', $missing));
            $this->assertSame([], $extra, "Peran {$role->label()} punya izin takorterduga: ".implode(', ', $extra));
        }
    }

    /**
     * Setiap izin yang diberikan enum harus benar-benar membuka halamannya.
     * Ini yang menangkap kasus "Perawat tidak bisa buka /inpatient".
     */
    public function test_roles_can_open_the_pages_their_permissions_grant(): void
    {
        $pages = [
            Permission::VIEW_INPATIENT->value => '/inpatient',
            Permission::VIEW_BILLING->value => '/billing',
            Permission::VIEW_PHARMACY->value => '/pharmacy/prescriptions',
            Permission::VIEW_EMR->value => '/medical-records',
            Permission::VIEW_REPORTS->value => '/reports',
            Permission::VIEW_MASTER->value => '/master/drugs',
            Permission::VIEW_PATIENT->value => '/patients',
            Permission::VIEW_REGISTRATION->value => '/registrations',
            Permission::VIEW_AUDIT_LOG->value => '/admin/audit-logs',
        ];

        $checked = 0;

        foreach (RoleCode::cases() as $role) {
            $user = $this->userFor($role);

            foreach ($pages as $permission => $url) {
                if (! $user->hasPermission($permission)) {
                    continue;
                }

                $this->actingAs($user)->get($url)->assertOk();
                $checked++;
            }
        }

        $this->assertGreaterThan(10, $checked, 'Terlalu sedikit kombinasi peran/halaman yang diuji.');
    }
}
