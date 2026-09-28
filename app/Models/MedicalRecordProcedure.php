<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalRecordProcedure extends Model
{
    protected $fillable = [
        'medical_record_id',
        'name',
        'code',
        'fee',
        'notes',
    ];

    protected function casts(): array
    {
        return ['fee' => 'integer'];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }
}
