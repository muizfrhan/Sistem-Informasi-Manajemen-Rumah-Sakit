<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            // Prefix menjaga agar tidak bentrok dengan kode peran sistem
            // (super_admin, receptionist, dan seterusnya).
            'code' => 'test_'.$this->faker->unique()->slug(3),
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'sort_order' => 0,
        ];
    }
}
