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
        Schema::create('run_personnel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('run_id')->constrained('runs')->cascadeOnUpdate()->cascadeOnDelete();
            // staff_id sin FK temprana; se normaliza a employee_id y FK en migraciones de saneamiento
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->foreignId('function_id')->constrained('functions')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
            $table->unique(['run_id','staff_id']); // no duplicar persona en el mismo recorrido
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('run_personnel');
    }
};
