<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Zone;
use App\Models\Zonecoord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Zonecoord>
 */
class ZonecoordFactory extends Factory
{
    protected $model = Zonecoord::class;

    public function definition(): array
    {
        $zone = Zone::inRandomOrder()->first() ?? Zone::factory()->create();
        return [
            'zone_id' => $zone->id,
            'latitude' => fake()->latitude(-12.2, -11.7),
            'longitude' => fake()->longitude(-77.2, -76.7),
            'sequence' => fake()->numberBetween(0, 100),
        ];
    }
}














