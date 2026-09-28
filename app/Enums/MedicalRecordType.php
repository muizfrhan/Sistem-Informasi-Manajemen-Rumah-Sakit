<?php

namespace App\Enums;

enum MedicalRecordType: string
{
    case OUTPATIENT = 'OUTPATIENT';
    case INPATIENT = 'INPATIENT';
    case EMERGENCY = 'EMERGENCY';

    public function label(): string
    {
        return match ($this) {
            self::OUTPATIENT => 'Rawat Jalan',
            self::INPATIENT => 'Rawat Inap',
            self::EMERGENCY => 'Gawat Darurat',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::OUTPATIENT => 'RJ',
            self::INPATIENT => 'RI',
            self::EMERGENCY => 'IGD',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::OUTPATIENT => 'bg-sky-100 text-sky-800',
            self::INPATIENT => 'bg-violet-100 text-violet-800',
            self::EMERGENCY => 'bg-rose-100 text-rose-800',
        };
    }
}
