<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
        [
            'name' => null,
            'zone_id' => 1,
            'vehicle_id' => 1,
            'conductor_id' => 3,
            'shift_id' => 1,
            'weekdays' => '\"[5,6]\"',
            'start_date' => '2025-12-12',
            'end_date' => '2025-12-13',
            'created_at' => '2025-11-24 18:33:09',
            'updated_at' => '2025-11-24 18:33:09'
        ],
        [
            'name' => null,
            'zone_id' => 1,
            'vehicle_id' => 1,
            'conductor_id' => 3,
            'shift_id' => 1,
            'weekdays' => '\"[1,2,4]\"',
            'start_date' => '2025-11-05',
            'end_date' => '2025-11-19',
            'created_at' => '2025-11-24 18:34:49',
            'updated_at' => '2025-11-24 18:34:49'
        ],
        [
            'name' => null,
            'zone_id' => 1,
            'vehicle_id' => 2,
            'conductor_id' => 3,
            'shift_id' => 2,
            'weekdays' => '\"[1,3,5]\"',
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-14',
            'created_at' => '2025-11-24 18:42:39',
            'updated_at' => '2025-11-24 18:42:39'
        ],
        [
            'name' => null,
            'zone_id' => 1,
            'vehicle_id' => 1,
            'conductor_id' => 2,
            'shift_id' => 2,
            'weekdays' => '\"[1,3,4]\"',
            'start_date' => '2025-11-05',
            'end_date' => '2025-11-29',
            'created_at' => '2025-11-24 21:08:50',
            'updated_at' => '2025-11-24 21:08:50'
        ],
        ];

        foreach ($records as $record) {
            DB::table('programs')->insert($record);
        }
    }
}
