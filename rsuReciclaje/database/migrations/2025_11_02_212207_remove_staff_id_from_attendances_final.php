<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        // Eliminar staff_id si aún existe
        if (Schema::hasColumn('attendances', 'staff_id')) {
            try {
                // Eliminar FK de staff_id si existe
                $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'attendances' AND COLUMN_NAME = 'staff_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
                if (!empty($foreignKeys)) {
                    $fk = $foreignKeys[0]->CONSTRAINT_NAME;
                    DB::statement("ALTER TABLE `attendances` DROP FOREIGN KEY `{$fk}`");
                }
            } catch (\Exception $e) {
                // Ignorar
            }
            
            // Eliminar índice único si existe
            try {
                DB::statement("ALTER TABLE `attendances` DROP INDEX IF EXISTS `attendances_staff_id_date_unique`");
            } catch (\Exception $e) {
                // Ignorar
            }
            
            // Eliminar columna
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropColumn('staff_id');
            });
        }
        
        // Asegurar que employee_id existe y tiene FK
        if (!Schema::hasColumn('attendances', 'employee_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->unsignedBigInteger('employee_id')->after('id');
            });
        }
        
        // Verificar y crear FK si no existe
        try {
            $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'attendances' AND COLUMN_NAME = 'employee_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
            if (empty($foreignKeys)) {
                Schema::table('attendances', function (Blueprint $table) {
                    $table->foreign('employee_id')->references('id')->on('employees')->onUpdate('cascade')->onDelete('restrict');
                });
            }
        } catch (\Exception $e) {
            // Ignorar si ya existe
        }
    }

    public function down(): void {
        // No revertir
    }
};
