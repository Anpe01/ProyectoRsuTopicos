<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChangeReason;

class ChangeReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reasons = [
            [
                'name' => 'Permiso por enfermedad',
                'description' => 'El personal requiere permiso debido a enfermedad',
                'active' => true,
                'is_predefined' => true,
            ],
            [
                'name' => 'Vacaciones',
                'description' => 'El personal está en período de vacaciones',
                'active' => true,
                'is_predefined' => true,
            ],
            [
                'name' => 'Permiso personal',
                'description' => 'Permiso personal solicitado por el empleado',
                'active' => true,
                'is_predefined' => true,
            ],
            [
                'name' => 'Falta de asistencia',
                'description' => 'El personal no se presentó a trabajar',
                'active' => true,
                'is_predefined' => true,
            ],
            [
                'name' => 'Cambio de turno',
                'description' => 'Cambio de turno programado',
                'active' => true,
                'is_predefined' => true,
            ],
            [
                'name' => 'Cambio de vehículo',
                'description' => 'Cambio de vehículo por mantenimiento o disponibilidad',
                'active' => true,
                'is_predefined' => true,
            ],
            [
                'name' => 'Reasignación de personal',
                'description' => 'Reasignación de personal por necesidades operativas',
                'active' => true,
                'is_predefined' => true,
            ],
            [
                'name' => 'Emergencia',
                'description' => 'Cambio debido a una emergencia',
                'active' => true,
                'is_predefined' => true,
            ],
            [
                'name' => 'Capacitación',
                'description' => 'El personal está en capacitación',
                'active' => true,
                'is_predefined' => true,
            ],
            [
                'name' => 'Otro',
                'description' => 'Otro motivo no especificado',
                'active' => true,
                'is_predefined' => true,
            ],
        ];

        foreach ($reasons as $reason) {
            ChangeReason::firstOrCreate(
                ['name' => $reason['name']],
                $reason
            );
        }
    }
}
