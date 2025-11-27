<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
  public function up(): void {
    if (!Schema::hasTable('attendances')) {
      Schema::create('attendances', function (Blueprint $t) {
        $t->id();
        $t->date('date');
        $t->time('check_in');
        $t->time('check_out')->nullable();
        $t->boolean('present')->default(true);
        $t->enum('method', ['Manual','PIN','QR','Geo'])->default('Manual');
        $t->text('notes')->nullable();
        $t->foreignId('employee_id')->constrained('employees')->cascadeOnUpdate()->restrictOnDelete();
        $t->timestamps();
      });
    } else {
      // Cambiar staff_id a employee_id si existe staff_id y no existe employee_id
      if (Schema::hasColumn('attendances','staff_id') && !Schema::hasColumn('attendances','employee_id')) {
        // Paso 1: Crear la nueva columna
        Schema::table('attendances', function (Blueprint $t) {
          $t->unsignedBigInteger('employee_id')->nullable()->after('id');
        });
        // Paso 2: Migrar datos
        DB::statement('UPDATE attendances SET employee_id = staff_id');
        // Paso 3: Hacer NOT NULL y agregar foreign key
        Schema::table('attendances', function (Blueprint $t) {
          $t->unsignedBigInteger('employee_id')->nullable(false)->change();
          $t->foreign('employee_id')->references('id')->on('employees')->onUpdate('cascade')->onDelete('restrict');
        });
        // Paso 4: Eliminar foreign key vieja y columna
        Schema::table('attendances', function (Blueprint $t) {
          // Obtener nombre real de la foreign key
          $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'attendances' AND COLUMN_NAME = 'staff_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
          if (!empty($foreignKeys)) {
            $fkName = $foreignKeys[0]->CONSTRAINT_NAME;
            $t->dropForeign([$fkName]);
          }
          $t->dropColumn('staff_id');
        });
      } elseif (!Schema::hasColumn('attendances','employee_id')) {
        Schema::table('attendances', function (Blueprint $t) {
          $t->foreignId('employee_id')->after('id')->constrained('employees')->cascadeOnUpdate()->restrictOnDelete();
        });
      }
      
      Schema::table('attendances', function (Blueprint $t) {

        // Renombrar time_in a check_in
        if (Schema::hasColumn('attendances','time_in') && !Schema::hasColumn('attendances','check_in')) {
          $t->renameColumn('time_in', 'check_in');
        } elseif (!Schema::hasColumn('attendances','check_in')) {
          $t->time('check_in')->after('date');
        }

        // Renombrar time_out a check_out
        if (Schema::hasColumn('attendances','time_out') && !Schema::hasColumn('attendances','check_out')) {
          $t->renameColumn('time_out', 'check_out');
        } elseif (!Schema::hasColumn('attendances','check_out')) {
          $t->time('check_out')->nullable()->after('check_in');
        }

        // Renombrar was_present a present
        if (Schema::hasColumn('attendances','was_present') && !Schema::hasColumn('attendances','present')) {
          $t->renameColumn('was_present', 'present');
        } elseif (!Schema::hasColumn('attendances','present')) {
          $t->boolean('present')->default(true)->after('check_out');
        }

        // Actualizar method a enum o string según el driver
        if (Schema::hasColumn('attendances','method')) {
          // Primero cambiar los valores antiguos a Manual
          DB::table('attendances')
            ->where(function($q) {
              $q->whereNotIn('method', ['Manual','PIN','QR','Geo'])
                ->orWhereNull('method');
            })
            ->update(['method' => 'Manual']);
          // En SQLite usar string, en MySQL usar enum
          if (DB::getDriverName() === 'sqlite') {
            // SQLite no soporta enum, usar string con check
            $t->string('method', 10)->default('Manual')->change();
          } else {
            $t->enum('method', ['Manual','PIN','QR','Geo'])->default('Manual')->change();
          }
        } else {
          if (DB::getDriverName() === 'sqlite') {
            $t->string('method', 10)->default('Manual')->after('present');
          } else {
            $t->enum('method', ['Manual','PIN','QR','Geo'])->default('Manual')->after('present');
          }
        }

        // Agregar notes si no existe
        if (!Schema::hasColumn('attendances','notes')) {
          $t->text('notes')->nullable()->after('method');
        }

        // Asegurar que date existe
        if (!Schema::hasColumn('attendances','date')) {
          $t->date('date')->after('id');
        }
      });
    }
  }

  public function down(): void {
    // opcional: Schema::dropIfExists('attendances');
  }
};
