<?php

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
        Schema::create('staff_function', function (Blueprint $table) {
            $table->id();
            // staff_id sin FK temprana
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->foreignId('function_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['staff_id', 'function_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_function');
    }
};
