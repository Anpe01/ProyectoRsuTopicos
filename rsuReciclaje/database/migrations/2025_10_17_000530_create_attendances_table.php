<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table): void {
            $table->id();
            // Definir staff_id sin FK temprana; se normaliza a employee_id después
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->date('date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->boolean('was_present')->default(false);
            $table->string('method', 30)->nullable(); // 'dni+pin' | 'manual' | 'biometrico'
            $table->timestamps();

            $table->unique(['staff_id','date']); // 1 registro por empleado por día
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};



