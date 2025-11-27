<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
        [
            'run_date' => '2025-11-05',
            'status' => 'Programado',
            'zone_id' => 1,
            'shift_id' => 2,
            'vehicle_id' => 1,
            'group_id' => 5,
            'notes' => null,
            'created_at' => '2025-11-24 21:08:50',
            'updated_at' => '2025-11-24 22:01:18'
        ],
        [
            'run_date' => '2025-11-06',
            'status' => 'Programado',
            'zone_id' => 1,
            'shift_id' => 2,
            'vehicle_id' => 1,
            'group_id' => 5,
            'notes' => null,
            'created_at' => '2025-11-24 21:08:50',
            'updated_at' => '2025-11-24 22:01:18'
        ],
        [
            'run_date' => '2025-11-10',
            'status' => 'Programado',
            'zone_id' => 1,
            'shift_id' => 2,
            'vehicle_id' => 1,
            'group_id' => 5,
            'notes' => null,
            'created_at' => '2025-11-24 21:08:50',
            'updated_at' => '2025-11-24 22:01:19'
        ],
        [
            'run_date' => '2025-11-12',
            'status' => 'Programado',
            'zone_id' => 1,
            'shift_id' => 2,
            'vehicle_id' => 1,
            'group_id' => 5,
            'notes' => null,
            'created_at' => '2025-11-24 21:08:50',
            'updated_at' => '2025-11-24 22:01:19'
        ],
        [
            'run_date' => '2025-11-13',
            'status' => 'Programado',
            'zone_id' => 1,
            'shift_id' => 2,
            'vehicle_id' => 1,
            'group_id' => 5,
            'notes' => null,
            'created_at' => '2025-11-24 21:08:50',
            'updated_at' => '2025-11-24 22:01:19'
        ],
        [
            'run_date' => '2025-11-17',
            'status' => 'Programado',
            'zone_id' => 1,
            'shift_id' => 2,
            'vehicle_id' => 1,
            'group_id' => 5,
            'notes' => null,
            'created_at' => '2025-11-24 21:08:50',
            'updated_at' => '2025-11-24 22:01:19'
        ],
        [
            'run_date' => '2025-11-19',
            'status' => 'Programado',
            'zone_id' => 1,
            'shift_id' => 2,
            'vehicle_id' => 1,
            'group_id' => 5,
            'notes' => null,
            'created_at' => '2025-11-24 21:08:50',
            'updated_at' => '2025-11-24 22:01:19'
        ],
        [
            'run_date' => '2025-11-20',
            'status' => 'Programado',
            'zone_id' => 1,
            'shift_id' => 2,
            'vehicle_id' => 1,
            'group_id' => 5,
            'notes' => null,
            'created_at' => '2025-11-24 21:08:50',
            'updated_at' => '2025-11-24 22:01:19'
        ],
        [
            'run_date' => '2025-11-24',
            'status' => 'Programado',
            'zone_id' => 1,
            'shift_id' => 2,
            'vehicle_id' => 1,
            'group_id' => 5,
            'notes' => null,
            'created_at' => '2025-11-24 21:08:50',
            'updated_at' => '2025-11-24 22:01:19'
        ],
        [
            'run_date' => '2025-11-26',
            'status' => 'Programado',
            'zone_id' => 1,
            'shift_id' => 2,
            'vehicle_id' => 1,
            'group_id' => 5,
            'notes' => null,
            'created_at' => '2025-11-24 21:08:50',
            'updated_at' => '2025-11-24 22:01:19'
        ],
        [
            'run_date' => '2025-11-27',
            'status' => 'Programado',
            'zone_id' => 1,
            'shift_id' => 2,
            'vehicle_id' => 1,
            'group_id' => 5,
            'notes' => null,
            'created_at' => '2025-11-24 21:08:50',
            'updated_at' => '2025-11-24 22:01:19'
        ],
        ];

        foreach ($records as $record) {
            DB::table('runs')->insert($record);
        }
    }
}
