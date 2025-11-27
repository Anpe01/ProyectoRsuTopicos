<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Seeders de catálogos básicos (deben ejecutarse primero)
            BrandSeeder::class,
            VehicletypeSeeder::class,
            ColorSeeder::class,
            UbigeoSeeder::class,
            BrandmodelSeeder::class,
            FunctionsSeeder::class,
            // Seeders de entidades principales
            EmployeeSeeder::class,
            ShiftSeeder::class,
            ZoneSeeder::class,
            VehicleSeeder::class,
            PersonnelGroupSeeder::class,
            // Seeders de relaciones y datos operativos
            ContractSeeder::class,
            AttendanceSeeder::class,
            ProgramSeeder::class,
            RunSeeder::class,
            RunPersonnelSeeder::class,
            RunChangeSeeder::class,
            ChangeReasonSeeder::class,
        ]);
    }
}
