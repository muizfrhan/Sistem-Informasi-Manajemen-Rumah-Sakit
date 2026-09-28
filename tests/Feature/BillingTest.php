<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\MedicalRecordStatus;
use App\Enums\PaymentMethod;
use App\Enums\RoleCode;
use App\Models\Drug;
use App\Models\Invoice;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    private function finalizedRecord(float $fee = 100000): MedicalRecord
    {
        $record = MedicalRecord::factory()->create([
            'status' => MedicalRecordStatus::FINALIZED,
            'finalized_at' => now(),
        ]);

        $record->procedures()->create(['name' => 'Observasi', 'fee' => (int) $fee]);

        return $record;
    }

    public function test_invoice_is_generated_from_a_finalized_record(): void
    {
        $record = $this->finalizedRecord(100000);

        $this->actingAs($this->userFor(RoleCode::CASHIER))
            ->post(route('billing.store'), [
                'medical_record_id' => $record->id,
                'consultation_fee' => 50000,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $invoice = Invoice::firstOrFail();

        $this->assertSame(InvoiceStatus::UNPAID, $invoice->status);
        $this->assertSame(150000, $invoice->total);
        $this->assertSame(0, $invoice->paid_total);
        $this->assertCount(2, $invoice->items);
    }

    public function test_invoice_includes_dispensed_prescription_items(): void
    {
        $record = $this->finalizedRecord(0);

        $drug = Drug::factory()->create(['price' => 2000]);
        $prescription = Prescription::factory()->create(['medical_record_id' => $record->id]);
        $prescription->items()->create([
            'drug_id' => $drug->id, 'quantity' => 5,
            'unit_price' => 2000, 'subtotal' => 10000,
        ]);

        app(BillingService::class)->generateFromMedicalRecord($record, 0);

        $invoice = Invoice::firstOrFail();

        $this->assertSame(10000, $invoice->total);
        $this->assertSame('DRUG', $invoice->items->first()->item_type->value);
    }

    public function test_draft_record_cannot_be_billed(): void
    {
        $record = MedicalRecord::factory()->create(['status' => MedicalRecordStatus::DRAFT]);

        $this->actingAs($this->userFor(RoleCode::CASHIER))
            ->post(route('billing.store'), ['medical_record_id' => $record->id])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(0, Invoice::count());
    }

    public function test_a_visit_cannot_be_billed_twice(): void
    {
        $record = $this->finalizedRecord();
        $cashier = $this->userFor(RoleCode::CASHIER);

        $this->actingAs($cashier)
            ->post(route('billing.store'), ['medical_record_id' => $record->id])
            ->assertSessionHas('success');

        $this->actingAs($cashier)
            ->post(route('billing.store'), ['medical_record_id' => $record->id])
            ->assertSessionHas('error');

        $this->assertSame(1, Invoice::count());
    }

    public function test_full_payment_marks_invoice_paid(): void
    {
        $invoice = Invoice::factory()->withItem(150000)->create();

        $this->actingAs($this->userFor(RoleCode::CASHIER))
            ->post(route('billing.payments.store', $invoice), [
                'amount' => 150000,
                'method' => PaymentMethod::CASH->value,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $invoice->refresh();

        $this->assertSame(InvoiceStatus::PAID, $invoice->status);
        $this->assertSame(150000, $invoice->paid_total);
        $this->assertNotNull($invoice->paid_at);
        $this->assertSame(0, $invoice->outstanding);
    }

    public function test_partial_payment_marks_invoice_partial(): void
    {
        $invoice = Invoice::factory()->withItem(150000)->create();

        $this->actingAs($this->userFor(RoleCode::CASHIER))
            ->post(route('billing.payments.store', $invoice), [
                'amount' => 50000,
                'method' => PaymentMethod::QRIS->value,
            ])
            ->assertSessionHas('success');

        $invoice->refresh();

        $this->assertSame(InvoiceStatus::PARTIAL, $invoice->status);
        $this->assertSame(50000, $invoice->paid_total);
        $this->assertSame(100000, $invoice->outstanding);
        $this->assertNull($invoice->paid_at);
    }

    public function test_overpayment_is_rejected(): void
    {
        $invoice = Invoice::factory()->withItem(100000)->create();

        $this->actingAs($this->userFor(RoleCode::CASHIER))
            ->post(route('billing.payments.store', $invoice), [
                'amount' => 150000,
                'method' => PaymentMethod::CASH->value,
            ])
            ->assertSessionHas('error');

        $this->assertSame(0, Invoice::first()->paid_total);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_paid_invoice_cannot_receive_more_payment(): void
    {
        $invoice = Invoice::factory()->withItem(100000)->paid()->create();

        $this->actingAs($this->userFor(RoleCode::CASHIER))
            ->post(route('billing.payments.store', $invoice), [
                'amount' => 1000,
                'method' => PaymentMethod::CASH->value,
            ])
            ->assertSessionHas('error');
    }

    public function test_doctor_cannot_record_payment(): void
    {
        $invoice = Invoice::factory()->withItem()->create();

        $this->actingAs($this->userFor(RoleCode::DOCTOR))
            ->post(route('billing.payments.store', $invoice), [
                'amount' => 1000,
                'method' => PaymentMethod::CASH->value,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_receptionist_cannot_record_payment(): void
    {
        $invoice = Invoice::factory()->withItem()->create();

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->post(route('billing.payments.store', $invoice), [
                'amount' => 1000,
                'method' => PaymentMethod::CASH->value,
            ])
            ->assertForbidden();
    }

    public function test_cashier_can_manage_billing_but_not_patient_data(): void
    {
        Invoice::factory()->withItem()->create();

        // Kasir memegang izin penagihan, sehingga boleh membuat tagihan.
        $this->actingAs($this->userFor(RoleCode::CASHIER))
            ->get(route('billing.index'))
            ->assertOk();

        $this->actingAs($this->userFor(RoleCode::CASHIER))
            ->get(route('billing.create'))
            ->assertOk();

        // Namun tidak boleh mengelola data pasien.
        $this->actingAs($this->userFor(RoleCode::CASHIER))
            ->get(route('patients.create'))
            ->assertForbidden();
    }

    public function test_invoice_with_payment_cannot_be_cancelled(): void
    {
        $invoice = Invoice::factory()->withItem(100000)->partial(50000)->create();

        $this->actingAs($this->userFor(RoleCode::CASHIER))
            ->delete(route('billing.destroy', $invoice))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(InvoiceStatus::PARTIAL, $invoice->refresh()->status);
    }

    public function test_unpaid_invoice_can_be_cancelled(): void
    {
        $invoice = Invoice::factory()->withItem()->create();

        $this->actingAs($this->userFor(RoleCode::CASHIER))
            ->delete(route('billing.destroy', $invoice))
            ->assertRedirect(route('billing.index'))
            ->assertSessionHas('success');

        $this->assertSame(InvoiceStatus::CANCELLED, $invoice->refresh()->status);
    }

    public function test_discount_is_applied_to_total(): void
    {
        $record = $this->finalizedRecord(200000);
        $invoice = app(BillingService::class)->generateFromMedicalRecord($record, 0);

        app(BillingService::class)->setDiscount($invoice, 50000);

        $invoice->refresh();

        $this->assertSame(200000, $invoice->subtotal);
        $this->assertSame(50000, $invoice->discount);
        $this->assertSame(150000, $invoice->total);
        $this->assertSame(150000, $invoice->outstanding);
    }

    public function test_discount_cannot_exceed_subtotal(): void
    {
        $record = $this->finalizedRecord(100000);
        $invoice = app(BillingService::class)->generateFromMedicalRecord($record, 0);

        app(BillingService::class)->setDiscount($invoice, 500000);

        $invoice->refresh();

        // Diskon dibatasi pada nilai subtotal, dan tagihan tanpa sisa
        // tagihan dianggap lunas (bukan draf).
        $this->assertSame(100000, $invoice->subtotal);
        $this->assertSame(100000, $invoice->discount);
        $this->assertSame(0, $invoice->total);
        $this->assertSame(InvoiceStatus::PAID, $invoice->status);
    }
}
