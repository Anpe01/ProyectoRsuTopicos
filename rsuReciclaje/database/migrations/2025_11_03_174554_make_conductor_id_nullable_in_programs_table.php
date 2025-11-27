<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            // Eliminar la foreign key existente primero si existe
            try {
                $table->dropForeign(['conductor_id']);
            } catch (\Exception $e) {
                // La FK puede no existir, continuar
            }
            
            // Hacer el campo nullable
            $table->unsignedBigInteger('conductor_id')->nullable()->change();
            
            // Volver a agregar la foreign key si la tabla staff existe
            if (Schema::hasTable('staff')) {
                try {
                    $table->foreign('conductor_id')->references('id')->on('staff')->onUpdate('cascade')->onDelete('restrict');
                } catch (\Exception $e) {
                    // Si falla, intentar con employees
                    if (Schema::hasTable('employees')) {
                        $table->foreign('conductor_id')->references('id')->on('employees')->onUpdate('cascade')->onDelete('restrict');
                    }
                }
            } elseif (Schema::hasTable('employees')) {
                $table->foreign('conductor_id')->references('id')->on('employees')->onUpdate('cascade')->onDelete('restrict');
            }
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            try {
                $table->dropForeign(['conductor_id']);
            } catch (\Exception $e) {
                // La FK puede no existir
            }
            
            // Revertir a no nullable (puede causar errores si hay valores nulos)
            $table->unsignedBigInteger('conductor_id')->nullable(false)->change();
            
            // Restaurar FK
            if (Schema::hasTable('staff')) {
                $table->foreign('conductor_id')->references('id')->on('staff')->onUpdate('cascade')->onDelete('restrict');
            } elseif (Schema::hasTable('employees')) {
                $table->foreign('conductor_id')->references('id')->on('employees')->onUpdate('cascade')->onDelete('restrict');
            }
        });
    }
};
