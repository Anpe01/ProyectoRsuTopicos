<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table): void {
            $table->id();
            // Definir staff_id sin FK aquí para evitar errores de orden/compatibilidad.
            // La FK definitiva a employees(id) se aplica en migraciones de saneamiento posteriores.
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->enum('type', ['nombrado','permanente','eventual']);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('vigente'); // vigente|vencido (opcional)
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['staff_id','start_date','end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};



