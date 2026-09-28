<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'CASH';
    case BPJS = 'BPJS';
    case INSURANCE = 'INSURANCE';
    case TRANSFER = 'TRANSFER';
    case CARD = 'CARD';
    case QRIS = 'QRIS';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Tunai',
            self::BPJS => 'BPJS Kesehatan',
            self::INSURANCE => 'Asuransi',
            self::TRANSFER => 'Transfer Bank',
            self::CARD => 'Kartu Debit/Kredit',
            self::QRIS => 'QRIS',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::CASH => 'bg-emerald-100 text-emerald-800',
            self::BPJS => 'bg-sky-100 text-sky-800',
            self::INSURANCE => 'bg-violet-100 text-violet-800',
            self::TRANSFER => 'bg-amber-100 text-amber-800',
            self::CARD => 'bg-indigo-100 text-indigo-800',
            self::QRIS => 'bg-teal-100 text-teal-800',
        };
    }
}
