<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Poly;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        return [
            'poly_id' => Poly::factory(),
            'code' => 'DR'.str_pad((string) $this->faker->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'name' => 'dr. '.$this->faker->name().', Sp.',
            'nip' => $this->faker->numerify('#############'),
            'specialty' => $this->faker->randomElement(['Bedah Umum', 'Penyakit Dalam', 'Anak', 'Gigi']),
            'is_active' => true,
        ];
    }
}
