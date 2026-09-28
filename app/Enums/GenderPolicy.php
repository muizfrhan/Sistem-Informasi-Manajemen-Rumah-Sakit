<?php

namespace App\Enums;

enum GenderPolicy: string
{
    case ANY = 'ANY';
    case MALE = 'MALE';
    case FEMALE = 'FEMALE';

    public function label(): string
    {
        return match ($this) {
            self::ANY => 'Semua',
            self::MALE => 'Laki-laki',
            self::FEMALE => 'Perempuan',
        };
    }
}
