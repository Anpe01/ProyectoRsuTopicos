<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PersonnelGroup;
use App\Models\Zone;
use App\Models\Shift;
use App\Models\Vehicle;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkgroupController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $groups = PersonnelGroup::with(['zone', 'shift', 'vehicle', 'driver', 'helper1', 'helper2'])
                ->orderBy('id', 'desc')
                ->get();
            
            $data = $groups->map(function($g) {
                $daysMap = [1 => 'L', 2 => 'M', 3 => 'X', 4 => 'J', 5 => 'V', 6 => 'S', 7 => 'D'];
                // Obtener días de trabajo desde los campos booleanos
                $days = $g->days_of_week;
                
                $daysDisplay = 'N/A';
                if (!empty($days) && is_array($days)) {
                    $daysDisplay = implode(', ', array_map(function($d) use ($daysMap) {
                        return $daysMap[$d] ?? $d;
                    }, $days));
                }
                
                $vehicleName = 'N/A';
                if ($g->vehicle) {
                    $code = $g->vehicle->code ?? $g->vehicle->name ?? 'N/A';
                    $capacity = $g->vehicle->people_capacity ?? $g->vehicle->capacity ?? 0;
                    $vehicleName = "{$code} (Cap: {$capacity})";
                }
                
                return [
                    'id' => $g->id,
                    'name' => $g->name ?? 'N/A',
                    'zone_name' => $g->zone ? ($g->zone->name ?? 'N/A') : 'N/A',
                    'shift_name' => $g->shift ? ($g->shift->name ?? 'N/A') : 'N/A',
                    'vehicle_name' => $vehicleName,
                    'days_display' => $daysDisplay,
                ];
            });
            
            return response()->json(['data' => $data]);
        }
        
        return view('workgroups.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'zone_id' => ['required', 'exists:zones,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'days_of_week' => ['required', 'array', 'min:1'],
            'days_of_week.*' => ['integer', 'between:1,7'],
            'conductor_id' => ['nullable', 'exists:employees,id'],
            'ayudante1_id' => ['nullable', 'exists:employees,id'],
            'ayudante2_id' => ['nullable', 'exists:employees,id'],
        ]);

        DB::beginTransaction();
        try {
            $groupData = [
                'name' => $validated['name'],
                'zone_id' => $validated['zone_id'],
                'shift_id' => $validated['shift_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['conductor_id'] ?? null,
                'helper1_id' => $validated['ayudante1_id'] ?? null,
                'helper2_id' => $validated['ayudante2_id'] ?? null,
            ];
            
            // Crear grupo y establecer días de trabajo
            $group = PersonnelGroup::create($groupData);
            $group->setDaysOfWeek($validated['days_of_week']);
            $group->save();

            DB::commit();
            return response()->json(['ok' => true, 'msg' => 'Grupo de personal creado correctamente']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'ok' => false, 
                'msg' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear grupo de personal: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json([
                'ok' => false, 
                'msg' => 'Error al crear el grupo: ' . $e->getMessage(),
                'exception' => class_basename($e),
                'file' => $e->getFile() . ':' . $e->getLine()
            ], 500);
        }
    }

    public function show($id)
    {
        $group = PersonnelGroup::with(['zone', 'shift', 'vehicle', 'driver', 'helper1', 'helper2'])->findOrFail($id);

        return response()->json([
            'ok' => true,
            'data' => [
                'id' => $group->id,
                'name' => $group->name,
                'zone_id' => $group->zone_id,
                'shift_id' => $group->shift_id,
                'vehicle_id' => $group->vehicle_id,
                'days_of_week' => $group->days_of_week,
                'conductor_id' => $group->driver_id,
                'ayudante1_id' => $group->helper1_id,
                'ayudante2_id' => $group->helper2_id,
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $group = PersonnelGroup::findOrFail($id);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'zone_id' => ['required', 'exists:zones,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'days_of_week' => ['required', 'array', 'min:1'],
            'days_of_week.*' => ['integer', 'between:1,7'],
            'conductor_id' => ['nullable', 'exists:employees,id'],
            'ayudante1_id' => ['nullable', 'exists:employees,id'],
            'ayudante2_id' => ['nullable', 'exists:employees,id'],
        ]);

        DB::beginTransaction();
        try {
            $group->update([
                'name' => $validated['name'],
                'zone_id' => $validated['zone_id'],
                'shift_id' => $validated['shift_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['conductor_id'] ?? null,
                'helper1_id' => $validated['ayudante1_id'] ?? null,
                'helper2_id' => $validated['ayudante2_id'] ?? null,
            ]);
            
            // Actualizar días de trabajo
            $group->setDaysOfWeek($validated['days_of_week']);
            $group->save();

            DB::commit();
            return response()->json(['ok' => true, 'msg' => 'Grupo de personal actualizado correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'msg' => 'Error al actualizar el grupo: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $group = PersonnelGroup::findOrFail($id);
        
        DB::beginTransaction();
        try {
            $group->delete();
            DB::commit();
            return response()->json(['ok' => true, 'msg' => 'Grupo eliminado correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al eliminar grupo de personal: ' . $e->getMessage());
            return response()->json(['ok' => false, 'msg' => 'Error al eliminar el grupo: ' . $e->getMessage()], 500);
        }
    }
}
