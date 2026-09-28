<?php

namespace App\Services;

use App\Enums\PrescriptionStatus;
use App\Enums\StockMovementType;
use App\Models\Drug;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use Illuminate\Support\Facades\DB;

class PharmacyService
{
    public function __construct(private readonly NumberSequenceService $sequence) {}

    /**
     * Buat resep untuk sebuah rekam medis beserta itemnya.
     *
     * Nomor resep dibuat di sini (bukan oleh controller) agar kolom NOT NULL
     * selalu terisi dan penomoran hanya memiliki satu sumber.
     *
     * @param  list<array{drug_id:int, dosage?:string|null, route?:string|null, frequency?:string|null, duration?:string|null, instructions?:string|null, quantity?:int|null}>  $items
     */
    public function createForRecord(
        MedicalRecord $record,
        array $items,
        ?string $notes = null,
    ): Prescription {
        $prescription = Prescription::create([
            'prescription_number' => $this->sequence->next('prescription_number', 'RS-', null, 6),
            'medical_record_id' => $record->id,
            'patient_id' => $record->patient_id,
            'doctor_id' => $record->doctor_id,
            'status' => PrescriptionStatus::DRAFT,
            'notes' => $notes,
        ]);

        return $this->addItems($prescription, $items);
    }

    /**
     * Tambahkan item obat pada resep yang sudah ada.
     *
     * @param  list<array{drug_id:int, dosage?:string|null, route?:string|null, frequency?:string|null, duration?:string|null, instructions?:string|null, quantity?:int|null}>  $items
     */
    public function addItems(Prescription $prescription, array $items): Prescription
    {
        return DB::transaction(function () use ($prescription, $items) {
            $drugs = Drug::whereIn('id', array_column($items, 'drug_id'))->get()->keyBy('id');

            foreach ($items as $item) {
                $drug = $drugs[$item['drug_id']] ?? null;

                if ($drug === null) {
                    continue;
                }

                $quantity = max(1, (int) ($item['quantity'] ?? 1));

                $prescription->items()->create([
                    'drug_id' => $drug->id,
                    'dosage' => $item['dosage'] ?? null,
                    'route' => $item['route'] ?? null,
                    'frequency' => $item['frequency'] ?? null,
                    'duration' => $item['duration'] ?? null,
                    'instructions' => $item['instructions'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $drug->price,
                    'subtotal' => $quantity * $drug->price,
                ]);
            }

            return $prescription->refresh()->load('items.drug');
        });
    }

    /**
     * Serahkan resep: potong stok tiap obat dan catat mutasi stok.
     * Stok tidak boleh menjadi negatif.
     */
    public function dispense(Prescription $prescription): Prescription
    {
        if ($prescription->status !== PrescriptionStatus::DRAFT) {
            throw new \RuntimeException("Resep {$prescription->prescription_number} sudah tidak berstatus menunggu.");
        }

        if ($prescription->items->isEmpty()) {
            throw new \RuntimeException('Resep tidak memiliki item obat.');
        }

        return DB::transaction(function () use ($prescription) {
            $items = $prescription->items()->with('drug')->get();

            // Validasi stok seluruh item lebih dulu agar tidak ada resultset setengah.
            foreach ($items as $item) {
                if ($item->drug === null) {
                    throw new \RuntimeException("Data obat pada item #{$item->id} tidak ditemukan.");
                }

                if ($item->drug->isExpired()) {
                    throw new \RuntimeException("Obat {$item->drug->name} sudah kedaluwarsa dan tidak dapat dihentikan.");
                }

                if ($item->drug->stock < $item->quantity) {
                    throw new \RuntimeException(
                        "Stok {$item->drug->name} tidak mencukupi. Tersedia {$item->drug->stock} {$item->drug->unit}, diminta {$item->quantity}."
                    );
                }
            }

            foreach ($items as $item) {
                $this->applyStockMovement(
                    drug: $item->drug,
                    type: StockMovementType::OUT,
                    signedQuantity: -$item->quantity,
                    note: "Penyerahan resep {$prescription->prescription_number}",
                    prescriptionItemId: $item->id,
                );
            }

            $prescription->forceFill([
                'status' => PrescriptionStatus::DISPENSED,
                'dispensed_at' => now(),
                'dispensed_by' => auth()->id(),
            ])->save();

            return $prescription->refresh();
        });
    }

    public function cancel(Prescription $prescription): Prescription
    {
        if ($prescription->status !== PrescriptionStatus::DRAFT) {
            throw new \RuntimeException('Resep yang sudah diserahkan tidak dapat dibatalkan.');
        }

        $prescription->forceFill(['status' => PrescriptionStatus::CANCELLED])->save();

        return $prescription->refresh();
    }

    /** Penyesuaian stok manual oleh apoteker (opname, koreksi, Vitamins). */
    public function adjustStock(Drug $drug, int $targetStock, string $note = 'Penyesuaian stok'): Drug
    {
        if ($targetStock < 0) {
            throw new \RuntimeException('Jumlah stok tidak boleh negatif.');
        }

        return DB::transaction(function () use ($drug, $targetStock, $note) {
            $delta = $targetStock - (int) $drug->stock;

            if ($delta !== 0) {
                $this->applyStockMovement(
                    drug: $drug,
                    type: StockMovementType::ADJUSTMENT,
                    signedQuantity: $delta,
                    note: $note,
                );
            }

            return $drug->refresh();
        });
    }

    /**
     * Menerapkan satu mutasi stok dan mencatat jejaknya.
     *
     * @param  int  $signedQuantity  Selisih bertanda: positif menambah, negatif mengurangi.
     *                               Nilai mutasi yang disimpan tetap positif.
     */
    protected function applyStockMovement(
        Drug $drug,
        StockMovementType $type,
        int $signedQuantity,
        string $note,
        ?int $prescriptionItemId = null,
    ): void {
        $before = (int) $drug->stock;
        $after = $before + $signedQuantity;

        // Stok tidak boleh menjadi negatif, termasuk lewat penyesuaian.
        if ($after < 0) {
            throw new \RuntimeException("Stok {$drug->name} tidak cukup untuk pengurangan tersebut.");
        }

        $drug->forceFill(['stock' => $after])->save();

        $drug->stockMovements()->create([
            'prescription_item_id' => $prescriptionItemId,
            'type' => $type,
            'quantity' => abs($signedQuantity),
            'stock_before' => $before,
            'stock_after' => $after,
            'note' => $note,
            'user_id' => auth()->id(),
        ]);
    }
}
