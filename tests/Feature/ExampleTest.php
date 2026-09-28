<?php

namespace Tests\Feature;

use App\Enums\RoleCode;
use App\Models\Poly;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pengguna yang sudah masuk diarahkan ke dashboard, tidak melihat halaman
     * pemasaran.
     */
    public function test_root_redirects_authenticated_user_to_dashboard(): void
    {
        Poly::factory()->create();

        $this->actingAs($this->userFor(RoleCode::RECEPTIONIST))
            ->get('/')
            ->assertRedirect(route('dashboard'));
    }

    /**
     * Tamu melihat halaman depan, dan halaman tersebut hanya kerangka mount —
     * sisanya dirender Vue. Yang diuji adalah data yang benar-benar dikirim
     * Blade ke Vue lewat #landing-config, bukan teks yang sudah dirender browser.
     */
    public function test_root_renders_vue_mount_point_for_guests(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('id="landing-app"', false)
            ->assertSee('id="landing-config"', false);

        $config = $this->landingConfig();

        $this->assertNull($config['dashboardUrl'], 'Tamu tidak boleh mendapat tautan dashboard.');
        $this->assertSame(route('login'), $config['loginUrl']);
        $this->assertSame(route('demo.store'), $config['demoAction']);
        $this->assertNotEmpty($config['csrfToken']);
    }

    /** Formulir demo harus tetap bisa terkirim tanpa JavaScript. */
    public function test_root_includes_noscript_demo_form(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<noscript>', false)
            ->assertSee('name="company_website"', false)
            ->assertSee(route('demo.store'), false);
    }

    public function test_health_check_endpoint_is_available(): void
    {
        $this->get('/up')->assertOk();
    }

    /** @return array<string, mixed> */
    protected function landingConfig(): array
    {
        $html = $this->get('/')->getContent();

        $this->assertMatchesRegularExpression(
            '#<script type="application/json" id="landing-config">(.*?)</script>#s',
            $html,
            'Blok #landing-config tidak ada di halaman depan.'
        );

        preg_match('#<script type="application/json" id="landing-config">(.*?)</script>#s', $html, $m);

        $decoded = json_decode($m[1], true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($decoded);

        return $decoded;
    }
}
