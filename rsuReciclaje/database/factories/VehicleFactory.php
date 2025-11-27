<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Brandmodel;
use App\Models\Color;
use App\Models\Vehicle;
use App\Models\Vehicletype;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        $brand = Brand::inRandomOrder()->first() ?? Brand::factory()->create();
        $model = Brandmodel::where('brand_id', $brand->id)->inRandomOrder()->first()
            ?? Brandmodel::create(['brand_id' => $brand->id, 'name' => 'Genérico', 'description' => 'Modelo de prueba']);
        $type = Vehicletype::inRandomOrder()->first() ?? Vehicletype::create(['name' => 'Camión', 'description' => 'Camión']);
        $color = Color::inRandomOrder()->first() ?? Color::create(['name' => 'Blanco', 'code' => '#FFFFFF', 'description' => 'Color blanco']);

        $plate = strtoupper(fake()->bothify('???-###'));

        return [
            'name' => fake()->words(2, true),
            'code' => strtoupper(fake()->bothify('VEH-####')),
            'plate' => $plate,
            'year' => fake()->numberBetween(2010, (int) date('Y')),
            'load_capacity' => fake()->randomFloat(1, 1, 15),
            'description' => fake()->sentence(),
            'status' => 1,
            'brand_id' => $brand->id,
            'model_id' => $model->id,
            'type_id' => $type->id,
            'color_id' => $color->id,
        ];
    }
}














