<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Drug extends Model
{
    protected $fillable = [
        'code',
        'name',
        'generic_name',
        'form',
        'strength',
        'unit',
        'price',
        'stock',
        'min_stock',
        'requires_prescription',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock' => 'integer',
            'min_stock' => 'integer',
            'requires_prescription' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
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
            ->orWhere('generic_name', 'ILIKE', $like));
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    public function getDisplayNameAttribute(): string
    {
        return collect([$this->name, $this->strength, $this->form])
            ->filter()
            ->implode(' ');
    }
}
