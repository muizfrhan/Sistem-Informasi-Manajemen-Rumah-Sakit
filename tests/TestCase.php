<?php

namespace Tests;

use App\Enums\RoleCode;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    protected function userFor(RoleCode $roleCode, array $attributes = []): User
    {
        $role = Role::where('code', $roleCode->value)->firstOrFail();

        return tap(User::factory()->create($attributes + ['role_id' => $role->id]), function (User $user) use ($role) {
            $user->setRelation('role', $role->load('permissions'));
        });
    }

    protected function superAdmin(array $attributes = []): User
    {
        return $this->userFor(RoleCode::SUPER_ADMIN, $attributes);
    }
}
