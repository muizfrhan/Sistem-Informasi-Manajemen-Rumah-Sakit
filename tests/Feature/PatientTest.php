<?php

namespace Tests\Feature;

use App\Casts\EncryptedNik;
use App\Enums\Gender;
use App\Enums\InsuranceType;
use App\Enums\RoleCode;
use App\Models\Patient;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ahmad Fauzi',
            'nik' => '3201234567890001',
            'gender' => Gender::MALE->value,
            'birth_date' => '1990-05-14',
            'birth_place' => 'Bandung',
            'blood_type' => 'O',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 12',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'insurance_type' => InsuranceType::NONE->value,
        ], $overrides);
    }

    public function test_receptionist_can_create_patient(): void
    {
        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->post('/patients', $this->payload())
            ->assertRedirect();

        $patient = Patient::firstOrFail();

        $this->assertSame('Ahmad Fauzi', $patient->name);
        $this->assertSame('000001', $patient->mr_number);
        $this->assertSame('3201234567890001', $patient->nik);
    }

    public function test_mr_number_is_sequential_and_unique(): void
    {
        $receptionist = $this->userFor(RoleCode::RECEPTIONIST);

        $this->actingAs($receptionist)->post('/patients', $this->payload(['nik' => '3201234567890002']));
        $this->actingAs($receptionist)->post('/patients', $this->payload(['nik' => '3201234567890003']));

        $this->assertSame(['000001', '000002'], Patient::orderBy('id')->pluck('mr_number')->all());
    }

    public function test_nik_is_encrypted_at_rest(): void
    {
        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->post('/patients', $this->payload());

        $raw = DB::table('patients')->first();

        $this->assertNotSame('3201234567890001', $raw->nik);
        $this->assertStringNotContainsString('3201234567890001', $raw->nik);
        $this->assertSame(EncryptedNik::blindIndex('3201234567890001'), $raw->nik_hash);
    }

    public function test_duplicate_nik_is_rejected(): void
    {
        $receptionist = $this->userFor(RoleCode::RECEPTIONIST);

        $this->actingAs($receptionist)->post('/patients', $this->payload());
        $this->actingAs($receptionist)
            ->post('/patients', $this->payload(['name' => 'Pasien Lain']))
            ->assertSessionHas('error');

        $this->assertSame(1, Patient::count());
    }

    public function test_patient_can_be_searched_by_nik_via_blind_index(): void
    {
        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->post('/patients', $this->payload());

        $this->actingAs($this->userFor(RoleCode::DOCTOR))
            ->get('/patients?q=3201234567890001')
            ->assertOk()
            ->assertSee('Ahmad Fauzi');
    }

    public function test_patient_can_be_searched_by_name_and_mr_number(): void
    {
        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->post('/patients', $this->payload());

        $this->actingAs($this->userFor(RoleCode::DOCTOR))
            ->get('/patients?q=000001')
            ->assertOk()
            ->assertSee('Ahmad Fauzi');

        $this->actingAs($this->userFor(RoleCode::DOCTOR))
            ->get('/patients?q=Fauzi')
            ->assertOk()
            ->assertSee('Ahmad Fauzi');
    }

    public function test_nik_must_be_exactly_16_digits(): void
    {
        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->post('/patients', $this->payload(['nik' => '12345']))
            ->assertSessionHasErrors('nik');

        $this->assertSame(0, Patient::count());
    }

    public function test_bpjs_number_is_required_when_using_insurance(): void
    {
        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->post('/patients', $this->payload([
                'insurance_type' => InsuranceType::BPJS->value,
                'insurance_name' => 'BPJS Kesehatan',
            ]))
            ->assertSessionHasErrors('insurance_number');
    }

    public function test_cashier_cannot_manage_patients(): void
    {
        $this->actingAs($this->userFor(RoleCode::CASHIER))
            ->get('/patients/create')
            ->assertForbidden();
    }

    public function test_patient_can_be_toggled_active(): void
    {
        $patient = Patient::factory()->create();

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->patch("/patients/{$patient->id}/toggle")
            ->assertRedirect();

        $this->assertFalse($patient->refresh()->is_active);
    }

    public function test_patient_with_history_cannot_be_deleted(): void
    {
        $patient = Patient::factory()->has(Registration::factory()->count(1))->create();

        $this->actingAs($this->superAdmin())
            ->delete("/patients/{$patient->id}")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertModelExists($patient);
    }

    public function test_nik_is_masked_in_views(): void
    {
        $patient = Patient::factory()->create(['nik' => '3201234567890001']);

        $this->assertSame('320123******0001', $patient->masked_nik);
    }

    public function test_showing_patient_writes_audit_trail(): void
    {
        config(['simrs.audit_logging_enabled' => true]);

        $patient = Patient::factory()->create();

        $this->actingAs($this->userFor(RoleCode::DOCTOR))
            ->get("/patients/{$patient->id}")
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'viewed',
            'auditable_type' => Patient::class,
            'auditable_id' => $patient->id,
        ]);
    }
}
