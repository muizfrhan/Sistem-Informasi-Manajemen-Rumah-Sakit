<?php

namespace App\Services;

use App\Enums\RegistrationStatus;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Poly;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class QueueService
{
    public function __construct(private readonly NumberSequenceService $sequence) {}

    /**
     * Daftarkan pasien ke antrean sebuah poli. Nomor antrean direset per poli per hari.
     *
     * @throws \RuntimeException jika pasien sudah terdaftar di poli yang sama pada hari yang sama
     */
    public function register(Patient $patient, Poly $poly, ?Doctor $doctor = null, ?string $complaint = null, bool $isEmergency = false): Registration
    {
        return DB::transaction(function () use ($patient, $poly, $doctor, $complaint, $isEmergency) {
            $visitDate = today();

            $duplicate = Registration::query()
                ->where('patient_id', $patient->id)
                ->where('poly_id', $poly->id)
                ->whereDate('visit_date', $visitDate)
                ->whereNotIn('status', [RegistrationStatus::CANCELLED, RegistrationStatus::NO_SHOW])
                ->lockForUpdate()
                ->first();

            if ($duplicate) {
                throw new \RuntimeException("Pasien {$patient->name} sudah terdaftar di {$poly->name} pada hari ini (No. Antrean {$duplicate->queue_code}).");
            }

            $doctor ??= $this->pickDoctor($poly);

            $queueNumber = $this->sequence->queueNumber('queue:poly:'.$poly->id, $visitDate);

            return Registration::create([
                'registration_number' => $this->sequence->registrationNumber($visitDate),
                'patient_id' => $patient->id,
                'poly_id' => $poly->id,
                'doctor_id' => $doctor?->id,
                'visit_date' => $visitDate,
                'queue_number' => $queueNumber,
                'queue_code' => $poly->queue_prefix.'-'.str_pad((string) $queueNumber, 3, '0', STR_PAD_LEFT),
                'status' => RegistrationStatus::REGISTERED,
                'is_emergency' => $isEmergency,
                'complaint' => $complaint,
                'registered_at' => now(),
                'registered_by' => auth()->id(),
            ]);
        });
    }

    public function advance(Registration $registration, RegistrationStatus $target): Registration
    {
        return DB::transaction(function () use ($registration, $target) {
            $fresh = Registration::query()->lockForUpdate()->findOrFail($registration->id);

            if ($fresh->status === $target) {
                return $fresh;
            }

            if (! $fresh->status->canTransitionTo($target)) {
                throw new \RuntimeException("Status tidak dapat diubah dari {$fresh->status->label()} ke {$target->label()}.");
            }

            $fresh->status = $target;
            $fresh->called_at ??= $target === RegistrationStatus::CALLED ? now() : null;
            $fresh->examined_at ??= $target === RegistrationStatus::IN_EXAMINATION ? now() : null;
            $fresh->finished_at ??= $target === RegistrationStatus::DONE ? now() : null;
            $fresh->save();

            return $fresh;
        });
    }

    public function callNext(Poly $poly, ?Doctor $doctor = null): ?Registration
    {
        $next = Registration::query()
            ->where('poly_id', $poly->id)
            ->whereDate('visit_date', today())
            ->whereIn('status', [RegistrationStatus::QUEUING, RegistrationStatus::REGISTERED])
            ->when($doctor, fn ($q) => $q->where('doctor_id', $doctor->id))
            ->orderByRaw('is_emergency DESC')
            ->orderBy('queue_number')
            ->first();

        return $next ? $this->advance($next, RegistrationStatus::CALLED) : null;
    }

    public function pickDoctor(Poly $poly): ?Doctor
    {
        return Doctor::query()
            ->where('poly_id', $poly->id)
            ->where('is_active', true)
            ->orderByRaw('random()')
            ->first();
    }
}
