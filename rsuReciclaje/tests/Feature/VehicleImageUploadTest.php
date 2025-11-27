<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Brandmodel;
use App\Models\Color;
use App\Models\Vehicle;
use App\Models\Vehicletype;
use App\Models\Vehicleimage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VehicleImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_subir_imagen_y_marcar_perfil(): void
    {
        Storage::fake('public');

        $brand = Brand::create(['name' => 'MarcaB', 'description' => 'Prueba']);
        $model = Brandmodel::create(['brand_id' => $brand->id, 'name' => 'M1', 'description' => 'Desc']);
        $type = Vehicletype::create(['name' => 'Camión', 'description' => '']);
        $color = Color::create(['name' => 'Blanco', 'code' => '#FFFFFF', 'description' => '']);
        $vehicle = Vehicle::create([
            'name' => 'Veh1', 'code' => 'V10', 'plate' => 'ABC-123', 'year' => 2020,
            'description' => 'Desc', 'status' => 1,
            'brand_id' => $brand->id, 'model_id' => $model->id,
            'type_id' => $type->id, 'color_id' => $color->id,
        ]);

        $file = UploadedFile::fake()->image('foto.jpg');
        $res = $this->post(route('vehicles.images.store', $vehicle), [
            'image' => $file,
            'profile' => true,
        ]);
        $res->assertSessionHas('success');

        $image = Vehicleimage::first();
        $this->assertTrue((bool) $image->profile);

        // Subir otra y marcar como perfil
        $file2 = UploadedFile::fake()->image('foto2.jpg');
        $res2 = $this->post(route('vehicles.images.store', $vehicle), [
            'image' => $file2,
            'profile' => true,
        ]);
        $res2->assertSessionHas('success');

        $this->assertEquals(1, Vehicleimage::where('vehicle_id', $vehicle->id)->where('profile', true)->count());
    }
}














