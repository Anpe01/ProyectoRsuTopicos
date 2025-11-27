<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Si no existe la nueva, la creamos (TIME para HH:MM:SS, nullable)
            if (!Schema::hasColumn('attendances','work_time')) {
                $table->time('work_time')->nullable()->after('notes');
            }

            // Si por algún motivo existe la vieja 'time', elimínala (evita conflictos)
            if (Schema::hasColumn('attendances','time')) {
                $table->dropColumn('time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances','work_time')) {
                $table->dropColumn('work_time');
            }
        });
    }
};
