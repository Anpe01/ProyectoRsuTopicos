<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Mapear tipos antiguos a los nuevos 3 tipos
        $typeMapping = [
            // Tipos antiguos -> nuevo tipo
            'Indefinido' => 'a tiempo completo',
            'Permanente' => 'a tiempo completo',
            'nombrado' => 'nombrado',  // Ya es correcto
            'Temporal' => 'temporal',  // Ya es correcto
            'temporal' => 'temporal',  // Ya es correcto
            'Parcial' => 'a tiempo completo',
            'Prácticas' => 'temporal',
            'Locación' => 'temporal',
            'Otro' => 'temporal',
            'eventual' => 'temporal',
        ];

        // Actualizar valores existentes en la base de datos
        foreach ($typeMapping as $oldType => $newType) {
            DB::table('contracts')
                ->where('type', $oldType)
                ->update(['type' => $newType]);
        }

        // Asegurar que el campo type solo acepte los 3 nuevos valores
        // Nota: En MySQL/SQLite no podemos cambiar un string a enum directamente,
        // pero validaremos en el código. Si usas PostgreSQL, se puede hacer un CHECK constraint
    }

    public function down(): void
    {
        // No revertimos para no perder la conversión de datos
    }
};
