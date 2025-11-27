<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 100)->unique();
            $table->string('plate', 20)->unique();
            $table->integer('year');
            $table->double('load_capacity')->nullable();
            $table->text('description');
            $table->integer('status')->default(1);
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('model_id')->constrained('brandmodels')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('type_id')->constrained('vehicletypes')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('color_id')->constrained('colors')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};



