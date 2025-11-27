<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrar datos de programs a personnel_groups si existen
        if (Schema::hasTable('programs') && Schema::hasTable('personnel_groups')) {
            $programs = DB::table('programs')->whereNotNull('name')->get();
            
            foreach ($programs as $program) {
                // Verificar si ya existe un grupo con este nombre
                $existing = DB::table('personnel_groups')
                    ->where('name', $program->name)
                    ->first();
                
                if ($existing) {
                    $personnelGroupId = $existing->id;
                } else {
                    // Convertir weekdays (JSON) a campos booleanos
                    $weekdays = json_decode($program->weekdays ?? '[]', true);
                    if (!is_array($weekdays)) {
                        $weekdays = [];
                    }
                    
                    $personnelGroupId = DB::table('personnel_groups')->insertGetId([
                    'name' => $program->name,
                    'zone_id' => $program->zone_id,
                    'shift_id' => $program->shift_id,
                    'vehicle_id' => $program->vehicle_id,
                    'driver_id' => $program->conductor_id ?? null,
                    'helper1_id' => null, // No hay datos en programs para estos
                    'helper2_id' => null,
                    'mon' => in_array(1, $weekdays),
                    'tue' => in_array(2, $weekdays),
                    'wed' => in_array(3, $weekdays),
                    'thu' => in_array(4, $weekdays),
                    'fri' => in_array(5, $weekdays),
                    'sat' => in_array(6, $weekdays),
                    'sun' => in_array(7, $weekdays),
                    'active' => true,
                    'created_at' => $program->created_at ?? now(),
                    'updated_at' => $program->updated_at ?? now(),
                    ]);
                }
                
                // Si hay personal asignado en program_personnel, migrarlo
                if (Schema::hasTable('program_personnel')) {
                    $personnel = DB::table('program_personnel')
                        ->where('program_id', $program->id)
                        ->get();
                    
                    foreach ($personnel as $p) {
                        if ($p->role === 'conductor') {
                            DB::table('personnel_groups')
                                ->where('id', $personnelGroupId)
                                ->update(['driver_id' => $p->staff_id]);
                        } elseif ($p->role === 'ayudante') {
                            // Asignar al primer ayudante disponible
                            $group = DB::table('personnel_groups')->where('id', $personnelGroupId)->first();
                            if (!$group->helper1_id) {
                                DB::table('personnel_groups')
                                    ->where('id', $personnelGroupId)
                                    ->update(['helper1_id' => $p->staff_id]);
                            } elseif (!$group->helper2_id) {
                                DB::table('personnel_groups')
                                    ->where('id', $personnelGroupId)
                                    ->update(['helper2_id' => $p->staff_id]);
                            }
                        }
                    }
                }
            }
        }
    }

    public function down(): void
    {
        // No revertir automáticamente - los datos en personnel_groups se mantienen
    }
};
