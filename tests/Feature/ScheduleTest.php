<?php

namespace Tests\Feature;

use App\Enums\RoleCode;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Poly;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_capacity_is_derived_from_time_range(): void
    {
        $schedule = DoctorSchedule::factory()->create([
            'day_of_week' => 1,
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'slot_minutes' => 15,
        ]);

        // 4 jam = 240 menit / 15 menit per pasien = 16 pasien
        $this->assertSame(16, $schedule->capacity);
        $this->assertSame('Senin', $schedule->day_label);
        $this->assertSame('08:00 - 12:00', $schedule->time_range);
    }

    public function test_doctor_can_have_a_schedule_on_a_given_day(): void
    {
        $doctor = Doctor::factory()->create(['poly_id' => Poly::factory()->create()->id]);

        $this->actingAs($this->userFor(RoleCode::SUPER_ADMIN))
            ->post(route('schedules.store'), [
                'doctor_id' => $doctor->id,
                'day_of_week' => 1,
                'start_time' => '08:00',
                'end_time' => '12:00',
                'slot_minutes' => 15,
                'quota' => 16,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('doctor_schedules', [
            'doctor_id' => $doctor->id,
            'day_of_week' => 1,
        ]);
    }

    public function test_duplicate_schedule_on_same_day_is_rejected(): void
    {
        $doctor = Doctor::factory()->create();
        $admin = $this->userFor(RoleCode::SUPER_ADMIN);

        $payload = [
            'doctor_id' => $doctor->id,
            'day_of_week' => 2,
            'start_time' => '08:00',
            'end_time' => '12:00',
            'slot_minutes' => 15,
        ];

        $this->actingAs($admin)->post(route('schedules.store'), $payload)->assertSessionHas('success');

        $this->actingAs($admin)
            ->post(route('schedules.store'), $payload)
            ->assertSessionHas('error');

        $this->assertSame(1, DoctorSchedule::count());
    }

    public function test_end_time_must_be_after_start_time(): void
    {
        $this->actingAs($this->userFor(RoleCode::SUPER_ADMIN))
            ->post(route('schedules.store'), [
                'doctor_id' => Doctor::factory()->create()->id,
                'day_of_week' => 1,
                'start_time' => '12:00',
                'end_time' => '08:00',
                'slot_minutes' => 15,
            ])
            ->assertSessionHasErrors('end_time');
    }

    public function test_invalid_day_is_rejected(): void
    {
        $this->actingAs($this->userFor(RoleCode::SUPER_ADMIN))
            ->post(route('schedules.store'), [
                'doctor_id' => Doctor::factory()->create()->id,
                'day_of_week' => 9,
                'start_time' => '08:00',
                'end_time' => '12:00',
                'slot_minutes' => 15,
            ])
            ->assertSessionHasErrors('day_of_week');
    }

    public function test_viewing_schedule_does_not_require_manage_permission(): void
    {
        // Perawat dan dokter boleh melihat jadwal meski tidak boleh mengubah.
        foreach ([RoleCode::NURSE, RoleCode::RECEPTIONIST, RoleCode::MANAGER] as $role) {
            $this->actingAs($this->userFor($role))
                ->get(route('schedules.index'))
                ->assertOk("Role {$role->value} seharusnya bisa melihat jadwal.");
        }
    }

    public function test_manage_schedule_is_restricted(): void
    {
        $doctor = Doctor::factory()->create();

        // Apoteker dan kasir tidak memegang izin jadwal.
        foreach ([RoleCode::PHARMACIST, RoleCode::CASHIER] as $role) {
            $this->actingAs($this->userFor($role))
                ->get(route('schedules.index'))
                ->assertForbidden("Role {$role->value} seharusnya tidak bisa membuka jadwal.");
        }

        $this->actingAs($this->userFor(RoleCode::PHARMACIST))
            ->post(route('schedules.store'), [
                'doctor_id' => $doctor->id,
                'day_of_week' => 1,
                'start_time' => '08:00',
                'end_time' => '12:00',
                'slot_minutes' => 15,
            ])
            ->assertForbidden();

        $this->assertSame(0, DoctorSchedule::count());
    }

    public function test_doctors_on_duty_respects_todays_weekday(): void
    {
        $doctor = Doctor::factory()->create();
        DoctorSchedule::factory()->create([
            'doctor_id' => $doctor->id,
            'day_of_week' => now()->isoWeekday(),
            'is_active' => true,
        ]);

        DoctorSchedule::factory()->create([
            'doctor_id' => Doctor::factory()->create()->id,
            'day_of_week' => now()->isoWeekday() % 7 + 1,
            'is_active' => true,
        ]);

        $onDuty = Doctor::query()->active()->today()->pluck('id');

        $this->assertTrue($onDuty->contains($doctor->id));
        $this->assertCount(1, $onDuty);
    }

    public function test_inactive_schedule_excludes_doctor_from_today(): void
    {
        $doctor = Doctor::factory()->create();

        DoctorSchedule::factory()->create([
            'doctor_id' => $doctor->id,
            'day_of_week' => now()->isoWeekday(),
            'is_active' => false,
        ]);

        $this->assertFalse(Doctor::query()->active()->today()->pluck('id')->contains($doctor->id));
    }
}
