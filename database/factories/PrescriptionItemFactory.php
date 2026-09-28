<?php

namespace Database\Factories;

use App\Models\Drug;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PrescriptionItem> */
class PrescriptionItemFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 30);
        $price = fake()->numberBetween(500, 20_000);

        return [
            'prescription_id' => Prescription::factory(),
            'drug_id' => Drug::factory(),
            'dosage' => '1 tablet',
            'route' => 'oral',
            'frequency' => '3x sehari',
            'duration' => '7 hari',
            'instructions' => null,
            'quantity' => $quantity,
            'unit_price' => $price,
            'subtotal' => $quantity * $price,
        ];
    }
}
