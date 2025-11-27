<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonnelGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
        [
            'name' => 'oajdadads',
            'zone_id' => 1,
            'shift_id' => 2,
            'vehicle_id' => 2,
            'driver_id' => 3,
            'helper1_id' => 1,
            'helper2_id' => 2,
            'mon' => 1,
            'tue' => 1,
            'wed' => 1,
            'thu' => 1,
            'fri' => 1,
            'sat' => 0,
            'sun' => 0,
            'active' => 1,
            'created_at' => '2025-11-20 23:31:35',
            'updated_at' => '2025-11-20 23:31:35'
        ],
        [
            'name' => 'aaa',
            'zone_id' => 1,
            'shift_id' => 1,
            'vehicle_id' => 1,
            'driver_id' => 3,
            'helper1_id' => 1,
            'helper2_id' => 2,
            'mon' => 1,
            'tue' => 1,
            'wed' => 1,
            'thu' => 1,
            'fri' => 1,
            'sat' => 0,
            'sun' => 0,
            'active' => 1,
            'created_at' => '2025-11-20 23:32:14',
            'updated_at' => '2025-11-20 23:32:14'
        ],
        [
            'name' => 'asdadadad',
            'zone_id' => 1,
            'shift_id' => 2,
            'vehicle_id' => 1,
            'driver_id' => 2,
            'helper1_id' => 3,
            'helper2_id' => 1,
            'mon' => 1,
            'tue' => 0,
            'wed' => 0,
            'thu' => 0,
            'fri' => 1,
            'sat' => 0,
            'sun' => 1,
            'active' => 1,
            'created_at' => '2025-11-20 23:34:42',
            'updated_at' => '2025-11-20 23:34:42'
        ],
        ];

        foreach ($records as $record) {
            DB::table('personnel_groups')->insert($record);
        }
    }
}
