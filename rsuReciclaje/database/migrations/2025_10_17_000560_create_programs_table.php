<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('zone_id')->constrained('zones')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('conductor_id')->constrained('staff')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnUpdate()->restrictOnDelete();
            $table->json('weekdays'); // [1,2,3,4,5] para lunes a viernes
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};












