<?php

namespace Database\Factories;

use App\Enums\PrescriptionStatus;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Prescription> */
class PrescriptionFactory extends Factory
{
    public function definition(): array
    {
        $record = MedicalRecord::factory();

        return [
            'prescription_number' => 'RS-'.fake()->unique()->numerify('######'),
            'medical_record_id' => $record,
            'patient_id' => fn (array $attributes) => MedicalRecord::find($attributes['medical_record_id'])?->patient_id,
            'doctor_id' => fn (array $attributes) => MedicalRecord::find($attributes['medical_record_id'])?->doctor_id,
            'status' => PrescriptionStatus::DRAFT,
            'notes' => null,
        ];
    }

    public function dispensed(): static
    {
        return $this->state(fn () => [
            'status' => PrescriptionStatus::DISPENSED,
            'dispensed_at' => now(),
            'dispensed_by' => null,
        ]);
    }
}
