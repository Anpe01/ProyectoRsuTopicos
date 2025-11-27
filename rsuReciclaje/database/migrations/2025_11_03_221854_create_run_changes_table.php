<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('run_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('run_id')->constrained('runs')->cascadeOnDelete();
            $table->enum('change_type', ['turno', 'vehiculo', 'personal'])->index();
            $table->string('old_value', 255)->nullable();
            $table->string('new_value', 255)->nullable();
            $table->text('notes')->nullable(); // Motivo del cambio
            $table->timestamps();
            
            $table->index(['run_id', 'change_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('run_changes');
    }
};
