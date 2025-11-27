<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RunChangeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
        [
            'run_id' => 59,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 234-UWU',
            'new_value' => 'Vehículo: 123-ja2',
            'notes' => 'no hay mas carros',
            'created_at' => '2025-11-24 22:00:52',
            'updated_at' => '2025-11-24 22:00:52'
        ],
        [
            'run_id' => 60,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 234-UWU',
            'new_value' => 'Vehículo: 123-ja2',
            'notes' => 'no hay mas carros',
            'created_at' => '2025-11-24 22:00:52',
            'updated_at' => '2025-11-24 22:00:52'
        ],
        [
            'run_id' => 61,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 234-UWU',
            'new_value' => 'Vehículo: 123-ja2',
            'notes' => 'no hay mas carros',
            'created_at' => '2025-11-24 22:00:52',
            'updated_at' => '2025-11-24 22:00:52'
        ],
        [
            'run_id' => 62,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 234-UWU',
            'new_value' => 'Vehículo: 123-ja2',
            'notes' => 'no hay mas carros',
            'created_at' => '2025-11-24 22:00:52',
            'updated_at' => '2025-11-24 22:00:52'
        ],
        [
            'run_id' => 63,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 234-UWU',
            'new_value' => 'Vehículo: 123-ja2',
            'notes' => 'no hay mas carros',
            'created_at' => '2025-11-24 22:00:52',
            'updated_at' => '2025-11-24 22:00:52'
        ],
        [
            'run_id' => 64,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 234-UWU',
            'new_value' => 'Vehículo: 123-ja2',
            'notes' => 'no hay mas carros',
            'created_at' => '2025-11-24 22:00:52',
            'updated_at' => '2025-11-24 22:00:52'
        ],
        [
            'run_id' => 65,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 234-UWU',
            'new_value' => 'Vehículo: 123-ja2',
            'notes' => 'no hay mas carros',
            'created_at' => '2025-11-24 22:00:52',
            'updated_at' => '2025-11-24 22:00:52'
        ],
        [
            'run_id' => 66,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 234-UWU',
            'new_value' => 'Vehículo: 123-ja2',
            'notes' => 'no hay mas carros',
            'created_at' => '2025-11-24 22:00:52',
            'updated_at' => '2025-11-24 22:00:52'
        ],
        [
            'run_id' => 67,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 234-UWU',
            'new_value' => 'Vehículo: 123-ja2',
            'notes' => 'no hay mas carros',
            'created_at' => '2025-11-24 22:00:52',
            'updated_at' => '2025-11-24 22:00:52'
        ],
        [
            'run_id' => 68,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 234-UWU',
            'new_value' => 'Vehículo: 123-ja2',
            'notes' => 'no hay mas carros',
            'created_at' => '2025-11-24 22:00:52',
            'updated_at' => '2025-11-24 22:00:52'
        ],
        [
            'run_id' => 69,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 234-UWU',
            'new_value' => 'Vehículo: 123-ja2',
            'notes' => 'no hay mas carros',
            'created_at' => '2025-11-24 22:00:52',
            'updated_at' => '2025-11-24 22:00:52'
        ],
        [
            'run_id' => 59,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 123-ja2',
            'new_value' => 'Vehículo: 234-UWU',
            'notes' => 'awea',
            'created_at' => '2025-11-24 22:01:18',
            'updated_at' => '2025-11-24 22:01:18'
        ],
        [
            'run_id' => 60,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 123-ja2',
            'new_value' => 'Vehículo: 234-UWU',
            'notes' => 'awea',
            'created_at' => '2025-11-24 22:01:19',
            'updated_at' => '2025-11-24 22:01:19'
        ],
        [
            'run_id' => 61,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 123-ja2',
            'new_value' => 'Vehículo: 234-UWU',
            'notes' => 'awea',
            'created_at' => '2025-11-24 22:01:19',
            'updated_at' => '2025-11-24 22:01:19'
        ],
        [
            'run_id' => 62,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 123-ja2',
            'new_value' => 'Vehículo: 234-UWU',
            'notes' => 'awea',
            'created_at' => '2025-11-24 22:01:19',
            'updated_at' => '2025-11-24 22:01:19'
        ],
        [
            'run_id' => 63,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 123-ja2',
            'new_value' => 'Vehículo: 234-UWU',
            'notes' => 'awea',
            'created_at' => '2025-11-24 22:01:19',
            'updated_at' => '2025-11-24 22:01:19'
        ],
        [
            'run_id' => 64,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 123-ja2',
            'new_value' => 'Vehículo: 234-UWU',
            'notes' => 'awea',
            'created_at' => '2025-11-24 22:01:19',
            'updated_at' => '2025-11-24 22:01:19'
        ],
        [
            'run_id' => 65,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 123-ja2',
            'new_value' => 'Vehículo: 234-UWU',
            'notes' => 'awea',
            'created_at' => '2025-11-24 22:01:19',
            'updated_at' => '2025-11-24 22:01:19'
        ],
        [
            'run_id' => 66,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 123-ja2',
            'new_value' => 'Vehículo: 234-UWU',
            'notes' => 'awea',
            'created_at' => '2025-11-24 22:01:19',
            'updated_at' => '2025-11-24 22:01:19'
        ],
        [
            'run_id' => 67,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 123-ja2',
            'new_value' => 'Vehículo: 234-UWU',
            'notes' => 'awea',
            'created_at' => '2025-11-24 22:01:19',
            'updated_at' => '2025-11-24 22:01:19'
        ],
        [
            'run_id' => 68,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 123-ja2',
            'new_value' => 'Vehículo: 234-UWU',
            'notes' => 'awea',
            'created_at' => '2025-11-24 22:01:19',
            'updated_at' => '2025-11-24 22:01:19'
        ],
        [
            'run_id' => 69,
            'change_type' => 'vehiculo',
            'old_value' => 'Vehículo: 123-ja2',
            'new_value' => 'Vehículo: 234-UWU',
            'notes' => 'awea',
            'created_at' => '2025-11-24 22:01:19',
            'updated_at' => '2025-11-24 22:01:19'
        ],
        ];

        foreach ($records as $record) {
            DB::table('run_changes')->insert($record);
        }
    }
}
