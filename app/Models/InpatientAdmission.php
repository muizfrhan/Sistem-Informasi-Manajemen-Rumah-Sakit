<?php

namespace App\Models;

use App\Enums\AdmissionStatus;
use Database\Factories\InpatientAdmissionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InpatientAdmission extends Model
{
    /** @use HasFactory<InpatientAdmissionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'admission_number',
        'patient_id',
        'registration_id',
        'room_id',
        'doctor_id',
        'bed_number',
        'status',
        'bed_occupancy_key',
        'diagnosis',
        'notes',
        'admitted_at',
        'discharged_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AdmissionStatus::class,
            'bed_occupancy_key' => 'string',
            'admitted_at' => 'datetime',
            'discharged_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Kunci beds selalu mengikuti status, sehingga tidak mungkin
        // tertinggal bilaXiv admission sudah berakhir.
        static::saving(function (self $admission) {
            $admission->bed_occupancy_key = $admission->status === AdmissionStatus::ADMITTED
                ? $admission->room_id.'-'.strtoupper(trim((string) $admission->bed_number))
                : null;
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function scopeAdmitted(Builder $query): Builder
    {
        return $query->where('status', AdmissionStatus::ADMITTED);
    }

    /** Lama rawat dalam hari, dibulatkan ke atas. */
    public function getLengthOfStayAttribute(): int
    {
        $end = $this->discharged_at ?? now();

        return max(1, (int) $end->diffInDays($this->admitted_at) + 1);
    }
}
