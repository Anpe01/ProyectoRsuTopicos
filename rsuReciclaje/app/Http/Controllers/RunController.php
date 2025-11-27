<?php
namespace App\Http\Controllers;

use App\Models\Run;
use App\Models\Program;
use App\Models\PersonnelGroup;
use App\Models\Employee;
use App\Models\FunctionModel;
use App\Models\RunPersonnel;
use App\Models\RunChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class RunController extends Controller
{
    public function index()
    {
        $runs = Run::with([
            'zone',
            'vehicle',
            'shift',
            'group',
            'changes'
        ])->latest('run_date')->paginate(10);

        $groups = PersonnelGroup::with(['zone','vehicle','shift'])->orderBy('id','desc')->get(['id','zone_id','vehicle_id','shift_id']);
        $employees = Employee::orderBy('last_name')->orderBy('first_name')->get(['id','dni','first_name','last_name']);
        $functions = FunctionModel::orderBy('name')->get(['id','name']);

        return view('runs.index', compact('runs','groups','employees','functions'));
    }

    public function show(Run $run)
    {
        if (request()->ajax() || request()->wantsJson()) {
            $run->load(['zone', 'shift', 'vehicle', 'group.driver.jobFunction', 'group.helper1.jobFunction', 'group.helper2.jobFunction', 'changes', 'personnel.employee', 'personnel.function']);
            
            // Obtener personal asignado directamente al run con su rol
            $runPersonnel = $run->personnel->map(function($p) {
                // Determinar el nombre del rol basándose en el campo role
                $roleName = 'Sin rol';
                if ($p->role === 'conductor') {
                    $roleName = 'Conductor';
                } elseif ($p->role === 'ayudante1') {
                    $roleName = 'Ayudante 1';
                } elseif ($p->role === 'ayudante2') {
                    $roleName = 'Ayudante 2';
                }
                
                return [
                    'id' => $p->employee ? $p->employee->id : null,
                    'name' => $p->employee ? ($p->employee->first_name . ' ' . $p->employee->last_name) : 'N/A',
                    'function_id' => $p->function_id,
                    'function_name' => $roleName, // Usar el rol como nombre de función
                    'role' => $p->role,
                    'from_group' => false,
                ];
            })->filter(function($p) {
                return $p['id'] !== null;
            });
            
            // Obtener roles de personal que ya está en run_personnel (para no duplicar)
            $runPersonnelByRole = $runPersonnel->keyBy('role');
            $runPersonnelIds = $runPersonnel->pluck('id')->toArray();
            
            // Obtener personal del grupo que NO esté ya en run_personnel
            // Solo agregar si no existe un registro con ese rol en run_personnel
            $groupPersonnel = collect();
            if ($run->group) {
                // Buscar funciones por nombre
                $conductorFunction = FunctionModel::where('name', 'Conductor')->first();
                $ayudanteFunction = FunctionModel::where('name', 'Ayudante')->first();
                
                // Agregar conductor si existe y NO hay un registro con rol 'conductor' en run_personnel
                if ($run->group->driver_id && $run->group->driver && !isset($runPersonnelByRole['conductor'])) {
                    $driverFunction = $run->group->driver->jobFunction ?? $conductorFunction;
                    $groupPersonnel->push([
                        'id' => $run->group->driver->id,
                        'name' => $run->group->driver->first_name . ' ' . $run->group->driver->last_name,
                        'function_id' => $driverFunction ? $driverFunction->id : null,
                        'function_name' => 'Conductor',
                        'role' => 'conductor',
                        'from_group' => true,
                    ]);
                }
                
                // Agregar ayudante 1 si existe y NO hay un registro con rol 'ayudante1' en run_personnel
                if ($run->group->helper1_id && $run->group->helper1 && !isset($runPersonnelByRole['ayudante1'])) {
                    $helper1Function = $run->group->helper1->jobFunction ?? $ayudanteFunction;
                    $groupPersonnel->push([
                        'id' => $run->group->helper1->id,
                        'name' => $run->group->helper1->first_name . ' ' . $run->group->helper1->last_name,
                        'function_id' => $helper1Function ? $helper1Function->id : null,
                        'function_name' => 'Ayudante 1',
                        'role' => 'ayudante1',
                        'from_group' => true,
                    ]);
                }
                
                // Agregar ayudante 2 si existe y NO hay un registro con rol 'ayudante2' en run_personnel
                if ($run->group->helper2_id && $run->group->helper2 && !isset($runPersonnelByRole['ayudante2'])) {
                    $helper2Function = $run->group->helper2->jobFunction ?? $ayudanteFunction;
                    $groupPersonnel->push([
                        'id' => $run->group->helper2->id,
                        'name' => $run->group->helper2->first_name . ' ' . $run->group->helper2->last_name,
                        'function_id' => $helper2Function ? $helper2Function->id : null,
                        'function_name' => 'Ayudante 2',
                        'role' => 'ayudante2',
                        'from_group' => true,
                    ]);
                }
            }
            
            // Combinar personal de run_personnel con personal del grupo (que no ha sido cambiado)
            // Ordenar por rol: conductor primero, luego ayudante1, luego ayudante2
            $personnel = $runPersonnel->merge($groupPersonnel)->sortBy(function($p) {
                $order = ['conductor' => 1, 'ayudante1' => 2, 'ayudante2' => 3];
                return $order[$p['role'] ?? ''] ?? 99;
            })->values();
            
            return response()->json([
                'ok' => true,
                'data' => [
                    'id' => $run->id,
                    'run_date' => $run->run_date ? $run->run_date->format('Y-m-d') : null,
                    'status' => $run->status,
                    'shift_id' => $run->shift_id,
                    'shift_name' => $run->shift ? $run->shift->name : 'N/A',
                    'vehicle_id' => $run->vehicle_id,
                    'vehicle_name' => $run->vehicle ? ($run->vehicle->code ?? $run->vehicle->name ?? 'N/A') : 'N/A',
                    'zone_id' => $run->zone_id,
                    'zone_name' => $run->zone ? $run->zone->name : 'N/A',
                    'group_id' => $run->group_id,
                    'group_name' => $run->group ? $run->group->name : 'N/A',
                    'personnel' => $personnel,
                    'changes' => $run->changes->map(function($change) {
                        return [
                            'id' => $change->id,
                            'type' => $change->change_type,
                            'old_value' => $change->old_value,
                            'new_value' => $change->new_value,
                            'notes' => $change->notes,
                            'created_at' => $change->created_at ? $change->created_at->format('Y-m-d H:i:s') : null,
                        ];
                    }),
                ]
            ]);
        }
        
        return view('runs.show', compact('run'));
    }

    public function store(Request $request)
    {
        $data = $this->rules($request);
        if (!in_array($data['status'], ['planned','in_progress','done','canceled'])) {
            return back()->with('error','Estado inválido.');
        }
        Run::create($data);
        return back()->with('success','Recorrido creado.');
    }

    public function update(Request $request, Run $run)
    {
        $data = $this->rules($request, $run->id);
        if (!in_array($data['status'], ['planned','in_progress','done','canceled'])) {
            return back()->with('error','Estado inválido.');
        }
        if (!empty($data['start_time']) && !empty($data['end_time']) && $data['end_time'] <= $data['start_time']) {
            return back()->with('error','La hora fin debe ser mayor que la hora inicio.');
        }
        $run->update($data);
        return back()->with('success','Recorrido actualizado.');
    }

    public function updateChanges(Request $request, Run $run)
    {
        $validated = $request->validate([
            'changes' => ['nullable', 'array'],
            'changes.*.type' => ['required_without:changes.*.id', 'in:turno,vehiculo,personal'],
            'changes.*.old_value' => ['nullable', 'string', 'max:255'],
            'changes.*.new_value' => ['nullable', 'string', 'max:255'],
            'changes.*.new_id' => ['nullable', 'integer'],
            'changes.*.old_id' => ['nullable', 'integer'],
            'changes.*.notes' => ['nullable', 'string'],
            'changes.*.id' => ['nullable', 'exists:run_changes,id'], // Para actualizar existentes
            'delete_ids' => ['nullable', 'array'],
            'delete_ids.*' => ['integer', 'exists:run_changes,id'],
        ]);
        
        // Validar que haya algo que procesar
        if (empty($validated['changes']) && empty($validated['delete_ids'])) {
            return response()->json([
                'ok' => false,
                'msg' => 'No hay cambios para procesar'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Eliminar cambios marcados para eliminar
            if (!empty($validated['delete_ids'])) {
                RunChange::whereIn('id', $validated['delete_ids'])->delete();
            }

            // Procesar cambios
            if (!empty($validated['changes'])) {
                foreach ($validated['changes'] as $changeData) {
                    if (isset($changeData['id'])) {
                        // Actualizar cambio existente (solo notas)
                        RunChange::where('id', $changeData['id'])
                            ->update(['notes' => $changeData['notes'] ?? '']);
                    } else {
                        // Crear nuevo cambio
                        $change = RunChange::create([
                            'run_id' => $run->id,
                            'change_type' => $changeData['type'],
                            'old_value' => $changeData['old_value'],
                            'new_value' => $changeData['new_value'],
                            'notes' => $changeData['notes'] ?? '',
                        ]);

                        // Aplicar el cambio al run según el tipo
                        if ($changeData['type'] === 'turno' && !empty($changeData['new_id'])) {
                            $run->shift_id = $changeData['new_id'];
                        } elseif ($changeData['type'] === 'vehiculo' && !empty($changeData['new_id'])) {
                            $run->vehicle_id = $changeData['new_id'];
                        } elseif ($changeData['type'] === 'personal') {
                            // Para personal, actualizar o crear registro en run_personnel
                            if (!empty($changeData['old_id']) && !empty($changeData['new_id'])) {
                                // Buscar el registro con el old_id para obtener su rol
                                $runPersonnel = RunPersonnel::where('run_id', $run->id)
                                    ->where('staff_id', $changeData['old_id'])
                                    ->first();
                                
                                // Si no existe en run_personnel, buscar en el grupo para determinar el rol
                                $role = null;
                                if ($runPersonnel) {
                                    $role = $runPersonnel->role;
                                } else {
                                    // Determinar el rol basándose en el grupo
                                    if ($run->group) {
                                        if ($run->group->driver_id == $changeData['old_id']) {
                                            $role = 'conductor';
                                        } elseif ($run->group->helper1_id == $changeData['old_id']) {
                                            $role = 'ayudante1';
                                        } elseif ($run->group->helper2_id == $changeData['old_id']) {
                                            $role = 'ayudante2';
                                        }
                                    }
                                }
                                
                                if ($runPersonnel) {
                                    // Si existe, actualizar con el nuevo personal manteniendo el rol
                                    $runPersonnel->staff_id = $changeData['new_id'];
                                    // Obtener function_id del nuevo empleado
                                    $newEmployee = Employee::find($changeData['new_id']);
                                    if ($newEmployee && $newEmployee->function_id) {
                                        $runPersonnel->function_id = $newEmployee->function_id;
                                    }
                                    $runPersonnel->save();
                                } else if ($role) {
                                    // Si no existe pero tenemos el rol, crear nuevo registro
                                    // Verificar si el nuevo personal ya está asignado
                                    $existingPersonnel = RunPersonnel::where('run_id', $run->id)
                                        ->where('staff_id', $changeData['new_id'])
                                        ->first();
                                    
                                    if (!$existingPersonnel) {
                                        // Obtener function_id del nuevo empleado
                                        $newEmployee = Employee::find($changeData['new_id']);
                                        $functionId = null;
                                        if ($newEmployee && $newEmployee->function_id) {
                                            $functionId = $newEmployee->function_id;
                                        } else {
                                            // Si no tiene función, buscar por nombre
                                            $conductorFunction = FunctionModel::where('name', 'Conductor')->first();
                                            $ayudanteFunction = FunctionModel::where('name', 'Ayudante')->first();
                                            $functionId = $role === 'conductor' 
                                                ? ($conductorFunction ? $conductorFunction->id : 1)
                                                : ($ayudanteFunction ? $ayudanteFunction->id : 2);
                                        }
                                        
                                        RunPersonnel::create([
                                            'run_id' => $run->id,
                                            'staff_id' => $changeData['new_id'],
                                            'function_id' => $functionId ?? 1,
                                            'role' => $role,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Guardar cambios en el run
            $run->save();

            DB::commit();
            return response()->json([
                'ok' => true,
                'msg' => 'Cambios guardados correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al guardar cambios de run: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'msg' => 'Error al guardar los cambios: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Run $run)
    {
        $runId = $run->id;
        
        // Si es petición AJAX, devolver JSON
        if (request()->ajax() || request()->wantsJson()) {
            DB::beginTransaction();
            try {
                \Log::info("Iniciando eliminación de programación completa desde run ID: {$runId}");
                
                // Cargar el run con sus relaciones para obtener los parámetros
                $run = Run::findOrFail($runId);
                
                // Identificar todos los runs relacionados de la misma programación
                // Agrupar por group_id y fechas cercanas (NO por shift_id/vehicle_id que pueden cambiar)
                $relatedRunIds = DB::table('runs')
                    ->where('group_id', $run->group_id)
                    ->where(function($query) use ($run) {
                        // Buscar runs en un rango de fechas: desde 45 días antes hasta 45 días después
                        $minDate = Carbon::parse($run->run_date)->subDays(45)->toDateString();
                        $maxDate = Carbon::parse($run->run_date)->addDays(45)->toDateString();
                        $query->whereBetween('run_date', [$minDate, $maxDate]);
                    })
                    ->pluck('id')
                    ->toArray();
                
                \Log::info("Encontrados " . count($relatedRunIds) . " runs relacionados a la programación (run ID: {$runId})");
                
                if (empty($relatedRunIds)) {
                    // Si no hay runs relacionados, eliminar solo el actual
                    $relatedRunIds = [$runId];
                }
                
                // Eliminar todos los cambios relacionados de todos los runs
                $changesDeleted = DB::table('run_changes')
                    ->whereIn('run_id', $relatedRunIds)
                    ->delete();
                \Log::info("Eliminados {$changesDeleted} cambios relacionados a " . count($relatedRunIds) . " runs");
                
                // Eliminar todo el personal asignado de todos los runs
                $personnelDeleted = DB::table('run_personnel')
                    ->whereIn('run_id', $relatedRunIds)
                    ->delete();
                \Log::info("Eliminados {$personnelDeleted} registros de personal relacionados a " . count($relatedRunIds) . " runs");
                
                // Eliminar todos los runs relacionados usando DB directo
                $runsDeleted = DB::table('runs')
                    ->whereIn('id', $relatedRunIds)
                    ->delete();
                \Log::info("Eliminados {$runsDeleted} runs de la programación (esperados: " . count($relatedRunIds) . ")");
                
                // Verificar que realmente se eliminaron todos
                $stillExists = DB::table('runs')
                    ->whereIn('id', $relatedRunIds)
                    ->exists();
                if ($stillExists) {
                    $remaining = DB::table('runs')
                        ->whereIn('id', $relatedRunIds)
                        ->pluck('id')
                        ->toArray();
                    throw new \Exception("Algunos runs no se eliminaron correctamente. IDs restantes: " . implode(', ', $remaining));
                }
                
                DB::commit();
                
                \Log::info("Programación completa eliminada exitosamente. Se eliminaron {$runsDeleted} runs");
                
                return response()->json([
                    'ok' => true,
                    'msg' => "Programación anulada y eliminada correctamente. Se eliminaron {$runsDeleted} recorrido(s)."
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error al eliminar programación ID ' . $runId . ': ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                return response()->json([
                    'ok' => false,
                    'msg' => 'Error al eliminar la programación: ' . $e->getMessage()
                ], 500);
            }
        }
        
        // Manejo tradicional (formulario normal)
        try {
            DB::transaction(function() use ($run, $runId) {
                \Log::info("Iniciando eliminación tradicional de programación completa desde run ID: {$runId}");
                
                // Cargar el run con sus relaciones
                $run = Run::findOrFail($runId);
                
                // Identificar todos los runs relacionados
                // Agrupar por group_id y fechas cercanas (NO por shift_id/vehicle_id que pueden cambiar)
                $relatedRunIds = DB::table('runs')
                    ->where('group_id', $run->group_id)
                    ->where(function($query) use ($run) {
                        $minDate = Carbon::parse($run->run_date)->subDays(45)->toDateString();
                        $maxDate = Carbon::parse($run->run_date)->addDays(45)->toDateString();
                        $query->whereBetween('run_date', [$minDate, $maxDate]);
                    })
                    ->pluck('id')
                    ->toArray();
                
                if (empty($relatedRunIds)) {
                    $relatedRunIds = [$runId];
                }
                
                // Eliminar cambios relacionados usando DB directo
                DB::table('run_changes')->whereIn('run_id', $relatedRunIds)->delete();
                
                // Eliminar personal asignado usando DB directo
                DB::table('run_personnel')->whereIn('run_id', $relatedRunIds)->delete();
                
                // Eliminar todos los runs relacionados usando DB directo
                $deleted = DB::table('runs')->whereIn('id', $relatedRunIds)->delete();
                
                // Verificar eliminación
                $stillExists = DB::table('runs')->whereIn('id', $relatedRunIds)->exists();
                if ($stillExists) {
                    throw new \Exception("Algunos runs no se eliminaron correctamente. Filas eliminadas: {$deleted}");
                }
                
                \Log::info("Eliminados {$deleted} runs de la programación");
            });
            return back()->with('success','Programación eliminada correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Error QueryException al eliminar programación: ' . $e->getMessage());
            return back()->with('error','No se puede eliminar la programación: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Error al eliminar programación: ' . $e->getMessage());
            return back()->with('error','Error al eliminar la programación: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un run individual (solo ese run, no todo el grupo)
     */
    public function destroyIndividual(Run $run)
    {
        $runId = $run->id;
        
        // Si es petición AJAX, devolver JSON
        if (request()->ajax() || request()->wantsJson()) {
            DB::beginTransaction();
            try {
                \Log::info("Iniciando eliminación individual de run ID: {$runId}");
                
                // Eliminar solo los cambios de este run
                $changesDeleted = DB::table('run_changes')
                    ->where('run_id', $runId)
                    ->delete();
                
                // Eliminar solo el personal asignado de este run
                $personnelDeleted = DB::table('run_personnel')
                    ->where('run_id', $runId)
                    ->delete();
                
                // Eliminar solo este run
                $run->delete();
                
                DB::commit();
                
                \Log::info("Run individual eliminado exitosamente. ID: {$runId}");
                
                return response()->json([
                    'ok' => true,
                    'msg' => "Recorrido eliminado correctamente."
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error al eliminar run individual ID ' . $runId . ': ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                return response()->json([
                    'ok' => false,
                    'msg' => 'Error al eliminar el recorrido: ' . $e->getMessage()
                ], 500);
            }
        }
        
        // Manejo tradicional (formulario normal)
        try {
            DB::transaction(function() use ($run, $runId) {
                \Log::info("Iniciando eliminación tradicional individual de run ID: {$runId}");
                
                // Eliminar cambios relacionados
                DB::table('run_changes')->where('run_id', $runId)->delete();
                
                // Eliminar personal asignado
                DB::table('run_personnel')->where('run_id', $runId)->delete();
                
                // Eliminar solo este run
                $run->delete();
                
                \Log::info("Run individual eliminado. ID: {$runId}");
            });
            return back()->with('success','Recorrido eliminado correctamente.');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar run individual: ' . $e->getMessage());
            return back()->with('error','Error al eliminar el recorrido: ' . $e->getMessage());
        }
    }

    private function rules(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'program_id' => ['required','exists:programs,id'],
            'run_date'   => ['required','date'],
            'start_time' => ['nullable','date_format:H:i'],
            'end_time'   => ['nullable','date_format:H:i'],
            'status'     => ['required', Rule::in(['planned','in_progress','done','canceled'])],
            'notes'      => ['nullable','string'],
        ]);
    }
}