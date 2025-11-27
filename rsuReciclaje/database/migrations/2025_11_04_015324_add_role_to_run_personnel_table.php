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
        Schema::table('run_personnel', function (Blueprint $table) {
            $table->string('role', 20)->nullable()->after('function_id')->comment('conductor, ayudante1, ayudante2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('run_personnel', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
