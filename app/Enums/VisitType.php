<?php

namespace App\Enums;

enum VisitType: string
{
    case OUTPATIENT = 'OUTPATIENT';
    case INPATIENT = 'INPATIENT';

    public function label(): string
    {
        return match ($this) {
            self::OUTPATIENT => 'Rawat Jalan',
            self::INPATIENT => 'Rawat Inap',
        };
    }
}
