<?php

namespace App\Services;

use App\Casts\EncryptedNik;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;

class PatientService
{
    public function __construct(private readonly NumberSequenceService $sequence) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Patient
    {
        return DB::transaction(function () use ($data) {
            $nik = preg_replace('/\D/', '', (string) $data['nik']);

            if (Patient::isNik($nik) && $this->nikExists($nik)) {
                throw new \RuntimeException('NIK tersebut sudah terdaftar. Gunakan data pasien yang sudah ada atau periksa kembali penulisan NIK.');
            }

            $patient = new Patient($data);
            $patient->nik = $nik;
            $patient->mr_number ??= $this->sequence->mrNumber();
            $patient->created_by ??= auth()->id();
            $patient->save();

            return $patient;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Patient $patient, array $data): Patient
    {
        return DB::transaction(function () use ($patient, $data) {
            if (! empty($data['nik'])) {
                $nik = preg_replace('/\D/', '', (string) $data['nik']);

                if (Patient::isNik($nik) && $this->nikExists($nik, $patient->id)) {
                    throw new \RuntimeException('NIK tersebut sudah digunakan oleh pasien lain.');
                }
            }

            $patient->fill($data)->save();

            return $patient->refresh();
        });
    }

    public function toggleActive(Patient $patient): Patient
    {
        $patient->is_active = ! $patient->is_active;
        $patient->save();

        return $patient;
    }

    public function findByNik(string $nik): ?Patient
    {
        $nik = preg_replace('/\D/', '', $nik);

        if (! Patient::isNik($nik)) {
            return null;
        }

        return Patient::withTrashed()->where('nik_hash', EncryptedNik::blindIndex($nik))->first();
    }

    protected function nikExists(string $nik, ?int $ignoreId = null): bool
    {
        return Patient::withTrashed()
            ->where('nik_hash', EncryptedNik::blindIndex($nik))
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists();
    }
}
