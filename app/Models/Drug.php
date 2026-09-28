<?php

namespace App\Models;

use Database\Factories\DrugFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Drug extends Model
{
    /** @use HasFactory<DrugFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'generic_name',
        'category',
        'form',
        'strength',
        'unit',
        'price',
        'stock',
        'min_stock',
        'expired_at',
        'requires_prescription',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock' => 'integer',
            'min_stock' => 'integer',
            'expired_at' => 'date',
            'requires_prescription' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(DrugStockMovement::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    public function isExpired(): bool
    {
        return $this->expired_at !== null && $this->expired_at->isPast();
    }

    /** Tanggal kedaluwarsa dalam 90 hari ke depan. */
    public function isExpiringSoon(int $days = 90): bool
    {
        return $this->expired_at !== null
            && ! $this->expired_at->isPast()
            && $this->expired_at->lte(today()->addDays($days));
    }

    /**
     * Status kesehatan stok untuk badge di UI.
     * Urutan prioritas: kedaluwarsa > habis > menipis > aman.
     */
    public function stockStatus(): string
    {
        return match (true) {
            $this->isExpired() => 'expired',
            $this->isOutOfStock() => 'out',
            $this->isLowStock() => 'low',
            default => 'ok',
        };
    }

    public function stockStatusLabel(): string
    {
        return match ($this->stockStatus()) {
            'expired' => 'Kedaluwarsa',
            'out' => 'Stok Habis',
            'low' => 'Stok Menipis',
            default => 'Stok Aman',
        };
    }

    public function stockStatusBadgeClass(): string
    {
        return match ($this->stockStatus()) {
            'expired' => 'bg-rose-100 text-rose-800',
            'out' => 'bg-rose-100 text-rose-800',
            'low' => 'bg-amber-100 text-amber-800',
            default => 'bg-emerald-100 text-emerald-800',
        };
    }

    public function scopeExpiring(Builder $query, int $days = 90): Builder
    {
        return $query->whereNotNull('expired_at')
            ->whereDate('expired_at', '<=', today()->addDays($days)->toDateString());
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

        return $query->where(fn (Builder $q) => $q
            ->where('code', 'ILIKE', $like)
            ->orWhere('name', 'ILIKE', $like)
            ->orWhere('generic_name', 'ILIKE', $like)
            ->orWhere('category', 'ILIKE', $like));
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }

    public function getDisplayNameAttribute(): string
    {
        return collect([$this->name, $this->strength, $this->form])
            ->filter()
            ->implode(' ');
    }
}
