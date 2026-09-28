<?php

namespace App\Enums;

enum InvoiceItemType: string
{
    case CONSULTATION = 'CONSULTATION';
    case PROCEDURE = 'PROCEDURE';
    case DRUG = 'DRUG';
    case LAB = 'LAB';
    case OTHER = 'OTHER';

    public function label(): string
    {
        return match ($this) {
            self::CONSULTATION => 'Konsultasi',
            self::PROCEDURE => 'Tindakan',
            self::DRUG => 'Obat',
            self::LAB => 'Pemeriksaan Lab',
            self::OTHER => 'Lainnya',
        };
    }
}
