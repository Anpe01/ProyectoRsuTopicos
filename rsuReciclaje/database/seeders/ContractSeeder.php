<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
        [
            'employee_id' => 1,
            'type' => 'temporal',
            'start_date' => '2025-10-01',
            'end_date' => '2025-12-13',
            'salary' => 1500.00,
            'department_id' => 14,
            'probation_months' => 2,
            'active' => 1,
            'termination_reason' => null,
            'status' => 'vigente',
            'notes' => null,
            'created_at' => '2025-11-18 22:37:40',
            'updated_at' => '2025-11-18 22:37:40'
        ],
        [
            'employee_id' => 3,
            'type' => 'a tiempo completo',
            'start_date' => '2025-09-01',
            'end_date' => '2026-02-28',
            'salary' => 1800.00,
            'department_id' => 14,
            'probation_months' => 12,
            'active' => 1,
            'termination_reason' => 'asdadadad',
            'status' => 'vigente',
            'notes' => null,
            'created_at' => '2025-11-20 23:05:49',
            'updated_at' => '2025-11-20 23:05:49'
        ],
        [
            'employee_id' => 2,
            'type' => 'nombrado',
            'start_date' => '2025-11-01',
            'end_date' => '2026-04-30',
            'salary' => 1300.00,
            'department_id' => 22,
            'probation_months' => 3,
            'active' => 1,
            'termination_reason' => null,
            'status' => 'vigente',
            'notes' => null,
            'created_at' => '2025-11-21 00:06:14',
            'updated_at' => '2025-11-21 00:06:14'
        ],
        [
            'employee_id' => 5,
            'type' => 'a tiempo completo',
            'start_date' => '2025-11-01',
            'end_date' => '2026-03-31',
            'salary' => 1200.00,
            'department_id' => 2,
            'probation_months' => 5,
            'active' => 1,
            'termination_reason' => null,
            'status' => 'vigente',
            'notes' => null,
            'created_at' => '2025-11-24 21:23:44',
            'updated_at' => '2025-11-24 21:23:44'
        ],
        [
            'employee_id' => 6,
            'type' => 'nombrado',
            'start_date' => '2025-11-01',
            'end_date' => '2026-06-30',
            'salary' => 4000.00,
            'department_id' => 16,
            'probation_months' => 3,
            'active' => 1,
            'termination_reason' => null,
            'status' => 'vigente',
            'notes' => null,
            'created_at' => '2025-11-24 21:24:16',
            'updated_at' => '2025-11-24 21:26:53'
        ],
        [
            'employee_id' => 4,
            'type' => 'temporal',
            'start_date' => '2025-11-01',
            'end_date' => '2026-03-31',
            'salary' => 1500.00,
            'department_id' => 24,
            'probation_months' => 4,
            'active' => 1,
            'termination_reason' => null,
            'status' => 'vigente',
            'notes' => null,
            'created_at' => '2025-11-24 21:24:42',
            'updated_at' => '2025-11-24 21:24:42'
        ],
        [
            'employee_id' => 7,
            'type' => 'a tiempo completo',
            'start_date' => '2025-11-01',
            'end_date' => '2026-08-31',
            'salary' => 1200.00,
            'department_id' => 25,
            'probation_months' => 1,
            'active' => 1,
            'termination_reason' => null,
            'status' => 'vigente',
            'notes' => null,
            'created_at' => '2025-11-24 21:29:23',
            'updated_at' => '2025-11-24 21:29:23'
        ],
        ];

        foreach ($records as $record) {
            DB::table('contracts')->insert($record);
        }
    }
}
