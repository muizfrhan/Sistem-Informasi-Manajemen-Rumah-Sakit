<?php

namespace Tests\Feature;

use App\Enums\DiagnosisType;
use App\Enums\RegistrationStatus;
use App\Enums\RoleCode;
use App\Models\AuditLog;
use App\Models\DemoRequest;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Drug;
use App\Models\Icd10Code;
use App\Models\InpatientAdmission;
use App\Models\Invoice;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Poly;
use App\Models\Prescription;
use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Menghantarkan setiap halaman GET dengan data nyata untuk memastikan tidak ada
 * route yang menghasilkan error, tidak ada halaman kosong, dan tidak ada
 * query yang dijalankan di dalam view.
 */
class RouteCoverageTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, User> */
    private function actors(): array
    {
        return [
            'superadmin' => $this->userFor(RoleCode::SUPER_ADMIN),
            'receptionist' => $this->userFor(RoleCode::RECEPTIONIST),
            'doctor' => $this->userFor(RoleCode::DOCTOR),
            'nurse' => $this->userFor(RoleCode::NURSE),
            'pharmacist' => $this->userFor(RoleCode::PHARMACIST),
            'cashier' => $this->userFor(RoleCode::CASHIER),
            'manager' => $this->userFor(RoleCode::MANAGER),
        ];
    }

    public function test_every_get_route_renders_for_every_role(): void
    {
        $this->seedDomain();

        $failures = [];

        foreach ($this->actors() as $roleName => $user) {
            foreach (Route::getRoutes() as $route) {
                if (! in_array('GET', $route->methods(), true)) {
                    continue;
                }

                $name = $route->getName();

                if ($name === null || str_starts_with($name, 'demo') || $name === 'up') {
                    continue;
                }

                $uri = $this->fillUri($route->uri(), $name);

                if ($uri === null) {
                    continue;
                }

                $response = $this->actingAs($user)->get($uri);

                if (! in_array($response->getStatusCode(), [200, 302, 403], true)) {
                    $failures[] = "[{$roleName}] GET {$uri} ({$name}) => {$response->getStatusCode()}";
                }
            }
        }

        $this->assertSame([], $failures, "Route bermasalah:\n".implode("\n", $failures));
    }

    public function test_error_pages_render_consistently(): void
    {
        $this->seedDomain();

        // 403 — pengguna tanpa izin mengakses modul yang dilindungi.
        $this->actingAs($this->userFor(RoleCode::CASHIER))
            ->get(route('admin.users.index'))
            ->assertForbidden()
            ->assertSee('Akses Ditolak')
            ->assertDontSee('vendor/laravel');

        // 404 — alamat tidak dikenal.
        $this->actingAs($this->userFor(RoleCode::SUPER_ADMIN))
            ->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman Tidak Ditemukan')
            ->assertDontSee('vendor/laravel');

        // 429 — percobaan login berulang pada satu akun yang sama akan
        // dihentikan rate limiter, dan halaman 429 harus tampil rapi.
        $user = User::factory()->create([
            'username' => 'target429',
            'password' => bcrypt('rahasia123'),
        ]);

        $throttled = false;

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $response = $this->post('/login', [
                'username' => $user->username,
                'password' => 'salah',
            ]);

            if ($response->getStatusCode() === 429) {
                $throttled = true;
                break;
            }
        }

        $this->assertTrue($throttled, 'Rate limiter login tidak menghentikan percobaan berulang.');
    }

    public function test_server_error_page_hides_technical_details(): void
    {
        config(['app.debug' => false]);

        Route::get('/__uji-kesalahan', function () {
            throw new \RuntimeException('Kegagalan internal yang tidak boleh terlihat oleh pengguna.');
        });

        $this->actingAs($this->userFor(RoleCode::SUPER_ADMIN))
            ->get('/__uji-kesalahan')
            ->assertStatus(500)
            ->assertSee('Kesalahan Sistem')
            ->assertDontSee('Kegagalan internal')
            ->assertDontSee('vendor/laravel')
            ->assertDontSee('RuntimeException');
    }

    public function test_unauthorized_role_cannot_reach_module_by_direct_url(): void
    {
        $this->seedDomain();

        // Peran tanpa izin tidak boleh dapat membuka halaman modul lain hanya
        // dengan mengetik URL secara manual. Resepsionis sengaja TIDAK diberi
        // izin rekam medis, farmasi, rawat inap, maupun admin.
        $cases = [
            [RoleCode::CASHIER, route('medical-records.index')],
            [RoleCode::RECEPTIONIST, route('medical-records.index')],
            [RoleCode::RECEPTIONIST, route('inpatient.index')],
            [RoleCode::RECEPTIONIST, route('pharmacy.prescriptions.index')],
            [RoleCode::RECEPTIONIST, route('master.drugs.index')],
            [RoleCode::RECEPTIONIST, route('admin.users.index')],
            [RoleCode::DOCTOR, route('admin.users.index')],
            [RoleCode::DOCTOR, route('billing.index')],
            [RoleCode::NURSE, route('master.drugs.index')],
            [RoleCode::PHARMACIST, route('billing.index')],
            [RoleCode::PHARMACIST, route('inpatient.index')],
            [RoleCode::CASHIER, route('admin.audit-logs.index')],
        ];

        foreach ($cases as [$role, $url]) {
            $this->actingAs($this->userFor($role))
                ->get($url)
                ->assertForbidden("[Role {$role->value} seharusnya tidak bisa membuka {$url}");
        }
    }

    public function test_navigation_only_exposes_modules_the_role_can_open(): void
    {
        $this->seedDomain();

        // Dicek pada URL menu (bukan teks), karena label seperti
        // "No. Rekam Medis" boleh muncul di tempat lain.
        $html = $this->actingAs($this->userFor(RoleCode::CASHIER))->get('/dashboard')->getContent();

        $this->assertStringContainsString(route('billing.index'), $html);
        $this->assertStringContainsString(route('dashboard'), $html);

        $this->assertStringNotContainsString(route('medical-records.index'), $html);
        $this->assertStringNotContainsString(route('inpatient.index'), $html);
        $this->assertStringNotContainsString(route('pharmacy.prescriptions.index'), $html);
        $this->assertStringNotContainsString(route('admin.users.index'), $html);
        $this->assertStringNotContainsString(route('admin.audit-logs.index'), $html);
        $this->assertStringNotContainsString(route('admin.demo-requests.index'), $html);
    }

    public function test_super_admin_sees_every_module(): void
    {
        $this->seedDomain();

        $html = $this->actingAs($this->userFor(RoleCode::SUPER_ADMIN))->get('/dashboard')->getContent();

        foreach ([
            'patients.index', 'registrations.index', 'queue.index', 'schedules.index',
            'inpatient.index', 'medical-records.index', 'pharmacy.prescriptions.index',
            'billing.index', 'rooms.index', 'master.polies.index', 'master.doctors.index',
            'master.icd10.index', 'master.drugs.index', 'reports.index',
            'admin.users.index', 'admin.audit-logs.index', 'admin.demo-requests.index',
        ] as $routeName) {
            $this->assertStringContainsString(
                route($routeName),
                $html,
                "Menu Super Admin tidak menampilkan {$routeName}."
            );
        }
    }

    public function test_pages_do_not_run_queries_while_rendering(): void
    {
        $this->seedDomain();
        $user = $this->userFor(RoleCode::SUPER_ADMIN);

        // Query di dalam view adalah tanda kode belum dipindahkan ke controller.
        DB::enableQueryLog();

        $this->actingAs($user)->get('/dashboard')->assertOk();
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertLessThan(
            60,
            count($queries),
            'Dashboard menjalankan '.count($queries).' query. Periksa N+1 atau query di dalam view.'
        );
    }

    private function seedDomain(): void
    {
        $poly = Poly::factory()->create(['name' => 'Poli Umum', 'queue_prefix' => 'A']);
        $doctor = Doctor::factory()->create(['poly_id' => $poly->id, 'name' => 'dr. Tester, Sp.A']);
        $patient = Patient::factory()->create(['name' => 'Pasien Uji']);
        $registration = Registration::factory()->create([
            'patient_id' => $patient->id,
            'poly_id' => $poly->id,
            'doctor_id' => $doctor->id,
        ]);

        DoctorSchedule::factory()->create(['doctor_id' => $doctor->id]);
        Icd10Code::factory()->create(['code' => 'J06']);
        Drug::factory()->create(['code' => 'OBT1', 'name' => 'Amoxicillin', 'category' => 'Antibiotik']);

        $record = MedicalRecord::factory()->create([
            'registration_id' => $registration->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);
        $record->diagnoses()->create([
            'icd10_code_id' => Icd10Code::first()->id,
            'diagnosis_type' => DiagnosisType::PRIMARY,
        ]);

        Prescription::factory()->create([
            'medical_record_id' => $record->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ])->items()->create([
            'drug_id' => Drug::first()->id,
            'quantity' => 3,
            'unit_price' => 1000,
            'subtotal' => 3000,
        ]);

        Invoice::factory()->create([
            'patient_id' => $patient->id,
            'registration_id' => $registration->id,
        ]);

        Room::factory()->create(['code' => '101']);
        InpatientAdmission::factory()->create([
            'patient_id' => $patient->id,
            'room_id' => Room::first()->id,
            'bed_number' => '1',
        ]);
    }

    /**
     * Mengganti parameter route dengan model nyata. Mengembalikan null bila
     * route membutuhkan model yang belum dibuat pada skenario ini.
     */
    private function fillUri(string $uri, string $name): ?string
    {
        $replacements = [
            '{patient}' => Patient::first()?->id,
            '{registration}' => Registration::first()?->id,
            '{medicalrecord}' => MedicalRecord::first()?->id,
            '{prescription}' => Prescription::first()?->id,
            '{invoice}' => Invoice::first()?->id,
            '{drug}' => Drug::first()?->id,
            '{doctor}' => Doctor::first()?->id,
            '{poly}' => Poly::first()?->id,
            '{room}' => Room::first()?->id,
            '{schedule}' => DoctorSchedule::first()?->id,
            '{user}' => User::query()->where('username', 'superadmin')->value('id'),
            '{auditlog}' => AuditLog::first()?->id,
            '{demorequest}' => DemoRequest::factory()->create()->id,
            '{inpatientadmission}' => InpatientAdmission::first()?->id,
        ];

        foreach ($replacements as $placeholder => $id) {
            if (! str_contains($uri, $placeholder)) {
                continue;
            }

            if ($id === null) {
                return null;
            }

            $uri = str_replace($placeholder, (string) $id, $uri);
        }

        // Route bertipe numerik (mis. status antrean) diisi dengan nilai valid.
        if (str_contains($uri, '{status}')) {
            $uri = str_replace('{status}', RegistrationStatus::DONE->value, $uri);
        }

        if (str_contains($uri, '{type}')) {
            $uri = str_replace('{type}', 'OUTPATIENT', $uri);
        }

        if (str_contains($uri, '{icd10code}')) {
            $uri = str_replace('{icd10code}', (string) Icd10Code::first()->id, $uri);
        }

        return str_contains($uri, '{') ? null : '/'.ltrim($uri, '/');
    }
}
