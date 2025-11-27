<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
        [
            'name' => 'adadad',
            'code' => '234-UWU',
            'plate' => 'abc-125',
            'year' => 2018,
            'load_capacity' => 1200,
            'fuel_capacity_l' => 12,
            'compaction_capacity_kg' => 12,
            'people_capacity' => 12,
            'description' => 'asda',
            'status' => 1,
            'brand_id' => 2,
            'logo_id' => 3,
            'brandmodel_id' => 1,
            'type_id' => 1,
            'color_id' => 1,
            'created_at' => '2025-11-18 21:56:55',
            'updated_at' => '2025-11-19 01:50:04'
        ],
        [
            'name' => 'adadad',
            'code' => '123-ja2',
            'plate' => 'abc-124',
            'year' => 2018,
            'load_capacity' => 1200,
            'fuel_capacity_l' => 12,
            'compaction_capacity_kg' => 12,
            'people_capacity' => 12,
            'description' => 'adad',
            'status' => 1,
            'brand_id' => 2,
            'logo_id' => 2,
            'brandmodel_id' => 1,
            'type_id' => 1,
            'color_id' => 1,
            'created_at' => '2025-11-18 22:07:37',
            'updated_at' => '2025-11-19 00:40:32'
        ],
        ];

        foreach ($records as $record) {
            DB::table('vehicles')->insert($record);
        }
    }
}
