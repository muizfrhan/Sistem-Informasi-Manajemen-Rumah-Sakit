<?php

namespace Tests\Feature;

use App\Enums\PrescriptionStatus;
use App\Enums\RoleCode;
use App\Enums\StockMovementType;
use App\Models\Drug;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Services\PharmacyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PharmacyTest extends TestCase
{
    use RefreshDatabase;

    private function prescription(int $stock = 100, int $quantity = 10, ?string $expiredAt = null): Prescription
    {
        $drug = Drug::factory()->create(['stock' => $stock, 'price' => 1000, 'expired_at' => $expiredAt]);

        $prescription = Prescription::factory()->create();
        $prescription->items()->create([
            'drug_id' => $drug->id,
            'quantity' => $quantity,
            'unit_price' => 1000,
            'subtotal' => 1000 * $quantity,
        ]);

        return $prescription->fresh('items');
    }

    public function test_dispensing_reduces_stock_and_records_movement(): void
    {
        $prescription = $this->prescription(stock: 100, quantity: 10);
        $drug = $prescription->items->first()->drug;

        $this->actingAs($this->userFor(RoleCode::PHARMACIST))
            ->post(route('pharmacy.prescriptions.dispense', $prescription))
            ->assertRedirect()
            ->assertSessionHas('success');

        $prescription->refresh();

        $this->assertSame(PrescriptionStatus::DISPENSED, $prescription->status);
        $this->assertNotNull($prescription->dispensed_at);
        $this->assertSame(90, $drug->refresh()->stock);

        $this->assertDatabaseHas('drug_stock_movements', [
            'drug_id' => $drug->id,
            'type' => StockMovementType::OUT->value,
            'quantity' => 10,
            'stock_before' => 100,
            'stock_after' => 90,
        ]);
    }

    public function test_dispensing_is_rejected_when_stock_is_insufficient(): void
    {
        $prescription = $this->prescription(stock: 5, quantity: 10);
        $drug = $prescription->items->first()->drug;

        $this->actingAs($this->userFor(RoleCode::PHARMACIST))
            ->post(route('pharmacy.prescriptions.dispense', $prescription))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(PrescriptionStatus::DRAFT, $prescription->refresh()->status);
        $this->assertSame(5, $drug->refresh()->stock);
        $this->assertDatabaseMissing('drug_stock_movements', ['drug_id' => $drug->id]);
    }

    public function test_dispensing_is_rejected_for_expired_drug(): void
    {
        $prescription = $this->prescription(
            stock: 100,
            quantity: 5,
            expiredAt: now()->subDay()->toDateString(),
        );

        $this->actingAs($this->userFor(RoleCode::PHARMACIST))
            ->post(route('pharmacy.prescriptions.dispense', $prescription))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(PrescriptionStatus::DRAFT, $prescription->refresh()->status);
    }

    public function test_prescription_cannot_be_dispensed_twice(): void
    {
        $prescription = $this->prescription();
        $pharmacist = $this->userFor(RoleCode::PHARMACIST);

        $this->actingAs($pharmacist)
            ->post(route('pharmacy.prescriptions.dispense', $prescription))
            ->assertSessionHas('success');

        $this->actingAs($pharmacist)
            ->post(route('pharmacy.prescriptions.dispense', $prescription))
            ->assertSessionHas('error');
    }

    public function test_doctor_cannot_dispense_prescriptions(): void
    {
        $prescription = $this->prescription();

        $this->actingAs($this->userFor(RoleCode::DOCTOR))
            ->post(route('pharmacy.prescriptions.dispense', $prescription))
            ->assertForbidden();

        $this->assertSame(PrescriptionStatus::DRAFT, $prescription->refresh()->status);
    }

    public function test_cashier_cannot_access_pharmacy(): void
    {
        $this->prescription();

        $this->actingAs($this->userFor(RoleCode::CASHIER))
            ->get(route('pharmacy.prescriptions.index'))
            ->assertForbidden();

        $this->actingAs($this->userFor(RoleCode::CASHIER))
            ->get(route('pharmacy.prescriptions.show', Prescription::first()))
            ->assertForbidden();
    }

    public function test_pharmacist_can_adjust_stock_with_movement_log(): void
    {
        $drug = Drug::factory()->create(['stock' => 20]);

        $this->actingAs($this->userFor(RoleCode::PHARMACIST))
            ->patch(route('pharmacy.drugs.stock', $drug), [
                'stock' => 35,
                'note' => 'Opname barang',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(35, $drug->refresh()->stock);

        $this->assertDatabaseHas('drug_stock_movements', [
            'drug_id' => $drug->id,
            'type' => StockMovementType::ADJUSTMENT->value,
            'quantity' => 15,
            'stock_before' => 20,
            'stock_after' => 35,
            'note' => 'Opname barang',
        ]);
    }

    public function test_stock_adjustment_can_reduce_stock(): void
    {
        $drug = Drug::factory()->create(['stock' => 50]);

        $this->actingAs($this->userFor(RoleCode::PHARMACIST))
            ->patch(route('pharmacy.drugs.stock', $drug), [
                'stock' => 30,
                'note' => 'Rusak',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(30, $drug->refresh()->stock);

        $this->assertDatabaseHas('drug_stock_movements', [
            'drug_id' => $drug->id,
            'type' => StockMovementType::ADJUSTMENT->value,
            'quantity' => 20,
            'stock_before' => 50,
            'stock_after' => 30,
        ]);
    }

    public function test_stock_cannot_be_negative(): void
    {
        $drug = Drug::factory()->create(['stock' => 20]);

        $this->actingAs($this->userFor(RoleCode::PHARMACIST))
            ->patch(route('pharmacy.drugs.stock', $drug), ['stock' => -5])
            ->assertSessionHasErrors('stock');

        $this->assertSame(20, $drug->refresh()->stock);
    }

    public function test_doctor_cannot_adjust_stock(): void
    {
        $drug = Drug::factory()->create(['stock' => 20]);

        $this->actingAs($this->userFor(RoleCode::DOCTOR))
            ->patch(route('pharmacy.drugs.stock', $drug), ['stock' => 999])
            ->assertForbidden();

        $this->assertSame(20, $drug->refresh()->stock);
    }

    public function test_drug_stock_status_reflects_conditions(): void
    {
        $this->assertSame('ok', Drug::factory()->create(['stock' => 100, 'min_stock' => 10])->stockStatus());
        $this->assertSame('low', Drug::factory()->create(['stock' => 5, 'min_stock' => 10])->stockStatus());
        $this->assertSame('out', Drug::factory()->outOfStock()->create()->stockStatus());
        $this->assertSame(
            'expired',
            Drug::factory()->expired()->create(['stock' => 100, 'min_stock' => 10])->stockStatus()
        );
    }

    public function test_dispense_validates_all_items_before_deducting_any_stock(): void
    {
        $plenty = Drug::factory()->create(['stock' => 100, 'price' => 1000]);
        $scarce = Drug::factory()->create(['stock' => 1, 'price' => 1000]);

        $prescription = Prescription::factory()->create();
        $prescription->items()->createMany([
            ['drug_id' => $plenty->id, 'quantity' => 10, 'unit_price' => 1000, 'subtotal' => 10000],
            ['drug_id' => $scarce->id, 'quantity' => 50, 'unit_price' => 1000, 'subtotal' => 50000],
        ]);

        $service = app(PharmacyService::class);

        try {
            $service->dispense($prescription->fresh('items'));
            $this->fail('Harus menolak karena satu item kekurangan stok.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('tidak mencukupi', $e->getMessage());
        }

        // Tidak ada stok yang terpotong karena validasi gagal.
        $this->assertSame(100, $plenty->refresh()->stock);
        $this->assertSame(1, $scarce->refresh()->stock);
        $this->assertDatabaseCount('drug_stock_movements', 0);
    }

    public function test_prescription_items_expose_readable_sig(): void
    {
        $item = PrescriptionItem::factory()->create([
            'dosage' => '1 tablet',
            'route' => 'oral',
            'frequency' => '3x sehari',
            'duration' => '7 hari',
        ]);

        $this->assertSame('1 tablet · oral · 3x sehari · 7 hari', $item->sig);
    }
}
