<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brandmodels', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('brand_id')
                ->constrained('brands')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('name', 100);
            $table->text('description');
            $table->timestamps();
            $table->unique(['brand_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brandmodels');
    }
};



