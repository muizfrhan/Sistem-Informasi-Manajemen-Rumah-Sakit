<?php

namespace App\Models;

use App\Enums\DiagnosisType;
use App\Enums\MedicalRecordStatus;
use App\Enums\MedicalRecordType;
use Database\Factories\MedicalRecordFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalRecord extends Model
{
    /** @use HasFactory<MedicalRecordFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'registration_id',
        'patient_id',
        'doctor_id',
        'created_by',
        'record_type',
        'status',
        'chief_complaint',
        'anamnesis',
        'physical_exam',
        'diagnosis',
        'treatment_plan',
        'notes',
        'blood_pressure',
        'pulse',
        'temperature',
        'respiratory_rate',
        'weight',
        'height',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'record_type' => MedicalRecordType::class,
            'status' => MedicalRecordStatus::class,
            'finalized_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(MedicalRecordDiagnosis::class);
    }

    public function procedures(): HasMany
    {
        return $this->hasMany(MedicalRecordProcedure::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function scopeFinalized(Builder $query): Builder
    {
        return $query->where('status', MedicalRecordStatus::FINALIZED);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', MedicalRecordStatus::DRAFT);
    }

    public function getPrimaryDiagnosisAttribute(): ?MedicalRecordDiagnosis
    {
        return $this->diagnoses->firstWhere('diagnosis_type', DiagnosisType::PRIMARY);
    }

    /** Ringkasan vital sign dalam format singkat, atau null bila belum diisi. */
    public function getVitalsSummaryAttribute(): ?string
    {
        $parts = array_filter([
            $this->blood_pressure ? 'TD '.$this->blood_pressure.' mmHg' : null,
            $this->pulse ? 'Nadi '.$this->pulse.'/mnt' : null,
            $this->temperature ? 'Suhu '.$this->temperature.' °C' : null,
            $this->respiratory_rate ? 'RR '.$this->respiratory_rate.'/mnt' : null,
            $this->weight ? 'BB '.$this->weight.' kg' : null,
            $this->height ? 'TB '.$this->height.' cm' : null,
        ]);

        return $parts === [] ? null : implode(' · ', $parts);
    }
}
