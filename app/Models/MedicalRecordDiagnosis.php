<?php

namespace App\Models;

use App\Enums\DiagnosisType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalRecordDiagnosis extends Model
{
    protected $fillable = [
        'medical_record_id',
        'icd10_code_id',
        'diagnosis_type',
        'description',
    ];

    protected function casts(): array
    {
        return ['diagnosis_type' => DiagnosisType::class];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function icd10Code(): BelongsTo
    {
        return $this->belongsTo(Icd10Code::class, 'icd10_code_id');
    }
}
