<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EmployeeFunction;
use Illuminate\Database\Seeder;

class FunctionsSeeder extends Seeder
{
    public function run(): void
    {
        EmployeeFunction::firstOrCreate(['name' => 'Conductor'], [
            'description' => 'Conductor de vehículo',
            'protected' => true,
        ]);
        
        EmployeeFunction::firstOrCreate(['name' => 'Ayudante'], [
            'description' => 'Ayudante de recolección',
            'protected' => true,
        ]);
    }
}


