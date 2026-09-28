<?php

namespace Database\Factories;

use App\Models\Icd10Code;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Icd10Code> */
class Icd10CodeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->regexify('[A-Z][0-9]{2}'),
            'description' => fake()->words(3, true),
            'category' => fake()->randomElement(['I', 'IX', 'X', 'XII', 'XIII']),
            'is_active' => true,
        ];
    }
}
