<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vacations', function (Blueprint $table) {
            if (Schema::hasColumn('vacations', 'staff_id') && !Schema::hasColumn('vacations', 'employee_id')) {
                $table->renameColumn('staff_id', 'employee_id');
            }
            if (!Schema::hasColumn('vacations','days')) {
                $table->unsignedSmallInteger('days')->nullable()->after('end_date');
            }
            if (!Schema::hasColumn('vacations','notes')) {
                $table->string('notes', 500)->nullable()->after('days');
            }
        });

        Schema::table('contracts', function (Blueprint $table) {
            // Evita futuros errores de consultas con staff_id
            if (Schema::hasColumn('contracts', 'staff_id') && !Schema::hasColumn('contracts', 'employee_id')) {
                $table->renameColumn('staff_id', 'employee_id');
            }
        });
    }

    public function down(): void
    {
        // No revertimos para no romper datos
    }
};
