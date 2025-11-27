<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
        [
            'name' => 'hjadhjahjd',
            'department_id' => 14,
            'province_id' => null,
            'area' => null,
            'description' => null,
            'district_id' => null,
            'polygon' => '{\"type\":\"Polygon\",\"coordinates\":[[[-79.830936,-6.771976],[-79.831869,-6.771891],[-79.831778,-6.771055],[-79.83085,-6.771161],[-79.830936,-6.771976]]]}',
            'area_km2' => 0.009,
            'avg_waste_tb' => 1200.00,
            'active' => 1,
            'created_at' => '2025-11-18 22:42:59',
            'updated_at' => '2025-11-18 22:42:59'
        ],
        ];

        foreach ($records as $record) {
            DB::table('zones')->insert($record);
        }
    }
}
