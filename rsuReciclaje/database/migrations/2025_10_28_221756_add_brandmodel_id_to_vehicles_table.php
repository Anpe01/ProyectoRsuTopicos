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
        Schema::table('vehicles', function (Blueprint $table): void {
            if (Schema::hasColumn('vehicles', 'model_id') && !Schema::hasColumn('vehicles', 'brandmodel_id')) {
                // Eliminar la foreign key existente
                $table->dropForeign(['model_id']);
                
                // Renombrar la columna
                $table->renameColumn('model_id', 'brandmodel_id');
                
                // Recrear la foreign key con el nuevo nombre
                $table->foreign('brandmodel_id')
                    ->references('id')
                    ->on('brandmodels')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table): void {
            if (Schema::hasColumn('vehicles', 'brandmodel_id') && !Schema::hasColumn('vehicles', 'model_id')) {
                // Eliminar la foreign key actual
                $table->dropForeign(['brandmodel_id']);
                
                // Renombrar la columna de vuelta
                $table->renameColumn('brandmodel_id', 'model_id');
                
                // Recrear la foreign key original
                $table->foreign('model_id')
                    ->references('id')
                    ->on('brandmodels')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            }
        });
    }
};
