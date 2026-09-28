<?php

namespace Database\Factories;

use App\Models\Drug;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Drug> */
class DrugFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'OB'.fake()->unique()->numerify('###'),
            'name' => fake()->unique()->words(2, true),
            'generic_name' => null,
            'category' => null,
            'form' => 'Tablet',
            'strength' => '500 mg',
            'unit' => 'Tablet',
            'price' => fake()->numberBetween(500, 50_000),
            'stock' => 1000,
            'min_stock' => 100,
            'expired_at' => now()->addYear()->toDateString(),
            'requires_prescription' => true,
            'is_active' => true,
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn () => ['stock' => 5, 'min_stock' => 100]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock' => 0, 'min_stock' => 100]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expired_at' => now()->subMonth()->toDateString()]);
    }

    public function expiringSoon(): static
    {
        return $this->state(fn () => ['expired_at' => now()->addDays(30)->toDateString()]);
    }
}
