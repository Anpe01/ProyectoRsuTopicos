<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colors', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 200)->nullable();
            $table->text('description');
            $table->timestamps();
            $table->unique(['name', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colors');
    }
};



