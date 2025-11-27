<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard.index');
Route::get('/api/dashboard/zones-status', [\App\Http\Controllers\DashboardController::class, 'getZonesStatus'])->name('api.dashboard.zones-status');
Route::get('/api/dashboard/available-personnel', [\App\Http\Controllers\DashboardController::class, 'getAvailablePersonnel'])->name('api.dashboard.available-personnel');
Route::post('/api/dashboard/update-personnel', [\App\Http\Controllers\DashboardController::class, 'updatePersonnelAssignment'])->name('api.dashboard.update-personnel');
Route::get('/api/dashboard/runs/{run}/edit', [\App\Http\Controllers\DashboardController::class, 'getRunForEdit'])->name('api.dashboard.run-edit');
Route::get('/api/dashboard/edit-options', [\App\Http\Controllers\DashboardController::class, 'getEditOptions'])->name('api.dashboard.edit-options');
Route::post('/api/dashboard/runs/{run}/apply-changes', [\App\Http\Controllers\DashboardController::class, 'applyRunChanges'])->name('api.dashboard.apply-changes');
Route::get('/api/dashboard/runs/{run}/missing-personnel', [\App\Http\Controllers\DashboardController::class, 'detectMissingPersonnel'])->name('api.dashboard.missing-personnel');
Route::post('/api/dashboard/replace-missing-personnel', [\App\Http\Controllers\DashboardController::class, 'replaceMissingPersonnel'])->name('api.dashboard.replace-missing-personnel');

use App\Http\Controllers\{
  BrandController, BrandmodelController, VehicletypeController, ColorController, VehicleController,
  EmployeeController, FunctionController, ContractController, AttendanceController, VacationController,
  ShiftController, ZoneController, WorkgroupController, RunController,
  ChangeController, ChangeRequestController, ChangeApprovalController, ChangeReasonController,
  ZonecoordController, ProgramController, RunPersonnelController, MaintenanceController
};

// Vehículos
Route::resource('brands', BrandController::class);
Route::get('/brands/logos/{filename}', [BrandController::class, 'serveLogo'])->name('brands.logos.serve');
Route::resource('brandmodels', BrandmodelController::class);
Route::resource('vehicletypes', VehicletypeController::class);
Route::resource('colors', ColorController::class);
Route::resource('vehicles', VehicleController::class);
Route::resource('maintenances', MaintenanceController::class)->except(['create', 'show', 'edit']);

// Rutas para mantenimientos
Route::get('/maintenances/{maintenance}/schedules', [MaintenanceController::class, 'getSchedules'])->name('maintenances.schedules.index');
Route::post('/maintenances/schedules', [MaintenanceController::class, 'storeSchedule'])->name('maintenances.schedules.store');
Route::put('/maintenances/schedules/{schedule}', [MaintenanceController::class, 'updateSchedule'])->name('maintenances.schedules.update');
Route::delete('/maintenances/schedules/{schedule}', [MaintenanceController::class, 'destroySchedule'])->name('maintenances.schedules.destroy');
Route::get('/maintenances/schedules/{schedule}/days', [MaintenanceController::class, 'getScheduleDays'])->name('maintenances.schedules.days');
Route::match(['put', 'post'], '/maintenances/days/{day}', [MaintenanceController::class, 'updateDay'])->name('maintenances.days.update');
Route::delete('/maintenances/days/{day}', [MaintenanceController::class, 'destroyDay'])->name('maintenances.days.destroy');
Route::get('/maintenances/get-vehicles', [MaintenanceController::class, 'getVehicles'])->name('maintenances.get-vehicles');
Route::get('/maintenances/get-employees', [MaintenanceController::class, 'getEmployees'])->name('maintenances.get-employees');
Route::get('/maintenances/images/{filename}', [MaintenanceController::class, 'serveImage'])->name('maintenances.images.serve');

// Rutas para obtener runs de una programación
Route::get('/programs/{program}/runs', [ProgramController::class, 'getProgramRuns'])->name('programs.runs');
Route::get('/runs/{run}/related', [ProgramController::class, 'getRelatedRuns'])->name('runs.related');

// Empleados
Route::resource('employees', EmployeeController::class);
Route::resource('functions', FunctionController::class);
Route::resource('contracts', ContractController::class);
Route::get('/attendances', [AttendanceController::class,'index'])->name('attendances.index');
Route::post('/attendances', [AttendanceController::class,'store'])->name('attendances.store');
Route::get('/attendances/{attendance}/edit', [AttendanceController::class,'edit'])->name('attendances.edit');
Route::put('/attendances/{attendance}', [AttendanceController::class,'update'])->name('attendances.update');
Route::delete('/attendances/{attendance}', [AttendanceController::class,'destroy'])->name('attendances.destroy');

