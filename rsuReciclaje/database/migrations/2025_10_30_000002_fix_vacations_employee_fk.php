<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('vacations')) return;

        if (!Schema::hasColumn('vacations','employee_id')) {
            Schema::table('vacations', function (Blueprint $t) {
                $t->unsignedBigInteger('employee_id')->nullable()->after('id');
            });
        }

        if (Schema::hasColumn('vacations','staff_id')) {
            DB::statement('UPDATE vacations SET employee_id = staff_id WHERE employee_id IS NULL AND staff_id IS NOT NULL');

            try { Schema::table('vacations', fn (Blueprint $t) => $t->dropForeign(['staff_id'])); } catch (\Throwable $e) {
                try { DB::statement('ALTER TABLE `vacations` DROP FOREIGN KEY `vacations_staff_id_foreign`'); } catch (\Throwable $e2) {}
            }
            Schema::table('vacations', fn (Blueprint $t) => $t->dropColumn('staff_id'));
        }

        try { DB::statement('ALTER TABLE `vacations` DROP FOREIGN KEY `vacations_employee_id_foreign`'); } catch (\Throwable $e) {}
        Schema::table('vacations', function (Blueprint $t) {
            $t->unsignedBigInteger('employee_id')->nullable(false)->change();
            $t->foreign('employee_id')->references('id')->on('employees')
              ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        try { Schema::table('vacations', fn (Blueprint $t) => $t->dropForeign(['employee_id'])); } catch (\Throwable $e) {}
    }
};


