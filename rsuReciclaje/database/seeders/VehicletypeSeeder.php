<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Vehicletype;
use Illuminate\Database\Seeder;

class VehicletypeSeeder extends Seeder
{
    public function run(): void
    {
        Vehicletype::firstOrCreate(['name' => 'Camión'], ['description' => 'Camión de carga']);
        Vehicletype::firstOrCreate(['name' => 'Compactador'], ['description' => 'Camión compactador de residuos']);
    }
}














