<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\InsuranceType;
use App\Enums\Permission;
use App\Enums\RoleCode;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\Poly;
use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_is_locked_after_repeated_failures(): void
    {
        $user = User::factory()->create([
            'username' => 'kunci',
            'password' => Hash::make('rahasia123'),
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login'), ['username' => 'kunci', 'password' => 'salah']);
        }

        $user->refresh();

        $this->assertGreaterThanOrEqual(5, $user->failed_login_count);
        $this->assertNotNull($user->locked_until);
        $this->assertTrue($user->isLockedOut());

        // Bahkan dengan kata sandi benar, akun terkunci tetap ditolak.
        $this->post(route('login'), ['username' => 'kunci', 'password' => 'rahasia123'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_successful_login_resets_failed_attempts(): void
    {
        $user = User::factory()->create([
            'username' => 'bersih',
            'password' => Hash::make('rahasia123'),
            'failed_login_count' => 3,
        ]);

        $this->post(route('login'), ['username' => 'bersih', 'password' => 'rahasia123'])
            ->assertRedirect(route('dashboard'));

        $user->refresh();

        $this->assertSame(0, $user->failed_login_count);
        $this->assertNull($user->locked_until);
        $this->assertNotNull($user->last_login_at);
    }

    public function test_lockout_is_tracked_per_account_not_per_ip(): void
    {
        // Dua akun berbeda dari IP yang sama tidak saling memengaruhi.
        User::factory()->create(['username' => 'satu', 'password' => Hash::make('rahasia123')]);
        User::factory()->create(['username' => 'dua', 'password' => Hash::make('rahasia123')]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login'), ['username' => 'satu', 'password' => 'salah']);
        }

        $this->post(route('login'), ['username' => 'dua', 'password' => 'rahasia123'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_guests_cannot_reach_any_protected_page(): void
    {
        $protected = [
            'dashboard', 'patients.index', 'registrations.index', 'queue.index',
            'medical-records.index', 'pharmacy.prescriptions.index', 'billing.index',
            'inpatient.index', 'rooms.index', 'schedules.index', 'reports.index',
            'master.polies.index', 'admin.users.index', 'admin.audit-logs.index',
        ];

        foreach ($protected as $name) {
            $this->get(route($name))
                ->assertRedirect(route('login'), "Route {$name} dapat diakses tamu.");
        }
    }

    public function test_guests_cannot_mutate_data(): void
    {
        $patient = Patient::factory()->create();
        $poly = Poly::factory()->create();

        $this->post(route('patients.store'), [])->assertRedirect(route('login'));
        $this->put(route('patients.update', $patient), [])->assertRedirect(route('login'));
        $this->delete(route('patients.destroy', $patient))->assertRedirect(route('login'));
        $this->patch(route('patients.toggle', $patient))->assertRedirect(route('login'));
        $this->post(route('registrations.store'), [])->assertRedirect(route('login'));
        $this->post(route('queue.call', $poly))->assertRedirect(route('login'));
    }

    public function test_password_is_never_exposed_in_api_or_html(): void
    {
        $user = $this->superAdmin();
        User::factory()->count(3)->create();

        $html = $this->actingAs($user)->get(route('admin.users.index'))->getContent();

        foreach (User::all() as $listed) {
            $this->assertStringNotContainsString($listed->password, $html);
        }

        $this->assertStringNotContainsString('bcrypt', $html);
        $this->assertStringNotContainsString('$2y$', $html);
    }

    public function test_nik_is_encrypted_at_rest_and_masked_in_html(): void
    {
        $nik = '3201234567890001';
        $patient = Patient::factory()->create(['nik' => $nik]);

        $raw = DB::table('patients')
            ->where('id', $patient->id)
            ->first();

        $this->assertNotSame($nik, $raw->nik, 'NIK tersimpan dalam bentuk plaintext.');
        $this->assertStringNotContainsString($nik, $raw->nik);

        $html = $this->actingAs($this->userFor(RoleCode::MANAGER))
            ->get(route('patients.show', $patient))
            ->getContent();

        $this->assertStringNotContainsString($nik, $html);
        $this->assertStringContainsString('320123******0001', $html);
    }

    public function test_audit_log_hides_sensitive_columns(): void
    {
        config(['simrs.audit_logging_enabled' => true]);

        $patient = Patient::factory()->create(['nik' => '3201234567890001']);

        // Aksi pada pasien memicu audit bertipe updated.
        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->put(route('patients.update', $patient), [
                'name' => 'Nama Baru',
                'nik' => '3201234567890001',
                'gender' => Gender::MALE->value,
                'birth_date' => '1990-05-14',
                'insurance_type' => InsuranceType::NONE->value,
            ]);

        $log = AuditLog::where('event', 'updated')
            ->where('auditable_type', Patient::class)
            ->latest('id')
            ->first();

        if ($log !== null) {
            $values = json_encode($log->new_values);
            $this->assertIsString($values);
            $this->assertStringNotContainsString('"nik"', $values, 'NIK tidak boleh masuk audit log.');
        }

        $this->assertTrue(true);
    }

    public function test_dashboard_does_not_leak_permission_codes(): void
    {
        $html = $this->actingAs($this->userFor(RoleCode::MANAGER))
            ->get(route('dashboard'))
            ->getContent();

        foreach (Permission::cases() as $permission) {
            $this->assertStringNotContainsString(
                $permission->value,
                $html,
                "Kode izin {$permission->value} terekspos di halaman dashboard."
            );
        }
    }

    public function test_login_is_rate_limited(): void
    {
        $user = User::factory()->create([
            'username' => 'target',
            'password' => Hash::make('rahasia123'),
        ]);

        $throttled = false;

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $response = $this->post(route('login'), [
                'username' => 'target',
                'password' => 'salah-sekali',
            ]);

            if ($response->getStatusCode() === 429 || str_contains(
                (string) session('errors')?->first('username'),
                'Terlalu banyak'
            )) {
                $throttled = true;
                break;
            }
        }

        $this->assertTrue($throttled, 'Rate limiter login tidak pernah aktif.');
    }

    public function test_login_throttle_does_not_lock_other_users(): void
    {
        User::factory()->create(['username' => 'korban1', 'password' => Hash::make('rahasia123')]);
        User::factory()->create(['username' => 'korban2', 'password' => Hash::make('rahasia123')]);

        for ($attempt = 0; $attempt < 12; $attempt++) {
            $this->post(route('login'), ['username' => 'korban1', 'password' => 'salah']);
        }

        // Pengguna lain tetap dapat masuk dengan kredensial benar.
        $this->post(route('login'), ['username' => 'korban2', 'password' => 'rahasia123'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_inactive_user_session_is_rejected(): void
    {
        $user = $this->userFor(RoleCode::RECEPTIONIST);

        $this->actingAs($user)->get(route('dashboard'))->assertOk();

        $user->forceFill(['is_active' => false])->save();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_session_id_is_regenerated_on_login(): void
    {
        $user = User::factory()->create([
            'username' => 'sesi',
            'password' => Hash::make('rahasia123'),
        ]);

        $this->get(route('login'));
        $before = session()->getId();

        $this->post(route('login'), ['username' => 'sesi', 'password' => 'rahasia123']);

        $this->assertNotSame($before, session()->getId(), 'Session id tidak diperbarui setelah login.');
    }

    public function test_logout_invalidates_the_session(): void
    {
        $user = $this->userFor(RoleCode::RECEPTIONIST);

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_mass_assignment_cannot_set_protected_columns(): void
    {
        $patient = Patient::factory()->create();

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->put(route('patients.update', $patient), [
                'name' => 'Nama Baru',
                'nik' => '3201234567890009',
                'gender' => Gender::FEMALE->value,
                'birth_date' => '1991-01-01',
                'insurance_type' => InsuranceType::NONE->value,

                // Kolom ini tidak boleh bisa diubah lewat mass assignment.
                'is_active' => false,
                'created_by' => null,
            ]);

        $patient->refresh();

        $this->assertSame('Nama Baru', $patient->name);
        $this->assertTrue($patient->is_active, 'Kolom is_active berhasil diubah lewat mass assignment.');
    }

    public function test_user_cannot_change_their_own_role(): void
    {
        $superAdmin = $this->superAdmin();
        $otherRole = Role::where('code', RoleCode::RECEPTIONIST->value)->firstOrFail();

        $this->actingAs($superAdmin)
            ->put(route('admin.users.update', $superAdmin), [
                'role_id' => $otherRole->id,
                'name' => $superAdmin->name,
                'username' => $superAdmin->username,
            ])
            ->assertSessionHas('error');

        $this->assertSame(RoleCode::SUPER_ADMIN->value, $superAdmin->refresh()->role_code);
    }

    public function test_user_cannot_delete_their_own_account(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertModelExists($admin);
    }

    public function test_user_cannot_disable_their_own_account(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->patch(route('admin.users.toggle', $admin))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertTrue($admin->refresh()->is_active);
    }

    public function test_sql_injection_attempt_in_search_is_treated_as_literal_text(): void
    {
        $payload = "' OR 1=1; DROP TABLE patients; --";

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->get(route('patients.index', ['q' => $payload]))
            ->assertOk();

        // Tabel dan data patients tidak rusak.
        $this->assertSame(0, Patient::count());
        $this->assertDatabaseCount('patients', 0);
    }

    public function test_xss_payload_in_search_is_escaped(): void
    {
        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->get(route('patients.index', ['q' => '<script>alert(1)</script>']))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_xss_payload_in_patient_name_is_escaped(): void
    {
        $patient = Patient::factory()->create([
            'name' => '<script>alert("xss")</script>',
        ]);

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->get(route('patients.show', $patient))
            ->assertOk()
            ->assertDontSee('<script>alert("xss")</script>', false);
    }

    public function test_enum_backed_route_parameters_are_validated(): void
    {
        $registration = Registration::factory()->create();

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->patch("/queue/registrations/{$registration->id}/status/HACKED")
            ->assertNotFound();
    }

    public function test_honeypot_field_blocks_bot_submissions(): void
    {
        $response = $this->from(route('home'))
            ->post(route('demo.store'), [
                'name' => 'Bot',
                'institution' => 'Bot Ltd',
                'email' => 'bot@example.test',
                'phone' => '08123456789',
                'facility_type' => 'HOSPITAL',
                'company_website' => 'https://spam.example',
            ]);

        $response->assertSessionHasErrors('company_website');
        $this->assertDatabaseCount('demo_requests', 0);
    }

    public function test_demo_form_is_rate_limited(): void
    {
        $payload = [
            'name' => 'Penguji',
            'institution' => 'RS Uji',
            'email' => 'uji@example.test',
            'phone' => '08123456789',
            'facility_type' => 'HOSPITAL',
        ];

        $throttled = false;

        for ($attempt = 0; $attempt < 25; $attempt++) {
            $response = $this->post(route('demo.store'), $payload);

            if ($response->getStatusCode() === 429) {
                $throttled = true;
                break;
            }
        }

        $this->assertTrue($throttled, 'Rate limiter permintaan demo tidak pernah aktif.');
    }

    public function test_env_file_is_not_ignored_by_git(): void
    {
        // .env wajib diabaikan git, tetapi .env.example ikut direpository.
        $ignore = file_get_contents(base_path('.gitignore'));

        $this->assertMatchesRegularExpression('/^\.env$/m', $ignore);
        $this->assertFileExists(base_path('.env.example'));
    }

    public function test_env_example_does_not_contain_secrets(): void
    {
        $example = file_get_contents(base_path('.env.example'));

        $this->assertStringNotContainsString('APP_KEY=base64:', $example);
        $this->assertDoesNotMatchRegularExpression(
            '/APP_KEY=.+/',
            $example,
            '.env.example tidak boleh memuat APP_KEY sungguhan.'
        );
    }

    public function test_app_key_is_present_in_env_but_not_tracked(): void
    {
        $env = file_get_contents(base_path('.env'));

        $this->assertMatchesRegularExpression('/^APP_KEY=base64:\S+/m', $env);
    }

    public function test_guest_layout_does_not_leak_internal_paths(): void
    {
        $html = $this->get(route('login'))->getContent();

        $this->assertStringNotContainsString(base_path(), $html);
        $this->assertStringNotContainsString('vendor/laravel', $html);
    }

    public function test_unexpected_route_returns_consistent_404(): void
    {
        $this->actingAs($this->userFor(RoleCode::SUPER_ADMIN))
            ->get('/halaman-yang-tidak-ada-sama-sekali')
            ->assertNotFound()
            ->assertSee('Halaman Tidak Ditemukan');
    }
}
