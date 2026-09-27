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
            'role_id' => fn () => Role::factory()->create(['code' => RoleCode::RECEPTIONIST->value])->id,
            'username' => $this->faker->unique()->userName(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'is_active' => true,
        ];
    }

    public function withRole(RoleCode $roleCode): static
    {
        return $this->state(fn () => ['role_id' => Role::factory()->create(['code' => $roleCode->value])->id]);
    }
}
