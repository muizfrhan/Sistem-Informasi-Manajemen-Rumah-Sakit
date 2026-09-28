<?php

namespace Tests\Feature;

use App\Enums\AdmissionStatus;
use App\Enums\Gender;
use App\Enums\GenderPolicy;
use App\Enums\RoleCode;
use App\Enums\RoomStatus;
use App\Models\InpatientAdmission;
use App\Models\Patient;
use App\Models\Room;
use App\Services\InpatientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InpatientTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_be_admitted_to_a_free_bed(): void
    {
        $room = Room::factory()->create(['capacity' => 2]);
        $patient = Patient::factory()->create();

        $this->actingAs($this->userFor(RoleCode::NURSE))
            ->post(route('inpatient.admissions.store'), [
                'patient_id' => $patient->id,
                'room_id' => $room->id,
                'bed_number' => '1',
                'diagnosis' => 'Demam tifus',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $admission = InpatientAdmission::firstOrFail();

        $this->assertSame(AdmissionStatus::ADMITTED, $admission->status);
        $this->assertSame('1', $admission->bed_number);
        $this->assertNotNull($admission->admitted_at);

        // Kunci tempat tidur harus terisi selamaAdmisi.
        $this->assertSame($room->id.'-1', $admission->bed_occupancy_key);
    }

    public function test_same_bed_cannot_be_occupied_twice(): void
    {
        $room = Room::factory()->create(['capacity' => 2]);
        $nurse = $this->userFor(RoleCode::NURSE);

        $this->actingAs($nurse)->post(route('inpatient.admissions.store'), [
            'patient_id' => Patient::factory()->create()->id,
            'room_id' => $room->id,
            'bed_number' => '1',
        ])->assertSessionHas('success');

        $this->actingAs($nurse)->post(route('inpatient.admissions.store'), [
            'patient_id' => Patient::factory()->create()->id,
            'room_id' => $room->id,
            'bed_number' => '1',
        ])->assertSessionHas('error');

        $this->assertSame(1, InpatientAdmission::count());
    }

    public function test_room_cannot_exceed_capacity(): void
    {
        $room = Room::factory()->create(['capacity' => 1]);
        $nurse = $this->userFor(RoleCode::NURSE);

        $this->actingAs($nurse)->post(route('inpatient.admissions.store'), [
            'patient_id' => Patient::factory()->create()->id,
            'room_id' => $room->id,
            'bed_number' => '1',
        ])->assertSessionHas('success');

        $this->actingAs($nurse)->post(route('inpatient.admissions.store'), [
            'patient_id' => Patient::factory()->create()->id,
            'room_id' => $room->id,
            'bed_number' => '2',
        ])->assertSessionHas('error');

        $this->assertSame(1, InpatientAdmission::count());
    }

    public function test_patient_cannot_be_admitted_twice(): void
    {
        $patient = Patient::factory()->create();
        $nurse = $this->userFor(RoleCode::NURSE);

        $this->actingAs($nurse)->post(route('inpatient.admissions.store'), [
            'patient_id' => $patient->id,
            'room_id' => Room::factory()->create()->id,
            'bed_number' => '1',
        ])->assertSessionHas('success');

        $this->actingAs($nurse)->post(route('inpatient.admissions.store'), [
            'patient_id' => $patient->id,
            'room_id' => Room::factory()->create()->id,
            'bed_number' => '1',
        ])->assertSessionHas('error');
    }

    public function test_room_in_maintenance_cannot_be_used(): void
    {
        $room = Room::factory()->maintenance()->create();

        $this->actingAs($this->userFor(RoleCode::NURSE))
            ->post(route('inpatient.admissions.store'), [
                'patient_id' => Patient::factory()->create()->id,
                'room_id' => $room->id,
                'bed_number' => '1',
            ])
            ->assertSessionHas('error');

        $this->assertSame(0, InpatientAdmission::count());
    }

    public function test_gender_policy_is_enforced(): void
    {
        $room = Room::factory()->create(['gender_policy' => GenderPolicy::FEMALE]);
        $male = Patient::factory()->create(['gender' => Gender::MALE]);

        $this->actingAs($this->userFor(RoleCode::NURSE))
            ->post(route('inpatient.admissions.store'), [
                'patient_id' => $male->id,
                'room_id' => $room->id,
                'bed_number' => '1',
            ])
            ->assertSessionHas('error');

        $this->assertSame(0, InpatientAdmission::count());
    }

    public function test_discharge_frees_the_bed(): void
    {
        $room = Room::factory()->create(['capacity' => 1]);
        $patient = Patient::factory()->create();
        $nurse = $this->userFor(RoleCode::NURSE);

        $this->actingAs($nurse)->post(route('inpatient.admissions.store'), [
            'patient_id' => $patient->id,
            'room_id' => $room->id,
            'bed_number' => '1',
        ])->assertSessionHas('success');

        $admission = InpatientAdmission::firstOrFail();

        $this->actingAs($nurse)
            ->post(route('inpatient.admissions.discharge', $admission), ['notes' => 'Sembuh'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $admission->refresh();

        $this->assertSame(AdmissionStatus::DISCHARGED, $admission->status);
        $this->assertNotNull($admission->discharged_at);
        $this->assertNull($admission->bed_occupancy_key);

        // Tempat tidur kini bisa dipakai pasien lain.
        $this->actingAs($nurse)->post(route('inpatient.admissions.store'), [
            'patient_id' => Patient::factory()->create()->id,
            'room_id' => $room->id,
            'bed_number' => '1',
        ])->assertSessionHas('success');
    }

    public function test_discharge_twice_is_rejected(): void
    {
        $admission = InpatientAdmission::factory()->discharged()->create();
        $nurse = $this->userFor(RoleCode::NURSE);

        $this->actingAs($nurse)
            ->post(route('inpatient.admissions.discharge', $admission))
            ->assertSessionHas('error');
    }

    public function test_cashier_cannot_manage_inpatient(): void
    {
        $this->actingAs($this->userFor(RoleCode::CASHIER))
            ->get(route('inpatient.index'))
            ->assertForbidden();

        $this->actingAs($this->userFor(RoleCode::CASHIER))
            ->post(route('inpatient.admissions.store'), [
                'patient_id' => Patient::factory()->create()->id,
                'room_id' => Room::factory()->create()->id,
                'bed_number' => '1',
            ])
            ->assertForbidden();
    }

    public function test_receptionist_cannot_manage_inpatient(): void
    {
        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->post(route('inpatient.admissions.store'), [
                'patient_id' => Patient::factory()->create()->id,
                'room_id' => Room::factory()->create()->id,
                'bed_number' => '1',
            ])
            ->assertForbidden();
    }

    public function test_occupancy_summary_matches_real_data(): void
    {
        $room = Room::factory()->create(['capacity' => 4]);
        Room::factory()->maintenance()->create(['capacity' => 10]);
        Room::factory()->create(['capacity' => 2]);

        // Tiga tempat tidur berbeda dalam satu kamar.
        foreach (['1', '2', '3'] as $bed) {
            InpatientAdmission::factory()->create([
                'room_id' => $room->id,
                'bed_number' => $bed,
                'status' => AdmissionStatus::ADMITTED,
            ]);
        }

        $summary = app(InpatientService::class)->summary();

        $this->assertSame(2, $summary['rooms_available']);
        $this->assertSame(1, $summary['rooms_maintenance']);
        $this->assertSame(3, $summary['rooms_total']);
        $this->assertSame(6, $summary['capacity']);
        $this->assertSame(3, $summary['occupied']);
        $this->assertSame(3, $summary['available']);
        $this->assertSame(50, $summary['occupancy_rate']);
    }

    public function test_room_capacity_cannot_be_lowered_below_occupancy(): void
    {
        $room = Room::factory()->create(['capacity' => 4]);
        InpatientAdmission::factory()->create(['room_id' => $room->id, 'bed_number' => '1']);

        $this->actingAs($this->userFor(RoleCode::NURSE))
            ->put(route('rooms.update', $room), [
                'code' => $room->code,
                'name' => $room->name,
                'room_class' => $room->room_class->value,
                'capacity' => 0,
                'status' => RoomStatus::AVAILABLE->value,
                'gender_policy' => GenderPolicy::ANY->value,
                'daily_rate' => $room->daily_rate,
            ])
            ->assertSessionHasErrors('capacity');
    }

    public function test_room_with_history_cannot_be_deleted(): void
    {
        $room = Room::factory()->create();
        InpatientAdmission::factory()->create(['room_id' => $room->id]);

        $this->actingAs($this->userFor(RoleCode::NURSE))
            ->delete(route('rooms.destroy', $room))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertModelExists($room);
    }
}
