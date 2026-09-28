<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case DRAFT = 'DRAFT';
    case UNPAID = 'UNPAID';
    case PARTIAL = 'PARTIAL';
    case PAID = 'PAID';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draf',
            self::UNPAID => 'Belum Lunas',
            self::PARTIAL => 'L Sebagian',
            self::PAID => 'Lunas',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-slate-100 text-slate-700',
            self::UNPAID => 'bg-rose-100 text-rose-800',
            self::PARTIAL => 'bg-amber-100 text-amber-800',
            self::PAID => 'bg-emerald-100 text-emerald-800',
            self::CANCELLED => 'bg-slate-200 text-slate-600',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::DRAFT, self::UNPAID, self::PARTIAL], true);
    }
}
