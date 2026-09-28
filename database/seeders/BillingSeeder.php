<?php

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\MedicalRecord;
use App\Services\BillingService;
use App\Services\NumberSequenceService;
use Illuminate\Database\Seeder;

/**
 * Seeder penagihan. Tagihan disusun dari rekam medis yang sudah difinalisasi
 * sehingga nominal selalu konsisten dengan tindakan yang tercatat.
 */
class BillingSeeder extends Seeder
{
    public function __construct(
        private readonly BillingService $billing,
        private readonly NumberSequenceService $sequence,
    ) {}

    public function run(): void
    {
        $records = MedicalRecord::query()
            ->finalized()
            ->whereDoesntHave('registration.invoices')
            ->with('prescriptions.items')
            ->orderBy('id')
            ->get();

        if ($records->isEmpty()) {
            return;
        }

        $methods = [PaymentMethod::CASH, PaymentMethod::QRIS, PaymentMethod::BPJS, PaymentMethod::CARD];
        $now = now();

        foreach ($records as $index => $record) {
            $issuedAt = $now->copy()->subDays(max(0, $records->count() - $index));

            // Sebagian ditagih dengan diskon agar ada riwayat pembayaran bertahap.
            $invoice = $this->billing->generateFromMedicalRecord($record, 50000);

            $invoice->forceFill(['issued_at' => $issuedAt, 'created_at' => $issuedAt])->save();

            if ($invoice->total <= 0) {
                continue;
            }

            // Tiga dari empat tagihan dibayar penuh, sisanya dibayar sebagian.
            $payFull = $index % 4 !== 3;

            if ($payFull) {
                $method = $invoice->insurance_type === 'BPJS'
                    ? PaymentMethod::BPJS
                    : $methods[$index % 3];

                $this->billing->recordPayment(
                    invoice: $invoice->refresh(),
                    amount: $invoice->total,
                    method: $method,
                    reference: 'TRX-'.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT),
                );

                continue;
            }

            $partial = (int) floor($invoice->total / 2);

            if ($partial > 0) {
                $this->billing->recordPayment(
                    invoice: $invoice->refresh(),
                    amount: $partial,
                    method: PaymentMethod::CASH,
                    reference: 'DP-'.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT),
                );
            }
        }

        // Pastikan ada setidaknya satu tagihan berstatus Lunas agar dashboard
        // menampilkan pendapatan yang bukan nol.
        if (! Invoice::query()->paid()->exists()) {
            $first = Invoice::query()->where('total', '>', 0)->orderBy('id')->first();

            if ($first !== null) {
                $first->forceFill(['status' => InvoiceStatus::PAID->value])->save();
            }
        }
    }
}
