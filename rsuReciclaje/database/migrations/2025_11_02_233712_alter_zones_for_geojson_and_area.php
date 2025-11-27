<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('zones', function (Blueprint $t) {
            if (!Schema::hasColumn('zones','department_id')) {
                $t->unsignedBigInteger('department_id')->nullable()->index();
            }
            if (!Schema::hasColumn('zones','polygon')) {
                $t->json('polygon')->nullable();     // GeoJSON
            }
            if (!Schema::hasColumn('zones','area_km2')) {
                $t->decimal('area_km2',10,3)->default(0);
            }
            if (!Schema::hasColumn('zones','avg_waste_tb')) {
                $t->decimal('avg_waste_tb',10,2)->nullable();
            }
            if (!Schema::hasColumn('zones','active')) {
                $t->boolean('active')->default(true);
            }
            if (!Schema::hasColumn('zones','description')) {
                $t->text('description')->nullable();
            }
        });
    }

    public function down(): void {
        Schema::table('zones', function (Blueprint $t) {
            $cols = ['department_id','polygon','area_km2','avg_waste_tb','active','description'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('zones',$c)) {
                    $t->dropColumn($c);
                }
            }
        });
    }
};
