<?php

namespace Database\Factories;

use App\Models\Poly;
use Illuminate\Database\Eloquent\Factories\Factory;

class PolyFactory extends Factory
{
    protected $model = Poly::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->lexify('P???')),
            'name' => 'Poli '.$this->faker->word(),
            'description' => $this->faker->sentence(),
            'queue_prefix' => strtoupper($this->faker->unique()->lexify('???')),
            'is_active' => true,
        ];
    }
}
