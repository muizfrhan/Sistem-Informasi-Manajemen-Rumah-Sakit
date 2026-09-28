<?php

namespace App\Models;

use App\Enums\PrescriptionStatus;
use Database\Factories\PrescriptionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Model
{
    /** @use HasFactory<PrescriptionFactory> */
    use HasFactory;

    protected $fillable = [
        'prescription_number',
        'medical_record_id',
        'patient_id',
        'doctor_id',
        'status',
        'notes',
        'dispensed_at',
        'dispensed_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => PrescriptionStatus::class,
            'dispensed_at' => 'datetime',
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function dispenser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', PrescriptionStatus::DRAFT);
    }

    public function scopeDispensed(Builder $query): Builder
    {
        return $query->where('status', PrescriptionStatus::DISPENSED);
    }

    public function getTotalAttribute(): int
    {
        return (int) $this->items->sum('subtotal');
    }

    public function getItemCountAttribute(): int
    {
        return $this->items->count();
    }
}
