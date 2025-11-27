<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::table('zones', function (Blueprint $t) {
            // Ubigeo (si no existen, agregar)
            if (!Schema::hasColumn('zones','department_id')) {
                $t->unsignedBigInteger('department_id')->nullable()->after('name')->index();
            }
            if (!Schema::hasColumn('zones','province_id')) {
                $t->unsignedBigInteger('province_id')->nullable()->after('department_id')->index();
            }
            // district_id ya existe, solo verificar
            if (!Schema::hasColumn('zones','district_id')) {
                $t->unsignedBigInteger('district_id')->nullable()->after('province_id')->index();
            }

            // GeoJSON y campos nuevos
            if (!Schema::hasColumn('zones','polygon')) {
                $t->json('polygon')->nullable()->after('district_id'); // GeoJSON
            }
            if (!Schema::hasColumn('zones','area_km2')) {
                $t->decimal('area_km2',10,3)->default(0)->after('polygon');
            }
            if (!Schema::hasColumn('zones','avg_waste_tb')) {
                $t->decimal('avg_waste_tb',10,2)->nullable()->after('area_km2');
            }
            if (!Schema::hasColumn('zones','active')) {
                $t->boolean('active')->default(true)->after('avg_waste_tb');
            }
            
            // Descripción ya existe, solo verificar
            if (!Schema::hasColumn('zones','description')) {
                $t->text('description')->nullable();
            }
        });

        // Actualizar área antigua si existe (después de crear las columnas)
        if (Schema::hasColumn('zones','area') && Schema::hasColumn('zones','area_km2')) {
            DB::statement('UPDATE zones SET area_km2 = COALESCE(area, 0) WHERE area_km2 = 0 OR area_km2 IS NULL');
        }
    }

    public function down(): void {
        Schema::table('zones', function (Blueprint $t) {
            $cols = ['department_id','province_id','polygon','area_km2','avg_waste_tb','active'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('zones', $col)) {
                    $t->dropColumn($col);
                }
            }
            // No eliminamos district_id porque ya existía
        });
    }
};
