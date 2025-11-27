<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Brandmodel;
use Illuminate\Database\Seeder;

class BrandmodelSeeder extends Seeder
{
    public function run(): void
    {
        $pairs = [
            'Toyota' => ['Hilux', 'Corolla'],
            'Nissan' => ['Frontier', 'Versa'],
        ];

        foreach ($pairs as $brandName => $models) {
            $brand = Brand::where('name', $brandName)->first();
            if (!$brand) {
                continue;
            }
            foreach ($models as $m) {
                Brandmodel::firstOrCreate([
                    'brand_id' => $brand->id,
                    'name' => $m,
                ], ['description' => 'Modelo de prueba']);
            }
        }
    }
}














