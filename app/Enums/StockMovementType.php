<?php

namespace App\Enums;

enum StockMovementType: string
{
    case IN = 'IN';
    case OUT = 'OUT';
    case ADJUSTMENT = 'ADJUSTMENT';
    case EXPIRED = 'EXPIRED';

    public function label(): string
    {
        return match ($this) {
            self::IN => 'Penerimaan',
            self::OUT => 'Pengeluaran',
            self::ADJUSTMENT => 'Penyesuaian',
            self::EXPIRED => 'Kedaluwarsa',
        };
    }

    /** Tanda pengaruhnya terhadap stok (positif menambah, negatif mengurangi). */
    public function sign(): int
    {
        return $this === self::IN ? 1 : -1;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::IN => 'bg-emerald-100 text-emerald-800',
            self::OUT => 'bg-sky-100 text-sky-800',
            self::ADJUSTMENT => 'bg-amber-100 text-amber-800',
            self::EXPIRED => 'bg-rose-100 text-rose-800',
        };
    }
}
