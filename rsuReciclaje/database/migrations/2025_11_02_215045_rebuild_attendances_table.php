<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $t) {
                $t->id();
                $t->foreignId('employee_id')->constrained()->cascadeOnDelete();
                $t->date('attendance_date');
                $t->unsignedTinyInteger('period');  // 1=Entrada, 2=Salida
                $t->unsignedTinyInteger('status');   // 1=Presente, 0=Falta, 2=Justificado(opcional)
                $t->text('notes')->nullable();
                $t->timestamps();
            });
            return;
        }

        // Asegurar employee_id con FK
        if (!Schema::hasColumn('attendances', 'employee_id')) {
            Schema::table('attendances', function (Blueprint $t) {
                $t->unsignedBigInteger('employee_id')->after('id')->nullable();
            });
            
            // Migrar de staff_id si existe
            if (Schema::hasColumn('attendances', 'staff_id')) {
                DB::statement('UPDATE attendances SET employee_id = staff_id WHERE employee_id IS NULL');
            }
            
            Schema::table('attendances', function (Blueprint $t) {
                $t->unsignedBigInteger('employee_id')->nullable(false)->change();
            });
        }

        // Añadir nuevas columnas
        Schema::table('attendances', function (Blueprint $t) {
            if (!Schema::hasColumn('attendances', 'attendance_date')) {
                $t->date('attendance_date')->after('employee_id')->nullable();
            }
            if (!Schema::hasColumn('attendances', 'period')) {
                $t->unsignedTinyInteger('period')->after('attendance_date')->default(1);
            }
            if (!Schema::hasColumn('attendances', 'status')) {
                $t->unsignedTinyInteger('status')->after('period')->default(1);
            }
            if (!Schema::hasColumn('attendances', 'notes')) {
                $t->text('notes')->nullable()->after('status');
            }
        });

        // Migración de datos
        if (Schema::hasColumn('attendances','attendance_date')) {
            DB::statement("
                UPDATE attendances
                SET attendance_date = COALESCE(
                    attendance_date,
                    CASE 
                        WHEN date IS NOT NULL THEN date
                        ELSE DATE(created_at)
                    END
                )
                WHERE attendance_date IS NULL
            ");
        }

        if (Schema::hasColumn('attendances','period')) {
            if (Schema::hasColumn('attendances','check_in')) {
                DB::statement("UPDATE attendances SET period = 1 WHERE period IS NULL AND check_in IS NOT NULL");
            }
            if (Schema::hasColumn('attendances','check_out')) {
                DB::statement("UPDATE attendances SET period = 2 WHERE period IS NULL AND check_out IS NOT NULL AND check_in IS NULL");
            }
            // Si tiene ambos, priorizar entrada
            if (Schema::hasColumn('attendances','check_in') && Schema::hasColumn('attendances','check_out')) {
                DB::statement("UPDATE attendances SET period = 1 WHERE period IS NULL AND check_in IS NOT NULL");
            }
        }

        if (Schema::hasColumn('attendances','status')) {
            if (Schema::hasColumn('attendances','present')) {
                DB::statement("UPDATE attendances SET status = CASE WHEN present=1 THEN 1 ELSE 0 END WHERE status IS NULL");
            } else {
                DB::statement("UPDATE attendances SET status = 1 WHERE status IS NULL");
            }
        }

        // Asegurar FK de employee_id
        try {
            $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'attendances' AND COLUMN_NAME = 'employee_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
            if (empty($foreignKeys)) {
                Schema::table('attendances', function (Blueprint $t) {
                    $t->foreign('employee_id')->references('id')->on('employees')->onUpdate('cascade')->onDelete('cascade');
                });
            }
        } catch (\Exception $e) {
            // Ignorar si ya existe
        }

        // Eliminar columnas antiguas
        Schema::table('attendances', function (Blueprint $t) {
            foreach (['date','check_in','check_out','present','method','work_time','time','staff_id'] as $old) {
                if (Schema::hasColumn('attendances',$old)) {
                    try {
                        $t->dropColumn($old);
                    } catch (\Exception $e) {
                        // Ignorar si hay problemas
                    }
                }
            }
        });

        // Hacer attendance_date NOT NULL después de migrar
        try {
            DB::statement("UPDATE attendances SET attendance_date = DATE(created_at) WHERE attendance_date IS NULL");
            Schema::table('attendances', function (Blueprint $t) {
                $t->date('attendance_date')->nullable(false)->change();
            });
        } catch (\Exception $e) {
            // Si falla, al menos intentamos
        }
    }

    public function down(): void
    {
        // No revertir intencionalmente
    }
};
