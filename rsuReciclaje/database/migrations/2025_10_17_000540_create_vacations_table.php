<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacations', function (Blueprint $table): void {
            $table->id();
            // Definir staff_id sin FK temprana; será normalizado a employee_id luego
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->unsignedSmallInteger('year');         // año de la vacación
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('days');         // cantidad de días del periodo
            $table->string('status', 20)->default('aprobado'); // opcional
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['staff_id','year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacations');
    }
};



