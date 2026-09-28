<?php

namespace App\Enums;

enum RoomStatus: string
{
    case AVAILABLE = 'AVAILABLE';
    case MAINTENANCE = 'MAINTENANCE';

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Operasional',
            self::MAINTENANCE => 'Pemeliharaan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::AVAILABLE => 'bg-emerald-100 text-emerald-800',
            self::MAINTENANCE => 'bg-amber-100 text-amber-800',
        };
    }
}
