<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);       // Ej: Mañana, Tarde, Noche
            $table->time('start_time');
            $table->time('end_time');          // end > start (validación app)
            $table->string('description',150)->nullable();
            $table->timestamps();
            $table->unique(['name','start_time','end_time']); // evita duplicados exactos
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};



