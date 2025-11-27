<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('run_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('status', 20)->default('planned'); // planned|in_progress|done|canceled
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['program_id','run_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('runs');
    }
};




