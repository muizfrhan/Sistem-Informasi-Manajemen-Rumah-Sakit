<?php

namespace Database\Seeders;

use App\Enums\Permission;
use App\Enums\RoleCode;
use App\Models\Permission as PermissionModel;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Permission::cases() as $permission) {
            PermissionModel::updateOrCreate(
                ['code' => $permission->value],
                ['name' => $permission->label(), 'group' => $permission->group()]
            );
        }

        $permissions = PermissionModel::pluck('id', 'code');

        foreach (RoleCode::cases() as $index => $roleCode) {
            $role = Role::updateOrCreate(
                ['code' => $roleCode->value],
                [
                    'name' => $roleCode->label(),
                    'description' => $roleCode->description(),
                    'sort_order' => $index,
                ]
            );

            $ids = collect($roleCode->permissions())
                ->map(fn (Permission $p) => $permissions[$p->value])
                ->filter()
                ->all();

            $role->permissions()->sync($ids);
        }
    }
}
