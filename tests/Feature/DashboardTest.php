<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Antenna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_page_loads_successfully(): void
    {
        Antenna::factory()->count(5)->create(['city' => 'Paris']);
        Antenna::factory()->count(3)->create(['city' => 'Lyon']);

        $response = $this->get('/');

        $response->assertOk()
            ->assertViewIs('dashboard')
            ->assertViewHas('cities')
            ->assertViewHas('defaultApiKey')
            ->assertSee('Supervision Réseau Mobile')
            ->assertSee('Paris')
            ->assertSee('Lyon');
    }

    public function test_swagger_documentation_page_loads_successfully(): void
    {
        $response = $this->get('/docs');

        $response->assertOk()
            ->assertViewIs('swagger')
            ->assertSee('SwaggerUIBundle')
            ->assertSee('/openapi.yaml');
    }

    public function test_openapi_yaml_is_accessible(): void
    {
        $response = $this->get('/openapi.yaml');

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/yaml; charset=utf-8');
    }
}