Route::get('/kiosk', [AttendanceController::class,'kiosk'])->name('attendances.kiosk');
Route::post('/kiosk/attend', [AttendanceController::class,'kioskStore'])->name('attendances.kiosk.store');
Route::resource('vacations', VacationController::class);

// Endpoint JSON para DataTables de vacaciones
Route::get('vacations/data/table', [VacationController::class, 'datatable'])
    ->name('vacations.datatable');

// Programación
Route::resource('shifts', ShiftController::class)->except(['show','create','edit']);
Route::resource('zones', ZoneController::class);
Route::get('/zones/map/show', [ZoneController::class, 'map'])->name('zones.map');

// Endpoints Ajax para ubigeo
Route::get('/api/departments', function(){
    return response()->json(\App\Models\Department::orderBy('name')->get(['id','name']));
});
Route::get('/ubigeo/provinces/{department}', [\App\Http\Controllers\UbigeoController::class,'provinces']);
Route::get('/ubigeo/districts/{province}',   [\App\Http\Controllers\UbigeoController::class,'districts']);

Route::resource('workgroups', WorkgroupController::class);
Route::resource('runs', RunController::class);
Route::post('/runs/{run}/update-changes', [RunController::class, 'updateChanges'])->name('runs.update-changes');
Route::delete('/runs/{run}/individual', [RunController::class, 'destroyIndividual'])->name('runs.destroy-individual');

// Gestión de cambios
Route::get('/changes', [ChangeController::class, 'index'])->name('changes.index');
Route::resource('change-reasons', ChangeReasonController::class);
Route::get('/api/change-reasons/active', [ChangeReasonController::class, 'getActive'])->name('api.change-reasons.active');

// Rutas adicionales (mantener compatibilidad)
Route::resource('programs', ProgramController::class);

// Endpoint para select dependiente (marca -> modelos)
Route::get('/api/brands/{brand}/models', [BrandmodelController::class, 'byBrand'])->name('brandmodels.byBrand');
// Endpoint para obtener logos disponibles
Route::get('/api/vehicles/logos', [VehicleController::class, 'getAvailableLogos'])->name('vehicles.logos');

// Endpoint JSON para Select2 de funciones
Route::get('/api/functions/options', [FunctionController::class, 'options'])->name('functions.options');

// Compatibilidad temporal para enlaces viejos del dashboard
Route::get('/staff', [EmployeeController::class,'index'])->name('staff.index');

// Alias defensivo si alguna vista usa otro path
Route::get('/roles', [FunctionController::class,'index'])->name('roles.index');

// Alias defensivo en caso de enlaces viejos:
Route::get('/contratos', [ContractController::class,'index'])->name('contratos.index');

// Alias opcional si alguna vista vieja usa otro path
Route::get('/asistencias', [AttendanceController::class, 'index'])->name('asistencias.index');

// Alias opcional si alguna vista vieja usa otro path
Route::get('/vacaciones', [VacationController::class, 'index'])->name('vacaciones.index');

// Alias opcional si alguna vista vieja usa otro path
Route::get('/turnos', [ShiftController::class,'index'])->name('turnos.index');

// Asignación de personal al recorrido (pivot)
Route::resource('runpersonnel', RunPersonnelController::class)->only(['store','destroy']);

// Redirigir recorridos a programaciones (ya que el usuario quiere ver Programaciones, no Recorridos)
Route::get('/recorridos', [ProgramController::class,'schedules'])->name('recorridos.index');

// Coordenadas (hija)
Route::resource('zonecoords', ZonecoordController::class)->only(['store','update','destroy']);

// Alias defensivo si alguna pantalla vieja usa otra ruta:
Route::get('/zonas', [ZoneController::class, 'index'])->name('zonas.index');

// Alias defensivo si alguna vista vieja usa otro path
Route::get('/programacion', [ProgramController::class,'index'])->name('programacion.index');
Route::get('/programaciones', [ProgramController::class,'schedules'])->name('programaciones.index');

// Ruta para el menú de AdminLTE - Programación
Route::get('/admin/schedulings', [ProgramController::class,'schedules'])->name('admin.schedulings.index');

