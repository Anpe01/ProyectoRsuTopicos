<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            // Hacer district_id nullable si no lo es
            if (Schema::hasColumn('zones', 'district_id')) {
                $table->unsignedBigInteger('district_id')->nullable()->change();
            }
            
            // También hacer province_id nullable si existe
            if (Schema::hasColumn('zones', 'province_id')) {
                $table->unsignedBigInteger('province_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        // No revertimos para mantener compatibilidad
    }
};
