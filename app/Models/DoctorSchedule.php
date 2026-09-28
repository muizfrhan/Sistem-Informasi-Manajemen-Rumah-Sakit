<?php

namespace App\Models;

use Database\Factories\DoctorScheduleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Jadwal praktik dokter per hari dalam seminggu (1 = Senin ... 7 = Minggu).
 */
class DoctorSchedule extends Model
{
    /** @use HasFactory<DoctorScheduleFactory> */
    use HasFactory;

    /** @var array<int, string> */
    public const DAYS = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    /** @var array<int, string> Urutan untuk iterasi grid mingguan. */
    public const ORDERED_DAYS = [1, 2, 3, 4, 5, 6, 7];

    protected $fillable = [
        'doctor_id',
        'poly_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_minutes',
        'quota',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'slot_minutes' => 'integer',
            'quota' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function poly(): BelongsTo
    {
        return $this->belongsTo(Poly::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForDay(Builder $query, int $day): Builder
    {
        return $query->where('day_of_week', $day);
    }

    public function getDayLabelAttribute(): string
    {
        return self::DAYS[$this->day_of_week] ?? '-';
    }

    public function getTimeRangeAttribute(): string
    {
        return substr((string) $this->start_time, 0, 5).' - '.substr((string) $this->end_time, 0, 5);
    }

    /** Perkiraan jumlah pasien yang dapat dilayani per hari. */
    public function getCapacityAttribute(): int
    {
        if (! $this->slot_minutes) {
            return 0;
        }

        $start = strtotime(substr((string) $this->start_time, 0, 5));
        $end = strtotime(substr((string) $this->end_time, 0, 5));

        $minutes = max(0, (int) round(($end - $start) / 60));

        return intdiv($minutes, $this->slot_minutes);
    }
}
