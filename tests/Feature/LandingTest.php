<?php

namespace Tests\Feature;

use App\Enums\RoleCode;
use App\Models\Poly;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_the_public_landing_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('SIMRS', false);
    }

    public function test_authenticated_user_is_redirected_to_dashboard(): void
    {
        Poly::factory()->create();

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->get('/')
            ->assertRedirect(route('dashboard'));
    }

    public function test_health_check_endpoint_is_available(): void
    {
        $this->get('/up')->assertOk();
    }
}
