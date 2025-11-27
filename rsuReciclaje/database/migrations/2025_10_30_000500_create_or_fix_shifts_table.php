<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('shifts')) {
            Schema::create('shifts', function (Blueprint $t) {
                $t->id();
                $t->string('name', 100);
                $t->time('start_time');   // Hora de entrada (24h)
                $t->time('end_time');     // Hora de salida (24h)
                $t->text('description')->nullable();
                $t->boolean('active')->default(true);
                $t->timestamps();
                $t->unique('name');
            });
            return;
        }

        // Normalización si ya existe la tabla
        Schema::table('shifts', function (Blueprint $t) {
            if (!Schema::hasColumn('shifts','name'))        $t->string('name',100)->after('id');
            if (!Schema::hasColumn('shifts','start_time'))  $t->time('start_time')->after('name');
            if (!Schema::hasColumn('shifts','end_time'))    $t->time('end_time')->after('start_time');
            if (!Schema::hasColumn('shifts','description')) $t->text('description')->nullable()->after('end_time');
            if (!Schema::hasColumn('shifts','active'))      $t->boolean('active')->default(true)->after('description');
            try { $t->unique('name'); } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        // No se elimina la tabla para evitar pérdida de datos
    }
};


