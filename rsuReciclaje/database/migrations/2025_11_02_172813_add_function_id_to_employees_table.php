<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Agrega la FK si no existe
            if (!Schema::hasColumn('employees','function_id')) {
                $table->foreignId('function_id')
                      ->after('dni')
                      ->nullable(false)
                      ->constrained('functions')
                      ->cascadeOnUpdate()
                      ->restrictOnDelete();
            }
        });

        // Si veníamos usando otra columna (ej. employeetype_id), copiar valores y eliminarla
        if (Schema::hasColumn('employees','employeetype_id')) {
            DB::statement('UPDATE employees SET function_id = employeetype_id WHERE function_id IS NULL');

            Schema::table('employees', function (Blueprint $table) {
                // Elimina FK y columna antigua de forma segura
                if (method_exists($table, 'dropConstrainedForeignId')) {
                    $table->dropConstrainedForeignId('employeetype_id');
                } else {
                    $table->dropForeign(['employeetype_id']);
                    $table->dropColumn('employeetype_id');
                }
            });
        }
    }

    public function down(): void
    {
        // (Rollback mínimo)
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees','function_id')) {
                if (method_exists($table, 'dropConstrainedForeignId')) {
                    $table->dropConstrainedForeignId('function_id');
                } else {
                    $table->dropForeign(['function_id']);
                    $table->dropColumn('function_id');
                }
            }
        });
    }
};
