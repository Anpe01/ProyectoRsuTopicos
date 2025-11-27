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
        Schema::table('functions', function (Blueprint $table) {
            if (!Schema::hasColumn('functions', 'active')) {
                $table->boolean('active')->default(true)->after('protected');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('functions', function (Blueprint $table) {
            if (Schema::hasColumn('functions', 'active')) {
                $table->dropColumn('active');
            }
        });
    }
};
