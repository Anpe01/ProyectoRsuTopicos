<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('attendances')) {
            try {
                DB::statement("ALTER TABLE `attendances` MODIFY `time` TIME NULL DEFAULT NULL");
            } catch (\Throwable $e) {
                try {
                    DB::statement("ALTER TABLE `attendances` MODIFY `time` VARCHAR(8) NULL DEFAULT NULL");
                } catch (\Throwable $e2) { /* noop */ }
            }
        }
    }

    public function down(): void
    {
        // no-op para no romper inserts antiguos
    }
};


