<?php

namespace App\Models;

use App\Enums\AdmissionStatus;
use App\Enums\GenderPolicy;
use App\Enums\RoomClass;
use App\Enums\RoomStatus;
use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'room_class',
        'capacity',
        'status',
        'gender_policy',
        'daily_rate',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'room_class' => RoomClass::class,
            'status' => RoomStatus::class,
            'gender_policy' => GenderPolicy::class,
            'capacity' => 'integer',
            'daily_rate' => 'integer',
        ];
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(InpatientAdmission::class);
    }

    /**
     * Pasien yang sedang menghuni kamar ini.
     *
     * Catatan: nama relasi yang ditebak Laravel adalah `activeAdmissions`
     * (camelCase, mengikuti nama method), bukan `active_admissions`. Karena itu
     * akses pada property harus memakai nama tersebut.
     */
    public function activeAdmissions(): HasMany
    {
        return $this->hasMany(InpatientAdmission::class, 'room_id')
            ->where('status', AdmissionStatus::ADMITTED);
    }

    /** Jumlah tempat tidur yang sedang dihuni pasien. */
    public function occupiedBeds(): int
    {
        return (int) ($this->active_admissions_count
            ?? $this->activeAdmissions()->count());
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', RoomStatus::AVAILABLE);
    }

    /** Kapasitas terisi berdasarkan tempat tidur yang sedang dihuni pasien. */
    public function availableBeds(): int
    {
        return max(0, (int) $this->capacity - $this->occupiedBeds());
    }

    public function isFullyOccupied(): bool
    {
        return $this->availableBeds() === 0;
    }
}
