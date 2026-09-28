<?php

namespace App\Enums;

enum AdmissionStatus: string
{
    case ADMITTED = 'ADMITTED';
    case DISCHARGED = 'DISCHARGED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::ADMITTED => 'Dirawat',
            self::DISCHARGED => 'Pulang',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::ADMITTED => 'bg-sky-100 text-sky-800',
            self::DISCHARGED => 'bg-emerald-100 text-emerald-800',
            self::CANCELLED => 'bg-slate-200 text-slate-700',
        };
    }
}
