<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Vehicle;
use App\Models\Vehicleimage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleimageProfileRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_solo_una_imagen_de_perfil_por_vehiculo(): void
    {
        $vehicle = Vehicle::factory()->create();
        Vehicleimage::factory()->create(['vehicle_id' => $vehicle->id, 'profile' => true]);

        // Intentar crear otra profile=true
        $second = Vehicleimage::factory()->make(['vehicle_id' => $vehicle->id, 'profile' => true]);

        $exists = Vehicleimage::where('vehicle_id', $vehicle->id)->where('profile', true)->exists();
        $this->assertTrue($exists);

        $canCreateSecondProfile = !Vehicleimage::where('vehicle_id', $vehicle->id)->where('profile', true)->exists();
        $this->assertFalse($canCreateSecondProfile, 'Debe impedir más de una imagen de perfil por vehículo.');
    }
}














