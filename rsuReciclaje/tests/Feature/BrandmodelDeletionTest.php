<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Brandmodel;
use App\Models\Color;
use App\Models\Vehicle;
use App\Models\Vehicletype;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandmodelDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_permite_borrar_brandmodel_con_vehicle(): void
    {
        $brand = Brand::create(['name' => 'MarcaZ', 'description' => 'Prueba']);
        $model = Brandmodel::create(['brand_id' => $brand->id, 'name' => 'M1', 'description' => 'Desc']);
        $type = Vehicletype::create(['name' => 'Camión', 'description' => '']);
        $color = Color::create(['name' => 'Blanco', 'code' => '#FFFFFF', 'description' => '']);
        Vehicle::create([
            'name' => 'Veh1', 'code' => 'V1', 'plate' => 'ABC-123', 'year' => 2020,
            'description' => 'Desc', 'status' => 1,
            'brand_id' => $brand->id, 'model_id' => $model->id,
            'type_id' => $type->id, 'color_id' => $color->id,
        ]);

        $response = $this->delete(route('brandmodels.destroy', $model));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('brandmodels', ['id' => $model->id]);
    }
}














