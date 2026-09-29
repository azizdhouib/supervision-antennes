<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AntennaStatus;
use App\Models\Antenna;
use Illuminate\Database\Eloquent\Factories\Factory;

class AntennaFactory extends Factory
{
    protected $model = Antenna::class;

    public function definition(): array
    {
        $frenchCities = [
            'Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice',
            'Nantes', 'Montpellier', 'Strasbourg', 'Bordeaux', 'Lille',
            'Rennes', 'Toulon', 'Reims', 'Saint-Étienne', 'Le Havre',
        ];

        return [
            'name' => 'ANT-'.strtoupper(fake()->bothify('??-####')),
            'city' => fake()->randomElement($frenchCities),
            'status' => AntennaStatus::UP,
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }

    public function down(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AntennaStatus::DOWN,
        ]);
    }

    public function up(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AntennaStatus::UP,
        ]);
    }
}
