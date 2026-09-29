<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AntennaStatus;
use App\Enums\InterventionPriority;
use App\Models\Antenna;
use App\Models\Intervention;
use Illuminate\Database\Seeder;

class AntennaSeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            'Paris' => 20,
            'Marseille' => 15,
            'Lyon' => 15,
            'Toulouse' => 10,
            'Nice' => 8,
            'Nantes' => 8,
            'Strasbourg' => 6,
            'Bordeaux' => 8,
            'Lille' => 6,
            'Rennes' => 4,
        ];

        $techs = [
            'Jean Dupont', 'Marc Lefebvre', 'Claire Fontaine',
            'David Mercier', 'Hélène Roussel', 'Alexandre Vasseur',
        ];

        foreach ($cities as $city => $count) {
            for ($i = 1; $i <= $count; $i++) {
                $status = ($i % 5 === 0) ? AntennaStatus::DOWN : AntennaStatus::UP;

                $antenna = Antenna::create([
                    'name' => sprintf('ANT-%s-%04d', strtoupper(substr($city, 0, 3)), $i),
                    'city' => $city,
                    'status' => $status,
                    'created_at' => now()->subDays(rand(30, 365)),
                    'updated_at' => now(),
                ]);

                if ($i % 3 === 0) {
                    Intervention::create([
                        'antenna_id' => $antenna->id,
                        'description' => 'Contrôle préventif trimestriel et test de débit 5G',
                        'technician_identity' => $techs[array_rand($techs)],
                        'priority' => InterventionPriority::LOW,
                        'created_at' => now()->subDays(rand(10, 25)),
                        'updated_at' => now()->subDays(rand(8, 9)),
                        'ended_at' => now()->subDays(rand(8, 9)),
                    ]);
                }

                if ($status === AntennaStatus::DOWN) {
                    Intervention::create([
                        'antenna_id' => $antenna->id,
                        'description' => 'Avarie matérielle sur le sous-système RF. Remplacement d équipement nécessaire.',
                        'technician_identity' => $techs[array_rand($techs)],
                        'priority' => ($i % 2 === 0) ? InterventionPriority::HIGH : InterventionPriority::MEDIUM,
                        'created_at' => now()->subHours(rand(2, 36)),
                        'updated_at' => now(),
                        'ended_at' => null,
                    ]);
                }
            }
        }
    }
}
