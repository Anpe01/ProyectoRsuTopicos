<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
        [
            'name' => 'noche',
            'start_time' => '20:44:00',
            'end_time' => '10:44:00',
            'description' => 'asdad',
            'active' => 1,
            'created_at' => '2025-11-18 22:42:00',
            'updated_at' => '2025-11-18 22:42:00'
        ],
        [
            'name' => 'dia',
            'start_time' => '07:00:00',
            'end_time' => '17:30:00',
            'description' => 'dia',
            'active' => 1,
            'created_at' => '2025-11-18 22:42:27',
            'updated_at' => '2025-11-18 22:42:27'
        ],
        [
            'name' => 'tarde',
            'start_time' => '13:00:00',
            'end_time' => '20:00:00',
            'description' => 'rtarde',
            'active' => 1,
            'created_at' => '2025-11-20 23:30:20',
            'updated_at' => '2025-11-20 23:30:20'
        ],
        [
            'name' => 'madrugrada',
            'start_time' => '17:20:00',
            'end_time' => '22:10:00',
            'description' => 'asad',
            'active' => 0,
            'created_at' => '2025-11-24 21:15:33',
            'updated_at' => '2025-11-24 21:15:33'
        ],
        ];

        foreach ($records as $record) {
            DB::table('shifts')->insert($record);
        }
    }
}
