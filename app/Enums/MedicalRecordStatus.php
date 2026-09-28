<?php

namespace App\Enums;

/**
 * Status alur rekam medis. Rekam medis yang telah di-finalisasi bersifat
 * read-only dan tidak dapat dihapus (compliance).
 */
enum MedicalRecordStatus: string
{
    case DRAFT = 'DRAFT';
    case FINALIZED = 'FINALIZED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::FINALIZED => 'Selesai',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-amber-100 text-amber-800',
            self::FINALIZED => 'bg-emerald-100 text-emerald-800',
        };
    }

    public function isEditable(): bool
    {
        return $this === self::DRAFT;
    }
}
