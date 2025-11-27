<?php

namespace App\Http\Controllers;

use App\Models\Run;
use App\Models\Zone;
use App\Models\Shift;
use App\Models\Vehicle;
use App\Models\Employee;
use App\Models\RunPersonnel;
use App\Models\Attendance;
use App\Models\RunChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Mostrar dashboard general
     */
    public function index(Request $request)
    {
        $selectedDate = $request->input('date', now()->format('Y-m-d'));
        $shifts = Shift::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $selectedShiftId = $request->input('shift_id');
        if (!$selectedShiftId && $shifts->count() > 0) {
            $selectedShiftId = $shifts->first()->id;
        }
        
        return view('dashboard', [
            'selectedDate' => $selectedDate,
            'shifts' => $shifts,
            'selectedShiftId' => $selectedShiftId,
        ]);
    }

    /**
     * Obtener zonas programadas para un día con su estado de disponibilidad
     */
    public function getZonesStatus(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'shift_id' => ['nullable', 'exists:shifts,id'],
        ]);

        $date = Carbon::parse($validated['date'])->toDateString();
        $shiftId = $validated['shift_id'] ?? null;
        
        // Obtener todos los runs programados para esa fecha
        $runs = Run::whereDate('run_date', $date)
            ->when($shiftId, function ($query) use ($shiftId) {
                $query->where('shift_id', $shiftId);
            })
            ->with(['zone', 'shift', 'vehicle', 'personnel.employee'])
            ->get();

        // Agrupar por zona
        $zonesData = [];
        $summary = [
            'attendances' => 0,
            'available_supports' => 0,
            'complete_groups' => 0,
            'missing_total' => 0,
        ];
        $globalAvailableSupports = $this->getAvailablePersonnelForZone(null, $date);
        $summary['available_supports'] = isset($globalAvailableSupports['helpers'])
            ? count($globalAvailableSupports['helpers'])
            : 0;
        
        foreach ($runs as $run) {
            if (!$run->zone) {
                continue;
            }
            
            $zoneId = $run->zone_id;
            
            if (!isset($zonesData[$zoneId])) {
                $zonesData[$zoneId] = [
                    'zone_id' => $zoneId,
                    'zone_name' => $run->zone->name,
                    'runs' => [],
                    'required_personnel' => [
                        'conductors' => 0,
                        'helpers' => 0,
                    ],
                    'assigned_personnel' => [
                        'conductors' => [],
                        'helpers' => [],
                    ],
                    'available_personnel' => [
                        'conductors' => [],
                        'helpers' => [],
                    ],
                    'missing_personnel' => [
                        'conductors' => 0,
                        'helpers' => 0,
                    ],
                    'can_start' => true,
                    'issues' => [],
                ];
            }
            
            // Agregar run
            $zonesData[$zoneId]['runs'][] = [
                'id' => $run->id,
                'shift_name' => $run->shift ? $run->shift->name : 'N/A',
                'vehicle_code' => $run->vehicle ? ($run->vehicle->code ?? $run->vehicle->name ?? 'N/A') : 'N/A',
            ];
            
            // Contar personal requerido y asignado
            $zonesData[$zoneId]['required_personnel']['conductors']++;
            $zonesData[$zoneId]['required_personnel']['helpers'] += 2; // Normalmente 2 ayudantes por vehículo
            
            // Contar personal asignado
            foreach ($run->personnel as $personnel) {
                if (!$personnel->employee) {
                    continue;
                }
                
                if ($personnel->role === 'conductor') {
                    $zonesData[$zoneId]['assigned_personnel']['conductors'][] = [
                        'id' => $personnel->employee->id,
                        'name' => $personnel->employee->first_name . ' ' . $personnel->employee->last_name,
                        'run_id' => $run->id,
                    ];
                } elseif (in_array($personnel->role, ['ayudante1', 'ayudante2'])) {
                    $zonesData[$zoneId]['assigned_personnel']['helpers'][] = [
                        'id' => $personnel->employee->id,
                        'name' => $personnel->employee->first_name . ' ' . $personnel->employee->last_name,
                        'run_id' => $run->id,
                        'role' => $personnel->role,
                    ];
                }
            }
        }
        
        // Verificar disponibilidad y asistencia
        foreach ($zonesData as $zoneId => &$zoneData) {
            // Contar personal único asignado
            $uniqueConductors = collect($zoneData['assigned_personnel']['conductors'])
                ->unique('id')
                ->count();
            $uniqueHelpers = collect($zoneData['assigned_personnel']['helpers'])
                ->unique('id')
                ->count();
            
            // Verificar asistencia del personal asignado
            $assignedConductorIds = collect($zoneData['assigned_personnel']['conductors'])
                ->pluck('id')
                ->unique()
                ->toArray();
            $assignedHelperIds = collect($zoneData['assigned_personnel']['helpers'])
                ->pluck('id')
                ->unique()
                ->toArray();
            
            // Verificar qué conductores asignados asistieron
            $attendedConductorIds = [];
            $missingAttendanceConductors = [];
            if (!empty($assignedConductorIds)) {
                $attendedConductorIds = Attendance::whereIn('employee_id', $assignedConductorIds)
                    ->whereDate('attendance_date', $date)
                    ->where('status', Attendance::STATUS_PRESENT)
                    ->where('period', Attendance::PERIOD_IN)
                    ->pluck('employee_id')
                    ->toArray();
                
                // Identificar conductores asignados que NO tienen asistencia
                foreach ($assignedConductorIds as $conductorId) {
                    if (!in_array($conductorId, $attendedConductorIds)) {
                        $conductor = Employee::find($conductorId);
                        if ($conductor) {
                            $missingAttendanceConductors[] = $conductor->first_name . ' ' . $conductor->last_name;
                        }
                    }
                }
            }
            
            // Verificar qué ayudantes asignados asistieron
            $attendedHelperIds = [];
            $missingAttendanceHelpers = [];
            if (!empty($assignedHelperIds)) {
                $attendedHelperIds = Attendance::whereIn('employee_id', $assignedHelperIds)
                    ->whereDate('attendance_date', $date)
                    ->where('status', Attendance::STATUS_PRESENT)
                    ->where('period', Attendance::PERIOD_IN)
                    ->pluck('employee_id')
                    ->toArray();
                
                // Identificar ayudantes asignados que NO tienen asistencia
                foreach ($assignedHelperIds as $helperId) {
                    if (!in_array($helperId, $attendedHelperIds)) {
                        $helper = Employee::find($helperId);
                        if ($helper) {
                            $missingAttendanceHelpers[] = $helper->first_name . ' ' . $helper->last_name;
                        }
                    }
                }
            }
            
            // Calcular faltantes
            $zoneData['missing_personnel']['conductors'] = max(0, $zoneData['required_personnel']['conductors'] - count($attendedConductorIds));
            $zoneData['missing_personnel']['helpers'] = max(0, $zoneData['required_personnel']['helpers'] - count($attendedHelperIds));
            
            // Guardar información de personal presente
            $zoneData['present_personnel'] = [
                'conductors' => count($attendedConductorIds),
                'helpers' => count($attendedHelperIds),
            ];

            $summary['attendances'] += $zoneData['present_personnel']['conductors'] + $zoneData['present_personnel']['helpers'];
            
            // Determinar si puede iniciar y agregar mensajes específicos por asistencia
            if ($zoneData['missing_personnel']['conductors'] > 0) {
                $zoneData['can_start'] = false;
                
                // Si hay conductores asignados pero sin asistencia, indicar específicamente
                if (!empty($missingAttendanceConductors)) {
                    foreach ($missingAttendanceConductors as $conductorName) {
                        $zoneData['issues'][] = "Error por asistencia: El conductor {$conductorName} no tiene asistencia registrada";
                    }
                } else {
                    // Si no hay conductores asignados
                    $zoneData['issues'][] = "Faltan {$zoneData['missing_personnel']['conductors']} conductor(es)";
                }
            }
            
            if ($zoneData['missing_personnel']['helpers'] > 0) {
                $zoneData['can_start'] = false;
                
                // Si hay ayudantes asignados pero sin asistencia, indicar específicamente
                if (!empty($missingAttendanceHelpers)) {
                    foreach ($missingAttendanceHelpers as $helperName) {
                        $zoneData['issues'][] = "Error por asistencia: El ayudante {$helperName} no tiene asistencia registrada";
                    }
                } else {
                    // Si no hay ayudantes asignados
                    $zoneData['issues'][] = "Faltan {$zoneData['missing_personnel']['helpers']} ayudante(s)";
                }
            }
            
            // Obtener personal disponible para esta zona
            $zoneData['available_personnel'] = $this->getAvailablePersonnelForZone($zoneId, $date);

            if ($zoneData['can_start']) {
                $summary['complete_groups']++;
            }

            $summary['missing_total'] += $zoneData['missing_personnel']['conductors'] + $zoneData['missing_personnel']['helpers'];
        }
        
        return response()->json([
            'ok' => true,
            'date' => $date,
            'shift_id' => $shiftId,
            'summary' => $summary,
            'zones' => array_values($zonesData),
        ]);
    }

    /**
     * Obtener personal disponible para una zona en una fecha
     * (con asistencia y no asignado en ninguna programación del día)
     */
    public function getAvailablePersonnelForZone($zoneId, $date)
    {
        // Obtener IDs de empleados ya asignados en CUALQUIER zona en esta fecha
        $assignedEmployeeIds = RunPersonnel::whereHas('run', function($query) use ($date) {
            $query->whereDate('run_date', $date);
        })->pluck('staff_id')->toArray();
        
        // Obtener empleados con asistencia presente
        $attendedEmployeeIds = Attendance::whereDate('attendance_date', $date)
            ->where('status', Attendance::STATUS_PRESENT)
            ->where('period', Attendance::PERIOD_IN)
            ->pluck('employee_id')
            ->toArray();
        
        // Filtrar conductores disponibles
        $availableConductors = Employee::where('active', true)
            ->conductors()
            ->whereIn('id', $attendedEmployeeIds)
            ->whereNotIn('id', $assignedEmployeeIds)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'dni', 'first_name', 'last_name'])
            ->map(function($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->first_name . ' ' . $emp->last_name,
                    'dni' => $emp->dni,
                ];
            });
        
        // Filtrar ayudantes disponibles
        $availableHelpers = Employee::where('active', true)
            ->helpers()
            ->whereIn('id', $attendedEmployeeIds)
            ->whereNotIn('id', $assignedEmployeeIds)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'dni', 'first_name', 'last_name'])
            ->map(function($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->first_name . ' ' . $emp->last_name,
                    'dni' => $emp->dni,
                ];
            });
        
        return [
            'conductors' => $availableConductors,
            'helpers' => $availableHelpers,
        ];
    }

    /**
     * Endpoint para obtener personal disponible
     */
    public function getAvailablePersonnel(Request $request)
    {
        $validated = $request->validate([
            'zone_id' => ['required', 'exists:zones,id'],
            'date' => ['required', 'date'],
        ]);

        $zoneId = $validated['zone_id'];
        $date = $validated['date'];
        
        $available = $this->getAvailablePersonnelForZone($zoneId, $date);
        
        return response()->json([
            'ok' => true,
            'data' => $available,
        ]);
    }

    /**
     * Detectar personal faltante en un recorrido
     */
    public function detectMissingPersonnel(Request $request, Run $run)
    {
        $date = $run->run_date->toDateString();
        $missingPersonnel = [];
        
        // Obtener personal asignado
        $assignedPersonnel = $run->personnel()->with('employee')->get();
        
        foreach ($assignedPersonnel as $personnel) {
            if (!$personnel->employee) {
                continue;
            }
            
            $employee = $personnel->employee;
            
            // Verificar asistencia
            $hasAttendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('attendance_date', $date)
                ->where('status', Attendance::STATUS_PRESENT)
                ->where('period', Attendance::PERIOD_IN)
                ->exists();
            
            if (!$hasAttendance) {
                // Obtener personal disponible para reemplazo
                $availableReplacements = $this->getAvailablePersonnelForZone($run->zone_id, $date);
                
                $roleType = $personnel->role === 'conductor' ? 'conductors' : 'helpers';
                $replacements = $availableReplacements[$roleType] ?? [];
                
                $missingPersonnel[] = [
                    'personnel_id' => $personnel->id,
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                    'dni' => $employee->dni,
                    'role' => $personnel->role,
                    'role_label' => $personnel->role === 'conductor' ? 'Conductor' : 
                                   ($personnel->role === 'ayudante1' ? 'Ayudante 1' : 'Ayudante 2'),
                    'reason' => 'No tiene asistencia registrada para esta fecha',
                    'available_replacements' => $replacements,
                ];
            }
        }
        
        return response()->json([
            'ok' => true,
            'data' => [
                'run_id' => $run->id,
                'run_date' => $date,
                'zone_name' => $run->zone->name ?? 'N/A',
                'missing_personnel' => $missingPersonnel,
                'total_missing' => count($missingPersonnel),
            ],
        ]);
    }

    /**
     * Reemplazar personal faltante
     */
    public function replaceMissingPersonnel(Request $request)
    {
        $validated = $request->validate([
            'run_id' => ['required', 'exists:runs,id'],
            'replacements' => ['required', 'array'],
            'replacements.*.personnel_id' => ['required', 'exists:run_personnel,id'],
            'replacements.*.new_employee_id' => ['required', 'exists:employees,id'],
            'replacements.*.reason_id' => ['nullable', 'exists:change_reasons,id'],
            'replacements.*.custom_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $run = Run::findOrFail($validated['run_id']);
        $date = $run->run_date->toDateString();

        DB::beginTransaction();
        try {
            $replacedCount = 0;
            $errors = [];

            foreach ($validated['replacements'] as $replacement) {
                $personnel = RunPersonnel::find($replacement['personnel_id']);
                
                if (!$personnel || $personnel->run_id != $run->id) {
                    $errors[] = "Registro de personal no válido";
                    continue;
                }

                $newEmployee = Employee::find($replacement['new_employee_id']);
                if (!$newEmployee) {
                    $errors[] = "Empleado no encontrado";
                    continue;
                }

                // Validar que el nuevo empleado no esté ya asignado en este recorrido
                $alreadyAssigned = RunPersonnel::where('run_id', $run->id)
                    ->where('staff_id', $newEmployee->id)
                    ->where('id', '!=', $personnel->id)
                    ->exists();

                if ($alreadyAssigned) {
                    $errors[] = "El empleado {$newEmployee->first_name} {$newEmployee->last_name} ya está asignado a este recorrido";
                    continue;
                }

                // Validar disponibilidad del nuevo empleado
                $availability = $newEmployee->isAvailableForScheduling(
                    $run->zone_id,
                    $date,
                    $run->id
                );

                if (!$availability['available']) {
                    $errors[] = "El empleado {$newEmployee->first_name} {$newEmployee->last_name} no está disponible: " . implode(', ', $availability['issues']);
                    continue;
                }

                // Validar asistencia del nuevo empleado
                $hasAttendance = Attendance::where('employee_id', $newEmployee->id)
                    ->whereDate('attendance_date', $date)
                    ->where('status', Attendance::STATUS_PRESENT)
                    ->where('period', Attendance::PERIOD_IN)
                    ->exists();

                if (!$hasAttendance) {
                    $errors[] = "El empleado {$newEmployee->first_name} {$newEmployee->last_name} no tiene asistencia registrada para esta fecha";
                    continue;
                }

                // Obtener motivo del cambio
                $reason = '';
                if (!empty($replacement['reason_id'])) {
                    $changeReason = \App\Models\ChangeReason::find($replacement['reason_id']);
                    if ($changeReason) {
                        $reason = $changeReason->name;
                        if ($changeReason->description) {
                            $reason .= ' - ' . $changeReason->description;
                        }
                    }
                } else {
                    $reason = $replacement['custom_reason'] ?? 'Reemplazo por ausencia de personal';
                }

                // Obtener empleado anterior
                $oldEmployee = Employee::find($personnel->staff_id);
                
                // Actualizar asignación
                $personnel->staff_id = $newEmployee->id;
                if ($newEmployee->function_id) {
                    $personnel->function_id = $newEmployee->function_id;
                }
                $personnel->save();

                // Registrar cambio en historial
                RunChange::create([
                    'run_id' => $run->id,
                    'change_type' => 'personal',
                    'old_value' => $oldEmployee ? 
                        ($personnel->role === 'conductor' ? 'Conductor: ' : 
                         ($personnel->role === 'ayudante1' ? 'Ayudante 1: ' : 'Ayudante 2: ')) . 
                        $oldEmployee->first_name . ' ' . $oldEmployee->last_name : 
                        'Sin asignar',
                    'new_value' => ($personnel->role === 'conductor' ? 'Conductor: ' : 
                                   ($personnel->role === 'ayudante1' ? 'Ayudante 1: ' : 'Ayudante 2: ')) . 
                                  $newEmployee->first_name . ' ' . $newEmployee->last_name,
                    'notes' => $reason,
                ]);

                $replacedCount++;
            }

            if (!empty($errors)) {
                DB::rollBack();
                return response()->json([
                    'ok' => false,
                    'msg' => 'Algunos reemplazos no pudieron realizarse',
                    'errors' => $errors,
                    'replaced_count' => $replacedCount,
                ], 422);
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'msg' => "Se reemplazaron {$replacedCount} miembro(s) del personal correctamente",
                'replaced_count' => $replacedCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al reemplazar personal faltante: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'msg' => 'Error al reemplazar el personal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar asignación de personal en una zona
     */
    public function updatePersonnelAssignment(Request $request)
    {
        $validated = $request->validate([
            'run_id' => ['required', 'exists:runs,id'],
            'role' => ['required', 'in:conductor,ayudante1,ayudante2'],
            'employee_id' => ['required', 'exists:employees,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $run = Run::findOrFail($validated['run_id']);
        $date = $run->run_date->toDateString();
        
        // Verificar que el empleado esté disponible
        $available = $this->getAvailablePersonnelForZone($run->zone_id, $date);
        
        $isAvailable = false;
        if ($validated['role'] === 'conductor') {
            $isAvailable = $available['conductors']->contains('id', $validated['employee_id']);
        } else {
            $isAvailable = $available['helpers']->contains('id', $validated['employee_id']);
        }
        
        if (!$isAvailable) {
            return response()->json([
                'ok' => false,
                'msg' => 'El empleado seleccionado no está disponible (no tiene asistencia o ya está asignado)'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Buscar o crear registro de personal
            $personnel = RunPersonnel::where('run_id', $run->id)
                ->where('role', $validated['role'])
                ->first();
            
            $oldEmployeeId = null;
            if ($personnel) {
                $oldEmployeeId = $personnel->staff_id;
                $personnel->staff_id = $validated['employee_id'];
                
                // Actualizar function_id
                $employee = Employee::find($validated['employee_id']);
                if ($employee && $employee->function_id) {
                    $personnel->function_id = $employee->function_id;
                }
                $personnel->save();
            } else {
                // Crear nuevo registro
                $employee = Employee::find($validated['employee_id']);
                $functionId = $employee->function_id ?? 1;
                
                $personnel = RunPersonnel::create([
                    'run_id' => $run->id,
                    'staff_id' => $validated['employee_id'],
                    'function_id' => $functionId,
                    'role' => $validated['role'],
                ]);
            }
            
            // Registrar cambio en historial
            $oldEmployee = $oldEmployeeId ? Employee::find($oldEmployeeId) : null;
            $newEmployee = Employee::find($validated['employee_id']);
            
            RunChange::create([
                'run_id' => $run->id,
                'change_type' => 'personal',
                'old_value' => $oldEmployee ? ($oldEmployee->first_name . ' ' . $oldEmployee->last_name) : 'Sin asignar',
                'new_value' => $newEmployee->first_name . ' ' . $newEmployee->last_name,
                'notes' => $validated['reason'] ?? 'Cambio desde dashboard por ausencia de personal',
            ]);
            
            DB::commit();
            
            return response()->json([
                'ok' => true,
                'msg' => 'Asignación actualizada correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar asignación desde dashboard: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'msg' => 'Error al actualizar la asignación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos de un run para editar desde el dashboard
     */
    public function getRunForEdit(Request $request, Run $run)
    {
        $run->load(['zone', 'shift', 'vehicle', 'personnel.employee', 'changes']);
        
        // Obtener personal actual
        $currentPersonnel = [];
        foreach ($run->personnel as $personnel) {
            if ($personnel->employee) {
                $roleLabel = '';
                if ($personnel->role === 'conductor') {
                    $roleLabel = 'Conductor';
                } elseif ($personnel->role === 'ayudante1') {
                    $roleLabel = 'Ayudante 1';
                } elseif ($personnel->role === 'ayudante2') {
                    $roleLabel = 'Ayudante 2';
                } else {
                    $roleLabel = ucfirst($personnel->role);
                }
                
                $currentPersonnel[] = [
                    'id' => $personnel->employee->id,
                    'name' => $personnel->employee->first_name . ' ' . $personnel->employee->last_name,
                    'dni' => $personnel->employee->dni,
                    'role' => $personnel->role,
                    'role_label' => $roleLabel,
                ];
            }
        }
        
        // Obtener cambios registrados
        $changes = $run->changes->map(function($change) {
            return [
                'id' => $change->id,
                'type' => $change->change_type,
                'old_value' => $change->old_value,
                'new_value' => $change->new_value,
                'notes' => $change->notes,
            ];
        });
        
        return response()->json([
            'ok' => true,
            'data' => [
                'id' => $run->id,
                'run_date' => $run->run_date->format('Y-m-d'),
                'zone_name' => $run->zone ? $run->zone->name : 'N/A',
                'current_shift' => [
                    'id' => $run->shift_id,
                    'name' => $run->shift ? $run->shift->name : 'N/A',
                ],
                'current_vehicle' => [
                    'id' => $run->vehicle_id,
                    'code' => $run->vehicle ? ($run->vehicle->code ?? $run->vehicle->name ?? 'N/A') : 'N/A',
                ],
                'current_personnel' => $currentPersonnel,
                'changes' => $changes,
            ]
        ]);
    }

    /**
     * Obtener opciones disponibles para editar (turnos, vehículos, personal disponible)
     */
    public function getEditOptions(Request $request)
    {
        try {
            $validated = $request->validate([
                'date' => ['required', 'date'],
                'run_id' => ['nullable', 'integer', 'exists:runs,id'],
            ]);

            $date = Carbon::parse($validated['date'])->toDateString();
            $runId = !empty($validated['run_id']) ? (int)$validated['run_id'] : null;

            // Obtener turnos activos
            $shifts = Shift::where('active', true)
                ->orderBy('name')
                ->get(['id', 'name']);

            // Obtener vehículos activos (status = 1)
            $vehicles = Vehicle::where('status', 1)
                ->orderBy('code')
                ->get(['id', 'code', 'name']);

            // Obtener personal disponible (no asignado en otros runs del día y con asistencia)
            // Cuando hay runId, excluimos solo los empleados asignados en otros runs (no el actual)
            // Esto permite cambiar roles dentro del mismo run
            $assignedEmployeeIds = [];
            if ($runId) {
                // Obtener IDs de empleados asignados en otros runs del mismo día (excluyendo el run actual)
                $assignedEmployeeIds = RunPersonnel::whereHas('run', function($query) use ($date, $runId) {
                    $query->whereDate('run_date', $date)
                          ->where('id', '!=', $runId); // Excluir el run actual
                })->pluck('staff_id')->unique()->toArray();
            } else {
                // Si no hay runId, excluir todos los asignados en el día
                $assignedEmployeeIds = RunPersonnel::whereHas('run', function($query) use ($date) {
                    $query->whereDate('run_date', $date);
                })->pluck('staff_id')->unique()->toArray();
            }

            // Obtener empleados con asistencia presente
            $attendedEmployeeIds = Attendance::whereDate('attendance_date', $date)
                ->where('status', Attendance::STATUS_PRESENT)
                ->where('period', Attendance::PERIOD_IN)
                ->pluck('employee_id')
                ->unique()
                ->toArray();

            // Si no hay empleados con asistencia, devolver array vacío pero con estructura correcta
            if (empty($attendedEmployeeIds)) {
                $availableEmployees = collect([]);
            } else {
                // Obtener empleados activos con asistencia y no asignados en otros runs
                $query = Employee::where('active', true)
                    ->whereIn('id', $attendedEmployeeIds);
                
                // Solo excluir si hay IDs asignados
                if (!empty($assignedEmployeeIds)) {
                    $query->whereNotIn('id', $assignedEmployeeIds);
                }
                
                $availableEmployees = $query->orderBy('last_name')
                    ->orderBy('first_name')
                    ->get(['id', 'dni', 'first_name', 'last_name', 'function_id'])
                    ->map(function($emp) {
                        return [
                            'id' => $emp->id,
                            'name' => $emp->first_name . ' ' . $emp->last_name,
                            'dni' => $emp->dni,
                            'is_conductor' => $emp->function_id == 1, // Asumiendo que function_id 1 es conductor
                        ];
                    });
            }

            // Log para debugging (puede removerse después)
            \Log::info('getEditOptions - Fecha: ' . $date . ', RunId: ' . ($runId ?? 'null'));
            \Log::info('getEditOptions - Empleados con asistencia: ' . count($attendedEmployeeIds));
            \Log::info('getEditOptions - Empleados asignados (excluir): ' . count($assignedEmployeeIds));
            \Log::info('getEditOptions - Empleados disponibles: ' . $availableEmployees->count());

            return response()->json([
                'ok' => true,
                'data' => [
                    'shifts' => $shifts,
                    'vehicles' => $vehicles,
                    'available_personnel' => $availableEmployees->values(), // Asegurar que sea un array indexado
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en getEditOptions: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'ok' => false,
                'msg' => 'Error al cargar las opciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aplicar cambios a un run desde el dashboard
     */
    public function applyRunChanges(Request $request, Run $run)
    {
        $validated = $request->validate([
            'changes' => ['nullable', 'array'],
            'changes.*.type' => ['required_without:changes.*.id', 'in:turno,vehiculo,personal'],
            'changes.*.old_value' => ['nullable', 'string', 'max:255'],
            'changes.*.new_value' => ['nullable', 'string', 'max:255'],
            'changes.*.new_id' => ['nullable', 'integer'],
            'changes.*.old_id' => ['nullable', 'integer'],
            'changes.*.notes' => ['nullable', 'string'],
            'changes.*.role' => ['nullable', 'in:conductor,ayudante1,ayudante2'],
            'delete_ids' => ['nullable', 'array'],
            'delete_ids.*' => ['integer', 'exists:run_changes,id'],
        ]);

        DB::beginTransaction();
        try {
            // Eliminar cambios marcados para eliminar
            if (!empty($validated['delete_ids'])) {
                RunChange::whereIn('id', $validated['delete_ids'])->delete();
            }

            // Procesar cambios
            if (!empty($validated['changes'])) {
                foreach ($validated['changes'] as $changeData) {
                    // Crear nuevo cambio
                    $change = RunChange::create([
                        'run_id' => $run->id,
                        'change_type' => $changeData['type'],
                        'old_value' => $changeData['old_value'] ?? '',
                        'new_value' => $changeData['new_value'] ?? '',
                        'notes' => $changeData['notes'] ?? '',
                    ]);

                    // Aplicar el cambio al run según el tipo
                    if ($changeData['type'] === 'turno' && !empty($changeData['new_id'])) {
                        $run->shift_id = $changeData['new_id'];
                        $run->save();
                    } elseif ($changeData['type'] === 'vehiculo' && !empty($changeData['new_id'])) {
                        $run->vehicle_id = $changeData['new_id'];
                        $run->save();
                    } elseif ($changeData['type'] === 'personal' && !empty($changeData['new_id']) && !empty($changeData['role'])) {
                        // Buscar o crear registro de personal
                        $runPersonnel = RunPersonnel::where('run_id', $run->id)
                            ->where('role', $changeData['role'])
                            ->first();
                        
                        $oldEmployeeId = null;
                        if ($runPersonnel) {
                            $oldEmployeeId = $runPersonnel->staff_id;
                            $runPersonnel->staff_id = $changeData['new_id'];
                            
                            $employee = Employee::find($changeData['new_id']);
                            if ($employee && $employee->function_id) {
                                $runPersonnel->function_id = $employee->function_id;
                            }
                            $runPersonnel->save();
                        } else {
                            $employee = Employee::find($changeData['new_id']);
                            $functionId = $employee->function_id ?? 1;
                            
                            RunPersonnel::create([
                                'run_id' => $run->id,
                                'staff_id' => $changeData['new_id'],
                                'function_id' => $functionId,
                                'role' => $changeData['role'],
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'msg' => 'Cambios aplicados correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al aplicar cambios desde dashboard: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'msg' => 'Error al aplicar los cambios: ' . $e->getMessage()
            ], 500);
        }
    }
}

