<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\District;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Zone>
 */
class ZoneFactory extends Factory
{
    protected $model = Zone::class;

    public function definition(): array
    {
        $district = District::inRandomOrder()->first() ?? District::factory()->create();
        return [
            'name' => 'Zona '.fake()->unique()->numerify('###'),
            'area' => fake()->randomFloat(2, 0.5, 50),
            'description' => fake()->sentence(),
            'district_id' => $district->id,
        ];
    }
}














