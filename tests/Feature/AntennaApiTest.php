<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AntennaStatus;
use App\Enums\InterventionPriority;
use App\Models\Antenna;
use App\Models\Intervention;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AntennaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_antennas_with_pagination(): void
    {
        Antenna::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/antennas?per_page=10');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'city',
                        'status',
                        'has_active_intervention',
                        'latest_intervention',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                ],
            ]);

        $this->assertCount(10, $response->json('data'));
        $this->assertSame(25, $response->json('meta.total'));
    }

    public function test_antenna_embeds_its_latest_intervention(): void
    {
        $antenna = Antenna::factory()->create([
            'status' => AntennaStatus::DOWN,
        ]);

        Intervention::factory()->create([
            'antenna_id' => $antenna->id,
            'description' => 'Première intervention clôturée',
            'created_at' => now()->subDays(5),
            'ended_at' => now()->subDays(4),
        ]);

        $latest = Intervention::factory()->active()->create([
            'antenna_id' => $antenna->id,
            'description' => 'Dernière intervention en cours',
            'priority' => InterventionPriority::HIGH,
            'created_at' => now()->subHour(),
        ]);

        $response = $this->getJson('/api/v1/antennas');

        $response->assertOk();
        $antennaData = collect($response->json('data'))->firstWhere('id', $antenna->id);

        $this->assertNotNull($antennaData);
        $this->assertTrue($antennaData['has_active_intervention']);
        $this->assertNotNull($antennaData['latest_intervention']);
        $this->assertSame($latest->id, $antennaData['latest_intervention']['id']);
        $this->assertSame('Dernière intervention en cours', $antennaData['latest_intervention']['description']);
        $this->assertSame('HIGH', $antennaData['latest_intervention']['priority']);
        $this->assertTrue($antennaData['latest_intervention']['is_active']);
    }

    public function test_can_filter_antennas_by_city(): void
    {
        Antenna::factory()->create(['city' => 'Paris']);
        Antenna::factory()->create(['city' => 'Paris']);
        Antenna::factory()->create(['city' => 'Lyon']);

        $response = $this->getJson('/api/v1/antennas?city=Paris');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));

        foreach ($response->json('data') as $item) {
            $this->assertSame('Paris', $item['city']);
        }
    }

    public function test_can_filter_antennas_by_status(): void
    {
        Antenna::factory()->create(['status' => AntennaStatus::UP]);
        Antenna::factory()->create(['status' => AntennaStatus::UP]);
        Antenna::factory()->create(['status' => AntennaStatus::DOWN]);

        $response = $this->getJson('/api/v1/antennas?status=DOWN');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('DOWN', $response->json('data.0.status'));
    }

    public function test_invalid_status_filter_returns_validation_error(): void
    {
        $response = $this->getJson('/api/v1/antennas?status=INVALID_STATUS');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }
}
