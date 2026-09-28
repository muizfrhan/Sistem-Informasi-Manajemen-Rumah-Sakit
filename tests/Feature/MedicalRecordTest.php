<?php

namespace Tests\Feature;

use App\Enums\DiagnosisType;
use App\Enums\MedicalRecordStatus;
use App\Enums\PrescriptionStatus;
use App\Enums\RoleCode;
use App\Models\Drug;
use App\Models\Icd10Code;
use App\Models\MedicalRecord;
use App\Models\Poly;
use App\Models\Prescription;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalRecordTest extends TestCase
{
    use RefreshDatabase;

    private function context(): array
    {
        $poly = Poly::factory()->create();
        $registration = Registration::factory()->create(['poly_id' => $poly->id]);
        $icd10 = Icd10Code::factory()->create(['code' => 'B01', 'description' => 'Demam tifus']);

        return compact('poly', 'registration', 'icd10');
    }

    public function test_doctor_can_create_a_draft_medical_record(): void
    {
        ['registration' => $registration, 'icd10' => $icd10] = $this->context();

        $this->actingAs($this->userFor(RoleCode::DOCTOR))
            ->post(route('medical-records.store'), [
                'registration_id' => $registration->id,
                'doctor_id' => $registration->doctor_id,
                'chief_complaint' => 'Demam sejak tiga hari',
                'diagnosis' => 'Demam tifus',
                'diagnoses' => [
                    ['icd10_code_id' => $icd10->id, 'diagnosis_type' => DiagnosisType::PRIMARY->value],
                ],
                'procedures' => [
                    ['name' => 'Observasi 24 Jam', 'fee' => 150000],
                ],
            ])
            ->assertRedirect();

        $record = MedicalRecord::firstOrFail();

        $this->assertSame(MedicalRecordStatus::DRAFT, $record->status);
        $this->assertSame($registration->patient_id, $record->patient_id);
        $this->assertCount(1, $record->diagnoses);
        $this->assertSame('Demam tifus', $record->diagnoses->first()->description);
        $this->assertCount(1, $record->procedures);
        $this->assertSame(150000, $record->procedures->first()->fee);
    }

    public function test_one_medical_record_per_registration(): void
    {
        ['registration' => $registration, 'icd10' => $icd10] = $this->context();

        $doctor = $this->userFor(RoleCode::DOCTOR);

        $payload = [
            'registration_id' => $registration->id,
            'doctor_id' => $registration->doctor_id,
            'diagnosis' => 'Demam tifus',
            'diagnoses' => [['icd10_code_id' => $icd10->id, 'diagnosis_type' => 'PRIMARY']],
        ];

        $this->actingAs($doctor)->post(route('medical-records.store'), $payload)->assertRedirect();

        $this->actingAs($doctor)
            ->post(route('medical-records.store'), $payload)
            ->assertSessionHasErrors('registration_id');

        $this->assertSame(1, MedicalRecord::count());
    }

    public function test_record_requires_diagnosis_before_finalization(): void
    {
        ['registration' => $registration] = $this->context();

        $record = MedicalRecord::factory()->create([
            'registration_id' => $registration->id,
            'status' => MedicalRecordStatus::DRAFT,
            'diagnosis' => 'Demam tifus',
        ]);

        $this->actingAs($this->userFor(RoleCode::DOCTOR))
            ->post(route('medical-records.finalize', $record))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(MedicalRecordStatus::DRAFT, $record->refresh()->status);
    }

    public function test_finalized_record_can_no_longer_be_edited(): void
    {
        ['registration' => $registration] = $this->context();

        $record = MedicalRecord::factory()->create([
            'registration_id' => $registration->id,
            'status' => MedicalRecordStatus::FINALIZED,
            'finalized_at' => now(),
        ]);

        $doctor = $this->userFor(RoleCode::DOCTOR);

        // Halaman edit diarahkan kembali dengan pesan.
        $this->actingAs($doctor)
            ->get(route('medical-records.edit', $record))
            ->assertRedirect(route('medical-records.show', $record))
            ->assertSessionHas('error');

        // Update langsung tetap ditolak backend.
        $this->actingAs($doctor)
            ->put(route('medical-records.update', $record), [
                'registration_id' => $registration->id,
                'doctor_id' => $record->doctor_id,
                'diagnosis' => 'Diubah',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNotSame('Diubah', $record->refresh()->diagnosis);
    }

    public function test_finalize_sets_timestamp_and_locks_the_record(): void
    {
        ['registration' => $registration, 'icd10' => $icd10] = $this->context();

        $record = MedicalRecord::factory()->create([
            'registration_id' => $registration->id,
            'status' => MedicalRecordStatus::DRAFT,
            'diagnosis' => 'Demam tifus',
        ]);

        $record->diagnoses()->create([
            'icd10_code_id' => $icd10->id,
            'diagnosis_type' => DiagnosisType::PRIMARY,
        ]);

        $this->actingAs($this->userFor(RoleCode::DOCTOR))
            ->post(route('medical-records.finalize', $record))
            ->assertRedirect()
            ->assertSessionHas('success');

        $record->refresh();

        $this->assertSame(MedicalRecordStatus::FINALIZED, $record->status);
        $this->assertNotNull($record->finalized_at);
    }

    public function test_receptionist_cannot_write_medical_records(): void
    {
        ['registration' => $registration, 'icd10' => $icd10] = $this->context();

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->get(route('medical-records.create'))
            ->assertForbidden();

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->post(route('medical-records.store'), [
                'registration_id' => $registration->id,
                'doctor_id' => $registration->doctor_id,
                'diagnoses' => [['icd10_code_id' => $icd10->id, 'diagnosis_type' => 'PRIMARY']],
            ])
            ->assertForbidden();

        $this->assertSame(0, MedicalRecord::count());
    }

    public function test_cashier_cannot_read_medical_records(): void
    {
        MedicalRecord::factory()->create();

        $this->actingAs($this->userFor(RoleCode::CASHIER))
            ->get(route('medical-records.index'))
            ->assertForbidden();
    }

    public function test_doctor_can_create_prescription_on_draft_record(): void
    {
        ['registration' => $registration] = $this->context();

        $drug = Drug::factory()->create(['price' => 1000, 'stock' => 100]);

        $record = MedicalRecord::factory()->create([
            'registration_id' => $registration->id,
            'status' => MedicalRecordStatus::DRAFT,
        ]);

        $this->actingAs($this->userFor(RoleCode::DOCTOR))
            ->post(route('medical-records.prescriptions.store', $record), [
                'items' => [
                    ['drug_id' => $drug->id, 'dosage' => '1 tablet', 'quantity' => 10],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $prescription = Prescription::firstOrFail();

        $this->assertSame(PrescriptionStatus::DRAFT, $prescription->status);
        $this->assertCount(1, $prescription->items);
        $this->assertSame(10000, $prescription->items->first()->subtotal);

        // Stok belum berkurang sebelum apoteker menyerahkan resep.
        $this->assertSame(100, $drug->refresh()->stock);
    }

    public function test_prescription_cannot_be_added_to_finalized_record(): void
    {
        ['registration' => $registration] = $this->context();

        $record = MedicalRecord::factory()->create([
            'registration_id' => $registration->id,
            'status' => MedicalRecordStatus::FINALIZED,
            'finalized_at' => now(),
        ]);

        $this->actingAs($this->userFor(RoleCode::DOCTOR))
            ->post(route('medical-records.prescriptions.store', $record), [
                'items' => [['drug_id' => Drug::factory()->create()->id, 'quantity' => 1]],
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(0, Prescription::count());
    }

    public function test_viewing_record_writes_audit_trail(): void
    {
        config(['simrs.audit_logging_enabled' => true]);

        $record = MedicalRecord::factory()->create();

        $this->actingAs($this->userFor(RoleCode::MANAGER))
            ->get(route('medical-records.show', $record))
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'viewed',
            'auditable_type' => MedicalRecord::class,
            'auditable_id' => $record->id,
        ]);
    }
}
