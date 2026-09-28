<?php

namespace Tests\Feature;

use App\Enums\Permission;
use App\Enums\RoleCode;
use App\Models\Role;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Mengunci matriks izin per peran agar tidak berubah tanpa disadari.
 *
 * Catatan: instance enum TIDAK boleh menjadi kunci array di PHP, karena itu
 * seluruh matriks di bawah memakai nilai string dari enum sebagai kunci.
 */
class RoleMatrixTest extends TestCase
{
    use RefreshDatabase;

    /** Izin yang wajib ada agar alur utama setiap peran tetap berfungsi. */
    private const REQUIRED = [
        'super_admin' => ['dashboard.view', 'user.manage', 'master.manage'],
        'receptionist' => [
            'dashboard.view', 'patient.manage', 'registration.manage', 'queue.manage',
        ],
        'doctor' => ['dashboard.view', 'patient.view', 'emr.manage', 'pharmacy.view'],
        'nurse' => [
            'dashboard.view', 'patient.view', 'emr.view', 'inpatient.manage', 'schedule.view',
        ],
        'pharmacist' => ['dashboard.view', 'pharmacy.view', 'pharmacy.manage'],
        'cashier' => ['dashboard.view', 'billing.view', 'billing.manage'],
        'manager' => ['dashboard.view', 'reports.view', 'audit.view'],
    ];

    /** Izin yang tidak boleh dimiliki peran mana pun di bawah ini. */
    private const FORBIDDEN = [
        'receptionist' => ['emr.manage', 'billing.manage', 'user.manage'],
        'doctor' => ['patient.manage', 'billing.manage', 'user.manage'],
        'nurse' => ['patient.manage', 'billing.manage', 'user.manage'],
        'pharmacist' => ['emr.manage', 'billing.manage', 'user.manage'],
        'cashier' => ['patient.manage', 'emr.manage', 'master.manage', 'user.manage'],
        'manager' => ['user.manage', 'billing.manage', 'master.manage', 'patient.manage'],
    ];

    public function test_every_role_has_the_required_permissions(): void
    {
        foreach (self::REQUIRED as $roleValue => $permissionCodes) {
            $user = $this->userFor(RoleCode::from($roleValue));

            foreach ($permissionCodes as $code) {
                $this->assertTrue(
                    $user->hasPermission(Permission::from($code)),
                    "Peran {$roleValue} seharusnya memiliki izin {$code}."
                );
            }
        }
    }

    public function test_forbidden_permissions_are_not_granted(): void
    {
        foreach (self::FORBIDDEN as $roleValue => $permissionCodes) {
            $user = $this->userFor(RoleCode::from($roleValue));

            foreach ($permissionCodes as $code) {
                $this->assertFalse(
                    $user->hasPermission(Permission::from($code)),
                    "Peran {$roleValue} TIDAK seharusnya memiliki izin {$code}."
                );
            }
        }
    }

    public function test_super_admin_bypasses_every_permission_check(): void
    {
        $user = $this->userFor(RoleCode::SUPER_ADMIN);

        foreach (Permission::cases() as $permission) {
            $this->assertTrue($user->hasPermission($permission), "Gagal pada {$permission->value}");
            $this->assertTrue($user->can($permission->value));
        }
    }

    public function test_permission_codes_are_unique_in_the_database(): void
    {
        $this->seedRolesAndPermissions();

        $codes = \App\Models\Permission::pluck('code');

        $this->assertSame(
            $codes->count(),
            $codes->unique()->count(),
            'Terdapat kode izin duplikat pada tabel permissions.'
        );
    }

    public function test_every_enum_permission_exists_in_the_database(): void
    {
        $this->seedRolesAndPermissions();

        $stored = \App\Models\Permission::pluck('code')->all();

        foreach (Permission::cases() as $permission) {
            $this->assertContains(
                $permission->value,
                $stored,
                "Izin {$permission->value} belum tersinkron ke tabel permissions."
            );
        }
    }

    public function test_every_role_has_permissions_in_the_database(): void
    {
        $this->seedRolesAndPermissions();

        foreach (RoleCode::cases() as $roleCode) {
            $role = Role::where('code', $roleCode->value)->first();

            $this->assertNotNull($role, "Peran {$roleCode->value} belum ada di tabel roles.");
            $this->assertGreaterThan(0, $role->permissions()->count());
        }
    }

    public function test_role_matrix_has_no_duplicated_entries(): void
    {
        foreach ([self::REQUIRED, self::FORBIDDEN] as $set) {
            foreach ($set as $roleValue => $codes) {
                $this->assertSame(
                    count($codes),
                    count(array_unique($codes)),
                    "Ada kode izin duplikat untuk peran {$roleValue}."
                );
            }
        }
    }

    private function seedRolesAndPermissions(): void
    {
        $this->seed(RolePermissionSeeder::class);
    }
}
