<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar foreign keys existentes primero
        if (Schema::hasTable('runs')) {
            // Obtener foreign keys existentes
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'runs' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            Schema::table('runs', function (Blueprint $table) use ($foreignKeys) {
                foreach ($foreignKeys as $fk) {
                    try {
                        $table->dropForeign($fk->CONSTRAINT_NAME);
                    } catch (\Exception $e) {
                        // Ignorar si no existe
                    }
                }
            });
            
            // Eliminar índices únicos si existen
            try {
                Schema::table('runs', function (Blueprint $table) {
                    $table->dropUnique(['program_id', 'run_date']);
                });
            } catch (\Exception $e) {
                // Ignorar si no existe
            }
            
            // Eliminar columnas antiguas si existen
            Schema::table('runs', function (Blueprint $table) {
                if (Schema::hasColumn('runs', 'program_id')) {
                    $table->dropColumn('program_id');
                }
                if (Schema::hasColumn('runs', 'start_time')) {
                    $table->dropColumn('start_time');
                }
                if (Schema::hasColumn('runs', 'end_time')) {
                    $table->dropColumn('end_time');
                }
            });
            
            // Agregar nuevas columnas si no existen
            Schema::table('runs', function (Blueprint $table) {
                if (!Schema::hasColumn('runs', 'zone_id')) {
                    $table->foreignId('zone_id')->nullable()->after('status');
                }
                if (!Schema::hasColumn('runs', 'shift_id')) {
                    $table->foreignId('shift_id')->nullable()->after('zone_id');
                }
                if (!Schema::hasColumn('runs', 'vehicle_id')) {
                    $table->foreignId('vehicle_id')->nullable()->after('shift_id');
                }
                if (!Schema::hasColumn('runs', 'group_id')) {
                    $table->foreignId('group_id')->nullable()->after('vehicle_id');
                }
            });
            
            // Agregar foreign keys
            Schema::table('runs', function (Blueprint $table) {
                $table->foreign('zone_id')->references('id')->on('zones')->nullOnDelete();
                $table->foreign('shift_id')->references('id')->on('shifts')->nullOnDelete();
                $table->foreign('vehicle_id')->references('id')->on('vehicles')->nullOnDelete();
                $table->foreign('group_id')->references('id')->on('personnel_groups')->nullOnDelete();
            });
            
            // Actualizar status por defecto si es necesario
            DB::statement("ALTER TABLE runs MODIFY COLUMN status VARCHAR(20) DEFAULT 'Programado'");
            
            // Agregar índices únicos
            Schema::table('runs', function (Blueprint $table) {
                try {
                    $table->unique(['group_id', 'run_date'], 'runs_group_date_unique');
                } catch (\Exception $e) {
                    // Ignorar si ya existe
                }
                try {
                    $table->unique(['vehicle_id', 'run_date'], 'runs_vehicle_date_unique');
                } catch (\Exception $e) {
                    // Ignorar si ya existe
                }
            });
        } else {
            // Si la tabla no existe, crearla desde cero
            Schema::create('runs', function (Blueprint $table) {
                $table->id();
                $table->date('run_date')->index();
                $table->string('status', 20)->default('Programado')->index();
                $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
                $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
                $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
                $table->foreignId('group_id')->nullable()->constrained('personnel_groups')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
                
                // Evita doble programación del mismo grupo el mismo día
                $table->unique(['group_id','run_date']);
                
                // Si programas por vehículo, evita duplicidad
                $table->unique(['vehicle_id','run_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('runs');
    }
};
