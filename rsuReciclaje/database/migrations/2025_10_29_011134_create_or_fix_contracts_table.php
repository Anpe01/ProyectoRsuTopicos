<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('contracts', function (Blueprint $t) {
            // Cambiar staff_id a employee_id
            if (Schema::hasColumn('contracts','staff_id') && !Schema::hasColumn('contracts','employee_id')) {
                $t->renameColumn('staff_id', 'employee_id');
            }
            
            // Cambiar type de enum a string
            if (Schema::hasColumn('contracts','type')) {
                $t->string('type', 30)->change();
            }
            
            // Cambiar end_date a nullable
            if (Schema::hasColumn('contracts','end_date')) {
                $t->date('end_date')->nullable()->change();
            }
            
            // Agregar columnas nuevas
            if (!Schema::hasColumn('contracts','salary')) {
                $t->decimal('salary', 10, 2)->default(0)->after('end_date');
            }
            
            if (!Schema::hasColumn('contracts','department_id')) {
                $t->foreignId('department_id')->after('salary')->constrained('departments')->cascadeOnUpdate()->restrictOnDelete();
            }
            
            // Verificar y agregar probation_months solo si no existe
            // Usar try-catch para evitar error si la columna ya existe
            if (!Schema::hasColumn('contracts','probation_months')) {
                try {
                    $t->unsignedTinyInteger('probation_months')->default(0)->after('department_id');
                } catch (\Exception $e) {
                    // Si la columna ya existe o hay otro error, continuar
                }
            }
            
            if (!Schema::hasColumn('contracts','active')) {
                $t->boolean('active')->default(true)->after('probation_months');
            }
            
            if (!Schema::hasColumn('contracts','termination_reason')) {
                $t->text('termination_reason')->nullable()->after('active');
            }
        });
    }
    
    public function down(): void {
        Schema::table('contracts', function (Blueprint $t) {
            if (Schema::hasColumn('contracts','employee_id') && Schema::hasColumn('contracts','staff_id') === false) {
                $t->renameColumn('employee_id', 'staff_id');
            }
            
            if (Schema::hasColumn('contracts','salary')) $t->dropColumn('salary');
            if (Schema::hasColumn('contracts','department_id')) {
                $t->dropForeign(['department_id']);
                $t->dropColumn('department_id');
            }
            if (Schema::hasColumn('contracts','probation_months')) $t->dropColumn('probation_months');
            if (Schema::hasColumn('contracts','active')) $t->dropColumn('active');
            if (Schema::hasColumn('contracts','termination_reason')) $t->dropColumn('termination_reason');
        });
    }
};
