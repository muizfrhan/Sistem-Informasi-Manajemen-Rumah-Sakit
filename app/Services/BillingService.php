<?php

namespace App\Services;

use App\Enums\InvoiceItemType;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\MedicalRecord;
use App\Models\Payment;
use App\Models\Prescription;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function __construct(private readonly NumberSequenceService $sequence) {}

    /**
     * Susun tagihan dari rekam medis: satu baris konsultasi, setiap tindakan,
     * dan setiap obat pada resep yang sudah diserahterimakan.
     */
    public function generateFromMedicalRecord(MedicalRecord $record, int $consultationFee = 0): Invoice
    {
        return DB::transaction(function () use ($record, $consultationFee) {
            $patient = $record->patient;

            $invoice = Invoice::create([
                'invoice_number' => $this->sequence->next('invoice_number', 'INV-', null, 6),
                'patient_id' => $patient->id,
                'registration_id' => $record->registration_id,
                'created_by' => auth()->id(),
                'status' => InvoiceStatus::UNPAID,
                'insurance_type' => $patient->insurance_type->value,
                'insurance_holder' => $patient->insurance_name,
                'issued_at' => today(),
                'due_at' => today()->addDays(7),
            ]);

            if ($consultationFee > 0) {
                $invoice->items()->create([
                    'item_type' => InvoiceItemType::CONSULTATION,
                    'description' => 'Biaya konsultasi '.($record->doctor?->name ?? 'dokter'),
                    'quantity' => 1,
                    'unit_price' => $consultationFee,
                    'subtotal' => $consultationFee,
                ]);
            }

            foreach ($record->procedures as $procedure) {
                if ($procedure->fee <= 0) {
                    continue;
                }

                $invoice->items()->create([
                    'item_type' => InvoiceItemType::PROCEDURE,
                    'description' => $procedure->name,
                    'source_type' => $procedure->getMorphClass(),
                    'source_id' => $procedure->id,
                    'quantity' => 1,
                    'unit_price' => $procedure->fee,
                    'subtotal' => $procedure->fee,
                ]);
            }

            foreach ($record->prescriptions()->with('items')->get() as $prescription) {
                $this->appendPrescriptionItems($invoice, $prescription);
            }

            return $this->recalculate($invoice);
        });
    }

    public function addManualItem(Invoice $invoice, string $description, int $quantity, int $unitPrice, string $type = 'OTHER'): Invoice
    {
        $this->assertEditable($invoice);

        $invoice->items()->create([
            'item_type' => $type,
            'description' => $description,
            'quantity' => max(1, $quantity),
            'unit_price' => max(0, $unitPrice),
            'subtotal' => max(1, $quantity) * max(0, $unitPrice),
        ]);

        return $this->recalculate($invoice);
    }

    public function removeItem(Invoice $invoice, int $itemId): Invoice
    {
        $this->assertEditable($invoice);

        $invoice->items()->whereKey($itemId)->delete();

        return $this->recalculate($invoice);
    }

    public function setDiscount(Invoice $invoice, int $discount): Invoice
    {
        $this->assertEditable($invoice);

        if ($discount < 0) {
            throw new \RuntimeException('Diskon tidak boleh negatif.');
        }

        $invoice->forceFill(['discount' => $discount])->save();

        return $this->recalculate($invoice);
    }

    /** Catat pembayaran. Mendukung pembayaran parsial; status invoice diturunkan otomatis. */
    public function recordPayment(Invoice $invoice, int $amount, PaymentMethod $method, ?string $reference = null, ?string $notes = null): Payment
    {
        if (! $invoice->status->isOpen()) {
            throw new \RuntimeException("Tagihan {$invoice->invoice_number} sudah berstatus {$invoice->status->label()}.");
        }

        $outstanding = $invoice->outstanding;

        if ($amount <= 0) {
            throw new \RuntimeException('Nominal pembayaran harus lebih besar dari nol.');
        }

        if ($amount > $outstanding) {
            throw new \RuntimeException(
                'Nominal pembayaran melebihi sisa tagihan. Sisa tagihan saat ini: Rp '.number_format($outstanding, 0, ',', '.')
            );
        }

        return DB::transaction(function () use ($invoice, $amount, $method, $reference, $notes) {
            $payment = $invoice->payments()->create([
                'payment_number' => $this->sequence->next('payment_number', 'PAY-', null, 6),
                'received_by' => auth()->id(),
                'amount' => $amount,
                'method' => $method,
                'reference' => $reference,
                'paid_at' => now(),
                'notes' => $notes,
            ]);

            $this->recalculate($invoice);

            return $payment;
        });
    }

    public function cancel(Invoice $invoice): Invoice
    {
        if ($invoice->paid_total > 0) {
            throw new \RuntimeException('Tagihan yang sudah menerima pembayaran tidak dapat dibatalkan.');
        }

        $invoice->forceFill(['status' => InvoiceStatus::CANCELLED])->save();

        return $invoice->refresh();
    }

    /** Hitung ulang subtotal, total, dan status berdasarkan item & pembayaran. */
    public function recalculate(Invoice $invoice): Invoice
    {
        $subtotal = (int) $invoice->items()->sum('subtotal');
        $itemCount = (int) $invoice->items()->count();

        // Diskon tidak boleh melebihi nilai tagihan.
        $discount = min((int) $invoice->discount, $subtotal);
        $total = max(0, $subtotal - $discount);
        $paid = (int) $invoice->payments()->sum('amount');

        // DRAFT berarti belum ada rincian biaya sama sekali. Tagihan yang
        // sudah memiliki item tetapi tidak bersisa untuk dibayar (misalnya
        // diskon 100%) dianggap LUNAS, bukan draf.
        $status = match (true) {
            $invoice->status === InvoiceStatus::CANCELLED => InvoiceStatus::CANCELLED,
            $itemCount === 0 => InvoiceStatus::DRAFT,
            $paid >= $total => InvoiceStatus::PAID,
            $paid > 0 => InvoiceStatus::PARTIAL,
            default => InvoiceStatus::UNPAID,
        };

        $invoice->forceFill([
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'paid_total' => $paid,
            'status' => $status,
            'paid_at' => $status === InvoiceStatus::PAID ? ($invoice->paid_at ?? now()) : null,
        ])->save();

        return $invoice->refresh()->load('items', 'payments');
    }

    protected function appendPrescriptionItems(Invoice $invoice, Prescription $prescription): void
    {
        foreach ($prescription->items as $item) {
            if ($item->subtotal <= 0) {
                continue;
            }

            $invoice->items()->create([
                'item_type' => InvoiceItemType::DRUG,
                'description' => $item->drug?->display_name ?? 'Obat',
                'source_type' => $item->getMorphClass(),
                'source_id' => $item->id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal,
            ]);
        }
    }

    protected function assertEditable(Invoice $invoice): void
    {
        if (! $invoice->status->isOpen()) {
            throw new \RuntimeException("Tagihan {$invoice->invoice_number} sudah berstatus {$invoice->status->label()} dan tidak dapat diubah.");
        }
    }
}
