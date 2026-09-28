<?php

namespace Database\Factories;

use App\Enums\MedicalRecordStatus;
use App\Enums\MedicalRecordType;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MedicalRecord> */
class MedicalRecordFactory extends Factory
{
    public function definition(): array
    {
        $registration = Registration::factory();

        return [
            'registration_id' => $registration,
            'patient_id' => fn (array $attributes) => Registration::find($attributes['registration_id'])?->patient_id
                ?? Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'created_by' => null,
            'record_type' => MedicalRecordType::OUTPATIENT,
            'status' => MedicalRecordStatus::DRAFT,
            'chief_complaint' => fake()->sentence(5),
            'anamnesis' => fake()->paragraph(),
            'physical_exam' => fake()->sentence(10),
            'diagnosis' => fake()->sentence(6),
            'treatment_plan' => fake()->paragraph(2),
            'blood_pressure' => '120/80',
            'pulse' => fake()->numberBetween(60, 100),
            'temperature' => fake()->randomFloat(1, 36.5, 39.0),
            'respiratory_rate' => fake()->numberBetween(16, 24),
            'weight' => fake()->randomFloat(1, 45, 95),
            'height' => fake()->randomFloat(1, 150, 185),
        ];
    }

    public function finalized(): static
    {
        return $this->state(fn () => [
            'status' => MedicalRecordStatus::FINALIZED,
            'finalized_at' => now(),
        ]);
    }
}
