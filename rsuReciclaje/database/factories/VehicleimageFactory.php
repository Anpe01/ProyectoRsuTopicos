<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\Vehicleimage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Vehicleimage>
 */
class VehicleimageFactory extends Factory
{
    protected $model = Vehicleimage::class;

    public function definition(): array
    {
        $vehicle = Vehicle::inRandomOrder()->first() ?? Vehicle::factory()->create();

        return [
            'vehicle_id' => $vehicle->id,
            'image' => 'vehicles/'.fake()->uuid().'.jpg',
            'profile' => false,
        ];
    }
}














