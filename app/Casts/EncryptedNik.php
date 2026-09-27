<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Enkripsi AES-256 (Laravel Crypt) pada kolom `nik`, disertai blind index
 * HMAC-SHA256 pada kolom `nik_hash` agar pencarian berbasis NIK tetap
 * memungkinkan tanpa menyimpan NIK dalam bentuk plaintext.
 *
 * @implements CastsAttributes<string|null, string|null>
 */
class EncryptedNik implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null || $value === '') {
            return [$key => null, 'nik_hash' => null];
        }

        return [
            $key => Crypt::encryptString((string) $value),
            'nik_hash' => self::blindIndex((string) $value),
        ];
    }

    public static function blindIndex(string $value): string
    {
        return hash_hmac('sha256', mb_strtoupper(trim($value)), config('app.key'));
    }
}
