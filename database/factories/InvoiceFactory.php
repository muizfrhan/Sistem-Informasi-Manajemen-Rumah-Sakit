<?php

namespace Database\Factories;

use App\Enums\InvoiceItemType;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Invoice> */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'invoice_number' => 'INV-'.fake()->unique()->numerify('######'),
            'patient_id' => Patient::factory(),
            'registration_id' => null,
            'created_by' => null,
            'status' => InvoiceStatus::UNPAID,
            'subtotal' => 0,
            'discount' => 0,
            'total' => 0,
            'paid_total' => 0,
            'issued_at' => today(),
            'due_at' => today()->addDays(7),
        ];
    }

    /**
     * Memastikan tagihan selalu punya minimal satu item.
     *
     * Total pada InvoiceService::recalculate() selalu dihitung ulang dari
     * jumlah item, sehingga factory harus membuat item yang konsisten agar
     * data uji tidak melanggar invarian tersebut.
     */
    public function withItem(int $price = 150000, int $quantity = 1): static
    {
        return $this->afterCreating(function (Invoice $invoice) use ($price, $quantity) {
            $invoice->items()->create([
                'item_type' => InvoiceItemType::CONSULTATION,
                'description' => 'Konsultasi dokter',
                'quantity' => $quantity,
                'unit_price' => $price,
                'subtotal' => $price * $quantity,
            ]);

            $invoice->forceFill([
                'subtotal' => $price * $quantity,
                'total' => $price * $quantity,
            ])->save();
        });
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::PAID,
            'paid_total' => $attributes['total'],
            'paid_at' => now(),
        ]);
    }

    public function partial(int $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::PARTIAL,
            'paid_total' => $amount,
        ]);
    }
}
