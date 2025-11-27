<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('staff') && ! Schema::hasTable('employees')) {
            Schema::rename('staff', 'employees');
        }
        // Mantén las columnas FK existentes (staff_id) por ahora para no romper.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('employees') && ! Schema::hasTable('staff')) {
            Schema::rename('employees', 'staff');
        }
    }
};
