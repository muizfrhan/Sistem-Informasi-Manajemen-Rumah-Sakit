<?php

namespace Database\Factories;

use App\Enums\RoleCode;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'role_id' => fn () => $this->resolveRoleId(RoleCode::RECEPTIONIST),
            'username' => $this->validUsername(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'is_active' => true,
        ];
    }

    /**
     * Username hasil generator Faker dapat mengandung titik, yang tidak
     * memenuhi aturan validasi `alpha_dash` pada aplikasi. Factory harus
     * menghasilkan data yang selalu lolos validasi.
     */
    private function validUsername(): string
    {
        do {
            $username = preg_replace('/[^A-Za-z0-9_-]/', '_', $this->faker->unique()->userName());
        } while (User::where('username', $username)->exists());

        return $username;
    }

    public function withRole(RoleCode $roleCode): static
    {
        return $this->state(fn () => ['role_id' => $this->resolveRoleId($roleCode)]);
    }

    /**
     * Memakai peran yang sudah ada bila tersedia, dan membuatnya bila belum.
     *
     * Peran selalu memiliki kode unik, sehingga membuat peran baru dengan kode
     * yang sama akan melanggar constraint. Karena itu factory ini mencari dulu
     * sebelum membuat.
     */
    private function resolveRoleId(RoleCode $roleCode): int
    {
        return Role::query()->firstOrCreate(
            ['code' => $roleCode->value],
            [
                'name' => $roleCode->label(),
                'description' => $roleCode->description(),
                'sort_order' => array_search($roleCode, RoleCode::cases(), true),
            ],
        )->id;
    }
}