// Ruta para "Ir al módulo" - En progreso
Route::get('/programacion/modulo', [ProgramController::class,'inProgress'])->name('programacion.modulo');

// Programación Masiva - Pantalla completa
Route::get('/programaciones/massive', [ProgramController::class,'massive'])->name('programaciones.massive');
Route::post('/programaciones/bulk', [ProgramController::class,'storeBulk'])->name('programaciones.store-bulk');

// Actualización Masiva de Programación
Route::get('/programaciones/bulk-update', [ProgramController::class,'bulkUpdateForm'])->name('programaciones.bulk-update');
Route::post('/programaciones/bulk-update', [ProgramController::class,'bulkUpdate'])->name('programaciones.bulk-update.store');
Route::get('/api/groups/active', [ProgramController::class,'getActiveGroups'])->name('api.groups.active');
Route::post('/programaciones/validate-availability', [ProgramController::class,'validateAvailability'])->name('programaciones.validate-availability');

// Endpoints para Nueva Programación

// Endpoints JSON para selects cascada ubigeo
Route::get('/api/ubigeo/departments', fn() => \App\Models\Department::orderBy('name')->get(['id','name']));
Route::get('/api/ubigeo/departments/{department}/provinces', fn(\App\Models\Department $department) => $department->provinces()->orderBy('name')->get(['id','name']));
Route::get('/api/ubigeo/provinces/{province}/districts', fn(\App\Models\Province $province) => $province->districts()->orderBy('name')->get(['id','name']));

// Endpoints API para Grupo de Personal
Route::get('/api/zones/active', function() {
    return \App\Models\Zone::where('active', true)->orderBy('name')->get(['id', 'name']);
});
Route::get('/api/shifts/active', function() {
    return \App\Models\Shift::where('active', true)->orderBy('name')->get(['id', 'name']);
});
Route::get('/api/vehicles/active', function() {
    try {
        // El modelo tiene status como boolean en el cast, pero en BD es integer (1 = activo, 0 = inactivo)
        // Usar whereRaw para buscar directamente en la BD sin conversión del modelo
        $vehicles = \App\Models\Vehicle::whereRaw('status = 1')
            ->orderBy('code')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'people_capacity', 'brand_id', 'brandmodel_id']);
        
        // Si no hay resultados con status=1, devolver todos los vehículos
        // (puede ser que todos estén inactivos o que el campo status tenga otro valor)
        if ($vehicles->isEmpty()) {
            $vehicles = \App\Models\Vehicle::orderBy('code')
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'people_capacity', 'brand_id', 'brandmodel_id']);
        }
        
        return response()->json($vehicles);
    } catch (\Exception $e) {
        \Log::error('Error al cargar vehículos activos: ' . $e->getMessage());
        return response()->json(['error' => 'Error al cargar vehículos: ' . $e->getMessage()], 500);
    }
});
// Endpoints API para empleados disponibles
Route::get('/api/employees/available', [ProgramController::class, 'getAvailableEmployees'])->name('api.employees.available');
Route::get('/api/employees/active', function() {
    return \App\Models\Employee::where('active', true)
        ->orderBy('first_name')
        ->orderBy('last_name')
        ->get(['id', 'first_name', 'last_name']);
});
Route::get('/api/workgroups/active', function() {
    $workgroups = \App\Models\PersonnelGroup::with(['zone', 'vehicle', 'shift'])
        ->where('active', true)
        ->orderBy('name')
        ->get();
    
    return $workgroups->map(function($wg) {
        $vehicleName = 'N/A';
        if ($wg->vehicle) {
            $vehicleCode = $wg->vehicle->code ?? $wg->vehicle->name ?? 'N/A';
            // Agregar prefijo VEH- si no lo tiene y es un código corto
            if (strpos(strtoupper($vehicleCode), 'VEH-') !== 0 && strlen($vehicleCode) <= 10) {
                $vehicleName = 'VEH-' . strtoupper($vehicleCode);
            } else {
                $vehicleName = strtoupper($vehicleCode);
            }
        }
        
        return [
            'id' => $wg->id,
            'name' => $wg->name ?? 'N/A',
            'zone_id' => $wg->zone_id,
            'zone_name' => $wg->zone ? $wg->zone->name : 'N/A',
            'vehicle_id' => $wg->vehicle_id,
            'vehicle_name' => $vehicleName,
            'shift_id' => $wg->shift_id,
        ];
    });
});
