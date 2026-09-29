<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InterventionPriority;
use App\Models\Antenna;
use App\Models\Intervention;
use Illuminate\Database\Eloquent\Factories\Factory;

class InterventionFactory extends Factory
{
    protected $model = Intervention::class;

    public function definition(): array
    {
        $descriptions = [
            'Remplacement module radio 5G 3.5GHz défaillant',
            'Maintenance préventive et calibration des amplificateurs',
            'Coupure d alimentation électrique - intervention sur onduleur',
            'Ajustement de l azimut des panneaux d antenne suite tempête',
            'Mise à jour du firmware du contrôleur de station de base',
            'Remplacement câble coaxial défectueux',
            'Problème de connectivité fibre optique sur le backhaul',
        ];

        $technicians = [
            'Thomas Dubois', 'Émilie Martin', 'Alexandre Bernard',
            'Sophie Petit', 'Lucas Richard', 'Camille Robert',
            'Julien Moreau', 'Sarah Simon', 'Antoine Laurent',
        ];

        return [
            'antenna_id' => Antenna::factory(),
            'description' => fake()->randomElement($descriptions),
            'technician_identity' => fake()->randomElement($technicians),
            'priority' => fake()->randomElement(InterventionPriority::cases()),
            'created_at' => fake()->dateTimeBetween('-30 days', '-1 days'),
            'updated_at' => now(),
            'ended_at' => fake()->dateTimeBetween('-24 hours', 'now'),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'ended_at' => null,
            'created_at' => now()->subHours(fake()->numberBetween(1, 48)),
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'ended_at' => now(),
        ]);
    }
}
