<?php

namespace Tests\Feature;

use App\Enums\Permission;
use App\Enums\RoleCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_can_be_rendered(): void
    {
        $this->get('/login')->assertOk()->assertSee('SIMRS-RME');
    }

    public function test_users_can_authenticate_with_valid_credentials(): void
    {
        $user = $this->userFor(RoleCode::RECEPTIONIST, ['username' => 'resepsionis', 'password' => bcrypt('rahasia123')]);

        $response = $this->post('/login', [
            'username' => 'resepsionis',
            'password' => 'rahasia123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_is_rejected_with_wrong_password(): void
    {
        $this->userFor(RoleCode::RECEPTIONIST, ['username' => 'resepsionis']);

        $this->post('/login', ['username' => 'resepsionis', 'password' => 'salah'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_inactive_account_cannot_login(): void
    {
        $this->userFor(RoleCode::CASHIER, [
            'username' => 'kasir',
            'password' => bcrypt('rahasia123'),
            'is_active' => false,
        ]);

        $this->post('/login', ['username' => 'kasir', 'password' => 'rahasia123'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_last_login_metadata_is_recorded(): void
    {
        $this->userFor(RoleCode::NURSE, ['username' => 'perawat', 'password' => bcrypt('rahasia123')]);

        $this->post('/login', ['username' => 'perawat', 'password' => 'rahasia123']);

        $user = User::where('username', 'perawat')->firstOrFail();

        $this->assertNotNull($user->last_login_at);
        $this->assertNotNull($user->last_login_ip);
    }

    public function test_users_can_logout(): void
    {
        $this->actingAs($this->userFor(RoleCode::MANAGER))
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/patients')->assertRedirect(route('login'));
    }

    public function test_role_permission_matrix_is_applied(): void
    {
        $this->assertTrue($this->userFor(RoleCode::SUPER_ADMIN)->hasPermission(Permission::MANAGE_USER));
        $this->assertTrue($this->userFor(RoleCode::SUPER_ADMIN)->hasPermission(Permission::MANAGE_BILLING));

        $receptionist = $this->userFor(RoleCode::RECEPTIONIST);
        $this->assertTrue($receptionist->hasPermission(Permission::MANAGE_PATIENT));
        $this->assertFalse($receptionist->hasPermission(Permission::MANAGE_USER));
        $this->assertFalse($receptionist->hasPermission(Permission::VIEW_EMR));

        $pharmacist = $this->userFor(RoleCode::PHARMACIST);
        $this->assertTrue($pharmacist->hasPermission(Permission::MANAGE_PHARMACY));
        $this->assertFalse($pharmacist->hasPermission(Permission::MANAGE_PATIENT));

        $cashier = $this->userFor(RoleCode::CASHIER);
        $this->assertTrue($cashier->hasPermission(Permission::MANAGE_BILLING));
        $this->assertFalse($cashier->hasPermission(Permission::MANAGE_EMR));
    }

    public function test_unauthorized_role_gets_403_on_forbidden_page(): void
    {
        $this->actingAs($this->userFor(RoleCode::CASHIER))
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_navigation_only_exposes_permitted_modules(): void
    {
        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->get('/')
            ->assertOk()
            ->assertSee('Pendaftaran')
            ->assertDontSee('Pengguna');
    }
}
