<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatus;
use App\Enums\RoleCode;
use App\Models\Patient;
use App\Models\Poly;
use App\Models\Registration;
use App\Services\QueueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_be_registered_to_a_poly(): void
    {
        $poly = Poly::factory()->create(['queue_prefix' => 'A']);
        $patient = Patient::factory()->create();

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->post('/registrations', [
                'patient_id' => $patient->id,
                'poly_id' => $poly->id,
                'complaint' => 'Demam dan sakit kepala',
            ])
            ->assertRedirect(route('queue.index'));

        $registration = Registration::firstOrFail();

        $this->assertSame($poly->id, $registration->poly_id);
        $this->assertSame(1, $registration->queue_number);
        $this->assertSame('A-001', $registration->queue_code);
        $this->assertSame(RegistrationStatus::REGISTERED, $registration->status);
    }

    public function test_queue_number_increments_per_poly(): void
    {
        $poly = Poly::factory()->create(['queue_prefix' => 'B']);

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST));

        foreach (Patient::factory()->count(3)->create() as $patient) {
            $this->post('/registrations', ['patient_id' => $patient->id, 'poly_id' => $poly->id]);
        }

        $this->assertSame(['B-001', 'B-002', 'B-003'], Registration::orderBy('id')->pluck('queue_code')->all());
    }

    public function test_queue_number_is_independent_per_poly(): void
    {
        $polyA = Poly::factory()->create(['queue_prefix' => 'A']);
        $polyB = Poly::factory()->create(['queue_prefix' => 'B']);

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST));

        $this->post('/registrations', ['patient_id' => Patient::factory()->create()->id, 'poly_id' => $polyA->id]);
        $this->post('/registrations', ['patient_id' => Patient::factory()->create()->id, 'poly_id' => $polyB->id]);
        $this->post('/registrations', ['patient_id' => Patient::factory()->create()->id, 'poly_id' => $polyA->id]);

        $this->assertSame(
            ['A-001', 'B-001', 'A-002'],
            Registration::orderBy('id')->get()->map(fn ($r) => $r->queue_code)->all()
        );
    }

    public function test_duplicate_registration_on_same_poly_and_day_is_rejected(): void
    {
        $poly = Poly::factory()->create();
        $patient = Patient::factory()->create();

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->post('/registrations', ['patient_id' => $patient->id, 'poly_id' => $poly->id]);

        $this->post('/registrations', ['patient_id' => $patient->id, 'poly_id' => $poly->id])
            ->assertSessionHas('error');

        $this->assertSame(1, Registration::count());
    }

    public function test_patient_can_register_to_a_different_poly_on_same_day(): void
    {
        $patient = Patient::factory()->create();

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->post('/registrations', ['patient_id' => $patient->id, 'poly_id' => Poly::factory()->create()->id]);

        $this->post('/registrations', ['patient_id' => $patient->id, 'poly_id' => Poly::factory()->create()->id]);

        $this->assertSame(2, Registration::count());
    }

    public function test_status_can_advance_through_valid_flow(): void
    {
        $poly = Poly::factory()->create();
        $patient = Patient::factory()->create();

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->post('/registrations', ['patient_id' => $patient->id, 'poly_id' => $poly->id]);

        $registration = Registration::firstOrFail();

        foreach ([RegistrationStatus::QUEUING, RegistrationStatus::CALLED, RegistrationStatus::IN_EXAMINATION, RegistrationStatus::DONE] as $status) {
            $this->patch("/queue/registrations/{$registration->id}/status/{$status->value}")
                ->assertRedirect();

            $this->assertSame($status, $registration->refresh()->status);
        }

        $this->assertNotNull($registration->finished_at);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $registration = Registration::factory()->create(['status' => RegistrationStatus::REGISTERED]);

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->patch("/queue/registrations/{$registration->id}/status/DONE")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(RegistrationStatus::REGISTERED, $registration->refresh()->status);
    }

    public function test_call_next_promotes_earliest_waiting_patient(): void
    {
        $poly = Poly::factory()->create(['queue_prefix' => 'A']);
        $first = Registration::factory()->create(['poly_id' => $poly->id, 'status' => RegistrationStatus::QUEUING, 'queue_number' => 1, 'queue_code' => 'A-001']);
        $second = Registration::factory()->create(['poly_id' => $poly->id, 'status' => RegistrationStatus::QUEUING, 'queue_number' => 2, 'queue_code' => 'A-002']);

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->post("/queue/polies/{$poly->id}/call")
            ->assertRedirect();

        $this->assertSame(RegistrationStatus::CALLED, $first->refresh()->status);
        $this->assertSame(RegistrationStatus::QUEUING, $second->refresh()->status);
    }

    public function test_emergency_patient_is_prioritised_in_queue(): void
    {
        $poly = Poly::factory()->create();

        $normal = Registration::factory()->create([
            'poly_id' => $poly->id, 'status' => RegistrationStatus::QUEUING,
            'queue_number' => 1, 'is_emergency' => false,
        ]);
        $emergency = Registration::factory()->create([
            'poly_id' => $poly->id, 'status' => RegistrationStatus::QUEUING,
            'queue_number' => 2, 'is_emergency' => true,
        ]);

        app(QueueService::class)->callNext($poly);

        $this->assertSame(RegistrationStatus::CALLED, $emergency->refresh()->status);
        $this->assertSame(RegistrationStatus::QUEUING, $normal->refresh()->status);
    }

    public function test_call_next_returns_error_when_nobody_waiting(): void
    {
        $poly = Poly::factory()->create();

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->post("/queue/polies/{$poly->id}/call")
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_doctor_cannot_register_patients(): void
    {
        $this->actingAs($this->userFor(RoleCode::DOCTOR))
            ->get('/registrations/create')
            ->assertForbidden();
    }

    public function test_queue_board_renders(): void
    {
        Poly::factory()->count(2)->create();
        Registration::factory()->count(2)->create(['status' => RegistrationStatus::QUEUING]);

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->get('/queue')
            ->assertOk()
            ->assertSee('Papan Antrean');
    }
}
