<?php

namespace App\Enums;

enum PrescriptionStatus: string
{
    case DRAFT = 'DRAFT';
    case DISPENSED = 'DISPENSED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Menunggu Apoteker',
            self::DISPENSED => 'Sudah Diserahkan',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-amber-100 text-amber-800',
            self::DISPENSED => 'bg-emerald-100 text-emerald-800',
            self::CANCELLED => 'bg-slate-200 text-slate-700',
        };
    }
}
