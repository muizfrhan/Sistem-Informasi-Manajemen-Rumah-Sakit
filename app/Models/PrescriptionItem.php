<?php

namespace App\Models;

use Database\Factories\PrescriptionItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrescriptionItem extends Model
{
    /** @use HasFactory<PrescriptionItemFactory> */
    use HasFactory;

    protected $fillable = [
        'prescription_id',
        'drug_id',
        'dosage',
        'route',
        'frequency',
        'duration',
        'instructions',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(DrugStockMovement::class);
    }

    /** Aturan pakai yang disusun dalam satu baris, mis. "1 tablet / 3x sehari". */
    public function getSigAttribute(): string
    {
        return collect([
            $this->dosage ? rtrim($this->dosage, '.') : null,
            $this->route,
            $this->frequency,
            $this->duration,
        ])->filter()->implode(' · ');
    }
}
