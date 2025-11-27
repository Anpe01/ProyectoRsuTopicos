<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            ['name' => 'Blanco', 'code' => '#FFFFFF', 'description' => 'Color blanco'],
            ['name' => 'Verde', 'code' => '#008000', 'description' => 'Color verde'],
            ['name' => 'Azul', 'code' => '#0000FF', 'description' => 'Color azul'],
        ];

        foreach ($colors as $c) {
            Color::firstOrCreate(['name' => $c['name'], 'code' => $c['code']], ['description' => $c['description']]);
        }
    }
}














