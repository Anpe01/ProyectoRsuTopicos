<?php

declare(strict_types=1);

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
        Schema::create('maintenance_days', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('maintenance_schedule_id')->constrained('maintenance_schedules')->cascadeOnDelete();
            $table->date('date');
            $table->text('observation')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('executed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_days');
    }
};
