<?php

namespace App\Enums;

enum InsuranceType: string
{
    case NONE = 'NONE';
    case BPJS = 'BPJS';
    case ASURANSI = 'ASURANSI';
    case PERUSAHAAN = 'PERUSAHAAN';

    public function label(): string
    {
        return match ($this) {
            self::NONE => 'Mandiri',
            self::BPJS => 'BPJS Kesehatan',
            self::ASURANSI => 'Asuransi Swasta',
            self::PERUSAHAAN => 'Asuransi Perusahaan',
        };
    }

    /** @return list<self> */
    public static function options(): array
    {
        return self::cases();
    }
}
