<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    case REGISTERED = 'REGISTERED';
    case QUEUING = 'QUEUING';
    case CALLED = 'CALLED';
    case IN_EXAMINATION = 'IN_EXAMINATION';
    case DONE = 'DONE';
    case CANCELLED = 'CANCELLED';
    case NO_SHOW = 'NO_SHOW';

    public function label(): string
    {
        return match ($this) {
            self::REGISTERED => 'Terdaftar',
            self::QUEUING => 'Menunggu Panggilan',
            self::CALLED => 'Dipanggil',
            self::IN_EXAMINATION => 'Dalam Pemeriksaan',
            self::DONE => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
            self::NO_SHOW => 'Tidak Hadir',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::REGISTERED => 'bg-slate-100 text-slate-700',
            self::QUEUING => 'bg-amber-100 text-amber-800',
            self::CALLED => 'bg-sky-100 text-sky-800',
            self::IN_EXAMINATION => 'bg-indigo-100 text-indigo-800',
            self::DONE => 'bg-emerald-100 text-emerald-800',
            self::CANCELLED => 'bg-rose-100 text-rose-800',
            self::NO_SHOW => 'bg-zinc-200 text-zinc-700',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::REGISTERED, self::QUEUING, self::CALLED, self::IN_EXAMINATION], true);
    }

    /** Transisi status yang diizinkan sesuai alur operasional. */
    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::REGISTERED => [self::QUEUING, self::CALLED, self::CANCELLED],
            self::QUEUING => [self::CALLED, self::CANCELLED, self::NO_SHOW],
            self::CALLED => [self::IN_EXAMINATION, self::NO_SHOW, self::CANCELLED],
            self::IN_EXAMINATION => [self::DONE, self::CANCELLED],
            self::DONE, self::CANCELLED, self::NO_SHOW => [],
        };
    }
}
