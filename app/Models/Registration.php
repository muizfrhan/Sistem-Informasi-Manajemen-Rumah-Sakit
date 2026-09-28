<?php

namespace App\Models;

use App\Enums\RegistrationStatus;
use App\Enums\VisitType;
use Database\Factories\RegistrationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    /** @use HasFactory<RegistrationFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'registration_number',
        'patient_id',
        'poly_id',
        'doctor_id',
        'visit_date',
        'queue_number',
        'queue_code',
        'status',
        'is_emergency',
        'complaint',
        'visit_type',
        'registered_at',
        'called_at',
        'examined_at',
        'finished_at',
        'registered_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => RegistrationStatus::class,
            'visit_type' => VisitType::class,
            'visit_date' => 'date',
            'registered_at' => 'datetime',
            'called_at' => 'datetime',
            'examined_at' => 'datetime',
            'finished_at' => 'datetime',
            'is_emergency' => 'boolean',
            'queue_number' => 'integer',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function poly(): BelongsTo
    {
        return $this->belongsTo(Poly::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function registrar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function admission(): HasOne
    {
        return $this->hasOne(InpatientAdmission::class);
    }

    public function scopeForToday(Builder $query): Builder
    {
        return $query->whereDate('visit_date', today());
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [
            RegistrationStatus::REGISTERED,
            RegistrationStatus::QUEUING,
            RegistrationStatus::CALLED,
            RegistrationStatus::IN_EXAMINATION,
        ]);
    }
}
