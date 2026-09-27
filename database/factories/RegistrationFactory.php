<?php

namespace Database\Factories;

use App\Enums\RegistrationStatus;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Poly;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegistrationFactory extends Factory
{
    protected $model = \App\Models\Registration::class;

    public function definition(): array
    {
        return [
            'registration_number' => 'RJ-'.now()->format('Ymd').'-'.str_pad((string) $this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'patient_id' => Patient::factory(),
            'poly_id' => Poly::factory(),
            'doctor_id' => Doctor::factory(),
            'visit_date' => today(),
            'queue_number' => $this->faker->unique()->numberBetween(1, 999),
            'queue_code' => 'A-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'status' => RegistrationStatus::REGISTERED,
            'is_emergency' => false,
            'registered_at' => now(),
        ];
    }
}
