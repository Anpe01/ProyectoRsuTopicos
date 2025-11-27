<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Índices para vacations
        Schema::table('vacations', function (Blueprint $t) {
            try {
                $t->index(['employee_id','start_date'], 'vacations_employee_id_start_date_index');
            } catch (\Exception $e) {
                // Índice ya existe, ignorar
            }
            try {
                $t->index(['employee_id','end_date'], 'vacations_employee_id_end_date_index');
            } catch (\Exception $e) {
                // Índice ya existe, ignorar
            }
        });

        // Índices para contracts
        Schema::table('contracts', function (Blueprint $t) {
            try {
                $t->index(['employee_id','active'], 'contracts_employee_id_active_index');
            } catch (\Exception $e) {
                // Índice ya existe, ignorar
            }
            try {
                $t->index(['type'], 'contracts_type_index');
            } catch (\Exception $e) {
                // Índice ya existe, ignorar
            }
            try {
                $t->index(['start_date','end_date'], 'contracts_start_date_end_date_index');
            } catch (\Exception $e) {
                // Índice ya existe, ignorar
            }
        });
    }

    public function down(): void
    {
        Schema::table('vacations', function (Blueprint $t) {
            try {
                $t->dropIndex('vacations_employee_id_start_date_index');
            } catch (\Exception $e) {}
            try {
                $t->dropIndex('vacations_employee_id_end_date_index');
            } catch (\Exception $e) {}
        });

        Schema::table('contracts', function (Blueprint $t) {
            try {
                $t->dropIndex('contracts_employee_id_active_index');
            } catch (\Exception $e) {}
            try {
                $t->dropIndex('contracts_type_index');
            } catch (\Exception $e) {}
            try {
                $t->dropIndex('contracts_start_date_end_date_index');
            } catch (\Exception $e) {}
        });
    }
};
