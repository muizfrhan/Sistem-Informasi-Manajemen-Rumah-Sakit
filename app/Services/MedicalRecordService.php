<?php

namespace App\Services;

use App\Enums\DiagnosisType;
use App\Enums\MedicalRecordStatus;
use App\Enums\MedicalRecordType;
use App\Models\Doctor;
use App\Models\Icd10Code;
use App\Models\MedicalRecord;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class MedicalRecordService
{
    public function __construct(private readonly NumberSequenceService $sequence) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  list<array{icd10_code_id:int, diagnosis_type:string, description?:string|null}>  $diagnoses
     * @param  list<array{name:string, code?:string|null, fee?:int|null, notes?:string|null}>  $procedures
     */
    public function create(Registration $registration, array $data, array $diagnoses = [], array $procedures = []): MedicalRecord
    {
        return DB::transaction(function () use ($registration, $data, $diagnoses, $procedures) {
            $record = MedicalRecord::create($data + [
                'registration_id' => $registration->id,
                'patient_id' => $registration->patient_id,
                'doctor_id' => $data['doctor_id'] ?? $registration->doctor_id,
                'created_by' => auth()->id(),
                'record_type' => $data['record_type'] ?? $this->inferType($registration),
                'status' => MedicalRecordStatus::DRAFT,
            ]);

            $this->syncDiagnoses($record, $diagnoses);
            $this->syncProcedures($record, $procedures);

            return $record->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<array{icd10_code_id:int, diagnosis_type:string, description?:string|null}>  $diagnoses
     * @param  list<array{name:string, code?:string|null, fee?:int|null, notes?:string|null}>  $procedures
     */
    public function update(MedicalRecord $record, array $data, array $diagnoses = [], array $procedures = []): MedicalRecord
    {
        $this->assertEditable($record);

        return DB::transaction(function () use ($record, $data, $diagnoses, $procedures) {
            $record->fill($data)->save();

            $this->syncDiagnoses($record, $diagnoses);
            $this->syncProcedures($record, $procedures);

            return $record->refresh();
        });
    }

    /**
     * Finalisasi menutup rekam medis sehingga tidak dapat diubah lagi
     * (compliance: rekam medis yang sudah selesai bersifat permanen).
     */
    public function finalize(MedicalRecord $record): MedicalRecord
    {
        $this->assertEditable($record);

        if ($record->diagnoses->isEmpty()) {
            throw new \RuntimeException('Diagnosis (kode ICD-10) wajib diisi sebelum rekam medis difinalisasi.');
        }

        if (blank($record->diagnosis)) {
            throw new \RuntimeException('Ringkasan diagnosis wajib diisi sebelum rekam medis difinalisasi.');
        }

        $record->forceFill([
            'status' => MedicalRecordStatus::FINALIZED,
            'finalized_at' => now(),
        ])->save();

        return $record->refresh();
    }

    protected function assertEditable(MedicalRecord $record): void
    {
        if (! $record->status->isEditable()) {
            throw new \RuntimeException('Rekam medis yang sudah difinalisasi tidak dapat diubah.');
        }
    }

    /**
     * @param  list<array{icd10_code_id:int, diagnosis_type:string, description?:string|null}>  $diagnoses
     */
    protected function syncDiagnoses(MedicalRecord $record, array $diagnoses): void
    {
        $record->diagnoses()->delete();

        $codes = Icd10Code::whereIn('id', array_column($diagnoses, 'icd10_code_id'))->get()
            ->keyBy('id');

        foreach ($diagnoses as $item) {
            if (! isset($codes[$item['icd10_code_id']])) {
                continue;
            }

            $type = DiagnosisType::tryFrom($item['diagnosis_type'] ?? 'PRIMARY') ?? DiagnosisType::PRIMARY;

            $record->diagnoses()->create([
                'icd10_code_id' => $item['icd10_code_id'],
                'diagnosis_type' => $type->value,
                'description' => $item['description'] ?? $codes[$item['icd10_code_id']]->description,
            ]);
        }

        $record->load('diagnoses');
    }

    /**
     * @param  list<array{name:string, code?:string|null, fee?:int|null, notes?:string|null}>  $procedures
     */
    protected function syncProcedures(MedicalRecord $record, array $procedures): void
    {
        $record->procedures()->delete();

        foreach ($procedures as $item) {
            if (blank($item['name'] ?? null)) {
                continue;
            }

            $fee = max(0, (int) ($item['fee'] ?? 0));

            $record->procedures()->create([
                'name' => $item['name'],
                'code' => $item['code'] ?? null,
                'fee' => $fee,
                'notes' => $item['notes'] ?? null,
            ]);
        }

        $record->load('procedures');
    }

    protected function inferType(Registration $registration): MedicalRecordType
    {
        if ($registration->is_emergency) {
            return MedicalRecordType::EMERGENCY;
        }

        return $registration->visit_type->value === 'INPATIENT'
            ? MedicalRecordType::INPATIENT
            : MedicalRecordType::OUTPATIENT;
    }

    /** Daftar dokter yang dapat menulis rekam medis (dokter aktif poli terdaftar). */
    public function availableDoctors(?int $polyId = null)
    {
        return Doctor::query()
            ->active()
            ->when($polyId, fn ($q) => $q->where('poly_id', $polyId))
            ->orderBy('name')
            ->get();
    }
}
