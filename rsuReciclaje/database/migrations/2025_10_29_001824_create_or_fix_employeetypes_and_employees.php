<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    if (!Schema::hasTable('employeetypes')) {
      Schema::create('employeetypes', function (Blueprint $t) {
        $t->id();
        $t->string('name',100)->unique();
        $t->text('description')->nullable();
        $t->boolean('active')->default(true);
        $t->timestamps();
      });
    }

    Schema::table('employees', function (Blueprint $t) {
      if (!Schema::hasColumn('employees','dni'))          $t->string('dni',8)->unique()->after('id');
      if (!Schema::hasColumn('employees','first_name'))   $t->string('first_name',120)->after('dni');
      if (!Schema::hasColumn('employees','last_name'))    $t->string('last_name',120)->after('first_name');
      if (!Schema::hasColumn('employees','birth_date'))   $t->date('birth_date')->after('last_name');
      if (!Schema::hasColumn('employees','phone'))        $t->string('phone',20)->nullable()->after('birth_date');
      if (!Schema::hasColumn('employees','email'))        $t->string('email',120)->nullable()->unique()->after('phone');
      if (!Schema::hasColumn('employees','photo_path'))   $t->string('photo_path',255)->nullable()->after('email');
      if (!Schema::hasColumn('employees','password'))     $t->string('password')->after('photo_path');
      if (!Schema::hasColumn('employees','address'))      $t->string('address',255)->nullable()->after('password');
      if (!Schema::hasColumn('employees','active'))       $t->boolean('active')->default(true)->after('address');
      if (!Schema::hasColumn('employees','employeetype_id')) {
        $t->foreignId('employeetype_id')->nullable()
          ->constrained('employeetypes')->cascadeOnUpdate()->nullOnDelete();
      }
    });
  }

  public function down(): void {
    Schema::table('employees', function (Blueprint $t) {
      if (Schema::hasColumn('employees','employeetype_id')) {
        $t->dropForeign(['employeetype_id']); 
        $t->dropColumn('employeetype_id');
      }
    });
    // no drop de empleados
    // Schema::dropIfExists('employeetypes'); // no borrar en down para no perder catálogo
  }
};
