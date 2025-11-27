<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'employee_id')) {
                $table->unsignedBigInteger('employee_id')->after('id');
            }
        });

        if (Schema::hasColumn('attendances', 'staff_id')) {
            // Migrar datos si existiera staff_id
            try {
                DB::statement('UPDATE attendances SET employee_id = staff_id WHERE employee_id IS NULL');
            } catch (\Exception $e) {
                // Ignorar si no hay datos o ya fue migrado
            }
            try {
                Schema::table('attendances', function (Blueprint $table) {
                    $table->dropColumn('staff_id');
                });
            } catch (\Exception $e) {
                // Ignorar si ya fue eliminada
            }
        }

        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'time')) {
                $table->dropColumn('time');
            }
            if (!Schema::hasColumn('attendances', 'date')) {
                $table->date('date')->index()->after('employee_id');
            } else {
                $table->date('date')->change();
            }
            if (!Schema::hasColumn('attendances', 'check_in')) {
                $table->time('check_in')->nullable()->after('date');
            }
            if (!Schema::hasColumn('attendances', 'check_out')) {
                $table->time('check_out')->nullable()->after('check_in');
            }
            if (!Schema::hasColumn('attendances', 'present')) {
                $table->boolean('present')->default(true)->after('check_out');
            }
            if (!Schema::hasColumn('attendances', 'method')) {
                $table->string('method', 20)->default('Manual')->after('present');
            }
            if (!Schema::hasColumn('attendances', 'notes')) {
                $table->string('notes', 255)->nullable()->after('method');
            }
        });

        Schema::table('attendances', function (Blueprint $table) {
            // FK segura
            try {
                $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'attendances' AND COLUMN_NAME = 'employee_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
                if (empty($foreignKeys)) {
                    $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
                }
            } catch (\Exception $e) {
                // Ignorar si ya existe
            }
        });
    }

    public function down(): void {
        // No es necesario revertir completamente; dejar vacío
    }
};
