<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AntennaStatus;
use App\Models\Antenna;
use App\Models\Intervention;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterventionApiTest extends TestCase
{
    use RefreshDatabase;

    private string $apiKey = 'test-api-key';

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->apiKey,
        ];
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $antenna = Antenna::factory()->create(['status' => AntennaStatus::UP]);

        $createResponse = $this->postJson('/api/v1/interventions', [
            'antenna_id' => $antenna->id,
            'description' => 'Test maintenance',
            'technician_identity' => 'Jean Dupont',
            'priority' => 'MEDIUM',
        ]);

        $createResponse->assertUnauthorized()
            ->assertJson([
                'error' => 'UNAUTHORIZED',
            ]);

        $intervention = Intervention::factory()->active()->create([
            'antenna_id' => $antenna->id,
        ]);

        $closeResponse = $this->patchJson("/api/v1/interventions/{$intervention->id}/close");

        $closeResponse->assertUnauthorized()
            ->assertJson([
                'error' => 'UNAUTHORIZED',
            ]);
    }

    public function test_can_authenticate_with_x_api_key_header(): void
    {
        $antenna = Antenna::factory()->create(['status' => AntennaStatus::UP]);

        $response = $this->postJson(
            '/api/v1/interventions',
            [
                'antenna_id' => $antenna->id,
                'description' => 'Maintenance par X-API-KEY',
                'technician_identity' => 'Jean Dupont',
                'priority' => 'LOW',
            ],
            ['X-API-KEY' => $this->apiKey]
        );

        $response->assertCreated();
    }

    public function test_nominal_intervention_creation_switches_antenna_to_down(): void
    {
        $antenna = Antenna::factory()->create([
            'status' => AntennaStatus::UP,
        ]);

        $payload = [
            'antenna_id' => $antenna->id,
            'description' => 'Changement du bloc radio émetteur',
            'technician_identity' => 'Thomas Dubois',
            'priority' => 'HIGH',
        ];

        $response = $this->postJson('/api/v1/interventions', $payload, $this->authHeaders());

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'antenna_id',
                    'description',
                    'technician_identity',
                    'priority',
                    'is_active',
                    'ended_at',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'data' => [
                    'antenna_id' => $antenna->id,
                    'description' => 'Changement du bloc radio émetteur',
                    'technician_identity' => 'Thomas Dubois',
                    'priority' => 'HIGH',
                    'is_active' => true,
                    'ended_at' => null,
                ],
            ]);

        $this->assertDatabaseHas('interventions', [
            'antenna_id' => $antenna->id,
            'technician_identity' => 'Thomas Dubois',
            'priority' => 'HIGH',
            'ended_at' => null,
        ]);

        $antenna->refresh();
        $this->assertSame(AntennaStatus::DOWN, $antenna->status);
    }

    public function test_cannot_create_second_active_intervention_on_same_antenna(): void
    {
        $antenna = Antenna::factory()->create([
            'status' => AntennaStatus::DOWN,
        ]);

        Intervention::factory()->active()->create([
            'antenna_id' => $antenna->id,
            'description' => 'Intervention déjà en cours',
        ]);

        $payload = [
            'antenna_id' => $antenna->id,
            'description' => 'Deuxième intervention concurrente',
            'technician_identity' => 'Émilie Martin',
            'priority' => 'MEDIUM',
        ];

        $response = $this->postJson('/api/v1/interventions', $payload, $this->authHeaders());

        $response->assertStatus(409)
            ->assertJson([
                'error' => 'ACTIVE_INTERVENTION_CONFLICT',
            ]);

        $this->assertSame(1, Intervention::where('antenna_id', $antenna->id)->count());
    }

    public function test_nominal_intervention_closing_switches_antenna_back_to_up(): void
    {
        $antenna = Antenna::factory()->create([
            'status' => AntennaStatus::DOWN,
        ]);

        $intervention = Intervention::factory()->active()->create([
            'antenna_id' => $antenna->id,
        ]);

        $this->assertSame(AntennaStatus::DOWN, $antenna->status);
        $this->assertTrue($intervention->isActive());

        $response = $this->patchJson(
            "/api/v1/interventions/{$intervention->id}/close",
            [],
            $this->authHeaders()
        );

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $intervention->id,
                    'is_active' => false,
                ],
            ]);

        $this->assertNotNull($response->json('data.ended_at'));

        $intervention->refresh();
        $this->assertNotNull($intervention->ended_at);
        $this->assertFalse($intervention->isActive());

        $antenna->refresh();
        $this->assertSame(AntennaStatus::UP, $antenna->status);
    }

    public function test_closing_already_closed_intervention_returns_422(): void
    {
        $antenna = Antenna::factory()->create(['status' => AntennaStatus::UP]);

        $intervention = Intervention::factory()->ended()->create([
            'antenna_id' => $antenna->id,
        ]);

        $response = $this->patchJson(
            "/api/v1/interventions/{$intervention->id}/close",
            [],
            $this->authHeaders()
        );

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'INTERVENTION_ALREADY_ENDED',
            ]);
    }

    public function test_validation_errors_when_required_fields_are_missing(): void
    {
        $response = $this->postJson('/api/v1/interventions', [], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'antenna_id',
                'description',
                'technician_identity',
                'priority',
            ]);
    }

    public function test_validation_error_when_antenna_does_not_exist(): void
    {
        $payload = [
            'antenna_id' => 999999,
            'description' => 'Test sur antenne inexistante',
            'technician_identity' => 'Jean Dupont',
            'priority' => 'LOW',
        ];

        $response = $this->postJson('/api/v1/interventions', $payload, $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['antenna_id']);
    }
}
