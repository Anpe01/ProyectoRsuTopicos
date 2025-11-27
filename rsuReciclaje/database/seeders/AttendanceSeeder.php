<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
        [
            'notes' => 'aaa',
            'created_at' => '2025-11-18 22:19:49',
            'updated_at' => '2025-11-18 22:19:49',
            'employee_id' => 1,
            'attendance_date' => '2025-11-18',
            'period' => 1,
            'status' => 1
        ],
        [
            'notes' => null,
            'created_at' => '2025-11-25 17:51:30',
            'updated_at' => '2025-11-25 17:51:30',
            'employee_id' => 3,
            'attendance_date' => '2025-11-01',
            'period' => 1,
            'status' => 1
        ],
        [
            'notes' => null,
            'created_at' => '2025-11-25 17:51:38',
            'updated_at' => '2025-11-25 17:51:38',
            'employee_id' => 2,
            'attendance_date' => '2025-11-01',
            'period' => 1,
            'status' => 1
        ],
        [
            'notes' => null,
            'created_at' => '2025-11-25 17:54:45',
            'updated_at' => '2025-11-25 17:54:45',
            'employee_id' => 3,
            'attendance_date' => '2025-11-05',
            'period' => 1,
            'status' => 1
        ],
        [
            'notes' => null,
            'created_at' => '2025-11-25 17:54:54',
            'updated_at' => '2025-11-25 17:54:54',
            'employee_id' => 2,
            'attendance_date' => '2025-11-05',
            'period' => 1,
            'status' => 1
        ],
        [
            'notes' => null,
            'created_at' => '2025-11-25 17:55:27',
            'updated_at' => '2025-11-25 17:55:27',
            'employee_id' => 1,
            'attendance_date' => '2025-11-05',
            'period' => 1,
            'status' => 1
        ],
        ];

        foreach ($records as $record) {
            DB::table('attendances')->insert($record);
        }
    }
}
