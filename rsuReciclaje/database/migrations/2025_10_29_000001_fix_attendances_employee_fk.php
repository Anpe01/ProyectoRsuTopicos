<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) Crear employee_id si no existe (nullable temporalmente)
        if (!Schema::hasColumn('attendances', 'employee_id')) {
            Schema::table('attendances', function (Blueprint $t) {
                // Insertar sin posición específica para evitar depender de columnas inexistentes
                $t->unsignedBigInteger('employee_id')->nullable();
            });
        }

        // 2) Copiar valores de staff_id -> employee_id si aplica
        if (Schema::hasColumn('attendances', 'staff_id')) {
            DB::statement('UPDATE attendances SET employee_id = staff_id WHERE employee_id IS NULL AND staff_id IS NOT NULL');
        }

        // 3) Soltar FK de staff_id si existe y eliminar columna staff_id
        if (Schema::hasColumn('attendances', 'staff_id')) {
            // Intentar obtener el nombre real de la FK y soltarla
            try {
                $rows = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'attendances' AND COLUMN_NAME = 'staff_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
                if (!empty($rows)) {
                    $fk = $rows[0]->CONSTRAINT_NAME;
                    DB::statement("ALTER TABLE `attendances` DROP FOREIGN KEY `{$fk}`");
                }
            } catch (\Throwable $e) { /* ignorar */ }

            // Dropear columna sólo si aún existe
            if (Schema::hasColumn('attendances', 'staff_id')) {
                try {
                    Schema::table('attendances', function (Blueprint $t) {
                        $t->dropColumn('staff_id');
                    });
                } catch (\Throwable $e) { /* ignorar */ }
            }
        }

        // 4) Añadir FK correcta a employees y hacer NOT NULL
        // 4) Añadir FK correcta a employees y hacer NOT NULL (drop seguro por nombre real)
        // Intentar soltar una FK existente sobre employee_id si la hay
        try {
            $rows = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'attendances' AND COLUMN_NAME = 'employee_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
            if (!empty($rows)) {
                $fk = $rows[0]->CONSTRAINT_NAME;
                DB::statement("ALTER TABLE `attendances` DROP FOREIGN KEY `{$fk}`");
            }
        } catch (\Throwable $e) { /* ignorar */ }

        Schema::table('attendances', function (Blueprint $t) {
            // asegurar NOT NULL
            try { $t->unsignedBigInteger('employee_id')->nullable(false)->change(); } catch (\Throwable $e) {}

            // FK a employees
            try {
                $t->foreign('employee_id')
                  ->references('id')->on('employees')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
            } catch (\Throwable $e) { /* ignorar si ya existe */ }
        });
    }

    public function down(): void
    {
        // No restauramos staff_id; como mínimo soltamos FK de employee_id
        try {
            Schema::table('attendances', function (Blueprint $t) {
                $t->dropForeign(['employee_id']);
            });
        } catch (\Throwable $e) {}
    }
};


