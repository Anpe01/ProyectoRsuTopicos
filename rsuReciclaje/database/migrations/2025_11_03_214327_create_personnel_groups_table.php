<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnel_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            
            // Preconfiguración de roles
            $table->foreignId('driver_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('helper1_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('helper2_id')->nullable()->constrained('employees')->nullOnDelete();
            
            // Días de trabajo (preconfig)
            $table->boolean('mon')->default(false);
            $table->boolean('tue')->default(false);
            $table->boolean('wed')->default(false);
            $table->boolean('thu')->default(false);
            $table->boolean('fri')->default(false);
            $table->boolean('sat')->default(false);
            $table->boolean('sun')->default(false);
            
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel_groups');
    }
};
