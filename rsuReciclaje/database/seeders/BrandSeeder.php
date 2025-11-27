<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        Brand::firstOrCreate(['name' => 'Toyota'], ['description' => 'Marca japonesa', 'logo' => null]);
        Brand::firstOrCreate(['name' => 'Nissan'], ['description' => 'Marca japonesa', 'logo' => null]);
    }
}














