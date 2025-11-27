<?php
namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Run;
use App\Models\PersonnelGroup;
use App\Models\Zone;
use App\Models\Vehicle;
use App\Models\Shift;
use App\Models\Employee;
use App\Models\FunctionModel;
use App\Models\RunPersonnel;
use App\Models\RunChange;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProgramController extends Controller
{
    public function massive()
    {
        return view('programs.create');
    }

    /**
     * Obtener empleados disponibles para programación
     * Filtrados por rol, con validación de contratos y vacaciones
     */
    public function getAvailableEmployees(Request $request)
    {
        $validated = $request->validate([
            'role' => ['required', 'in:conductor,ayudante'],
            'zone_id' => ['required', 'exists:zones,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'exclude_run_id' => ['nullable', 'integer', 'exists:runs,id'],
        ]);

        $role = $validated['role'];
        $zoneId = $validated['zone_id'];
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = $validated['end_date'] ? Carbon::parse($validated['end_date']) : $startDate;
        $excludeRunId = $validated['exclude_run_id'] ?? null;

        // Filtrar por rol
        $query = Employee::where('active', true);
        if ($role === 'conductor') {
            $query->conductors();
        } else {
            $query->helpers();
        }

        $employees = $query->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Filtrar por disponibilidad
        $availableEmployees = [];
        $current = $startDate->copy();
        
        while ($current->lte($endDate)) {
            foreach ($employees as $employee) {
                $availability = $employee->isAvailableForScheduling(
                    $zoneId,
                    $current->toDateString(),
                    $excludeRunId
                );

                if ($availability['available']) {
                    $key = $employee->id;
                    if (!isset($availableEmployees[$key])) {
                        $availableEmployees[$key] = [
                            'id' => $employee->id,
                            'dni' => $employee->dni,
                            'first_name' => $employee->first_name,
                            'last_name' => $employee->last_name,
                            'full_name' => $employee->first_name . ' ' . $employee->last_name,
                            'function_id' => $employee->function_id,
                        ];
                    }
                }
            }
            $current->addDay();
        }

        return response()->json(array_values($availableEmployees));
    }

    /**
     * Mostrar formulario de actualización masiva
     */
    public function bulkUpdateForm()
    {
        $zones = Zone::where('active', true)->orderBy('name')->get(['id', 'name']);
        $shifts = Shift::where('active', true)->orderBy('name')->get(['id', 'name']);
        $vehicles = Vehicle::where('status', true)->orderBy('code')->get(['id', 'code', 'name']);
        $employees = Employee::where('active', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'dni', 'first_name', 'last_name', 'function_id']);
        $changeReasons = \App\Models\ChangeReason::where('active', true)
            ->orderBy('is_predefined', 'desc')
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'is_predefined']);
        
        return view('programs.bulk-update', compact('zones', 'shifts', 'vehicles', 'employees', 'changeReasons'));
    }

    /**
     * Procesar actualización masiva de programación
     */
    public function bulkUpdate(Request $request)
    {
        $rules = [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'zone_id' => ['required', 'exists:zones,id'],
            'change_type' => ['required', 'in:conductor,ocupante,turno,vehiculo'],
            'new_value_id' => ['required', 'integer'],
            'ocupante_role' => ['required_if:change_type,ocupante', 'in:ayudante1,ayudante2'],
            'change_reason_id' => ['nullable', 'exists:change_reasons,id'],
            'reason' => ['required_without:change_reason_id', 'string', 'max:500'],
        ];

        // Validar que el new_value_id existe según el tipo de cambio
        $changeType = $request->input('change_type');
        if ($changeType === 'conductor' || $changeType === 'ocupante') {
            $rules['new_value_id'][] = 'exists:employees,id';
        } elseif ($changeType === 'turno') {
            $rules['new_value_id'][] = 'exists:shifts,id';
        } elseif ($changeType === 'vehiculo') {
            $rules['new_value_id'][] = 'exists:vehicles,id';
        }

        try {
        $validated = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Errores de validación en actualización masiva:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'ok' => false,
                'msg' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
        }

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $zoneId = $validated['zone_id'];
        $changeType = $validated['change_type'];
        $newValueId = $validated['new_value_id'];
        
        // Obtener motivo del cambio (predefinido o personalizado)
        $reason = '';
        if (!empty($validated['change_reason_id'])) {
            $changeReason = \App\Models\ChangeReason::find($validated['change_reason_id']);
            if ($changeReason) {
                $reason = $changeReason->name;
                if ($changeReason->description) {
                    $reason .= ' - ' . $changeReason->description;
                }
            }
        } else {
            $reason = $validated['reason'] ?? '';
        }

        // Buscar todos los runs que cumplan las condiciones
        $runs = Run::where('zone_id', $zoneId)
            ->whereDate('run_date', '>=', $startDate->toDateString())
            ->whereDate('run_date', '<=', $endDate->toDateString())
            ->with(['zone', 'shift', 'vehicle', 'personnel'])
            ->get();

        if ($runs->isEmpty()) {
            return response()->json([
                'ok' => false,
                'msg' => 'No se encontraron programaciones que cumplan los criterios seleccionados.'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $updatedCount = 0;
            $errors = [];

            foreach ($runs as $run) {
                try {
                    $oldValue = null;
                    $newValue = null;
                    $changeTypeForHistory = null;

                    switch ($changeType) {
                        case 'conductor':
                            // Validar disponibilidad del nuevo conductor
                            $newEmployee = Employee::find($newValueId);
                            if (!$newEmployee) {
                                throw new \Exception("Empleado no encontrado");
                            }
                            
                            // Validar disponibilidad (contrato, vacaciones, duplicados)
                            $availability = $newEmployee->isAvailableForScheduling(
                                $run->zone_id,
                                $run->run_date->toDateString(),
                                $run->id
                            );
                            
                            if (!$availability['available']) {
                                throw new \Exception("Empleado no disponible: " . implode(', ', $availability['issues']));
                            }
                            
                            // Validar asistencia registrada
                            $hasAttendance = \App\Models\Attendance::where('employee_id', $newValueId)
                                ->whereDate('attendance_date', $run->run_date->toDateString())
                                ->where('status', \App\Models\Attendance::STATUS_PRESENT)
                                ->where('period', \App\Models\Attendance::PERIOD_IN)
                                ->exists();
                            
                            if (!$hasAttendance) {
                                throw new \Exception("El empleado no tiene asistencia registrada para esta fecha. No se puede asignar sin asistencia.");
                            }
                            
                            // Actualizar o crear conductor en run_personnel
                            $oldPersonnel = RunPersonnel::where('run_id', $run->id)
                                ->where('role', 'conductor')
                                ->first();
                            
                            if ($oldPersonnel) {
                                $oldEmployee = Employee::find($oldPersonnel->staff_id);
                                $oldValue = $oldEmployee ? ('Conductor: ' . $oldEmployee->first_name . ' ' . $oldEmployee->last_name) : 'Conductor: N/A';
                                $oldPersonnel->staff_id = $newValueId;
                            } else {
                                // Crear nuevo registro
                                $oldValue = 'Conductor: Sin asignar';
                                $oldPersonnel = new RunPersonnel();
                                $oldPersonnel->run_id = $run->id;
                                $oldPersonnel->role = 'conductor';
                                $oldPersonnel->staff_id = $newValueId;
                            }
                            
                            // Obtener function_id del nuevo empleado
                            if ($newEmployee->function_id) {
                                $oldPersonnel->function_id = $newEmployee->function_id;
                            } else {
                                $conductorFunction = FunctionModel::where('name', 'Conductor')->first();
                                $oldPersonnel->function_id = $conductorFunction ? $conductorFunction->id : 1;
                            }
                            $oldPersonnel->save();
                            
                            $newValue = 'Conductor: ' . $newEmployee->first_name . ' ' . $newEmployee->last_name;
                            $changeTypeForHistory = 'personal';
                            break;

                        case 'ocupante':
                            // Validar disponibilidad del nuevo ocupante
                            $newEmployee = Employee::find($newValueId);
                            if (!$newEmployee) {
                                throw new \Exception("Empleado no encontrado");
                            }
                            
                            // Validar disponibilidad (contrato, vacaciones, duplicados)
                            $availability = $newEmployee->isAvailableForScheduling(
                                $run->zone_id,
                                $run->run_date->toDateString(),
                                $run->id
                            );
                            
                            if (!$availability['available']) {
                                throw new \Exception("Empleado no disponible: " . implode(', ', $availability['issues']));
                            }
                            
                            // Validar asistencia registrada
                            $hasAttendance = \App\Models\Attendance::where('employee_id', $newValueId)
                                ->whereDate('attendance_date', $run->run_date->toDateString())
                                ->where('status', \App\Models\Attendance::STATUS_PRESENT)
                                ->where('period', \App\Models\Attendance::PERIOD_IN)
                                ->exists();
                            
                            if (!$hasAttendance) {
                                throw new \Exception("El empleado no tiene asistencia registrada para esta fecha. No se puede asignar sin asistencia.");
                            }
                            
                            // Actualizar ocupante (ayudante1 o ayudante2)
                            $role = $validated['ocupante_role'];
                            $roleLabel = $role === 'ayudante1' ? 'Ayudante 1' : 'Ayudante 2';
                            
                            $oldPersonnel = RunPersonnel::where('run_id', $run->id)
                                ->where('role', $role)
                                ->first();
                            
                            if ($oldPersonnel) {
                                $oldEmployee = Employee::find($oldPersonnel->staff_id);
                                $oldValue = $oldEmployee ? ($roleLabel . ': ' . $oldEmployee->first_name . ' ' . $oldEmployee->last_name) : ($roleLabel . ': N/A');
                                $oldPersonnel->staff_id = $newValueId;
                            } else {
                                $oldValue = $roleLabel . ': Sin asignar';
                                $oldPersonnel = new RunPersonnel();
                                $oldPersonnel->run_id = $run->id;
                                $oldPersonnel->role = $role;
                                $oldPersonnel->staff_id = $newValueId;
                            }
                            
                            // Obtener function_id del nuevo empleado
                            if ($newEmployee->function_id) {
                                $oldPersonnel->function_id = $newEmployee->function_id;
                            } else {
                                $ayudanteFunction = FunctionModel::where('name', 'Ayudante')->first();
                                $oldPersonnel->function_id = $ayudanteFunction ? $ayudanteFunction->id : 2;
                            }
                            $oldPersonnel->save();
                            
                            $newValue = $roleLabel . ': ' . $newEmployee->first_name . ' ' . $newEmployee->last_name;
                            $changeTypeForHistory = 'personal';
                            break;

                        case 'turno':
                            // Actualizar turno
                            $oldShift = Shift::find($run->shift_id);
                            $oldValue = $oldShift ? ('Turno: ' . $oldShift->name) : 'Turno: N/A';
                            
                            $run->shift_id = $newValueId;
                            $run->save();
                            
                            $newShift = Shift::find($newValueId);
                            $newValue = $newShift ? ('Turno: ' . $newShift->name) : 'Turno: N/A';
                            $changeTypeForHistory = 'turno';
                            break;

                        case 'vehiculo':
                            // Actualizar vehículo
                            $oldVehicle = Vehicle::find($run->vehicle_id);
                            $oldVehicleCode = $oldVehicle ? ($oldVehicle->code ?? $oldVehicle->name ?? 'N/A') : 'N/A';
                            $oldValue = 'Vehículo: ' . $oldVehicleCode;
                            
                            $run->vehicle_id = $newValueId;
                            $run->save();
                            
                            $newVehicle = Vehicle::find($newValueId);
                            $newVehicleCode = $newVehicle ? ($newVehicle->code ?? $newVehicle->name ?? 'N/A') : 'N/A';
                            $newValue = 'Vehículo: ' . $newVehicleCode;
                            $changeTypeForHistory = 'vehiculo';
                            break;
                    }

                    // Registrar cambio en historial
                    RunChange::create([
                        'run_id' => $run->id,
                        'change_type' => $changeTypeForHistory,
                        'old_value' => $oldValue,
                        'new_value' => $newValue,
                        'notes' => $reason,
                    ]);

                    $updatedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Error en run ID {$run->id}: " . $e->getMessage();
                    \Log::error("Error al actualizar run {$run->id} en actualización masiva: " . $e->getMessage());
                }
            }

            if ($updatedCount === 0) {
                DB::rollBack();
                return response()->json([
                    'ok' => false,
                    'msg' => 'No se pudo actualizar ninguna programación. Errores: ' . implode(', ', $errors)
                ], 500);
            }

            DB::commit();

            $message = "Se actualizaron {$updatedCount} programación(es) correctamente.";
            if (!empty($errors)) {
                $message .= " Se encontraron " . count($errors) . " error(es).";
            }

            return response()->json([
                'ok' => true,
                'msg' => $message,
                'updated_count' => $updatedCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en actualización masiva: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'msg' => 'Error al procesar la actualización masiva: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getActiveGroups(Request $request)
    {
        $shiftId = $request->input('shift_id');
        
        $query = PersonnelGroup::with(['zone', 'shift', 'vehicle', 'driver', 'helper1', 'helper2'])
            ->where('active', true);
        
        if ($shiftId) {
            $query->where('shift_id', $shiftId);
        }
        
        $groups = $query->orderBy('name')->get();
        
        return response()->json($groups->map(function($group) {
            $daysMap = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
            $days = $group->days_of_week;
            $daysDisplay = 'N/A';
            if (!empty($days) && is_array($days)) {
                $daysDisplay = implode(', ', array_map(function($d) use ($daysMap) {
                    return $daysMap[$d] ?? '';
                }, $days));
            }
            
            $vehicleCode = 'N/A';
            $vehicleCapacity = 0;
            if ($group->vehicle) {
                $vehicleCode = $group->vehicle->code ?? $group->vehicle->name ?? 'N/A';
                $vehicleCapacity = $group->vehicle->people_capacity ?? $group->vehicle->capacity ?? 0;
            }
            
            return [
                'id' => $group->id,
                'name' => strtoupper($group->name ?? 'N/A'),
                'zone_id' => $group->zone_id,
                'zone_name' => $group->zone ? $group->zone->name : 'N/A',
                'shift_id' => $group->shift_id,
                'shift_name' => $group->shift ? strtoupper($group->shift->name) : 'N/A',
                'vehicle_id' => $group->vehicle_id,
                'vehicle_code' => $vehicleCode,
                'vehicle_capacity' => $vehicleCapacity,
                'days_of_week' => $days,
                'days_display' => $daysDisplay,
                'driver_id' => $group->driver_id,
                'driver_name' => $group->driver ? ($group->driver->first_name . ' ' . $group->driver->last_name) : null,
                'helper1_id' => $group->helper1_id,
                'helper1_name' => $group->helper1 ? ($group->helper1->first_name . ' ' . $group->helper1->last_name) : null,
                'helper2_id' => $group->helper2_id,
                'helper2_name' => $group->helper2 ? ($group->helper2->first_name . ' ' . $group->helper2->last_name) : null,
            ];
        }));
    }

    public function validateAvailability(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'group_ids' => ['required', 'array'],
            'group_ids.*' => ['exists:personnel_groups,id'],
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = $validated['end_date'] ? Carbon::parse($validated['end_date']) : $startDate;
        
        $groupsValidation = [];
        
        foreach ($validated['group_ids'] as $groupId) {
            $group = PersonnelGroup::with(['driver', 'helper1', 'helper2'])->findOrFail($groupId);
            $daysOfWeek = $group->days_of_week;
            
            $groupConflicts = [];
            $conflictDates = [];
            $vacationIssues = [];
            
            // Verificar vacaciones del personal del grupo
            $personnelToCheck = [];
            if ($group->driver_id) {
                $personnelToCheck[] = ['id' => $group->driver_id, 'role' => 'Conductor', 'employee' => $group->driver];
            }
            if ($group->helper1_id) {
                $personnelToCheck[] = ['id' => $group->helper1_id, 'role' => 'Ayudante 1', 'employee' => $group->helper1];
            }
            if ($group->helper2_id) {
                $personnelToCheck[] = ['id' => $group->helper2_id, 'role' => 'Ayudante 2', 'employee' => $group->helper2];
            }
            
            // Verificar disponibilidad completa del personal (contrato, vacaciones, duplicados)
            // Verificar día por día en los días programados
            $currentCheck = $startDate->copy();
            $checkedEmployees = [];
            $personnelAvailabilityIssues = [];
            
            while ($currentCheck->lte($endDate)) {
                $carbonDay = $currentCheck->dayOfWeek;
                $dayNumber = $carbonDay == 0 ? 7 : $carbonDay;
                
                // Solo verificar en los días de la semana programados
                if (in_array($dayNumber, $daysOfWeek, true)) {
                    foreach ($personnelToCheck as $personnel) {
                        if ($personnel['employee']) {
                            $employee = $personnel['employee'];
                            $employeeKey = $personnel['role'] . '_' . $employee->id . '_' . $currentCheck->format('Y-m-d');
                            
                            // Verificar disponibilidad completa usando isAvailableForScheduling
                            $availability = $employee->isAvailableForScheduling(
                                $group->zone_id,
                                $currentCheck->toDateString()
                            );
                            
                            if (!$availability['available']) {
                                // Solo agregar una vez por empleado y día
                                if (!isset($checkedEmployees[$employeeKey])) {
                                    $issues = $availability['issues'];
                                    
                                    // Si el problema es vacaciones, agregar a vacation_issues con detalles
                                    $hasVacationIssue = false;
                                    foreach ($issues as $issue) {
                                        if (strpos($issue, 'vacaciones') !== false) {
                                            $hasVacationIssue = true;
                                            // Obtener detalles de vacaciones
                                            $vacations = $employee->getVacationsInRange(
                                                $startDate->toDateString(),
                                                $endDate->toDateString()
                                            );
                                            
                                            foreach ($vacations as $vacation) {
                                                $vacationIssues[] = [
                                                    'employee_id' => $employee->id,
                                                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                                                    'role' => $personnel['role'],
                                                    'vacation_start' => $vacation['start_date_formatted'],
                                                    'vacation_end' => $vacation['end_date_formatted'],
                                                    'vacation_days' => $vacation['days'],
                                                    'message' => "{$personnel['role']}: {$employee->first_name} {$employee->last_name} tiene vacaciones del {$vacation['start_date_formatted']} al {$vacation['end_date_formatted']} ({$vacation['days']} días) - No puede estar disponible"
                                                ];
                                            }
                                        }
                                    }
                                    
                                    // Si hay otros problemas además de vacaciones, agregarlos a personnelAvailabilityIssues
                                    $otherIssues = array_filter($issues, function($issue) {
                                        return strpos($issue, 'vacaciones') === false;
                                    });
                                    
                                    if (!empty($otherIssues)) {
                                        $personnelAvailabilityIssues[] = [
                                            'employee_id' => $employee->id,
                                            'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                                            'role' => $personnel['role'],
                                            'date' => $currentCheck->format('d/m/Y'),
                                            'issues' => $otherIssues,
                                            'message' => "{$personnel['role']}: {$employee->first_name} {$employee->last_name} - " . implode(', ', $otherIssues)
                                        ];
                                    }
                                    
                                    $checkedEmployees[$employeeKey] = true;
                                }
                            }
                        }
                    }
                }
                $currentCheck->addDay();
            }
            
            // Verificar conflictos de programación existente
            $current = $startDate->copy();
            while ($current->lte($endDate)) {
                $carbonDay = $current->dayOfWeek;
                $dayNumber = $carbonDay == 0 ? 7 : $carbonDay;
                
                if (in_array($dayNumber, $daysOfWeek, true)) {
                    $existing = Run::where('group_id', $groupId)
                        ->whereDate('run_date', $current->toDateString())
                        ->first();
                    
                    if ($existing) {
                        $dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                        $dayName = $dayNames[$current->dayOfWeek] ?? '';
                        
                        $conflictDates[] = [
                            'date' => $current->format('Y-m-d'),
                            'date_formatted' => $current->format('d/m/Y'),
                            'day_name' => $dayName,
                        ];
                    }
                }
                $current->addDay();
            }
            
            $hasConflicts = !empty($conflictDates) || !empty($vacationIssues) || !empty($personnelAvailabilityIssues);
            
            $groupsValidation[] = [
                'group_id' => $groupId,
                'group_name' => $group->name,
                'has_conflicts' => $hasConflicts,
                'conflict_count' => count($conflictDates),
                'conflict_dates' => $conflictDates,
                'vacation_issues' => $vacationIssues,
                'vacation_issues_count' => count($vacationIssues),
                'personnel_availability_issues' => $personnelAvailabilityIssues,
                'personnel_availability_issues_count' => count($personnelAvailabilityIssues),
                'is_valid' => !$hasConflicts,
            ];
        }
        
        $totalConflicts = array_sum(array_column($groupsValidation, 'conflict_count'));
        $totalVacationIssues = array_sum(array_column($groupsValidation, 'vacation_issues_count'));
        $totalPersonnelIssues = array_sum(array_column($groupsValidation, 'personnel_availability_issues_count'));
        $groupsWithConflicts = array_filter($groupsValidation, fn($g) => $g['has_conflicts']);
        
        $hasAnyIssues = $totalConflicts > 0 || $totalVacationIssues > 0 || $totalPersonnelIssues > 0;
        
        $message = 'No hay conflictos de disponibilidad. Todos los grupos están disponibles en el rango de fechas seleccionado.';
        if ($hasAnyIssues) {
            $messageParts = [];
            if ($totalConflicts > 0) {
                $messageParts[] = "{$totalConflicts} conflicto(s) de programación";
            }
            if ($totalVacationIssues > 0) {
                $messageParts[] = "{$totalVacationIssues} personal con vacaciones";
            }
            if ($totalPersonnelIssues > 0) {
                $messageParts[] = "{$totalPersonnelIssues} problema(s) de disponibilidad del personal";
            }
            $message = "Se encontraron " . implode(', ', $messageParts) . " en " . count($groupsWithConflicts) . " grupo(s).";
        }
        
        return response()->json([
            'ok' => !$hasAnyIssues,
            'groups' => $groupsValidation,
            'total_conflicts' => $totalConflicts,
            'total_vacation_issues' => $totalVacationIssues,
            'total_personnel_availability_issues' => $totalPersonnelIssues,
            'groups_with_conflicts' => count($groupsWithConflicts),
            'message' => $message,
        ]);
    }

    public function storeBulk(Request $request)
    {
        $validated = $request->validate([
            'shift_id' => ['required', 'exists:shifts,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'groups' => ['required', 'array', 'min:1'],
            'groups.*.group_id' => ['required', 'exists:personnel_groups,id'],
            'groups.*.vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'groups.*.conductor_id' => ['nullable', 'exists:employees,id'],
            'groups.*.ayudante1_id' => ['nullable', 'exists:employees,id'],
            'groups.*.ayudante2_id' => ['nullable', 'exists:employees,id'],
        ]);

        if (empty($validated['end_date'])) {
            $validated['end_date'] = $validated['start_date'];
        }

        DB::beginTransaction();
        try {
            $totalCreated = 0;
            $conductorFunction = \App\Models\FunctionModel::where('name', 'Conductor')->first();
            $ayudanteFunction = \App\Models\FunctionModel::where('name', 'Ayudante')->first();

            foreach ($validated['groups'] as $groupData) {
                $group = PersonnelGroup::findOrFail($groupData['group_id']);
                $daysOfWeek = $group->days_of_week;
                
                $start = Carbon::parse($validated['start_date']);
                $end = Carbon::parse($validated['end_date']);
                
                $current = $start->copy();
                while ($current->lte($end)) {
                    $carbonDay = $current->dayOfWeek;
                    $dayNumber = $carbonDay == 0 ? 7 : $carbonDay;
                    
                    if (in_array($dayNumber, $daysOfWeek, true)) {
                        $existing = Run::where('group_id', $group->id)
                            ->whereDate('run_date', $current->toDateString())
                            ->first();
                        
                        if (!$existing) {
                            $newRun = Run::create([
                                'run_date' => $current->toDateString(),
                                'status' => 'Programado',
                                'zone_id' => $group->zone_id,
                                'shift_id' => $validated['shift_id'],
                                'vehicle_id' => $groupData['vehicle_id'] ?? $group->vehicle_id,
                                'group_id' => $group->id,
                            ]);
                            
                            // Asignar personal con validaciones
                            $conductorId = $groupData['conductor_id'] ?? $group->driver_id;
                            $ayudante1Id = $groupData['ayudante1_id'] ?? $group->helper1_id;
                            $ayudante2Id = $groupData['ayudante2_id'] ?? $group->helper2_id;
                            
                            $personnelErrors = [];
                            
                            // Validar y asignar conductor
                            if ($conductorId) {
                                $conductor = Employee::find($conductorId);
                                if ($conductor) {
                                    $availability = $conductor->isAvailableForScheduling(
                                        $newRun->zone_id,
                                        $current->toDateString()
                                    );
                                    
                                    if ($availability['available']) {
                                        $functionId = $conductor->function_id ?? ($conductorFunction ? $conductorFunction->id : 1);
                                        RunPersonnel::create([
                                            'run_id' => $newRun->id,
                                            'staff_id' => $conductorId,
                                            'function_id' => $functionId,
                                            'role' => 'conductor',
                                        ]);
                                    } else {
                                        $personnelErrors[] = "Conductor {$conductor->first_name} {$conductor->last_name}: " . implode(', ', $availability['issues']);
                                    }
                                }
                            }
                            
                            // Validar y asignar ayudante 1
                            if ($ayudante1Id) {
                                $ayudante1 = Employee::find($ayudante1Id);
                                if ($ayudante1) {
                                    $availability = $ayudante1->isAvailableForScheduling(
                                        $newRun->zone_id,
                                        $current->toDateString()
                                    );
                                    
                                    if ($availability['available']) {
                                        $functionId = $ayudante1->function_id ?? ($ayudanteFunction ? $ayudanteFunction->id : 2);
                                        RunPersonnel::create([
                                            'run_id' => $newRun->id,
                                            'staff_id' => $ayudante1Id,
                                            'function_id' => $functionId,
                                            'role' => 'ayudante1',
                                        ]);
                                    } else {
                                        $personnelErrors[] = "Ayudante 1 {$ayudante1->first_name} {$ayudante1->last_name}: " . implode(', ', $availability['issues']);
                                    }
                                }
                            }
                            
                            // Validar y asignar ayudante 2
                            if ($ayudante2Id) {
                                $ayudante2 = Employee::find($ayudante2Id);
                                if ($ayudante2) {
                                    $availability = $ayudante2->isAvailableForScheduling(
                                        $newRun->zone_id,
                                        $current->toDateString()
                                    );
                                    
                                    if ($availability['available']) {
                                        $functionId = $ayudante2->function_id ?? ($ayudanteFunction ? $ayudanteFunction->id : 2);
                                        RunPersonnel::create([
                                            'run_id' => $newRun->id,
                                            'staff_id' => $ayudante2Id,
                                            'function_id' => $functionId,
                                            'role' => 'ayudante2',
                                        ]);
                                    } else {
                                        $personnelErrors[] = "Ayudante 2 {$ayudante2->first_name} {$ayudante2->last_name}: " . implode(', ', $availability['issues']);
                                    }
                                }
                            }
                            
                            // Si hay errores de personal, eliminar el run y lanzar excepción
                            if (!empty($personnelErrors)) {
                                $newRun->delete();
                                throw new \Exception("Error en fecha {$current->format('d/m/Y')}: " . implode(' | ', $personnelErrors));
                            }
                            
                            $totalCreated++;
                        }
                    }
                    $current->addDay();
                }
            }

            DB::commit();
            return response()->json([
                'ok' => true,
                'msg' => "Programación creada correctamente. Se crearon {$totalCreated} recorrido(s)."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear programación masiva: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'msg' => 'Error al crear la programación: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        try {
        $programs = Program::with(['zone.district.province.department','vehicle.brand','vehicle.model','shift'])
            ->latest('id')->paginate(10);

        // Para selects de los modales
        $zones    = Zone::orderBy('name')->get(['id','name']);
        $vehicles = Vehicle::with(['brand','model'])->orderBy('id','desc')->get(['id','name','brand_id','model_id']);
        $shifts   = Shift::orderBy('start_time')->get(['id','name','start_time','end_time']);

            // Obtener grupos de personal activos
            $workgroups = PersonnelGroup::where('active', true)
                ->orderBy('name')
                ->get(['id', 'name']);

            return view('programs.index', compact('programs','zones','vehicles','shifts','workgroups'));
        } catch (\Exception $e) {
            \Log::error('Error en ProgramController@index: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Si hay error, pasar array vacío para workgroups
            $workgroups = collect([]);
            return view('programs.index', compact('programs','zones','vehicles','shifts','workgroups'));
        }
    }

    /**
     * Obtener runs relacionados a un run específico (misma programación)
     */
    public function getRelatedRuns($runId)
    {
        $run = Run::with(['zone', 'shift', 'vehicle', 'group'])->findOrFail($runId);
        
        // Buscar todos los runs relacionados (mismo group_id y fechas cercanas)
        $relatedRuns = Run::with(['zone', 'shift', 'vehicle', 'group'])
            ->where('group_id', $run->group_id)
            ->where(function($query) use ($run) {
                // Fechas dentro de un rango razonable (±45 días del run actual)
                $minDate = Carbon::parse($run->run_date)->subDays(45)->toDateString();
                $maxDate = Carbon::parse($run->run_date)->addDays(45)->toDateString();
                $query->whereBetween('run_date', [$minDate, $maxDate]);
            })
            ->orderBy('run_date', 'asc')
            ->get();
        
        $runsData = $relatedRuns->map(function($r) {
            $statusBadge = '';
            switch($r->status) {
                case 'Programado':
                case 'planned':
                    $statusBadge = '<span class="badge badge-info">Programado</span>';
                    break;
                case 'En Progreso':
                case 'in_progress':
                    $statusBadge = '<span class="badge badge-warning">En Progreso</span>';
                    break;
                case 'Completado':
                case 'done':
                    $statusBadge = '<span class="badge badge-success">Completado</span>';
                    break;
                case 'Cancelado':
                case 'canceled':
                    $statusBadge = '<span class="badge badge-danger">Cancelado</span>';
                    break;
                default:
                    $statusBadge = '<span class="badge badge-secondary">' . htmlspecialchars($r->status) . '</span>';
            }
            
            return [
                'id' => $r->id,
                'date' => $r->run_date->format('d/m/Y'),
                'status' => $statusBadge,
                'zone' => $r->zone ? $r->zone->name : 'N/A',
                'shift' => $r->shift ? $r->shift->name : 'N/A',
                'vehicle' => $r->vehicle ? ($r->vehicle->code ?? $r->vehicle->name) : 'N/A',
                'group' => $r->group ? $r->group->name : 'N/A',
            ];
        });
        
        return response()->json([
            'ok' => true,
            'runs' => $runsData
        ]);
    }

    /**
     * Obtener runs (recorridos) de una programación
     */
    public function getProgramRuns($programId)
    {
        $program = Program::findOrFail($programId);
        
        // Buscar runs que coincidan con los criterios de la programación
        // (zona, turno, vehículo) y estén dentro del rango de fechas
        $runs = Run::with(['zone', 'shift', 'vehicle', 'group'])
            ->where('zone_id', $program->zone_id)
            ->where('shift_id', $program->shift_id)
            ->where('vehicle_id', $program->vehicle_id)
            ->whereDate('run_date', '>=', $program->start_date)
            ->whereDate('run_date', '<=', $program->end_date)
            ->orderBy('run_date', 'asc')
            ->get();
        
        $runsData = $runs->map(function($run) {
            $statusBadge = '';
            switch($run->status) {
                case 'Programado':
                case 'planned':
                    $statusBadge = '<span class="badge badge-info">Programado</span>';
                    break;
                case 'En Progreso':
                case 'in_progress':
                    $statusBadge = '<span class="badge badge-warning">En Progreso</span>';
                    break;
                case 'Completado':
                case 'done':
                    $statusBadge = '<span class="badge badge-success">Completado</span>';
                    break;
                case 'Cancelado':
                case 'canceled':
                    $statusBadge = '<span class="badge badge-danger">Cancelado</span>';
                    break;
                default:
                    $statusBadge = '<span class="badge badge-secondary">' . htmlspecialchars($run->status) . '</span>';
            }
            
            return [
                'id' => $run->id,
                'date' => $run->run_date->format('d/m/Y'),
                'status' => $statusBadge,
                'zone' => $run->zone ? $run->zone->name : 'N/A',
                'shift' => $run->shift ? $run->shift->name : 'N/A',
                'vehicle' => $run->vehicle ? ($run->vehicle->code ?? $run->vehicle->name) : 'N/A',
                'group' => $run->group ? $run->group->name : 'N/A',
            ];
        });
        
        return response()->json([
            'ok' => true,
            'runs' => $runsData
        ]);
    }

    /**
     * Vista simple "En Progreso"
     */
    public function inProgress()
    {
        return view('programs.in-progress');
    }

    /**
     * Vista de Programaciones (Programs)
     */
    public function schedules(Request $request)
    {
        if ($request->ajax()) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $search = $request->input('search.value');
            
            $query = Run::with([
                'zone',
                'shift',
                'vehicle',
                'group'
            ]);
            
            // Filtro por fechas - si no hay fechas, mostrar todas
            if ($startDate) {
                $query->whereDate('run_date', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('run_date', '<=', $endDate);
            }
            
            // Si no hay filtros de fecha, mostrar desde el inicio del mes actual hasta 3 meses en el futuro
            if (!$startDate && !$endDate) {
                $query->whereDate('run_date', '>=', now()->startOfMonth()->toDateString())
                      ->whereDate('run_date', '<=', now()->addMonths(3)->endOfMonth()->toDateString());
            }
            
            // Búsqueda
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->whereHas('zone', function($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('shift', function($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('vehicle', function($subQ) use ($search) {
                        $subQ->where('code', 'like', "%{$search}%")
                             ->orWhere('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('group', function($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%");
                    });
                });
            }
            
            $runs = $query->with(['changes'])->orderBy('run_date', 'asc')->get();
            
            // Agrupar runs por programación (mismo group_id y fechas cercanas)
            // NO exigir shift_id y vehicle_id iguales porque pueden haber sido modificados
            $groupedRuns = [];
            $processedRunIds = [];
            
            foreach ($runs as $run) {
                // Si ya procesamos este run, saltarlo
                if (in_array($run->id, $processedRunIds)) {
                    continue;
                }
                
                // Buscar todos los runs relacionados de la misma programación
                // Agrupar por group_id y fechas cercanas (no por shift_id/vehicle_id que pueden cambiar)
                $relatedRuns = $runs->filter(function($r) use ($run, $processedRunIds) {
                    // No incluir runs ya procesados
                    if (in_array($r->id, $processedRunIds)) {
                        return false;
                    }
                    
                    // Debe tener el mismo group_id (grupo de personal)
                    if ($r->group_id !== $run->group_id) {
                        return false;
                    }
                    
                    // Fechas dentro de un rango razonable (±45 días del run actual)
                    $runDate = Carbon::parse($run->run_date);
                    $rDate = Carbon::parse($r->run_date);
                    $minDate = $runDate->copy()->subDays(45);
                    $maxDate = $runDate->copy()->addDays(45);
                    
                    return $rDate->between($minDate, $maxDate);
                });
                
                // Marcar todos los runs relacionados como procesados
                $relatedRunIds = $relatedRuns->pluck('id')->toArray();
                $processedRunIds = array_merge($processedRunIds, $relatedRunIds);
                
                // Obtener la fecha más temprana del grupo (fecha de inicio de la programación)
                $dates = $relatedRuns->pluck('run_date')->map(function($date) {
                    return Carbon::parse($date);
                })->sort();
                
                $minDate = $dates->first();
                
                // Formato de fecha: mostrar solo la fecha de inicio
                $dateDisplay = $minDate->format('Y-m-d');
                
                // Estado: "Reprogramado" si algún run tiene cambios, "Programado" si no
                $hasChanges = $relatedRuns->some(function($r) {
                    return $r->changes && $r->changes->count() > 0;
                });
                $statusText = $hasChanges ? 'Reprogramado' : 'Programado';
                $statusBadge = $hasChanges 
                    ? '<span class="badge badge-warning" style="background-color: #ffc107; color: #000; padding: 6px 12px; border-radius: 12px; font-weight: 500;">' . $statusText . '</span>'
                    : '<span class="badge badge-secondary" style="background-color: #6c757d; color: white; padding: 6px 12px; border-radius: 12px; font-weight: 500;">' . $statusText . '</span>';
                
                // Turno en mayúsculas - usar el turno más común o el original
                // Si todos tienen el mismo turno, usar ese; si no, usar el del run más antiguo
                $shiftCounts = $relatedRuns->groupBy('shift_id')->map->count();
                $mostCommonShiftId = $shiftCounts->sortDesc()->keys()->first();
                $displayRun = $relatedRuns->firstWhere('shift_id', $mostCommonShiftId) ?? $relatedRuns->first();
                $shiftName = $displayRun->shift ? strtoupper($displayRun->shift->name) : 'N/A';
                
                // Código del vehículo - usar el vehículo más común o el original
                $vehicleCounts = $relatedRuns->groupBy('vehicle_id')->map->count();
                $mostCommonVehicleId = $vehicleCounts->sortDesc()->keys()->first();
                $displayVehicleRun = $relatedRuns->firstWhere('vehicle_id', $mostCommonVehicleId) ?? $relatedRuns->first();
                $vehicleCode = 'N/A';
                if ($displayVehicleRun->vehicle) {
                    $vehicleCode = $displayVehicleRun->vehicle->code ?? $displayVehicleRun->vehicle->name ?? 'N/A';
                    // Agregar prefijo VEH- si no lo tiene y es un código corto
                    if (strpos(strtoupper($vehicleCode), 'VEH-') !== 0 && strlen($vehicleCode) <= 10) {
                        $vehicleCode = 'VEH-' . strtoupper($vehicleCode);
                    }
                }
                
                // Zona - usar la zona del run más antiguo (normalmente no cambia)
                $zoneName = $run->zone ? $run->zone->name : 'N/A';
                
                // Usar el ID del primer run como identificador principal
                $primaryRunId = $relatedRuns->first()->id;
                
                $groupedRuns[] = [
                    'id' => $primaryRunId,
                    'start_date' => $dateDisplay,
                    'status' => $statusBadge,
                    'zone' => $zoneName,
                    'shift' => $shiftName,
                    'vehicle' => $vehicleCode,
                    'group' => $run->group ? $run->group->name : 'N/A',
                    'run_ids' => $relatedRunIds, // Guardar IDs de todos los runs para eliminación
                    'count' => $relatedRuns->count(), // Cantidad de runs en esta programación
                ];
            }
            
            // Ordenar por fecha de inicio (descendente)
            usort($groupedRuns, function($a, $b) {
                $dateA = Carbon::parse($a['start_date']);
                $dateB = Carbon::parse($b['start_date']);
                return $dateB->gt($dateA) ? 1 : -1;
            });
            
            return response()->json([
                'data' => $groupedRuns,
                'recordsTotal' => count($groupedRuns),
                'recordsFiltered' => count($groupedRuns),
            ]);
        }
        
        // Valores por defecto para los filtros
        // Mostrar desde el inicio del mes actual hasta 3 meses en el futuro
        $defaultStartDate = now()->startOfMonth()->format('Y-m-d');
        $defaultEndDate = now()->addMonths(3)->endOfMonth()->format('Y-m-d');
        
        return view('programs.schedules', compact('defaultStartDate', 'defaultEndDate'));
    }

    public function store(Request $request)
    {
        // Si es petición AJAX (desde el modal), manejar diferente
        if ($request->ajax() || $request->wantsJson()) {
            $validated = $request->validate([
                'workgroup_id' => ['required', 'exists:personnel_groups,id'],
                'zone_id' => ['required', 'exists:zones,id'],
                'shift_id' => ['required', 'exists:shifts,id'],
                'vehicle_id' => ['required', 'exists:vehicles,id'],
                'start_date' => ['required', 'date'],
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
                'days_of_week' => ['required', 'array', 'min:1'],
                'days_of_week.*' => ['integer', 'between:1,7'],
                'conductor_id' => ['nullable', 'exists:employees,id'],
                'ayudante1_id' => ['nullable', 'exists:employees,id'],
                'ayudante2_id' => ['nullable', 'exists:employees,id'],
            ]);

            // Si end_date no viene, usar start_date
            if (empty($validated['end_date'])) {
                $validated['end_date'] = $validated['start_date'];
            }

            DB::beginTransaction();
            try {
                // Obtener el grupo de personal
                $group = PersonnelGroup::findOrFail($validated['workgroup_id']);
                
                // Crear registro en la tabla programs para que aparezca en el listado
                // Nota: La tabla programs no tiene columnas 'status' ni 'notes', solo las columnas básicas
                $program = Program::create([
                    'zone_id' => $validated['zone_id'],
                    'vehicle_id' => $validated['vehicle_id'],
                    'shift_id' => $validated['shift_id'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'weekdays' => json_encode($validated['days_of_week']),
                    'conductor_id' => $group->driver_id, // Usar el conductor del grupo
                ]);
                
                // Crear runs para cada día seleccionado en el rango de fechas
                $start = Carbon::parse($validated['start_date']);
                $end = Carbon::parse($validated['end_date']);
                $daysOfWeek = $validated['days_of_week'];
                $created = 0;
                
                $current = $start->copy();
                while ($current->lte($end)) {
                    // Carbon's dayOfWeek: 0=Sunday, 1=Monday, ..., 6=Saturday
                    // Nuestro formato: 1=Monday, 2=Tuesday, ..., 7=Sunday
                    $carbonDay = $current->dayOfWeek;
                    $dayNumber = $carbonDay == 0 ? 7 : $carbonDay; // Convertir Sunday de 0 a 7
                    
                    // Asegurar que daysOfWeek sean enteros para comparación estricta
                    $daysOfWeekInt = array_map('intval', $daysOfWeek);
                    
                    if (in_array($dayNumber, $daysOfWeekInt, true)) {
                        // Verificar si ya existe un run para este grupo y fecha específica
                        // Solo verificar por grupo, no por vehículo (múltiples grupos pueden usar el mismo vehículo)
                        $existing = Run::where('group_id', $group->id)
                            ->whereDate('run_date', $current->toDateString())
                            ->first();
                        
                        if (!$existing) {
                            $newRun = Run::create([
                                'run_date' => $current->toDateString(),
                                'status' => 'Programado',
                                'zone_id' => $validated['zone_id'],
                                'shift_id' => $validated['shift_id'],
                                'vehicle_id' => $validated['vehicle_id'],
                                'group_id' => $group->id,
                            ]);
                            
                            // Crear personal asignado si fue proporcionado
                            // Priorizar el personal del formulario, sino usar el del grupo
                            $conductorId = $validated['conductor_id'] ?? $group->driver_id;
                            $ayudante1Id = $validated['ayudante1_id'] ?? $group->helper1_id;
                            $ayudante2Id = $validated['ayudante2_id'] ?? $group->helper2_id;
                            
                            // Buscar funciones
                            $conductorFunction = \App\Models\FunctionModel::where('name', 'Conductor')->first();
                            $ayudanteFunction = \App\Models\FunctionModel::where('name', 'Ayudante')->first();
                            
                            $personnelErrors = [];
                            
                            // Validar y asignar conductor
                            if ($conductorId) {
                                $conductor = Employee::find($conductorId);
                                if ($conductor) {
                                    $availability = $conductor->isAvailableForScheduling(
                                        $newRun->zone_id,
                                        $current->toDateString()
                                    );
                                    
                                    if ($availability['available']) {
                                        $functionId = $conductor->function_id ?? ($conductorFunction ? $conductorFunction->id : 1);
                                        RunPersonnel::create([
                                            'run_id' => $newRun->id,
                                            'staff_id' => $conductorId,
                                            'function_id' => $functionId,
                                            'role' => 'conductor',
                                        ]);
                                    } else {
                                        $personnelErrors[] = "Conductor {$conductor->first_name} {$conductor->last_name}: " . implode(', ', $availability['issues']);
                                    }
                                }
                            }
                            
                            // Validar y asignar ayudante 1
                            if ($ayudante1Id) {
                                $ayudante1 = Employee::find($ayudante1Id);
                                if ($ayudante1) {
                                    $availability = $ayudante1->isAvailableForScheduling(
                                        $newRun->zone_id,
                                        $current->toDateString()
                                    );
                                    
                                    if ($availability['available']) {
                                        $functionId = $ayudante1->function_id ?? ($ayudanteFunction ? $ayudanteFunction->id : 2);
                                        RunPersonnel::create([
                                            'run_id' => $newRun->id,
                                            'staff_id' => $ayudante1Id,
                                            'function_id' => $functionId,
                                            'role' => 'ayudante1',
                                        ]);
                                    } else {
                                        $personnelErrors[] = "Ayudante 1 {$ayudante1->first_name} {$ayudante1->last_name}: " . implode(', ', $availability['issues']);
                                    }
                                }
                            }
                            
                            // Validar y asignar ayudante 2
                            if ($ayudante2Id) {
                                $ayudante2 = Employee::find($ayudante2Id);
                                if ($ayudante2) {
                                    $availability = $ayudante2->isAvailableForScheduling(
                                        $newRun->zone_id,
                                        $current->toDateString()
                                    );
                                    
                                    if ($availability['available']) {
                                        $functionId = $ayudante2->function_id ?? ($ayudanteFunction ? $ayudanteFunction->id : 2);
                                        RunPersonnel::create([
                                            'run_id' => $newRun->id,
                                            'staff_id' => $ayudante2Id,
                                            'function_id' => $functionId,
                                            'role' => 'ayudante2',
                                        ]);
                                    } else {
                                        $personnelErrors[] = "Ayudante 2 {$ayudante2->first_name} {$ayudante2->last_name}: " . implode(', ', $availability['issues']);
                                    }
                                }
                            }
                            
                            // Si hay errores de personal, eliminar el run y lanzar excepción
                            if (!empty($personnelErrors)) {
                                $newRun->delete();
                                throw new \Exception("Error en fecha {$current->format('d/m/Y')}: " . implode(' | ', $personnelErrors));
                            }
                            
                            $created++;
                        }
                    }
                    $current->addDay();
                }

                DB::commit();
                return response()->json([
                    'ok' => true, 
                    'msg' => "Programación creada correctamente. Se crearon {$created} recorrido(s).",
                    'program_id' => $program->id
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                // Si se creó el programa pero falló la creación de runs, eliminarlo
                if (isset($program) && $program->id) {
                    try {
                        $program->delete();
                    } catch (\Exception $deleteEx) {
                        \Log::error('Error al eliminar programa después de fallo: ' . $deleteEx->getMessage());
                    }
                }
                \Log::error('Error al crear programación: ' . $e->getMessage());
                return response()->json([
                    'ok' => false, 
                    'msg' => 'Error al crear la programación: ' . $e->getMessage()
                ], 500);
            }
        }

        // Manejo tradicional (formulario normal)
        $data = $this->validateData($request);
        $data['days_of_week'] = array_values(array_map('intval', $request->input('days', [])));

        // regla simple: fechas válidas y rango correcto
        if ($data['start_date'] > $data['end_date']) {
            return back()->with('error','La fecha de inicio no puede ser mayor que la fecha fin.');
        }

        Program::create($data);
        return back()->with('success','Programación creada.');
    }

    public function update(Request $request, Program $program)
    {
        $data = $this->validateData($request, $program->id);
        $data['days_of_week'] = array_values(array_map('intval', $request->input('days', [])));

        if ($data['start_date'] > $data['end_date']) {
            return back()->with('error','La fecha de inicio no puede ser mayor que la fecha fin.');
        }

        $program->update($data);
        return back()->with('success','Programación actualizada.');
    }

    public function destroy(Program $program)
    {
        try {
            DB::transaction(fn() => $program->delete());
        } catch (\Illuminate\Database\QueryException $e) {
            // por si hay runs asociados (FK 23000)
            return back()->with('error','No se puede eliminar: existen dependencias.');
        }
        return back()->with('success','Programación eliminada.');
    }

    private function validateData(Request $request, $ignoreId = null): array
    {
        $daysRule = ['nullable','array', Rule::in([[1],[2],[3],[4],[5],[6],[7]])]; // sólo validará tipo; abajo normalizamos
        return $request->validate([
            'zone_id'    => ['required','exists:zones,id'],
            'vehicle_id' => ['required','exists:vehicles,id'],
            'shift_id'   => ['required','exists:shifts,id'],
            'start_date' => ['required','date'],
            'end_date'   => ['required','date'],
            'days'       => ['nullable','array'], // checkboxes; se castea a days_of_week
            'notes'      => ['nullable','string'],
        ]);
    }
}
