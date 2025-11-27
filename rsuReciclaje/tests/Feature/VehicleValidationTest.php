<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Brandmodel;
use App\Models\Color;
use App\Models\Vehicletype;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_vehiculo_valida_plate_y_year(): void
    {
        $brand = Brand::create(['name' => 'MarcaA', 'description' => 'Prueba']);
        $model = Brandmodel::create(['brand_id' => $brand->id, 'name' => 'M1', 'description' => 'Desc']);
        $type = Vehicletype::create(['name' => 'Camión', 'description' => '']);
        $color = Color::create(['name' => 'Blanco', 'code' => '#FFFFFF', 'description' => '']);

        // Placa inválida
        $response = $this->post(route('vehicles.store'), [
            'name' => 'Veh',
            'code' => 'COD-001',
            'plate' => 'XX-99999',
            'year' => 1940,
            'description' => 'Desc',
            'status' => 1,
            'brand_id' => $brand->id,
            'model_id' => $model->id,
            'type_id' => $type->id,
            'color_id' => $color->id,
        ]);
        $response->assertSessionHasErrors(['plate', 'year']);

        // Placa válida y año válido
        $response2 = $this->post(route('vehicles.store'), [
            'name' => 'Veh',
            'code' => 'COD-002',
            'plate' => 'ABC-123',
            'year' => (int) date('Y'),
            'description' => 'Desc',
            'status' => 1,
            'brand_id' => $brand->id,
            'model_id' => $model->id,
            'type_id' => $type->id,
            'color_id' => $color->id,
        ]);
        $response2->assertSessionHasNoErrors();
    }
}














