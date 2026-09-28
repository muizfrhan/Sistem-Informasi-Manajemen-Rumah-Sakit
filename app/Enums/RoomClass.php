<?php

namespace App\Enums;

enum RoomClass: string
{
    case VIP = 'VIP';
    case CLASS_1 = 'CLASS_1';
    case CLASS_2 = 'CLASS_2';
    case CLASS_3 = 'CLASS_3';
    case ICU = 'ICU';
    case ISOLATION = 'ISOLATION';

    public function label(): string
    {
        return match ($this) {
            self::VIP => 'VIP',
            self::CLASS_1 => 'Kelas 1',
            self::CLASS_2 => 'Kelas 2',
            self::CLASS_3 => 'Kelas 3',
            self::ICU => 'ICU',
            self::ISOLATION => 'Isolasi',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::VIP => 'violet',
            self::CLASS_1 => 'blue',
            self::CLASS_2 => 'teal',
            self::CLASS_3 => 'slate',
            self::ICU => 'red',
            self::ISOLATION => 'amber',
        };
    }
}
