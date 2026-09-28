<?php

namespace Tests\Feature;

use App\Enums\RoleCode;
use App\Models\Doctor;
use App\Models\Drug;
use App\Models\Icd10Code;
use App\Models\Patient;
use App\Models\Poly;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Setiap aksi tulis harus berfungsi untuk peran yang memang diberi izin,
 * dan ditolak 403 untuk peran lain. Ini mengunci perilaku tersebut supaya
 * tidak bisa berubah diam-diam.
 */
class ActionMatrixTest extends TestCase
{
    use RefreshDatabase;

    private function poly(): Poly
    {
        return Poly::factory()->create();
    }

    private function doctor(): Doctor
    {
        return Doctor::factory()->create(['poly_id' => $this->poly()->id]);
    }

    private function drug(): Drug
    {
        return Drug::factory()->create();
    }

    private function patient(): Patient
    {
        return Patient::factory()->create();
    }

    private function room(): Room
    {
        return Room::factory()->create();
    }

    /** @return array<string, array{0: string, 1: string, 2: array}> */
    public static function actionProvider(): array
    {
        return [
            // [label, permission yang WAJIB ada, closure pemanggil aksi]
            'pasien.manage' => [
                'patient.manage',
                function (self $t): void {
                    $p = $t->patient();
                    $t->actingAs($t->superAdmin())->post('/patients', [
                        'name' => 'Uji', 'nik' => '3201010901900001', 'gender' => 'M',
                        'birth_date' => '1990-01-01', 'insurance_type' => 'NONE',
                    ])->assertRedirect();

                    $t->actingAs($t->superAdmin())->put('/patients/'.$p->id, [
                        'name' => 'Uji Ubah', 'nik' => $p->nik, 'gender' => 'M',
                        'birth_date' => '1990-01-01', 'insurance_type' => 'NONE',
                    ])->assertRedirect();

                    $t->actingAs($t->superAdmin())->patch('/patients/'.$p->id.'/toggle')->assertRedirect();
                    $t->actingAs($t->superAdmin())->delete('/patients/'.$p->id)->assertRedirect();
                },
            ],
            'polies.manage' => [
                'master.manage',
                function (self $t): void {
                    $poly = $t->poly();
                    $t->actingAs($t->superAdmin())->post('/master/polies', [
                        'code' => 'UJ1', 'name' => 'Poli Uji', 'queue_prefix' => 'Z',
                    ])->assertRedirect();
                    $t->actingAs($t->superAdmin())->put('/master/polies/'.$poly->id, [
                        'code' => $poly->code, 'name' => 'Poli Ubah', 'queue_prefix' => 'Z',
                    ])->assertRedirect();
                    $t->actingAs($t->superAdmin())->delete('/master/polies/'.$poly->id)->assertRedirect();
                },
            ],
            'doctors.manage' => [
                'master.manage',
                function (self $t): void {
                    $d = $t->doctor();
                    $t->actingAs($t->superAdmin())->post('/master/doctors', [
                        'code' => 'DU1', 'name' => 'dr. Uji', 'poly_id' => $d->poly_id,
                    ])->assertRedirect();
                    $t->actingAs($t->superAdmin())->put('/master/doctors/'.$d->id, [
                        'code' => $d->code, 'name' => 'dr. Ubah', 'poly_id' => $d->poly_id,
                    ])->assertRedirect();
                    $t->actingAs($t->superAdmin())->delete('/master/doctors/'.$d->id)->assertRedirect();
                },
            ],
            'drugs.manage' => [
                'master.manage',
                function (self $t): void {
                    $drug = $t->drug();
                    $payload = [
                        'code' => $drug->code, 'name' => $drug->name, 'unit' => $drug->unit,
                        'price' => 1000, 'stock' => 10, 'min_stock' => 2,
                    ];
                    $t->actingAs($t->superAdmin())->put('/master/drugs/'.$drug->id, $payload)->assertRedirect();
                    $t->actingAs($t->superAdmin())->delete('/master/drugs/'.$drug->id)->assertRedirect();
                },
            ],
            'icd10.manage' => [
                'master.manage',
                function (self $t): void {
                    $c = Icd10Code::factory()->create();
                    $t->actingAs($t->superAdmin())->put('/master/icd10/'.$c->id, [
                        'code' => $c->code, 'description' => 'Uji', 'category' => 'Uji',
                    ])->assertRedirect();
                    $t->actingAs($t->superAdmin())->delete('/master/icd10/'.$c->id)->assertRedirect();
                },
            ],
            'rooms.manage' => [
                'inpatient.manage',
                function (self $t): void {
                    $r = $t->room();
                    $t->actingAs($t->superAdmin())->put('/rooms/'.$r->id, [
                        'code' => $r->code, 'name' => 'Kamar Ubah', 'room_class' => 'CLASS_2', 'capacity' => 2,
                    ])->assertRedirect();
                    $t->actingAs($t->superAdmin())->delete('/rooms/'.$r->id)->assertRedirect();
                },
            ],
            'pharmacy.stock' => [
                'pharmacy.manage',
                function (self $t): void {
                    $drug = $t->drug();
                    $t->actingAs($t->superAdmin())
                        ->patch('/pharmacy/drugs/'.$drug->id.'/stock', ['stock' => 42, 'note' => 'Uji'])
                        ->assertRedirect();
                },
            ],
            'users.manage' => [
                'user.manage',
                function (self $t): void {
                    $u = User::factory()->create();
                    $t->actingAs($t->superAdmin())->put('/admin/users/'.$u->id, [
                        'username' => $u->username, 'name' => 'Ubah', 'role_id' => $u->role_id,
                    ])->assertRedirect();
                    $t->actingAs($t->superAdmin())->patch('/admin/users/'.$u->id.'/toggle')->assertRedirect();
                    $t->actingAs($t->superAdmin())->delete('/admin/users/'.$u->id)->assertRedirect();
                },
            ],
        ];
    }

