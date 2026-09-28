<?php

namespace Database\Factories;

use App\Enums\AdmissionStatus;
use App\Models\InpatientAdmission;
use App\Models\Patient;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InpatientAdmission> */
class InpatientAdmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'admission_number' => 'RI-'.fake()->unique()->numerify('######'),
            'patient_id' => Patient::factory(),
            'registration_id' => null,
            'room_id' => Room::factory(),
            'doctor_id' => null,
            'bed_number' => '1',
            'status' => AdmissionStatus::ADMITTED,
            'diagnosis' => fake()->sentence(4),
            'admitted_at' => now(),
            'discharged_at' => null,
        ];
    }

    public function discharged(): static
    {
        return $this->state(fn () => [
            'status' => AdmissionStatus::DISCHARGED,
            'discharged_at' => now()->addDays(fake()->numberBetween(1, 7)),
        ]);
    }
}
