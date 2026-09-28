<?php

namespace App\Services;

use App\Enums\AdmissionStatus;
use App\Enums\GenderPolicy;
use App\Enums\RoomStatus;
use App\Models\Doctor;
use App\Models\InpatientAdmission;
use App\Models\Patient;
use App\Models\Room;
use Illuminate\Support\Facades\DB;

class InpatientService
{
    public function __construct(private readonly NumberSequenceService $sequence) {}

    /**
     * Tempatkan pasien ke tempat tidur. Memverifikasi kapasitas kamar,
     * kebijakan gender, dan tidak ada pasien ganda di satu kamar.
     */
    public function admit(
        Patient $patient,
        Room $room,
        string $bedNumber,
        ?Doctor $doctor = null,
        ?int $registrationId = null,
        ?string $diagnosis = null,
        ?string $notes = null,
    ): InpatientAdmission {
        if ($room->status === RoomStatus::MAINTENANCE) {
            throw new \RuntimeException("Kamar {$room->code} sedang dalam pemeliharaan dan tidak dapat digunakan.");
        }

        if ($room->gender_policy !== GenderPolicy::ANY && $room->gender_policy->value !== $patient->gender->value) {
            throw new \RuntimeException(
                "Kamar {$room->code} hanya untuk pasien {$room->gender_policy->label()}."
            );
        }

        $occupiedBeds = $room->activeAdmissions()->pluck('bed_number')->all();

        if (in_array($bedNumber, $occupiedBeds, true)) {
            throw new \RuntimeException("Tempat tidur {$bedNumber} di kamar {$room->code} sudah terisi.");
        }

        if (count($occupiedBeds) >= $room->capacity) {
            throw new \RuntimeException("Kamar {$room->code} sudah penuh ({$room->capacity} tempat tidur).");
        }

        $duplicate = InpatientAdmission::query()
            ->admitted()
            ->where('patient_id', $patient->id)
            ->exists();

        if ($duplicate) {
            throw new \RuntimeException("{$patient->name} sedang dirawat inap. Akhiri perawatan sebelumnya lebih dulu.");
        }

        return DB::transaction(fn () => InpatientAdmission::create([
            'admission_number' => $this->sequence->next('admission_number', 'RI-', null, 6),
            'patient_id' => $patient->id,
            'registration_id' => $registrationId,
            'room_id' => $room->id,
            'doctor_id' => $doctor?->id,
            'bed_number' => $bedNumber,
            'status' => AdmissionStatus::ADMITTED,
            'diagnosis' => $diagnosis,
            'notes' => $notes,
            'admitted_at' => now(),
        ]));
    }

    public function discharge(InpatientAdmission $admission, ?string $notes = null): InpatientAdmission
    {
        if ($admission->status !== AdmissionStatus::ADMITTED) {
            throw new \RuntimeException('Perawatan inap ini sudah berakhir.');
        }

        $admission->forceFill([
            'status' => AdmissionStatus::DISCHARGED,
            'discharged_at' => now(),
            'notes' => $notes ?? $admission->notes,
        ])->save();

        return $admission->refresh();
    }

    public function cancel(InpatientAdmission $admission): InpatientAdmission
    {
        if ($admission->status !== AdmissionStatus::ADMITTED) {
            throw new \RuntimeException('Perawatan inap ini sudah berakhir.');
        }

        $admission->forceFill([
            'status' => AdmissionStatus::CANCELLED,
            'discharged_at' => now(),
        ])->save();

        return $admission->refresh();
    }

    /** Ringkasan ketersediaan kamar untuk dashboard. */
    public function summary(): array
    {
        $rooms = Room::query()
            ->available()
            ->withCount(['activeAdmissions'])
            ->get();

        $capacity = (int) $rooms->sum('capacity');
        $occupied = (int) $rooms->sum('active_admissions_count');

        return [
            'rooms_total' => Room::query()->count(),
            'rooms_available' => $rooms->count(),
            'rooms_maintenance' => Room::query()->where('status', RoomStatus::MAINTENANCE)->count(),
            'capacity' => $capacity,
            'occupied' => $occupied,
            'available' => max(0, $capacity - $occupied),
            'occupancy_rate' => $capacity > 0 ? (int) round($occupied / $capacity * 100) : 0,
        ];
    }
}
