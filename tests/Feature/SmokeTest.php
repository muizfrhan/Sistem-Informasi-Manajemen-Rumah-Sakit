<?php

namespace Tests\Feature;

use App\Enums\RoleCode;
use App\Models\Patient;
use App\Models\Poly;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function roles(): array
    {
        return [
            RoleCode::SUPER_ADMIN,
            RoleCode::RECEPTIONIST,
            RoleCode::DOCTOR,
            RoleCode::NURSE,
            RoleCode::PHARMACIST,
            RoleCode::CASHIER,
            RoleCode::MANAGER,
        ];
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_dashboard_renders_for_every_role(): void
    {
        foreach ($this->roles() as $role) {
            $this->actingAs($this->userFor($role))
                ->get('/dashboard')
                ->assertOk("Dashboard gagal untuk role {$role->value}");
        }
    }

    public function test_patient_pages_render(): void
    {
        $patient = Patient::factory()->has(Registration::factory()->count(2))->create();
        $receptionist = $this->userFor(RoleCode::RECEPTIONIST);
        $doctor = $this->userFor(RoleCode::DOCTOR);

        $this->actingAs($receptionist)->get(route('patients.index'))->assertOk();
        $this->actingAs($receptionist)->get(route('patients.create'))->assertOk();
        $this->actingAs($receptionist)->get(route('patients.show', $patient))->assertOk();
        $this->actingAs($receptionist)->get(route('patients.edit', $patient))->assertOk();

        $this->actingAs($receptionist)->get(route('registrations.index'))->assertOk();
        $this->actingAs($receptionist)->get(route('registrations.create'))->assertOk();
        $this->actingAs($receptionist)->get(route('registrations.show', $patient->registrations->first()))->assertOk();
        $this->actingAs($receptionist)->get(route('queue.index'))->assertOk();

        $this->actingAs($doctor)->get(route('medical-records.index'))->assertOk();
        $this->actingAs($doctor)->get(route('medical-records.create'))->assertOk();
    }

    public function test_empty_queue_board_renders_without_data(): void
    {
        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->get(route('queue.index'))
            ->assertOk()
            ->assertSee('Belum ada poli terdaftar');
    }

    public function test_queue_board_renders_with_poly(): void
    {
        Poly::factory()->create();
        Registration::factory()->count(3)->create();

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->get(route('queue.index'))
            ->assertOk();
    }
}
