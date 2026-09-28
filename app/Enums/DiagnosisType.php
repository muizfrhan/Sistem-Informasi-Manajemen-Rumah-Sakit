<?php

namespace App\Enums;

enum DiagnosisType: string
{
    case PRIMARY = 'PRIMARY';
    case SECONDARY = 'SECONDARY';
    case DIFFERENTIAL = 'DIFFERENTIAL';

    public function label(): string
    {
        return match ($this) {
            self::PRIMARY => 'Diagnosis Utama',
            self::SECONDARY => 'Diagnosis Tambahan',
            self::DIFFERENTIAL => 'Diagnosis Diferensial',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PRIMARY => 'bg-rose-100 text-rose-800',
            self::SECONDARY => 'bg-amber-100 text-amber-800',
            self::DIFFERENTIAL => 'bg-slate-100 text-slate-700',
        };
    }
}
