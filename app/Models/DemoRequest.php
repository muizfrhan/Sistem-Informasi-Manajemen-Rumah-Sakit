<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemoRequest extends Model
{
    protected $fillable = [
        'name',
        'institution',
        'position',
        'email',
        'phone',
        'facility_type',
        'bed_count',
        'message',
        'status',
        'internal_note',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'bed_count' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function isNew(): bool
    {
        return $this->status === 'NEW';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'NEW' => 'Baru',
            'CONTACTED' => 'Sudah Dihubungi',
            'DEMO_SCHEDULED' => 'Demo Dijadwalkan',
            'WON' => 'Menjadi Klien',
            'LOST' => 'Belum Tertarik',
            default => $this->status,
        };
    }

    public function facilityTypeLabel(): string
    {
        return match ($this->facility_type) {
            'HOSPITAL' => 'Rumah Sakit',
            'CLINIC' => 'Klinik',
            'PRIVATE_PRACTICE' => 'Praktik Mandiri',
            default => 'Lainnya',
        };
    }
}
