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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->double('fuel_capacity_l')->nullable()->after('load_capacity');
            $table->double('compaction_capacity_kg')->nullable()->after('fuel_capacity_l');
            $table->unsignedSmallInteger('people_capacity')->nullable()->after('compaction_capacity_kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['fuel_capacity_l', 'compaction_capacity_kg', 'people_capacity']);
        });
    }
};
