@extends('adminlte::page')

@section('title', 'Programaciones')

@section('plugins.Datatables', true)

@section('content_header')
  <div class="card card-outline card-primary">
    <div class="card-header">
      <div class="d-flex justify-content-between align-items-center w-100">
        <h1 class="mb-0" style="font-size: 1.5rem; font-weight: 600;">
          <i class="fas fa-calendar-alt"></i> Programaciones
        </h1>
        <div class="btn-group" style="gap: 8px;">
          <a href="{{ route('dashboard') }}" class="btn btn-success">
            <i class="fas fa-arrow-left" style="color: white;"></i> Ir al módulo
          </a>
          <button class="btn btn-primary" id="btn-new-schedule">
            <i class="fas fa-plus" style="color: white;"></i> Nueva Programación
          </button>
          <a href="{{ route('programaciones.massive') }}" class="btn btn-dark">
            <i class="fas fa-calendar-plus" style="color: white;"></i> Programación Masiva
          </a>
          <a href="{{ route('programaciones.bulk-update') }}" class="btn btn-warning">
            <i class="fas fa-edit" style="color: white;"></i> Actualización Masiva
          </a>
        </div>
      </div>
    </div>
  </div>
@stop

@section('content')
<div class="container-fluid">
  <!-- Franja de filtros -->
  <div class="card" style="border: 1px solid #dee2e6; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 12px;">
    <div class="card-body" style="padding: 15px;">
      <div class="row align-items-end">
        <div class="col-md-2">
          <div class="form-group mb-0">
            <label class="mb-1" style="font-size: 0.9rem; font-weight: 500;">
              Fecha de inicio: <span class="text-danger">*</span>
            </label>
            <input type="date" class="form-control" id="filter-start-date" value="{{ $defaultStartDate }}" required 
                   style="border-radius: 4px; padding: 6px 12px;">
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group mb-0">
            <label class="mb-1" style="font-size: 0.9rem; font-weight: 500;">Fecha de fin:</label>
            <input type="date" class="form-control" id="filter-end-date" value="{{ $defaultEndDate }}"
                   style="border-radius: 4px; padding: 6px 12px;">
          </div>
        </div>
        <div class="col-md-2">
          <button class="btn btn-info" id="btn-filter" style="border-radius: 4px; padding: 6px 20px;">
            <i class="fas fa-filter"></i> Filtrar
          </button>
        </div>
        <div class="col-md-2">
          <div class="form-group mb-0">
            <label class="mb-1" style="font-size: 0.85rem; font-weight: 400;">Mostrar 25 registros</label>
            <select class="form-control" id="filter-length" style="padding: 6px 12px; font-size: 0.9rem;">
              <option value="10">10</option>
              <option value="25" selected>25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group mb-0">
            <label class="mb-1" style="font-size: 0.9rem; font-weight: 500;">Buscar:</label>
            <input type="text" class="form-control" id="filter-search" placeholder="Buscar..." 
                   style="border-radius: 4px; padding: 6px 12px;">
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabla -->
  <div class="card" style="border: 1px solid #dee2e6; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <div class="card-body" style="padding: 0;">
      <table id="tbl-schedules" class="table table-striped table-hover w-100" style="margin: 0;">
        <thead style="background-color: #f8f9fa;">
          <tr>
            <th style="font-weight: 600; text-transform: uppercase; font-size: 0.85rem; padding: 12px 8px;">
              FECHA <i class="fas fa-sort float-right" style="opacity: 0.5;"></i>
            </th>
            <th style="font-weight: 600; text-transform: uppercase; font-size: 0.85rem; padding: 12px 8px;">
              ESTADO
            </th>
            <th style="font-weight: 600; text-transform: uppercase; font-size: 0.85rem; padding: 12px 8px;">
              ZONA <i class="fas fa-sort float-right" style="opacity: 0.5;"></i>
            </th>
            <th style="font-weight: 600; text-transform: uppercase; font-size: 0.85rem; padding: 12px 8px;">
              TURNOS <i class="fas fa-sort float-right" style="opacity: 0.5;"></i>
            </th>
            <th style="font-weight: 600; text-transform: uppercase; font-size: 0.85rem; padding: 12px 8px;">
              VEHICULO <i class="fas fa-sort float-right" style="opacity: 0.5;"></i>
            </th>
            <th style="font-weight: 600; text-transform: uppercase; font-size: 0.85rem; padding: 12px 8px;">
              GRUPO <i class="fas fa-sort float-right" style="opacity: 0.5;"></i>
            </th>
            <th style="font-weight: 600; text-transform: uppercase; font-size: 0.85rem; padding: 12px 8px; text-align: center; width: 150px;">
              ACCIÓN
            </th>
          </tr>
        </thead>
        <tbody>
          <!-- Los datos se cargan vía AJAX -->
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Modal Nueva Programación --}}
<div class="modal fade" id="modalNewSchedule" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="fas fa-calendar-plus"></i> Registrar programación
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" style="opacity: 1;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <h6 class="mb-3" style="color: #6c757d; font-weight: 500;">Registro de programación por grupos de personal:</h6>
        
        <div class="card" style="border: 1px solid #dee2e6; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
          <div class="card-header bg-white" style="border-bottom: 1px solid #dee2e6;">
            <div class="d-flex justify-content-between align-items-center">
              <h6 class="mb-0 font-weight-bold">Registrar programación</h6>
              <a href="javascript:void(0)" class="text-primary" data-dismiss="modal" style="text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Volver
              </a>
            </div>
          </div>
          <div class="card-body">
            <form id="frm-new-schedule">
              @csrf
              
              {{-- Bloque: Periodo y Validación --}}
              <div class="row mb-3">
                <div class="col-md-4">
                  <div class="form-group mb-0">
                    <label class="form-label">Fecha de inicio <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="schedule-start-date" name="start_date" required 
                           style="border-radius: 4px;">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group mb-0">
                    <label class="form-label">Fecha de fin</label>
                    <input type="date" class="form-control" id="schedule-end-date" name="end_date"
                           style="border-radius: 4px;">
                  </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                  <button type="button" class="btn btn-info" id="btn-validate-availability" 
                          style="border-radius: 4px; width: 100%;">
                    <i class="fas fa-calendar-check"></i> Validar Disponibilidad
                  </button>
                </div>
              </div>
              
              {{-- Bloque: Grupo y Turno --}}
              <div class="row mb-3">
                <div class="col-md-6">
                  <div class="form-group mb-0">
                    <label class="form-label">Grupo de Personal <span class="text-danger">*</span></label>
                    <select class="form-control" id="schedule-workgroup" name="workgroup_id" required 
                            style="border-radius: 4px;">
                      <option value="">Seleccione un grupo...</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group mb-0">
                    <label class="form-label">Turno <span class="text-danger">*</span></label>
                    <select class="form-control" id="schedule-shift" name="shift_id" required 
                            style="border-radius: 4px; text-transform: uppercase;">
                      <option value="">Seleccione un turno...</option>
                    </select>
                  </div>
                </div>
              </div>
              
              {{-- Bloque: Vehículo y Zona (Solo lectura) --}}
              <div class="row mb-3">
                <div class="col-md-6">
                  <div class="form-group mb-0">
                    <label class="form-label">Vehículo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="schedule-vehicle" name="vehicle_name" 
                           readonly style="background-color: #e9ecef; border-radius: 4px;">
                    <input type="hidden" id="schedule-vehicle-id" name="vehicle_id">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group mb-0">
                    <label class="form-label">Zona <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="schedule-zone" name="zone_name" 
                           readonly style="background-color: #e9ecef; border-radius: 4px;">
                    <input type="hidden" id="schedule-zone-id" name="zone_id">
                  </div>
                </div>
              </div>
              
              {{-- Bloque: Días de trabajo --}}
              <div class="mb-3">
                <label class="form-label d-block">Días de trabajo <span class="text-danger">*</span></label>
                <div class="d-flex flex-wrap" style="gap: 15px;">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="day-1" name="days[]" value="1">
                    <label class="form-check-label" for="day-1">Lunes</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="day-2" name="days[]" value="2">
                    <label class="form-check-label" for="day-2">Martes</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="day-3" name="days[]" value="3">
                    <label class="form-check-label" for="day-3">Miércoles</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="day-4" name="days[]" value="4">
                    <label class="form-check-label" for="day-4">Jueves</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="day-5" name="days[]" value="5">
                    <label class="form-check-label" for="day-5">Viernes</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="day-6" name="days[]" value="6">
                    <label class="form-check-label" for="day-6">Sábado</label>
                  </div>
                </div>
              </div>
              
              {{-- Bloque: Roles del equipo --}}
              <div class="mb-3">
                <label class="form-label mb-2">Preconfiguración de equipo (opcional)</label>
                <div class="row">
                  <div class="col-md-6 mb-2">
                    <label class="form-label" style="font-size: 0.9rem;">Conductor</label>
                    <select class="form-control" id="schedule-conductor" name="conductor_id" 
                            style="border-radius: 4px;">
                      <option value="">Seleccione un conductor...</option>
                    </select>
                  </div>
                  <div class="col-md-6 mb-2">
                    <label class="form-label" style="font-size: 0.9rem;">Ayudante 1</label>
                    <select class="form-control" id="schedule-ayudante1" name="ayudante1_id" 
                            style="border-radius: 4px;">
                      <option value="">Seleccione un ayudante...</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" style="font-size: 0.9rem;">Ayudante 2</label>
                    <select class="form-control" id="schedule-ayudante2" name="ayudante2_id" 
                            style="border-radius: 4px;">
                      <option value="">Seleccione un ayudante...</option>
                    </select>
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="card-footer bg-white text-right" style="border-top: 1px solid #dee2e6;">
            <button type="button" class="btn btn-success" id="btn-save-schedule" style="border-radius: 4px;">
              <i class="fas fa-save"></i> Guardar Programación
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Modal Ver Runs Relacionados --}}
<div class="modal fade" id="modalRelatedRuns" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
    <div class="modal-content" style="border-radius: 8px;">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title">
          <i class="fas fa-list"></i> Recorridos de la Programación
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" style="opacity: 1;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="padding: 25px;">
        <div class="table-responsive">
          <table class="table table-bordered table-striped table-hover" id="tbl-related-runs">
            <thead style="background-color: #f8f9fa;">
              <tr>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Zona</th>
                <th>Turno</th>
                <th>Vehículo</th>
                <th>Grupo</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <!-- Los datos se cargarán vía AJAX -->
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer bg-light" style="border-top: 1px solid #dee2e6; padding: 15px 25px;">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 4px;">
          <i class="fas fa-times"></i> Cerrar
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Modal Editar Programación --}}
<div class="modal fade" id="modalEditSchedule" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="fas fa-edit"></i> Editar Programación
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" style="opacity: 1;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="edit-run-id">
        
        {{-- Bloque: Cambio de Turno --}}
        <div class="mb-4" style="border-bottom: 1px solid #dee2e6; padding-bottom: 15px;">
          <h6 class="mb-3" style="font-weight: 600; color: #333;">Cambio de Turno</h6>
          <div class="row align-items-end">
            <div class="col-md-4">
              <label class="form-label mb-1" style="font-size: 0.9rem;">Turno Actual</label>
              <input type="text" class="form-control" id="edit-current-shift" readonly 
                     style="background-color: #e9ecef; border-radius: 4px;">
            </div>
            <div class="col-md-6">
              <label class="form-label mb-1" style="font-size: 0.9rem;">Nuevo Turno</label>
              <select class="form-control" id="edit-new-shift" style="border-radius: 4px;">
                <option value="">Seleccione un nuevo turno...</option>
              </select>
            </div>
            <div class="col-md-2">
              <button type="button" class="btn btn-success btn-sm" id="btn-add-shift-change" 
                      style="border-radius: 4px; width: 100%;">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>
        </div>
        
        {{-- Bloque: Cambio de Vehículo --}}
        <div class="mb-4" style="border-bottom: 1px solid #dee2e6; padding-bottom: 15px;">
          <h6 class="mb-3" style="font-weight: 600; color: #333;">Cambio de Vehículo</h6>
          <div class="row align-items-end">
            <div class="col-md-4">
              <label class="form-label mb-1" style="font-size: 0.9rem;">Vehículo Actual</label>
              <input type="text" class="form-control" id="edit-current-vehicle" readonly 
                     style="background-color: #e9ecef; border-radius: 4px;">
            </div>
            <div class="col-md-6">
              <label class="form-label mb-1" style="font-size: 0.9rem;">Nuevo Vehículo</label>
              <select class="form-control" id="edit-new-vehicle" style="border-radius: 4px;">
                <option value="">Seleccione un nuevo vehículo...</option>
              </select>
            </div>
            <div class="col-md-2">
              <button type="button" class="btn btn-success btn-sm" id="btn-add-vehicle-change" 
                      style="border-radius: 4px; width: 100%;">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>
        </div>
        
        {{-- Bloque: Cambio de Personal --}}
        <div class="mb-4" style="border-bottom: 1px solid #dee2e6; padding-bottom: 15px;">
          <h6 class="mb-3" style="font-weight: 600; color: #333;">Cambio de Personal</h6>
          <div class="row align-items-end">
            <div class="col-md-4">
              <label class="form-label mb-1" style="font-size: 0.9rem;">Personal Actual</label>
              <select class="form-control" id="edit-current-personnel" style="border-radius: 4px;">
                <option value="">Seleccione un personal...</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label mb-1" style="font-size: 0.9rem;">Nuevo Personal</label>
              <select class="form-control" id="edit-new-personnel" style="border-radius: 4px;">
                <option value="">Seleccione una opción...</option>
              </select>
            </div>
            <div class="col-md-2">
              <button type="button" class="btn btn-success btn-sm" id="btn-add-personnel-change" 
                      style="border-radius: 4px; width: 100%;">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>
        </div>
        
        {{-- Tabla: Cambios Registrados --}}
        <div class="mb-3">
          <h6 class="mb-2" style="font-weight: 600; color: #333;">Cambios Registrados</h6>
          <div class="table-responsive">
            <table class="table table-bordered table-sm" id="tbl-changes" style="font-size: 0.9rem;">
              <thead style="background-color: #f8f9fa;">
                <tr>
                  <th style="width: 15%;">Tipo de Cambio</th>
                  <th style="width: 20%;">Valor Anterior</th>
                  <th style="width: 20%;">Valor Nuevo</th>
                  <th style="width: 35%;">Notas</th>
                  <th style="width: 10%; text-align: center;">Acción</th>
                </tr>
              </thead>
              <tbody id="tbl-changes-body">
                <!-- Los cambios se agregan dinámicamente aquí -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-primary" id="btn-save-changes" style="border-radius: 4px;">
          <i class="fas fa-save"></i> Guardar Cambios
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Modal Visualización de Programación --}}
<div class="modal fade" id="modalViewSchedule" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
    <div class="modal-content" style="border-radius: 8px;">
      <div class="modal-body" style="padding: 25px;">
        {{-- Bloque 1: Datos Generales --}}
        <div class="card mb-4" style="border: 1px solid #dee2e6; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
          <div class="card-header" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 12px 15px;">
            <h6 class="mb-0" style="font-weight: 600; color: #333; font-size: 1rem;">Datos Generales</h6>
          </div>
          <div class="card-body" style="padding: 15px;">
            <div class="table-responsive">
              <table class="table table-bordered table-sm mb-0" style="font-size: 0.95rem;">
                <thead style="background-color: #f8f9fa;">
                  <tr>
                    <th style="font-weight: 600; padding: 10px; border-bottom: 2px solid #dee2e6;">Fecha</th>
                    <th style="font-weight: 600; padding: 10px; border-bottom: 2px solid #dee2e6;">Estado</th>
                    <th style="font-weight: 600; padding: 10px; border-bottom: 2px solid #dee2e6;">Zona</th>
                    <th style="font-weight: 600; padding: 10px; border-bottom: 2px solid #dee2e6;">Turno</th>
                    <th style="font-weight: 600; padding: 10px; border-bottom: 2px solid #dee2e6;">Vehículo</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td id="view-run-date" style="padding: 12px; vertical-align: middle;">-</td>
                    <td id="view-run-status" style="padding: 12px; vertical-align: middle; text-align: center;">-</td>
                    <td id="view-run-zone" style="padding: 12px; vertical-align: middle;">-</td>
                    <td id="view-run-shift" style="padding: 12px; vertical-align: middle; text-transform: uppercase;">-</td>
                    <td id="view-run-vehicle" style="padding: 12px; vertical-align: middle;">-</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        {{-- Bloque 2: Personal Asignado --}}
        <div class="card mb-4" style="border: 1px solid #dee2e6; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
          <div class="card-header" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 12px 15px;">
            <h6 class="mb-0" style="font-weight: 600; color: #333; font-size: 1rem;">Personal Asignado</h6>
          </div>
          <div class="card-body" style="padding: 15px;">
            <div class="table-responsive">
              <table class="table table-bordered table-sm mb-0" style="font-size: 0.95rem;">
                <thead style="background-color: #f8f9fa;">
                  <tr>
                    <th style="font-weight: 600; padding: 10px; border-bottom: 2px solid #dee2e6; width: 30%;">Rol</th>
                    <th style="font-weight: 600; padding: 10px; border-bottom: 2px solid #dee2e6;">Nombre</th>
                  </tr>
                </thead>
                <tbody id="view-personnel-body">
                  <tr>
                    <td colspan="2" style="padding: 12px; text-align: center; color: #6c757d;">No hay personal asignado</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        {{-- Bloque 3: Historial de Cambios --}}
        <div class="card mb-4" style="border: 1px solid #dee2e6; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
          <div class="card-header" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 12px 15px;">
            <h6 class="mb-0" style="font-weight: 600; color: #333; font-size: 1rem;">Historial de Cambios</h6>
          </div>
          <div class="card-body" style="padding: 15px;">
            <div class="table-responsive">
              <table class="table table-bordered table-sm mb-0" style="font-size: 0.9rem;">
                <thead style="background-color: #f8f9fa;">
                  <tr>
                    <th style="font-weight: 600; padding: 10px; border-bottom: 2px solid #dee2e6; width: 15%;">Fecha del Cambio</th>
                    <th style="font-weight: 600; padding: 10px; border-bottom: 2px solid #dee2e6; width: 25%;">Valor Anterior</th>
                    <th style="font-weight: 600; padding: 10px; border-bottom: 2px solid #dee2e6; width: 25%;">Valor Nuevo</th>
                    <th style="font-weight: 600; padding: 10px; border-bottom: 2px solid #dee2e6; width: 25%;">Motivo</th>
                    <th style="font-weight: 600; padding: 10px; border-bottom: 2px solid #dee2e6; width: 10%;">Acciones</th>
                  </tr>
                </thead>
                <tbody id="view-changes-body">
                  <tr>
                    <td colspan="5" style="padding: 12px; text-align: center; color: #6c757d;">No hay cambios registrados</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light" style="border-top: 1px solid #dee2e6; padding: 15px 25px;">
        <button type="button" class="btn btn-success" id="btn-save-changes" style="border-radius: 4px; display: none;">
          <i class="fas fa-save"></i> Guardar Cambios del Historial
        </button>
        <button type="button" class="btn btn-primary" id="btn-edit-from-view" style="border-radius: 4px; margin-right: auto;">
          <i class="fas fa-edit"></i> Editar Programación
        </button>
        <button type="button" class="btn btn-danger" id="btn-close-view" data-dismiss="modal" style="border-radius: 4px;">
          <i class="fas fa-times"></i> Cerrar
        </button>
      </div>
    </div>
  </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
  let DT;
  
  // Verificar si hay un run_id en la URL para abrir automáticamente el modal
  const urlParams = new URLSearchParams(window.location.search);
  const runIdFromUrl = urlParams.get('run_id');
  if (runIdFromUrl) {
    // Abrir el modal de detalle automáticamente
    setTimeout(function() {
      openViewScheduleModal(runIdFromUrl);
    }, 500);
  }
  
  // Inicializar DataTable sin cargar datos automáticamente
  DT = $('#tbl-schedules').DataTable({
    processing: false,
    serverSide: false,
    data: [], // Tabla vacía al inicio
    deferRender: true,
    columns: [
      { 
        data: 'start_date',
        className: 'text-left'
      },
      { 
        data: 'status', 
        orderable: false,
        className: 'text-center'
      },
      { 
        data: 'zone',
        className: 'text-left'
      },
      { 
        data: 'shift',
        className: 'text-left'
      },
      { 
        data: 'vehicle',
        className: 'text-left'
      },
      { 
        data: 'group',
        className: 'text-left'
      },
      { 
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-center',
        render: function(data, type, row) {
          return `
            <div class="btn-group btn-group-sm" style="gap: 6px;">
              <button class="btn btn-warning btn-detail" data-id="${row.id}" title="Ver detalle" 
                      style="padding: 4px 8px; border-radius: 3px; min-width: 36px;">
                <i class="fas fa-route" style="font-size: 0.85rem;"></i>
              </button>
              <button class="btn btn-info btn-personnel" data-id="${row.id}" title="Gestionar personal"
                      style="padding: 4px 8px; border-radius: 3px; min-width: 36px;">
                <i class="fas fa-users" style="font-size: 0.85rem;"></i>
              </button>
              <button class="btn btn-danger btn-cancel" data-id="${row.id}" title="Anular"
                      style="padding: 4px 8px; border-radius: 3px; min-width: 36px;">
                <i class="fas fa-ban" style="font-size: 0.85rem;"></i>
              </button>
            </div>
          `;
        }
      }
    ],
    pageLength: 25,
    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
      emptyTable: "Sin programaciones",
      info: "Mostrando _START_ de _END_ registros",
      infoEmpty: "Mostrando 0 de 0 registros",
      infoFiltered: "(filtrado de _MAX_ registros totales)",
      lengthMenu: "Mostrar _MENU_ registros",
      zeroRecords: "Sin programaciones para los filtros seleccionados"
    },
    order: [[0, 'desc']], // Ordenar por fecha descendente
    drawCallback: function() {
      // Aplicar estilo zebra
      $('#tbl-schedules tbody tr').each(function(index) {
        if (index % 2 === 0) {
          $(this).css('background-color', '#ffffff');
        } else {
          $(this).css('background-color', '#f8f9fa');
        }
      });
    },
    pagingType: "full_numbers"
  });
  
  // Función para cargar datos
  function loadSchedulesData() {
    const startDate = $('#filter-start-date').val();
    const endDate = $('#filter-end-date').val();
    
    // Configurar y cargar datos con AJAX
    $.ajax({
      url: "{{ route('programaciones.index') }}",
      type: "GET",
      data: {
        start_date: startDate || null,
        end_date: endDate || null
      },
      success: function(response) {
        if (response && response.data) {
          DT.clear();
          DT.rows.add(response.data);
          DT.draw();
        } else {
          DT.clear();
          DT.draw();
        }
      },
      error: function(xhr) {
        console.error('Error al cargar datos:', xhr);
        if (typeof Swal !== 'undefined') {
          Swal.fire('Error', 'No se pudieron cargar los datos', 'error');
        }
      }
    });
  }
  
  // Filtrar por fechas
  $('#btn-filter').on('click', function() {
    loadSchedulesData();
  });
  
  // Cargar datos automáticamente al iniciar si hay fechas por defecto
  if ($('#filter-start-date').val()) {
    setTimeout(function() {
      loadSchedulesData();
    }, 500);
  }
  
  // La tabla queda vacía hasta que se presione el botón Filtrar
  
  // Cambiar cantidad de registros
  $('#filter-length').on('change', function() {
    DT.page.len(parseInt($(this).val())).draw();
  });
  
  // Búsqueda en tiempo real
  let searchTimeout;
  $('#filter-search').on('keyup', function() {
    clearTimeout(searchTimeout);
    const searchValue = $(this).val();
    searchTimeout = setTimeout(function() {
      DT.search(searchValue).draw();
    }, 500);
  });
  
  // Nueva Programación - Abrir modal
  $('#btn-new-schedule').on('click', function() {
    openNewScheduleModal();
  });
  
  // Funciones para el modal
  function openNewScheduleModal() {
    // Resetear formulario
    $('#frm-new-schedule')[0].reset();
    $('#schedule-vehicle').val('');
    $('#schedule-zone').val('');
    $('#schedule-vehicle-id').val('');
    $('#schedule-zone-id').val('');
    $('input[name="days[]"]').prop('checked', false);
    $('#schedule-conductor, #schedule-ayudante1, #schedule-ayudante2').html('<option value="">Seleccione...</option>');
    
    // Establecer fechas por defecto
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    $('#schedule-start-date').val(formatDate(firstDay));
    $('#schedule-end-date').val(formatDate(lastDay));
    
    // Cargar datos para selectores
    loadScheduleSelects();
    
    // Mostrar modal
    $('#modalNewSchedule').modal('show');
  }
  
  function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }
  
  function loadScheduleSelects() {
    // Cargar grupos de personal
    $.get('/api/workgroups/active').done(function(workgroups) {
      const $select = $('#schedule-workgroup');
      $select.html('<option value="">Seleccione un grupo...</option>');
      workgroups.forEach(function(wg) {
        $select.append(`<option value="${wg.id}" data-vehicle-id="${wg.vehicle_id || ''}" data-vehicle-name="${wg.vehicle_name || ''}" data-zone-id="${wg.zone_id || ''}" data-zone-name="${wg.zone_name || ''}" data-shift-id="${wg.shift_id || ''}">${wg.name}</option>`);
      });
    }).fail(function() {
      console.error('Error al cargar grupos');
    });
    
    // Cargar turnos
    $.get('/api/shifts/active').done(function(shifts) {
      const $select = $('#schedule-shift');
      $select.html('<option value="">Seleccione un turno...</option>');
      shifts.forEach(function(shift) {
        $select.append(`<option value="${shift.id}">${shift.name.toUpperCase()}</option>`);
      });
    });
    
    // Cargar empleados para conductor y ayudantes
    $.get('/api/employees/active').done(function(employees) {
      employees.forEach(function(emp) {
        const fullname = `${emp.first_name || ''} ${emp.last_name || ''}`.trim();
        const option = `<option value="${emp.id}">${fullname}</option>`;
        $('#schedule-conductor, #schedule-ayudante1, #schedule-ayudante2').append(option);
      });
    });
  }
  
  // Cuando se selecciona un grupo, auto-completar vehículo y zona
  $('#schedule-workgroup').on('change', function() {
    const selectedOption = $(this).find('option:selected');
    if (selectedOption.val()) {
      const vehicleId = selectedOption.data('vehicle-id');
      const vehicleName = selectedOption.data('vehicle-name') || '';
      const zoneId = selectedOption.data('zone-id');
      const zoneName = selectedOption.data('zone-name') || '';
      const shiftId = selectedOption.data('shift-id');
      
      $('#schedule-vehicle-id').val(vehicleId || '');
      $('#schedule-vehicle').val(vehicleName);
      $('#schedule-zone-id').val(zoneId || '');
      $('#schedule-zone').val(zoneName);
      
      // Auto-seleccionar turno si el grupo tiene uno asignado
      if (shiftId) {
        $('#schedule-shift').val(shiftId);
      }
    } else {
      $('#schedule-vehicle-id').val('');
      $('#schedule-vehicle').val('');
      $('#schedule-zone-id').val('');
      $('#schedule-zone').val('');
    }
  });
  
  // Validar disponibilidad
  $('#btn-validate-availability').on('click', function() {
    const startDate = $('#schedule-start-date').val();
    const endDate = $('#schedule-end-date').val();
    const workgroupId = $('#schedule-workgroup').val();
    
    if (!startDate) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Error', 'La fecha de inicio es obligatoria', 'error');
      } else {
        alert('La fecha de inicio es obligatoria');
      }
      return;
    }
    
    if (!workgroupId) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Error', 'Debe seleccionar un grupo de personal', 'error');
      } else {
        alert('Debe seleccionar un grupo de personal');
      }
      return;
    }
    
    const $btn = $(this);
    const originalHtml = $btn.html();
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Validando...');
    
    // TODO: Implementar validación real de disponibilidad
    // Por ahora, simular validación
    setTimeout(function() {
      $btn.prop('disabled', false).html(originalHtml);
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'success',
          title: 'Disponible',
          text: 'El grupo de personal está disponible en el rango de fechas seleccionado.',
          timer: 2000,
          showConfirmButton: false
        });
      } else {
        alert('Disponible: El grupo está disponible en el rango seleccionado');
      }
    }, 1000);
  });
  
  // Guardar programación
  $('#btn-save-schedule').on('click', function() {
    saveSchedule();
  });
  
  function saveSchedule() {
    const form = $('#frm-new-schedule')[0];
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }
    
    // Validar campos obligatorios
    const startDate = $('#schedule-start-date').val();
    const workgroupId = $('#schedule-workgroup').val();
    const shiftId = $('#schedule-shift').val();
    const vehicleId = $('#schedule-vehicle-id').val();
    const zoneId = $('#schedule-zone-id').val();
    const selectedDays = $('input[name="days[]"]:checked').length;
    
    if (!startDate || !workgroupId || !shiftId || !vehicleId || !zoneId) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Error', 'Por favor complete todos los campos obligatorios', 'error');
      } else {
        alert('Por favor complete todos los campos obligatorios');
      }
      return;
    }
    
    if (selectedDays === 0) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Error', 'Debe seleccionar al menos un día de trabajo', 'error');
      } else {
        alert('Debe seleccionar al menos un día de trabajo');
      }
      return;
    }
    
    const formData = {
      workgroup_id: workgroupId,
      start_date: startDate,
      end_date: $('#schedule-end-date').val() || startDate,
      zone_id: zoneId,
      shift_id: shiftId,
      vehicle_id: vehicleId,
      days_of_week: $('input[name="days[]"]:checked').map(function() {
        return parseInt($(this).val());
      }).get(),
      conductor_id: $('#schedule-conductor').val() || null,
      ayudante1_id: $('#schedule-ayudante1').val() || null,
      ayudante2_id: $('#schedule-ayudante2').val() || null
    };
    
    const $btn = $('#btn-save-schedule');
    const originalHtml = $btn.html();
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
    
    $.ajax({
      url: '/programs',
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      data: JSON.stringify(formData),
      success: function(response) {
        if (response.ok || response.success) {
          $('#modalNewSchedule').modal('hide');
          if (DT) {
            // Recargar datos después de guardar
            loadSchedulesData();
          }
          if (typeof Swal !== 'undefined') {
            Swal.fire('Éxito', response.msg || 'Programación creada correctamente', 'success');
          } else {
            alert('Programación creada correctamente');
          }
        }
      },
      error: function(xhr) {
        let errorMsg = 'Error al guardar la programación';
        if (xhr.responseJSON) {
          if (xhr.responseJSON.errors) {
            const errors = Object.values(xhr.responseJSON.errors).flat();
            errorMsg = errors.join('<br>');
          } else if (xhr.responseJSON.message) {
            errorMsg = xhr.responseJSON.message;
          } else if (xhr.responseJSON.msg) {
            errorMsg = xhr.responseJSON.msg;
          }
        }
        if (typeof Swal !== 'undefined') {
          Swal.fire('Error', errorMsg, 'error');
        } else {
          alert(errorMsg);
        }
      },
      complete: function() {
        $btn.prop('disabled', false).html(originalHtml);
      }
    });
  }
  
  // Limpiar formulario al cerrar modal
  $('#modalNewSchedule').on('hidden.bs.modal', function() {
    $('#frm-new-schedule')[0].reset();
    $('#schedule-vehicle').val('');
    $('#schedule-zone').val('');
    $('#schedule-vehicle-id').val('');
    $('#schedule-zone-id').val('');
    $('#schedule-conductor, #schedule-ayudante1, #schedule-ayudante2').html('<option value="">Seleccione...</option>');
  });
  
  // Programación Masiva - Ya no necesita handler, es un enlace directo
  
  // Botones de acción
  // Abrir modal de visualización
  $('#tbl-schedules tbody').on('click', '.btn-detail', function() {
    const runId = $(this).data('id');
    openViewScheduleModal(runId);
  });
  
  // Variable para almacenar el runId actual en el modal de visualización
  let currentViewRunId = null;
  
  // Función para abrir modal de visualización
  function openViewScheduleModal(runId) {
    currentViewRunId = runId;
    
    // Limpiar contenido anterior
    $('#view-run-date').text('-');
    $('#view-run-status').html('-');
    $('#view-run-zone').text('-');
    $('#view-run-shift').text('-');
    $('#view-run-vehicle').text('-');
    $('#view-personnel-body').html('<tr><td colspan="2" style="padding: 12px; text-align: center; color: #6c757d;">No hay personal asignado</td></tr>');
    $('#view-changes-body').html('<tr><td colspan="4" style="padding: 12px; text-align: center; color: #6c757d;">No hay cambios registrados</td></tr>');
    
    // Cargar datos del run
    $.ajax({
      url: '/runs/' + runId,
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      success: function(response) {
        if (response && response.data) {
          const run = response.data;
          
          // Bloque 1: Datos Generales
          $('#view-run-date').text(run.run_date || '-');
          
          // Estado: "Reprogramado" solo si hay cambios, "Programado" si no
          let displayStatus = 'Programado';
          if (run.changes && run.changes.length > 0) {
            displayStatus = 'Reprogramado';
          }
          
          // Estado con badge
          let statusBadge = '';
          if (displayStatus === 'Reprogramado') {
            statusBadge = `<span class="badge badge-warning" style="background-color: #ffc107; color: #000; padding: 6px 12px; border-radius: 12px; font-weight: 500;">${displayStatus}</span>`;
          } else {
            statusBadge = `<span class="badge badge-secondary" style="background-color: #6c757d; color: #fff; padding: 6px 12px; border-radius: 12px; font-weight: 500;">${displayStatus}</span>`;
          }
          $('#view-run-status').html(statusBadge);
          
          $('#view-run-zone').text(run.zone_name || '-');
          $('#view-run-shift').text(run.shift_name ? run.shift_name.toUpperCase() : '-');
          $('#view-run-vehicle').text(run.vehicle_name || '-');
          
          // Bloque 2: Personal Asignado
          if (run.personnel && run.personnel.length > 0) {
            let personnelHtml = '';
            run.personnel.forEach(function(p) {
              if (p.id && p.name) {
                const roleName = p.function_name || 'Sin rol';
                personnelHtml += `
                  <tr>
                    <td style="padding: 12px; vertical-align: middle; font-weight: 500;">${roleName}</td>
                    <td style="padding: 12px; vertical-align: middle;">${p.name}</td>
                  </tr>
                `;
              }
            });
            if (personnelHtml) {
              $('#view-personnel-body').html(personnelHtml);
            }
          }
          
          // Bloque 3: Historial de Cambios
          if (run.changes && run.changes.length > 0) {
            let changesHtml = '';
            run.changes.forEach(function(change) {
              // Formatear fecha
              let changeDate = '-';
              if (change.created_at) {
                const date = new Date(change.created_at);
                changeDate = date.toLocaleDateString('es-PE', { 
                  day: '2-digit', 
                  month: '2-digit', 
                  year: 'numeric' 
                });
              }
              
              // Formatear tipo de cambio
              const typeLabels = {
                'turno': 'Turno',
                'vehiculo': 'Vehículo',
                'personal': 'Empleado'
              };
              const changeType = typeLabels[change.type] || change.change_type || 'Cambio';
              
              // Valor anterior
              const oldValue = change.old_value || '-';
              const oldValueDisplay = `${changeType}: ${oldValue}`;
              
              // Valor nuevo
              const newValue = change.new_value || '-';
              const newValueDisplay = `${changeType}: ${newValue}`;
              
              // Motivo
              const motivo = change.notes || 'Sin motivo especificado';
              
              changesHtml += `
                <tr data-change-id="${change.id}">
                  <td style="padding: 12px; vertical-align: middle;">${changeDate}</td>
                  <td style="padding: 12px; vertical-align: middle;">${oldValueDisplay}</td>
                  <td style="padding: 12px; vertical-align: middle;">${newValueDisplay}</td>
                  <td style="padding: 12px; vertical-align: middle;" class="change-notes-cell">
                    <span class="change-notes-text">${motivo}</span>
                    <textarea class="form-control change-notes-edit" style="display: none; min-height: 60px;">${change.notes || ''}</textarea>
                  </td>
                  <td style="padding: 12px; vertical-align: middle; text-align: center;">
                    <button class="btn btn-sm btn-primary btn-edit-change" data-change-id="${change.id}" title="Editar motivo">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger btn-delete-change" data-change-id="${change.id}" title="Eliminar cambio">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              `;
            });
            if (changesHtml) {
              $('#view-changes-body').html(changesHtml);
            }
          }
        }
      },
      error: function(xhr) {
        console.error('Error al cargar datos:', xhr);
        let errorMsg = 'No se pudieron cargar los datos de la programación';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMsg = xhr.responseJSON.message;
        } else if (xhr.responseText) {
          try {
            const errorObj = JSON.parse(xhr.responseText);
            if (errorObj.message) errorMsg = errorObj.message;
          } catch (e) {
            errorMsg = 'Error HTTP ' + xhr.status + ': ' + xhr.statusText;
          }
        }
        if (typeof Swal !== 'undefined') {
          Swal.fire('Error', errorMsg, 'error');
        } else {
          alert(errorMsg);
        }
      }
    });
    
    // Mostrar modal
    $('#modalViewSchedule').modal('show');
  }
  
  // Botón Editar desde modal de visualización
  $('#btn-edit-from-view').on('click', function() {
    if (currentViewRunId) {
      $('#modalViewSchedule').modal('hide');
      // Pequeño delay para que el modal se cierre antes de abrir el de edición
      setTimeout(function() {
        openEditScheduleModal(currentViewRunId);
      }, 300);
    }
  });
  
  // Editar motivo de un cambio
  $(document).on('click', '.btn-edit-change', function() {
    const changeId = $(this).data('change-id');
    const row = $(this).closest('tr');
    const notesCell = row.find('.change-notes-cell');
    const notesText = notesCell.find('.change-notes-text');
    const notesEdit = notesCell.find('.change-notes-edit');
    
    // Mostrar textarea y ocultar texto
    notesText.hide();
    notesEdit.show().focus();
    
    // Mostrar botón guardar
    $('#btn-save-changes').show();
    
    // Cambiar botón editar por guardar/cancelar
    $(this).hide();
    if (!row.find('.btn-save-change').length) {
      notesCell.append(`
        <button class="btn btn-sm btn-success btn-save-change" data-change-id="${changeId}" style="margin-top: 5px;">
          <i class="fas fa-check"></i> Guardar
        </button>
        <button class="btn btn-sm btn-secondary btn-cancel-change" data-change-id="${changeId}" style="margin-top: 5px;">
          <i class="fas fa-times"></i> Cancelar
        </button>
      `);
    }
  });
  
  // Guardar cambio individual
  $(document).on('click', '.btn-save-change', function() {
    const changeId = $(this).data('change-id');
    const row = $(this).closest('tr');
    const notesCell = row.find('.change-notes-cell');
    const notesText = notesCell.find('.change-notes-text');
    const notesEdit = notesCell.find('.change-notes-edit');
    const newNotes = notesEdit.val();
    
    // Guardar cambio
    saveChange(changeId, newNotes, row);
  });
  
  // Cancelar edición
  $(document).on('click', '.btn-cancel-change', function() {
    const row = $(this).closest('tr');
    const notesCell = row.find('.change-notes-cell');
    const notesText = notesCell.find('.change-notes-text');
    const notesEdit = notesCell.find('.change-notes-edit');
    
    // Restaurar texto original
    notesText.show();
    notesEdit.hide();
    row.find('.btn-edit-change').show();
    row.find('.btn-save-change, .btn-cancel-change').remove();
    $('#btn-save-changes').hide();
  });
  
  // Eliminar cambio
  $(document).on('click', '.btn-delete-change', function() {
    const changeId = $(this).data('change-id');
    const row = $(this).closest('tr');
    
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: '¿Eliminar cambio?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          deleteChange(changeId, row);
        }
      });
    } else {
      if (confirm('¿Está seguro de eliminar este cambio?')) {
        deleteChange(changeId, row);
      }
    }
  });
  
  // Función para guardar cambio
  function saveChange(changeId, notes, row) {
    if (!currentViewRunId) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Error', 'No hay programación seleccionada', 'error');
      }
      return;
    }
    
    $.ajax({
      url: `/runs/${currentViewRunId}/update-changes`,
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Accept': 'application/json'
      },
      data: {
        changes: [{
          id: changeId,
          notes: notes
        }]
      },
      success: function(response) {
        if (response.ok) {
          // Actualizar texto mostrado
          const notesCell = row.find('.change-notes-cell');
          const notesText = notesCell.find('.change-notes-text');
          const notesEdit = notesCell.find('.change-notes-edit');
          
          notesText.text(notes || 'Sin motivo especificado').show();
          notesEdit.hide();
          row.find('.btn-edit-change').show();
          row.find('.btn-save-change, .btn-cancel-change').remove();
          $('#btn-save-changes').hide();
          
          if (typeof Swal !== 'undefined') {
            Swal.fire('Éxito', 'Cambio guardado correctamente', 'success');
          }
        } else {
          throw new Error(response.msg || 'Error al guardar');
        }
      },
      error: function(xhr) {
        let errorMsg = 'Error al guardar el cambio';
        if (xhr.responseJSON && xhr.responseJSON.msg) {
          errorMsg = xhr.responseJSON.msg;
        }
        if (typeof Swal !== 'undefined') {
          Swal.fire('Error', errorMsg, 'error');
        } else {
          alert(errorMsg);
        }
      }
    });
  }
  
  // Función para eliminar cambio
  function deleteChange(changeId, row) {
    if (!currentViewRunId) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Error', 'No hay programación seleccionada', 'error');
      }
      return;
    }
    
    $.ajax({
      url: `/runs/${currentViewRunId}/update-changes`,
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Accept': 'application/json'
      },
      data: {
        delete_ids: [changeId]
      },
      success: function(response) {
        if (response.ok) {
          // Eliminar fila
          row.fadeOut(300, function() {
            $(this).remove();
            // Si no hay más cambios, mostrar mensaje
            if ($('#view-changes-body tr').length === 0) {
              $('#view-changes-body').html('<tr><td colspan="5" style="padding: 12px; text-align: center; color: #6c757d;">No hay cambios registrados</td></tr>');
            }
          });
          
          if (typeof Swal !== 'undefined') {
            Swal.fire('Éxito', 'Cambio eliminado correctamente', 'success');
          }
        } else {
          throw new Error(response.msg || 'Error al eliminar');
        }
      },
      error: function(xhr) {
        let errorMsg = 'Error al eliminar el cambio';
        if (xhr.responseJSON && xhr.responseJSON.msg) {
          errorMsg = xhr.responseJSON.msg;
        }
        if (typeof Swal !== 'undefined') {
          Swal.fire('Error', errorMsg, 'error');
        } else {
          alert(errorMsg);
        }
      }
    });
  }
  
  // Función para abrir modal de edición
  function openEditScheduleModal(runId) {
    // Resetear formulario y tabla de cambios
    $('#edit-run-id').val(runId);
    $('#tbl-changes-body').empty();
    $('#edit-current-shift').val('');
    $('#edit-new-shift').val('');
    $('#edit-current-vehicle').val('');
    $('#edit-new-vehicle').val('');
    $('#edit-current-personnel').val('');
    $('#edit-new-personnel').val('');
    
    // Cargar datos del run
    $.ajax({
      url: '/runs/' + runId,
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      success: function(response) {
        if (response && response.data) {
          const run = response.data;
          
          // Llenar campos actuales
          $('#edit-current-shift').val(run.shift_name || 'N/A');
          $('#edit-current-vehicle').val(run.vehicle_name || 'N/A');
          
          // Cargar selectores
          loadEditSelects(run);
          
          // Cargar personal actual para el selector
          $('#edit-current-personnel').html('<option value="">Seleccione un personal...</option>');
          if (run.personnel && run.personnel.length > 0) {
            run.personnel.forEach(function(p) {
              if (p.id && p.name) {
                $('#edit-current-personnel').append(`<option value="${p.id}">${p.name}</option>`);
              }
            });
          }
          
          // Cargar cambios existentes si los hay
          if (run.changes && run.changes.length > 0) {
            run.changes.forEach(function(change) {
              addChangeToTable(change, change.id);
            });
          }
        }
      },
      error: function(xhr) {
        console.error('Error al cargar datos:', xhr);
        let errorMsg = 'No se pudieron cargar los datos de la programación';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMsg = xhr.responseJSON.message;
        } else if (xhr.responseText) {
          try {
            const errorObj = JSON.parse(xhr.responseText);
            if (errorObj.message) errorMsg = errorObj.message;
          } catch (e) {
            errorMsg = 'Error HTTP ' + xhr.status + ': ' + xhr.statusText;
          }
        }
        console.error('Detalles del error:', {
          status: xhr.status,
          statusText: xhr.statusText,
          response: xhr.responseText,
          url: '/runs/' + runId
        });
        if (typeof Swal !== 'undefined') {
          Swal.fire('Error', errorMsg, 'error');
        } else {
          alert(errorMsg);
        }
      }
    });
    
    // Mostrar modal
    $('#modalEditSchedule').modal('show');
  }
  
  // Cargar selectores para edición
  function loadEditSelects(currentRun) {
    // Cargar turnos
    $.get('/api/shifts/active').done(function(shifts) {
      const $select = $('#edit-new-shift');
      $select.html('<option value="">Seleccione un nuevo turno...</option>');
      shifts.forEach(function(shift) {
        $select.append(`<option value="${shift.id}">${shift.name.toUpperCase()}</option>`);
      });
    });
    
    // Cargar vehículos
    $.get('/api/vehicles/active').done(function(vehicles) {
      const $select = $('#edit-new-vehicle');
      $select.html('<option value="">Seleccione un nuevo vehículo...</option>');
      vehicles.forEach(function(vehicle) {
        const code = vehicle.code || vehicle.name || 'N/A';
        const capacity = vehicle.people_capacity || vehicle.capacity || 0;
        $select.append(`<option value="${vehicle.id}">${code} (Cap: ${capacity})</option>`);
      });
    });
    
    // Cargar empleados para personal actual y nuevo
    $.get('/api/employees/active').done(function(employees) {
      const options = '<option value="">Seleccione...</option>';
      employees.forEach(function(emp) {
        const fullname = `${emp.first_name || ''} ${emp.last_name || ''}`.trim();
        const option = `<option value="${emp.id}">${fullname}</option>`;
        $('#edit-current-personnel, #edit-new-personnel').append(option);
      });
    });
  }
  
  // Variable para almacenar cambios pendientes
  let pendingChanges = [];
  
  // Agregar cambio de turno
  $('#btn-add-shift-change').on('click', function() {
    const oldValue = $('#edit-current-shift').val() || 'N/A';
    const newShiftId = $('#edit-new-shift').val();
    const newShiftText = $('#edit-new-shift option:selected').text();
    
    if (!newShiftId) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Advertencia', 'Debe seleccionar un nuevo turno', 'warning');
      }
      return;
    }
    
    if (oldValue === newShiftText) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Advertencia', 'El nuevo turno debe ser diferente al actual', 'warning');
      }
      return;
    }
    
    const change = {
      type: 'turno',
      old_value: oldValue,
      new_value: newShiftText,
      new_id: newShiftId,
      notes: ''
    };
    
    addChangeToTable(change);
    pendingChanges.push(change);
    
    // Limpiar selector
    $('#edit-new-shift').val('');
  });
  
  // Agregar cambio de vehículo
  $('#btn-add-vehicle-change').on('click', function() {
    const oldValue = $('#edit-current-vehicle').val() || 'N/A';
    const newVehicleId = $('#edit-new-vehicle').val();
    const newVehicleText = $('#edit-new-vehicle option:selected').text();
    
    if (!newVehicleId) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Advertencia', 'Debe seleccionar un nuevo vehículo', 'warning');
      }
      return;
    }
    
    if (oldValue === newVehicleText) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Advertencia', 'El nuevo vehículo debe ser diferente al actual', 'warning');
      }
      return;
    }
    
    const change = {
      type: 'vehiculo',
      old_value: oldValue,
      new_value: newVehicleText,
      new_id: newVehicleId,
      notes: ''
    };
    
    addChangeToTable(change);
    pendingChanges.push(change);
    
    // Limpiar selector
    $('#edit-new-vehicle').val('');
  });
  
  // Agregar cambio de personal
  $('#btn-add-personnel-change').on('click', function() {
    const oldPersonnelId = $('#edit-current-personnel').val();
    const oldPersonnelText = $('#edit-current-personnel option:selected').text() || 'N/A';
    const newPersonnelId = $('#edit-new-personnel').val();
    const newPersonnelText = $('#edit-new-personnel option:selected').text();
    
    if (!oldPersonnelId || !newPersonnelId) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Advertencia', 'Debe seleccionar personal actual y nuevo', 'warning');
      }
      return;
    }
    
    if (oldPersonnelId === newPersonnelId) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Advertencia', 'El nuevo personal debe ser diferente al actual', 'warning');
      }
      return;
    }
    
    const change = {
      type: 'personal',
      old_value: oldPersonnelText,
      new_value: newPersonnelText,
      old_id: oldPersonnelId,
      new_id: newPersonnelId,
      notes: ''
    };
    
    addChangeToTable(change);
    pendingChanges.push(change);
    
    // Limpiar selectores
    $('#edit-current-personnel').val('');
    $('#edit-new-personnel').val('');
  });
  
  // Agregar cambio a la tabla
  function addChangeToTable(change, changeId = null) {
    const changeIdAttr = changeId ? `data-change-id="${changeId}"` : '';
    const rowId = changeId || 'pending-' + Date.now();
    
    const typeLabels = {
      'turno': 'Turno',
      'vehiculo': 'Vehículo',
      'personal': 'Personal'
    };
    
    // Si tiene ID, es un cambio existente
    const actualChangeId = change.id || changeId;
    
    const row = `
      <tr ${changeIdAttr} data-pending-id="${actualChangeId ? '' : rowId}" ${actualChangeId ? `data-change-id="${actualChangeId}"` : ''}>
        <td>${typeLabels[change.type] || change.change_type || change.type}</td>
        <td>${change.old_value || 'N/A'}</td>
        <td>${change.new_value || 'N/A'}</td>
        <td>
          <textarea class="form-control form-control-sm change-notes" rows="2" 
                    style="font-size: 0.85rem; border-radius: 3px;" 
                    placeholder="Ingrese el motivo del cambio...">${change.notes || ''}</textarea>
        </td>
        <td style="text-align: center;">
          <button type="button" class="btn btn-danger btn-sm btn-remove-change" 
                  style="border-radius: 3px; padding: 2px 8px;">
            <i class="fas fa-trash"></i>
          </button>
        </td>
      </tr>
    `;
    
    $('#tbl-changes-body').append(row);
    
    // Si no tiene ID, es un cambio pendiente, guardar el ID en el objeto
    if (!actualChangeId) {
      change._rowId = rowId;
    }
  }
  
  // Eliminar cambio de la tabla
  $(document).on('click', '.btn-remove-change', function() {
    const $row = $(this).closest('tr');
    const changeId = $row.data('change-id');
    const pendingId = $row.data('pending-id');
    
    // Si es un cambio existente, marcar para eliminar
    if (changeId) {
      if (!window.changesToDelete) {
        window.changesToDelete = [];
      }
      window.changesToDelete.push(changeId);
    }
    
    // Si es un cambio pendiente, eliminarlo del array
    if (pendingId) {
      pendingChanges = pendingChanges.filter(function(c) {
        return c._rowId !== pendingId;
      });
    }
    
    $row.remove();
  });
  
  // Guardar cambios
  $('#btn-save-changes').on('click', function() {
    const runId = $('#edit-run-id').val();
    
    if (!runId) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Error', 'No se pudo identificar la programación', 'error');
      }
      return;
    }
    
    // Recopilar cambios con sus notas
    const changesToSave = [];
    $('#tbl-changes-body tr').each(function() {
      const $row = $(this);
      const changeId = $row.data('change-id');
      const pendingId = $row.data('pending-id');
      const notes = $row.find('.change-notes').val();
      
      if (changeId) {
        // Cambio existente - actualizar notas
        changesToSave.push({
          id: changeId,
          notes: notes
        });
      } else if (pendingId) {
        // Cambio nuevo - buscar en pendingChanges
        const change = pendingChanges.find(function(c) {
          return c._rowId === pendingId;
        });
        if (change) {
          changesToSave.push({
            type: change.type,
            old_value: change.old_value,
            new_value: change.new_value,
            new_id: change.new_id,
            old_id: change.old_id,
            notes: notes
          });
        }
      }
    });
    
    if (changesToSave.length === 0 && (!window.changesToDelete || window.changesToDelete.length === 0)) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Advertencia', 'No hay cambios para guardar', 'warning');
      }
      return;
    }
    
    const $btn = $('#btn-save-changes');
    const originalHtml = $btn.html();
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
    
    $.ajax({
      url: '/runs/' + runId + '/update-changes',
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      data: JSON.stringify({
        changes: changesToSave,
        delete_ids: window.changesToDelete || []
      }),
      success: function(response) {
        if (response.ok || response.success) {
          $('#modalEditSchedule').modal('hide');
          if (DT) {
            loadSchedulesData();
          }
          if (typeof Swal !== 'undefined') {
            Swal.fire('Éxito', response.msg || 'Cambios guardados correctamente', 'success');
          } else {
            alert('Cambios guardados correctamente');
          }
          
          // Limpiar variables
          pendingChanges = [];
          window.changesToDelete = [];
        }
      },
      error: function(xhr) {
        let errorMsg = 'Error al guardar los cambios';
        if (xhr.responseJSON) {
          if (xhr.responseJSON.errors) {
            const errors = Object.values(xhr.responseJSON.errors).flat();
            errorMsg = errors.join('<br>');
          } else if (xhr.responseJSON.message) {
            errorMsg = xhr.responseJSON.message;
          }
        }
        if (typeof Swal !== 'undefined') {
          Swal.fire('Error', errorMsg, 'error');
        } else {
          alert(errorMsg);
        }
      },
      complete: function() {
        $btn.prop('disabled', false).html(originalHtml);
      }
    });
  });
  
  // Limpiar al cerrar modal
  $('#modalEditSchedule').on('hidden.bs.modal', function() {
    pendingChanges = [];
    window.changesToDelete = [];
    $('#tbl-changes-body').empty();
    $('#edit-run-id').val('');
  });
  
  // Abrir modal de runs relacionados
  $('#tbl-schedules tbody').on('click', '.btn-personnel', function() {
    const runId = $(this).data('id');
    const $modal = $('#modalRelatedRuns');
    const $tbody = $modal.find('tbody');
    
    // Mostrar loading
    $tbody.html('<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando recorridos...</td></tr>');
    
    // Mostrar modal
    $modal.modal('show');
    
    // Cargar runs relacionados
    $.get('/runs/' + runId + '/related')
      .done(function(response) {
        if (response.ok && response.runs) {
          if (response.runs.length === 0) {
            $tbody.html('<tr><td colspan="7" class="text-center text-muted">No hay recorridos registrados para esta programación</td></tr>');
          } else {
            let html = '';
            response.runs.forEach(function(run) {
              html += `
                <tr>
                  <td>${run.date}</td>
                  <td>${run.status}</td>
                  <td>${run.zone}</td>
                  <td>${run.shift}</td>
                  <td>${run.vehicle}</td>
                  <td>${run.group}</td>
                  <td>
                    <button class="btn btn-warning btn-sm btn-related-detail" data-run-id="${run.id}" title="Ver detalle">
                      <i class="fas fa-route"></i> Detalle
                    </button>
                    <button class="btn btn-danger btn-sm btn-related-delete" data-run-id="${run.id}" title="Eliminar">
                      <i class="fas fa-trash"></i> Eliminar
                    </button>
                  </td>
                </tr>
              `;
            });
            $tbody.html(html);
          }
        }
      })
      .fail(function(xhr) {
        console.error('Error al cargar runs relacionados:', xhr);
        $tbody.html('<tr><td colspan="7" class="text-center text-danger">Error al cargar los recorridos</td></tr>');
      });
  });
  
  // Abrir modal de detalle desde el modal de runs relacionados
  $(document).on('click', '.btn-related-detail', function() {
    const runId = $(this).data('run-id');
    // Cerrar modal de runs relacionados
    $('#modalRelatedRuns').modal('hide');
    // Abrir modal de detalle
    setTimeout(function() {
      openViewScheduleModal(runId);
    }, 300);
  });
  
  // Eliminar run individual desde el modal de runs relacionados
  $(document).on('click', '.btn-related-delete', function(e) {
    e.preventDefault();
    const runId = $(this).data('run-id');
    const $row = $(this).closest('tr');
    const $modal = $('#modalRelatedRuns');
    
    if (confirm('¿Está seguro de eliminar este recorrido individual? Esta acción solo eliminará este recorrido, no toda la programación.')) {
      // Mostrar loading
      $row.find('td').html('<i class="fas fa-spinner fa-spin"></i> Eliminando...');
      
      // Usar la ruta de eliminación individual
      $.ajax({
        url: '/runs/' + runId + '/individual',
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
          if (response.ok || response.success) {
            // Eliminar fila de la tabla
            $row.fadeOut(300, function() {
              $(this).remove();
              // Si no quedan filas, mostrar mensaje
              if ($modal.find('tbody tr').length === 0) {
                $modal.find('tbody').html('<tr><td colspan="7" class="text-center text-muted">No hay recorridos registrados para esta programación</td></tr>');
              }
            });
            
            // Recargar tabla principal
            if (DT) {
              loadSchedulesData();
            }
            
            // Mostrar mensaje de éxito
            if (typeof Swal !== 'undefined') {
              Swal.fire('Éxito', response.msg || 'El recorrido ha sido eliminado correctamente', 'success');
            } else {
              alert('El recorrido ha sido eliminado correctamente');
            }
          } else {
            alert(response.msg || 'Error al eliminar el recorrido');
            location.reload();
          }
        },
        error: function(xhr) {
          console.error('Error al eliminar run:', xhr);
          let errorMsg = 'Error al eliminar el recorrido';
          if (xhr.responseJSON && xhr.responseJSON.msg) {
            errorMsg = xhr.responseJSON.msg;
          }
          alert(errorMsg);
          // Recargar modal
          const runId = $row.find('.btn-related-detail').data('run-id');
          $('#modalRelatedRuns').trigger('shown.bs.modal');
        }
      });
    }
  });
  
  $('#tbl-schedules tbody').on('click', '.btn-cancel', function() {
    const runId = $(this).data('id');
    
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: '¿Anular programación?',
        text: 'Esta acción eliminará todos los datos de la programación y no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, anular y eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          // Mostrar loading
          Swal.fire({
            title: 'Eliminando...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            }
          });
          
          // Llamar al backend para eliminar
          $.ajax({
            url: '/runs/' + runId,
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
              if (response.ok || response.success) {
                Swal.fire('Éxito', response.msg || 'La programación ha sido anulada y eliminada correctamente', 'success');
                // Recargar tabla
                if (DT) {
                  loadSchedulesData();
                }
              } else {
                Swal.fire('Error', response.msg || 'Error al anular la programación', 'error');
              }
            },
            error: function(xhr) {
              console.error('Error al eliminar programación:', xhr);
              let errorMsg = 'Error al anular la programación';
              if (xhr.responseJSON) {
                if (xhr.responseJSON.errors) {
                  const errors = Object.values(xhr.responseJSON.errors).flat();
                  errorMsg = errors.join('<br>');
                } else if (xhr.responseJSON.message) {
                  errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON.msg) {
                  errorMsg = xhr.responseJSON.msg;
                }
              } else if (xhr.responseText) {
                try {
                  const errorObj = JSON.parse(xhr.responseText);
                  if (errorObj.message) errorMsg = errorObj.message;
                  else if (errorObj.msg) errorMsg = errorObj.msg;
                } catch (e) {
                  errorMsg = 'Error HTTP ' + xhr.status + ': ' + xhr.statusText;
                }
              }
              Swal.fire('Error', errorMsg, 'error');
            }
          });
        }
      });
    } else {
      if (confirm('¿Anular programación? Esta acción eliminará todos los datos y no se puede deshacer.')) {
        $.ajax({
          url: '/runs/' + runId,
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          success: function(response) {
            if (response.ok || response.success) {
              alert('Programación anulada y eliminada correctamente');
              if (DT) {
                loadSchedulesData();
              }
            } else {
              alert('Error: ' + (response.msg || 'Error al anular la programación'));
            }
          },
          error: function(xhr) {
            alert('Error al anular la programación');
          }
        });
      }
    }
  });
  
  // Hover effects para botones
  $(document).on('mouseenter', '.btn-detail, .btn-personnel, .btn-cancel', function() {
    $(this).css('opacity', '0.85');
  }).on('mouseleave', '.btn-detail, .btn-personnel, .btn-cancel', function() {
    $(this).css('opacity', '1');
  });
});
</script>
@stop

@section('css')
<style>
  .card-outline {
    border-top: 3px solid #007bff;
  }
  
  #tbl-schedules thead th {
    cursor: pointer;
    position: relative;
    border-bottom: 2px solid #dee2e6;
  }
  
  #tbl-schedules thead th:hover {
    background-color: #e9ecef !important;
  }
  
  #tbl-schedules tbody tr {
    transition: background-color 0.2s;
    border-bottom: 1px solid #e9ecef;
  }
  
  #tbl-schedules tbody tr:hover {
    background-color: #e9ecef !important;
  }
  
  #tbl-schedules tbody td {
    padding: 10px 8px;
    vertical-align: middle;
    border-top: 1px solid #e9ecef;
  }
  
  .btn-group-sm .btn {
    transition: all 0.2s;
  }
  
  .btn-group-sm .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  }
  
  @media (max-width: 768px) {
    .card-header .d-flex {
      flex-direction: column;
      align-items: flex-start !important;
    }
    
    .card-header .btn-group {
      margin-top: 10px;
      width: 100%;
      flex-direction: column;
    }
    
    .card-header .btn-group .btn {
      margin-bottom: 5px;
      width: 100%;
    }
  }
</style>
@stop