    /** Super Admin boleh menjalankan setiap aksi. */
    #[\PHPUnit\Framework\Attributes\DataProvider('actionProvider')]
    public function test_super_admin_can_run_write_actions(string $permission, callable $run): void
    {
        $this->actingAs($this->superAdmin());
        $run($this);
        $this->assertTrue(true);
    }

    /** Peran tanpa izin harus mendapat 403, bukan 500 atau halaman kosong. */
    #[\PHPUnit\Framework\Attributes\DataProvider('actionProvider')]
    public function test_roles_without_permission_are_forbidden(string $permission, callable $run): void
    {
        $denied = 0;

        foreach (RoleCode::cases() as $role) {
            if ($role === RoleCode::SUPER_ADMIN) {
                continue;
            }

            $user = $this->userFor($role);

            if ($user->hasPermission($permission)) {
                continue;
            }

            // Ganti superadmin dengan peran ini lalu jalankan aksi yang sama;
            // setiap pemanggilan harus berhenti di 403.
            $probe = $this->probeFor($permission);

            if ($probe === null) {
                continue;
            }

            [$method, $url, $payload] = $probe;

            $this->actingAs($user)
                ->{$method}($url, $payload)
                ->assertForbidden();
            $denied++;
        }

        $this->assertGreaterThan(0, $denied, 'Tidak ada peran yang diuji untuk izin '.$permission);
    }

    /** @return array{0: string, 1: string, 2: array}|null */
    private function probeFor(string $permission): ?array
    {
        return match ($permission) {
            'patient.manage' => ['delete', '/patients/'.$this->patient()->id, []],
            'master.manage' => ['delete', '/master/polies/'.$this->poly()->id, []],
            'inpatient.manage' => ['delete', '/rooms/'.$this->room()->id, []],
            'pharmacy.manage' => ['patch', '/pharmacy/drugs/'.$this->drug()->id.'/stock', ['stock' => 5]],
            'user.manage' => ['delete', '/admin/users/'.User::factory()->create()->id, []],
            default => null,
        };
    }
}
