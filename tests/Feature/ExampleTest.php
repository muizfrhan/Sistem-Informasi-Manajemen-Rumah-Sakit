<?php

namespace Tests\Feature;

use App\Enums\RoleCode;
use App\Models\Poly;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_authenticated_user_to_dashboard(): void
    {
        Poly::factory()->create();

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->get('/')
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_health_check_endpoint_is_available(): void
    {
        $this->get('/up')->assertOk();
    }
}
