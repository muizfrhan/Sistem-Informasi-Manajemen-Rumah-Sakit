<?php

namespace App\Services;

use App\Models\NumberSequence;
use Illuminate\Support\Facades\DB;

class NumberSequenceService
{
    /**
     * Ambil nomor berikutnya secara thread-safe dengan mengunci baris sequence.
     * Wajib dipanggil di dalam transaksi database.
     */
    public function next(string $scope, ?string $prefix = null, ?int $period = null, int $pad = 6): string
    {
        $row = DB::table('number_sequences')
            ->where('scope', $scope)
            ->lockForUpdate()
            ->first();

        if ($row === null) {
            DB::table('number_sequences')->insert([
                'scope' => $scope,
                'prefix' => $prefix,
                'period' => $period ?? 0,
                'current_value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $current = 0;
        } else {
            if ($period !== null && (int) $row->period !== $period) {
                DB::table('number_sequences')
                    ->where('scope', $scope)
                    ->update(['period' => $period, 'current_value' => 0, 'updated_at' => now()]);

                $current = 0;
            } else {
                $current = (int) $row->current_value;
            }
        }

        $current++;

        DB::table('number_sequences')
            ->where('scope', $scope)
            ->update(['current_value' => $current, 'updated_at' => now()]);

        $number = str_pad((string) $current, $pad, '0', STR_PAD_LEFT);

        return $prefix ? $prefix.$number : $number;
    }

    public function mrNumber(): string
    {
        return $this->next('mr_number', null, null, 6);
    }

    public function registrationNumber(\DateTimeInterface $date): string
    {
        return $this->next(
            'registration_number',
            'RJ-'.$date->format('Ymd').'-',
            (int) $date->format('Ymd'),
            4
        );
    }

    public function queueNumber(string $scope, \DateTimeInterface $date): int
    {
        $value = $this->next(
            $scope,
            null,
            (int) $date->format('Ymd'),
            1
        );

        return (int) $value;
    }

    public function ensureScope(string $scope, ?string $prefix = null): NumberSequence
    {
        return NumberSequence::firstOrCreate(
            ['scope' => $scope],
            ['prefix' => $prefix, 'period' => 0, 'current_value' => 0]
        );
    }
}
