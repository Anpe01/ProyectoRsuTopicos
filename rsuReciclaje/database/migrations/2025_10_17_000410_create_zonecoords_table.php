<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zonecoords', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('zone_id')
                ->constrained('zones')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedSmallInteger('sequence')->default(0);
            $table->timestamps();
            $table->index(['zone_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zonecoords');
    }
};














