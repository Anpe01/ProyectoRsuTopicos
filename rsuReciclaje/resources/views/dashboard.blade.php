@extends('adminlte::page')

@section('title', 'Dashboard - RSU Reciclaje')

@section('plugins.Sweetalert2', true)
@section('plugins.Datatables', true)

@push('css')
<style>
  .dashboard-summary-card {
    border: none;
    border-radius: 14px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    padding: 1.5rem;
    color: #fff;
    min-height: 150px;
  }
  .dashboard-summary-card small {
    color: rgba(255, 255, 255, 0.8);
  }
  .zone-card {
    border-radius: 16px;
    padding: 1.25rem;
    min-height: 220px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    border: 1px solid rgba(15, 23, 42, 0.08);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
  }
  .zone-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
  }
  .zone-card-success {
    background: linear-gradient(135deg, #eafaf1, #dcf5e7);
    border-left: 6px solid #28a745;
  }
  .zone-card-warning {
    background: linear-gradient(135deg, #fff4e5, #ffe0c3);
    border-left: 6px solid #fd7e14;
  }
  .zone-card-danger {
    background: linear-gradient(135deg, #fdecea, #f9d2d0);
    border-left: 6px solid #dc3545;
  }
  .legend-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 10px;
  }
</style>
@endpush

@section('content_header')
<div class="d-flex flex-wrap justify-content-between align-items-end w-100" style="gap: 20px;">
  <div>
    <h1 class="mb-1 text-dark" style="font-weight: 700;">Dashboard General</h1>
    <p class="mb-0 text-muted">Supervisa las zonas programadas y gestiona la asistencia del personal.</p>
  </div>
  <div class="d-flex flex-wrap align-items-end" style="gap: 20px;">
    <div class="form-group mb-0">
      <label class="mb-1 text-muted" style="font-weight:600;">Seleccione una fecha:</label>
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
        </div>
        <input type="date"
               id="date-selector"
               class="form-control"
               placeholder="Seleccione una fecha:"
               value="{{ $selectedDate }}">
      </div>
    </div>
    <div class="form-group mb-0">
      <label class="mb-1 text-muted" style="font-weight:600;">Seleccione turno:</label>
      <select id="shift-selector" class="form-control">
        @forelse($shifts as $shift)
          <option value="{{ $shift->id }}" {{ $selectedShiftId == $shift->id ? 'selected' : '' }}>
            {{ strtoupper($shift->name) }}
          </option>
        @empty
          <option value="">Sin turnos activos</option>
        @endforelse
      </select>
    </div>
    <button class="btn btn-primary" id="btn-search">
      <i class="fas fa-search mr-1"></i> Buscar programación
    </button>
  </div>
</div>
@stop

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-9">
      <div class="row">
        <div class="col-sm-6 col-xl-3 mb-3">
          <div class="dashboard-summary-card" style="background: linear-gradient(135deg, #4f46e5, #7c3aed);">
            <p class="text-uppercase mb-1">Asistencias</p>
            <h2 class="mb-0" id="stat-attendance">0</h2>
            <small>Colaboradores con asistencia confirmada</small>
          </div>
        </div>
        <div class="col-sm-6 col-xl-3 mb-3">
          <div class="dashboard-summary-card" style="background: linear-gradient(135deg, #0ea5e9, #2563eb);">
            <p class="text-uppercase mb-1">Apoyos disponibles</p>
            <h2 class="mb-0" id="stat-supports">0</h2>
            <small>Ayudantes listos para apoyar</small>
          </div>
        </div>
        <div class="col-sm-6 col-xl-3 mb-3">
          <div class="dashboard-summary-card" style="background: linear-gradient(135deg, #16a34a, #15803d);">
            <p class="text-uppercase mb-1">Grupos completos</p>
            <h2 class="mb-0" id="stat-groups">0</h2>
            <small>Listos para iniciar recorrido</small>
          </div>
        </div>
        <div class="col-sm-6 col-xl-3 mb-3">
          <div class="dashboard-summary-card" style="background: linear-gradient(135deg, #dc2626, #b91c1c);">
            <p class="text-uppercase mb-1">Faltan</p>
            <h2 class="mb-0" id="stat-missing">0</h2>
            <small>Integrantes pendientes de asistencia</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-3">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-light">
          <strong>Leyenda de estados</strong>
        </div>
        <div class="card-body">
          <div class="d-flex align-items-center mb-3">
            <span class="legend-dot bg-success"></span>
            <div>
              <h6 class="mb-0">Verde</h6>
              <small>Grupo completo y listo para operar</small>
            </div>
          </div>
          <div class="d-flex align-items-center mb-3">
            <span class="legend-dot" style="background-color: #fd7e14;"></span>
            <div>
              <h6 class="mb-0">Amarillo</h6>
              <small>Faltan integrantes por confirmar asistencia</small>
            </div>
          </div>
          <div class="d-flex align-items-center">
            <span class="legend-dot bg-danger"></span>
            <div>
              <h6 class="mb-0">Rojo</h6>
              <small>Grupo no puede iniciar por ausencias críticas</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0 text-dark">Zonas programadas para la fecha seleccionada</h4>
      <span class="text-muted small" id="zones-counter">0 zonas</span>
    </div>
    <div class="row" id="zones-container">
      <div class="col-12 text-center text-muted py-5">
        <i class="fas fa-spinner fa-spin"></i> Esperando datos de programación...
      </div>
    </div>
  </div>
</div>

<!-- Modal para Cambiar Personal -->
<div class="modal fade" id="changePersonnelModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">
          <i class="fas fa-user-edit"></i> Cambiar Personal - <span id="modal-zone-name"></span>
        </h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="modal-runs-list"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal para Reemplazar Personal Faltante -->
<div class="modal fade" id="replaceMissingModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">
          <i class="fas fa-user-times"></i> Reemplazar Personal Faltante
        </h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="replace-missing-content">
          <div class="text-center">
            <div class="spinner-border" role="status">
              <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-2">Cargando información del personal faltante...</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btn-confirm-replace">
          <i class="fas fa-check"></i> Confirmar Reemplazos
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal para Editar Programación -->
<div class="modal fade" id="editProgramModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title">
          <i class="fas fa-edit"></i> Editar Programación
        </h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="edit-run-id">
        
        <!-- Cambio de Turno -->
        <div class="card mb-3">
          <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-clock"></i> Cambio de Turno</h6>
          </div>
          <div class="card-body">
            <div class="row align-items-end">
              <div class="col-md-4">
                <label><strong>Turno Actual:</strong></label>
                <input type="text" id="current-shift" class="form-control" readonly>
              </div>
              <div class="col-md-4">
                <label><strong>Nuevo Turno:</strong></label>
                <select id="new-shift" class="form-control">
                  <option value="">Seleccione un turno...</option>
                </select>
              </div>
              <div class="col-md-4">
                <button type="button" class="btn btn-success btn-block" id="btn-add-shift">
                  <i class="fas fa-plus"></i> Aplicar Cambio
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Cambio de Vehículo -->
        <div class="card mb-3">
          <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-truck"></i> Cambio de Vehículo</h6>
          </div>
          <div class="card-body">
            <div class="row align-items-end">
              <div class="col-md-4">
                <label><strong>Vehículo Actual:</strong></label>
                <input type="text" id="current-vehicle" class="form-control" readonly>
              </div>
              <div class="col-md-4">
                <label><strong>Nuevo Vehículo:</strong></label>
                <select id="new-vehicle" class="form-control">
                  <option value="">Seleccione un vehículo...</option>
                </select>
              </div>
              <div class="col-md-4">
                <button type="button" class="btn btn-success btn-block" id="btn-add-vehicle">
                  <i class="fas fa-plus"></i> Aplicar Cambio
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Cambio de Personal -->
        <div class="card mb-3">
          <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-users"></i> Cambio de Personal</h6>
          </div>
          <div class="card-body">
            <div class="row align-items-end">
              <div class="col-md-4">
                <label><strong>Personal Actual:</strong></label>
                <select id="current-personnel" class="form-control">
                  <option value="">Seleccione personal actual...</option>
                </select>
              </div>
              <div class="col-md-4">
                <label><strong>Nuevo Personal:</strong></label>
                <select id="new-personnel" class="form-control">
                  <option value="">Seleccione nuevo personal...</option>
                </select>
              </div>
              <div class="col-md-4">
                <button type="button" class="btn btn-success btn-block" id="btn-add-personnel">
                  <i class="fas fa-plus"></i> Aplicar Cambio
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabla de Cambios Registrados -->
        <div class="card">
          <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-history"></i> Cambios Registrados</h6>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered table-sm" id="changes-table">
                <thead>
                  <tr>
                    <th>Tipo de Cambio</th>
                    <th>Valor Anterior</th>
                    <th>Valor Nuevo</th>
                    <th>Notas</th>
                    <th>Acción</th>
                  </tr>
                </thead>
                <tbody id="changes-tbody">
                  <tr>
                    <td colspan="5" class="text-center text-muted">
                      No hay cambios registrados
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btn-save-changes">
          <i class="fas fa-save"></i> Guardar Cambios
        </button>
      </div>
    </div>
  </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
  let currentDate = $('#date-selector').val();
  let currentShiftId = $('#shift-selector').val();
  
  // Cargar datos iniciales
  loadZonesStatus();
  
  // Cambiar fecha
  $('#date-selector').on('change', function() {
    currentDate = $(this).val();
  });
  
  // Cambiar turno
  $('#shift-selector').on('change', function() {
    currentShiftId = $(this).val();
  });
  
  // Botón buscar
  $('#btn-search').on('click', function() {
    loadZonesStatus();
  });
  
  // Cargar estado de zonas
  function loadZonesStatus() {
    const container = $('#zones-container');
    container.html('<div class="col-12 text-center text-muted py-5"><i class="fas fa-spinner fa-spin"></i> Cargando datos...</div>');
    
    $.ajax({
      url: '{{ route("api.dashboard.zones-status") }}',
      method: 'GET',
      data: { 
        date: currentDate,
        shift_id: currentShiftId
      },
      success: function(response) {
        if (response.ok) {
          renderZonesCards(response.zones);
          updateSummary(response.zones);
        }
      },
      error: function() {
        container.html('<div class="col-12 text-center text-danger py-5"><i class="fas fa-exclamation-triangle"></i> Error al cargar los datos</div>');
        if (typeof Swal !== 'undefined') {
          Swal.fire('Error', 'No se pudieron cargar los datos', 'error');
        }
      }
    });
  }
  
  // Renderizar tarjetas de zonas
  function renderZonesCards(zones) {
    const container = $('#zones-container');
    container.empty();
    
    if (zones.length === 0) {
      container.append(`
        <div class="col-12 text-center text-muted py-5">
          <i class="fas fa-info-circle fa-2x mb-3"></i>
          <p>No hay zonas programadas para la fecha y turno seleccionados</p>
        </div>
      `);
      return;
    }
    
    zones.forEach(function(zone) {
      // Determinar clase y color según el estado
      let cardClass = '';
      let statusText = '';
      let statusIcon = '';
      
      if (zone.can_start) {
        cardClass = 'zone-card-success';
        statusText = 'Grupo completo y listo para operar';
        statusIcon = '<i class="fas fa-check-circle text-success"></i>';
      } else {
        // Determinar si es amarillo (faltan algunos) o rojo (crítico)
        const missingTotal = (zone.missing_personnel?.conductors || 0) + (zone.missing_personnel?.helpers || 0);
        const requiredTotal = (zone.required_personnel?.conductors || 0) + (zone.required_personnel?.helpers || 0);
        const missingPercent = requiredTotal > 0 ? (missingTotal / requiredTotal) * 100 : 0;
        
        if (missingPercent >= 50 || (zone.missing_personnel?.conductors || 0) > 0) {
          cardClass = 'zone-card-danger';
          statusText = 'Grupo no puede iniciar por ausencias críticas';
          statusIcon = '<i class="fas fa-times-circle text-danger"></i>';
        } else {
          cardClass = 'zone-card-warning';
          statusText = 'Faltan integrantes por confirmar asistencia';
          statusIcon = '<i class="fas fa-exclamation-triangle" style="color: #fd7e14;"></i>';
        }
      }
      
      const issuesHtml = zone.issues && zone.issues.length > 0
        ? '<div class="mt-2"><small class="text-muted"><strong>Problemas:</strong></small><ul class="mb-0 pl-3"><li>' + zone.issues.join('</li><li>') + '</li></ul></div>'
        : '';
      
      // Para cada run, crear un botón de edición
      let actionButtons = '';
      if (zone.can_start) {
        actionButtons = '<button class="btn btn-sm btn-success" disabled><i class="fas fa-check"></i> Listo</button>';
      } else {
        // Crear botones para cada run de la zona
        zone.runs.forEach(function(run) {
          actionButtons += `<button class="btn btn-sm btn-warning btn-edit-run mr-1 mb-1" data-run-id="${run.id}" data-zone-name="${zone.zone_name}">
               <i class="fas fa-edit"></i> Editar Recorrido #${run.id}
             </button>`;
          // Botón para reemplazar personal faltante
          actionButtons += `<button class="btn btn-sm btn-danger btn-replace-missing mr-1 mb-1" data-run-id="${run.id}" data-zone-name="${zone.zone_name}">
               <i class="fas fa-user-times"></i> Reemplazar Faltantes
             </button>`;
        });
      }
      
      container.append(`
        <div class="col-md-6 col-lg-4 mb-4">
          <div class="zone-card ${cardClass}">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <h5 class="mb-0 font-weight-bold">Zona: ${zone.zone_name}</h5>
              ${statusIcon}
            </div>
            <p class="mb-2 text-muted small">${statusText}</p>
            <div class="mb-2">
              <small class="d-block"><strong>Recorridos:</strong> ${zone.runs.length}</small>
              <small class="d-block"><strong>Conductores requeridos:</strong> ${zone.required_personnel?.conductors || 0}</small>
              <small class="d-block"><strong>Conductores presentes:</strong> ${zone.present_personnel?.conductors || 0}</small>
              <small class="d-block"><strong>Ayudantes requeridos:</strong> ${zone.required_personnel?.helpers || 0}</small>
              <small class="d-block"><strong>Ayudantes presentes:</strong> ${zone.present_personnel?.helpers || 0}</small>
            </div>
            ${issuesHtml}
            <div class="mt-3 d-flex flex-wrap justify-content-end">
              ${actionButtons}
            </div>
          </div>
        </div>
      `);
    });
  }
  
  // Actualizar resumen de estadísticas
  function updateSummary(zones) {
    let totalAttendance = 0;
    let totalSupports = 0;
    let completeGroups = 0;
    let totalMissing = 0;
    
    zones.forEach(function(zone) {
      // Asistencias: personal presente
      totalAttendance += (zone.present_personnel?.conductors || 0) + (zone.present_personnel?.helpers || 0);
      
      // Apoyos disponibles: ayudantes presentes que no están asignados
      totalSupports += (zone.present_personnel?.helpers || 0);
      
      // Grupos completos
      if (zone.can_start) {
        completeGroups++;
      }
      
      // Faltantes
      totalMissing += (zone.missing_personnel?.conductors || 0) + (zone.missing_personnel?.helpers || 0);
    });
    
    $('#stat-attendance').text(totalAttendance);
    $('#stat-supports').text(totalSupports);
    $('#stat-groups').text(completeGroups);
    $('#stat-missing').text(totalMissing);
    $('#zones-counter').text(zones.length + ' zona' + (zones.length !== 1 ? 's' : ''));
  }
  
  // Abrir modal para cambiar personal
  $(document).on('click', '.btn-change-personnel', function() {
    const zoneId = $(this).data('zone-id');
    const zoneName = $(this).data('zone-name');
    
    $('#modal-zone-name').text(zoneName);
    
    // Cargar personal disponible
    $.ajax({
      url: '{{ route("api.dashboard.available-personnel") }}',
      method: 'GET',
      data: { zone_id: zoneId, date: currentDate },
      success: function(response) {
        if (response.ok) {
          // Cargar runs de la zona para mostrar opciones de cambio
          loadZoneRunsForChange(zoneId, response.data);
        }
      },
      error: function() {
        if (typeof Swal !== 'undefined') {
          Swal.fire('Error', 'No se pudo cargar el personal disponible', 'error');
        }
      }
    });
    
    $('#changePersonnelModal').modal('show');
  });
  
  // Cargar runs de la zona para cambio
  function loadZoneRunsForChange(zoneId, availablePersonnel) {
    $.ajax({
      url: '{{ route("api.dashboard.zones-status") }}',
      method: 'GET',
      data: { 
        date: currentDate,
        shift_id: currentShiftId
      },
      success: function(response) {
        if (response.ok) {
          const zone = response.zones.find(z => z.zone_id == zoneId);
          if (zone) {
            renderRunsForChange(zone, availablePersonnel);
          }
        }
      }
    });
  }
  
  // Renderizar runs para cambio
  function renderRunsForChange(zone, availablePersonnel) {
    const container = $('#modal-runs-list');
    container.empty();
    
    if (zone.runs.length === 0) {
      container.append('<p class="text-muted">No hay recorridos programados</p>');
      return;
    }
    
    zone.runs.forEach(function(run) {
      const runPersonnel = zone.assigned_personnel;
      
      // Encontrar conductor y ayudantes del run
      const runConductors = runPersonnel.conductors.filter(c => c.run_id == run.id);
      const runHelpers = runPersonnel.helpers.filter(h => h.run_id == run.id);
      
      container.append(`
        <div class="card mb-3">
          <div class="card-header bg-light">
            <h6 class="mb-0">
              <i class="fas fa-route"></i> Recorrido #${run.id} - ${run.shift_name} - ${run.vehicle_code}
            </h6>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <label><strong>Conductor:</strong></label>
                <select class="form-control mb-2 change-personnel-select" 
                        data-run-id="${run.id}" 
                        data-role="conductor">
                  <option value="">Seleccione...</option>
                  ${availablePersonnel.conductors.map(emp => 
                    `<option value="${emp.id}">${emp.name} (DNI: ${emp.dni})</option>`
                  ).join('')}
                </select>
                ${runConductors.length > 0 ? `<small class="text-muted">Actual: ${runConductors[0].name}</small>` : '<small class="text-danger">Sin asignar</small>'}
              </div>
              <div class="col-md-6">
                <label><strong>Ayudante 1:</strong></label>
                <select class="form-control mb-2 change-personnel-select" 
                        data-run-id="${run.id}" 
                        data-role="ayudante1">
                  <option value="">Seleccione...</option>
                  ${availablePersonnel.helpers.map(emp => 
                    `<option value="${emp.id}">${emp.name} (DNI: ${emp.dni})</option>`
                  ).join('')}
                </select>
                ${runHelpers.filter(h => h.role === 'ayudante1').length > 0 ? 
                  `<small class="text-muted">Actual: ${runHelpers.find(h => h.role === 'ayudante1').name}</small>` : 
                  '<small class="text-danger">Sin asignar</small>'}
              </div>
              <div class="col-md-6">
                <label><strong>Ayudante 2:</strong></label>
                <select class="form-control mb-2 change-personnel-select" 
                        data-run-id="${run.id}" 
                        data-role="ayudante2">
                  <option value="">Seleccione...</option>
                  ${availablePersonnel.helpers.map(emp => 
                    `<option value="${emp.id}">${emp.name} (DNI: ${emp.dni})</option>`
                  ).join('')}
                </select>
                ${runHelpers.filter(h => h.role === 'ayudante2').length > 0 ? 
                  `<small class="text-muted">Actual: ${runHelpers.find(h => h.role === 'ayudante2').name}</small>` : 
                  '<small class="text-danger">Sin asignar</small>'}
              </div>
            </div>
            <div class="mt-2">
              <label><strong>Motivo del cambio:</strong></label>
              <textarea class="form-control change-reason" data-run-id="${run.id}" 
                        rows="2" placeholder="Describa el motivo del cambio..."></textarea>
            </div>
          </div>
        </div>
      `);
    });
  }
  
  // Guardar cambios de personal
  $(document).on('change', '.change-personnel-select', function() {
    const runId = $(this).data('run-id');
    const role = $(this).data('role');
    const employeeId = $(this).val();
    const reason = $(`.change-reason[data-run-id="${runId}"]`).val();
    
    if (!employeeId) {
      return;
    }
    
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: '¿Confirmar cambio?',
        text: 'Se actualizará la asignación de personal',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: '{{ route("api.dashboard.update-personnel") }}',
            method: 'POST',
            data: {
              run_id: runId,
              role: role,
              employee_id: employeeId,
              reason: reason || 'Cambio desde dashboard por ausencia de personal',
              _token: '{{ csrf_token() }}'
            },
            success: function(response) {
              if (response.ok) {
                Swal.fire('Éxito', response.msg, 'success').then(() => {
                  loadZonesStatus();
                  $('#changePersonnelModal').modal('hide');
                });
              } else {
                Swal.fire('Error', response.msg, 'error');
              }
            },
            error: function(xhr) {
              const msg = xhr.responseJSON?.msg || 'Error al actualizar la asignación';
              Swal.fire('Error', msg, 'error');
            }
          });
        }
      });
    }
  });

  // Variables para el modal de edición
  let pendingChanges = [];
  let currentRunData = null;
  let editOptions = null;

  // Abrir modal de edición de programación
  $(document).on('click', '.btn-edit-run', function() {
    const runId = $(this).data('run-id');
    const zoneName = $(this).data('zone-name');
    
    pendingChanges = [];
    $('#edit-run-id').val(runId);
    
    // Cargar datos del run
    $.ajax({
      url: `{{ url('api/dashboard/runs') }}/${runId}/edit`,
      method: 'GET',
      success: function(response) {
        if (response.ok) {
          currentRunData = response.data;
          loadRunDataIntoModal(response.data);
          
          // Cargar opciones disponibles
          loadEditOptions(runId);
        }
      },
      error: function() {
        if (typeof Swal !== 'undefined') {
          Swal.fire('Error', 'No se pudieron cargar los datos del recorrido', 'error');
        }
      }
    });
    
    $('#editProgramModal').modal('show');
  });

  // Cargar datos del run en el modal
  function loadRunDataIntoModal(data) {
    $('#current-shift').val(data.current_shift.name);
    $('#current-vehicle').val(data.current_vehicle.code);
    
    // Cargar personal actual
    const currentPersonnelSelect = $('#current-personnel');
    currentPersonnelSelect.empty().append('<option value="">Seleccione personal actual...</option>');
    data.current_personnel.forEach(function(person) {
      const roleLabel = person.role_label || person.role;
      currentPersonnelSelect.append(`<option value="${person.id}" data-role="${person.role}">${person.name} (${roleLabel})</option>`);
    });
    
    // Cargar cambios existentes
    renderChangesTable(data.changes);
  }

  // Cargar opciones disponibles
  function loadEditOptions(runId) {
    $.ajax({
      url: '{{ route("api.dashboard.edit-options") }}',
      method: 'GET',
      data: {
        date: currentDate,
        run_id: runId
      },
      success: function(response) {
        if (response.ok) {
          editOptions = response.data;
          
          // Cargar turnos
          const shiftSelect = $('#new-shift');
          shiftSelect.empty().append('<option value="">Seleccione un turno...</option>');
          if (response.data.shifts && response.data.shifts.length > 0) {
            response.data.shifts.forEach(function(shift) {
              shiftSelect.append(`<option value="${shift.id}">${shift.name}</option>`);
            });
          }
          
          // Cargar vehículos
          const vehicleSelect = $('#new-vehicle');
          vehicleSelect.empty().append('<option value="">Seleccione un vehículo...</option>');
          if (response.data.vehicles && response.data.vehicles.length > 0) {
            response.data.vehicles.forEach(function(vehicle) {
              vehicleSelect.append(`<option value="${vehicle.id}">${vehicle.code || vehicle.name}</option>`);
            });
          }
          
          // Cargar personal disponible
          const personnelSelect = $('#new-personnel');
          personnelSelect.empty().append('<option value="">Seleccione nuevo personal...</option>');
          
          console.log('Personal disponible recibido:', response.data.available_personnel);
          
          if (response.data.available_personnel && response.data.available_personnel.length > 0) {
            response.data.available_personnel.forEach(function(person) {
              personnelSelect.append(`<option value="${person.id}">${person.name} (DNI: ${person.dni})</option>`);
            });
          } else {
            console.warn('No hay personal disponible para la fecha seleccionada');
            personnelSelect.append('<option value="" disabled>No hay personal disponible con asistencia</option>');
          }
        }
      },
      error: function() {
        if (typeof Swal !== 'undefined') {
          Swal.fire('Error', 'No se pudieron cargar las opciones disponibles', 'error');
        }
      }
    });
  }

  // Agregar cambio de turno
  $('#btn-add-shift').on('click', function() {
    const newShiftId = $('#new-shift').val();
    const newShiftName = $('#new-shift option:selected').text();
    
    if (!newShiftId || newShiftId === currentRunData.current_shift.id) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Advertencia', 'Seleccione un turno diferente al actual', 'warning');
      }
      return;
    }
    
    pendingChanges.push({
      type: 'turno',
      old_value: currentRunData.current_shift.name,
      new_value: newShiftName,
      new_id: parseInt(newShiftId),
      notes: ''
    });
    
    renderChangesTable([...currentRunData.changes, ...pendingChanges]);
    $('#new-shift').val('');
  });

  // Agregar cambio de vehículo
  $('#btn-add-vehicle').on('click', function() {
    const newVehicleId = $('#new-vehicle').val();
    const newVehicleCode = $('#new-vehicle option:selected').text();
    
    if (!newVehicleId || newVehicleId == currentRunData.current_vehicle.id) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Advertencia', 'Seleccione un vehículo diferente al actual', 'warning');
      }
      return;
    }
    
    pendingChanges.push({
      type: 'vehiculo',
      old_value: currentRunData.current_vehicle.code,
      new_value: newVehicleCode,
      new_id: parseInt(newVehicleId),
      notes: ''
    });
    
    renderChangesTable([...currentRunData.changes, ...pendingChanges]);
    $('#new-vehicle').val('');
  });

  // Agregar cambio de personal
  $('#btn-add-personnel').on('click', function() {
    const currentPersonnelId = $('#current-personnel').val();
    const newPersonnelId = $('#new-personnel').val();
    const currentPersonnelName = $('#current-personnel option:selected').text();
    const newPersonnelName = $('#new-personnel option:selected').text();
    const role = $('#current-personnel option:selected').data('role');
    
    if (!currentPersonnelId || !newPersonnelId || currentPersonnelId == newPersonnelId) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Advertencia', 'Seleccione personal actual y nuevo diferente', 'warning');
      }
      return;
    }
    
    if (!role) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Advertencia', 'Debe seleccionar el personal actual primero', 'warning');
      }
      return;
    }
    
    pendingChanges.push({
      type: 'personal',
      old_value: currentPersonnelName,
      new_value: newPersonnelName,
      old_id: parseInt(currentPersonnelId),
      new_id: parseInt(newPersonnelId),
      role: role,
      notes: ''
    });
    
    renderChangesTable([...currentRunData.changes, ...pendingChanges]);
    $('#current-personnel').val('');
    $('#new-personnel').val('');
  });

  // Renderizar tabla de cambios
  function renderChangesTable(changes) {
    const tbody = $('#changes-tbody');
    tbody.empty();
    
    if (changes.length === 0) {
      tbody.append('<tr><td colspan="5" class="text-center text-muted">No hay cambios registrados</td></tr>');
      return;
    }
    
    changes.forEach(function(change, index) {
      const isPending = index >= (currentRunData.changes.length);
      const changeId = isPending ? null : change.id;
      const typeLabels = {
        'turno': 'Turno',
        'vehiculo': 'Vehículo',
        'personal': 'Personal'
      };
      
      tbody.append(`
        <tr data-change-index="${index}" ${isPending ? 'class="table-warning"' : ''}>
          <td>${typeLabels[change.type] || change.type}</td>
          <td>${change.old_value || '-'}</td>
          <td>${change.new_value || '-'}</td>
          <td>
            <input type="text" class="form-control form-control-sm change-notes" 
                   data-index="${index}" 
                   value="${change.notes || ''}" 
                   placeholder="Agregar notas...">
          </td>
          <td>
            ${isPending ? 
              `<button class="btn btn-sm btn-danger btn-remove-change" data-index="${index}">
                 <i class="fas fa-trash"></i>
               </button>` :
              `<button class="btn btn-sm btn-danger btn-delete-change" data-change-id="${changeId}">
                 <i class="fas fa-trash"></i>
               </button>`
            }
          </td>
        </tr>
      `);
    });
  }

  // Actualizar notas de cambios pendientes
  $(document).on('change', '.change-notes', function() {
    const index = $(this).data('index');
    if (pendingChanges[index]) {
      pendingChanges[index].notes = $(this).val();
    }
  });

  // Eliminar cambio pendiente
  $(document).on('click', '.btn-remove-change', function() {
    const index = $(this).data('index');
    pendingChanges.splice(index, 1);
    renderChangesTable([...currentRunData.changes, ...pendingChanges]);
  });

  // Eliminar cambio existente
  $(document).on('click', '.btn-delete-change', function() {
    const changeId = $(this).data('change-id');
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: '¿Eliminar cambio?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          // Marcar para eliminar
          const row = $(this).closest('tr');
          row.fadeOut(function() {
            row.remove();
            if ($('#changes-tbody tr').length === 0) {
              $('#changes-tbody').append('<tr><td colspan="5" class="text-center text-muted">No hay cambios registrados</td></tr>');
            }
          });
        }
      });
    }
  });

  // Guardar cambios
  $('#btn-save-changes').on('click', function() {
    const runId = $('#edit-run-id').val();
    const allChanges = [...currentRunData.changes, ...pendingChanges];
    const deleteIds = [];
    
    // Recopilar IDs de cambios a eliminar (los que fueron eliminados de la tabla)
    const existingChangeIds = currentRunData.changes.map(c => c.id);
    allChanges.forEach(function(change) {
      if (change.id && !existingChangeIds.includes(change.id)) {
        // Este cambio fue eliminado
        deleteIds.push(change.id);
      }
    });
    
    if (pendingChanges.length === 0 && deleteIds.length === 0) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Advertencia', 'No hay cambios para guardar', 'warning');
      }
      return;
    }
    
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: '¿Guardar cambios?',
        text: 'Se aplicarán los cambios a la programación',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          // Actualizar notas de cambios existentes
          const changesToSave = pendingChanges.map(function(change) {
            return {
              type: change.type,
              old_value: change.old_value,
              new_value: change.new_value,
              new_id: change.new_id,
              old_id: change.old_id || null,
              role: change.role || null,
              notes: change.notes || ''
            };
          });
          
          $.ajax({
            url: `{{ url('api/dashboard/runs') }}/${runId}/apply-changes`,
            method: 'POST',
            data: {
              changes: changesToSave,
              delete_ids: deleteIds,
              _token: '{{ csrf_token() }}'
            },
            success: function(response) {
              if (response.ok) {
                Swal.fire('Éxito', response.msg, 'success').then(() => {
                  $('#editProgramModal').modal('hide');
                  loadZonesStatus(); // Recargar zonas
                });
              } else {
                Swal.fire('Error', response.msg, 'error');
              }
            },
            error: function(xhr) {
              const msg = xhr.responseJSON?.msg || 'Error al guardar los cambios';
              Swal.fire('Error', msg, 'error');
            }
          });
        }
      });
    }
  });

  // Limpiar modal al cerrar
  $('#editProgramModal').on('hidden.bs.modal', function() {
    pendingChanges = [];
    currentRunData = null;
    editOptions = null;
    $('#new-shift').val('');
    $('#new-vehicle').val('');
    $('#current-personnel').val('');
    $('#new-personnel').val('');
  });

  // Abrir modal de reemplazo de personal faltante
  $(document).on('click', '.btn-replace-missing', function() {
    const runId = $(this).data('run-id');
    const zoneName = $(this).data('zone-name');
    currentRunIdForReplace = runId;
    
    $('#replace-missing-content').html(`
      <div class="text-center">
        <div class="spinner-border" role="status">
          <span class="sr-only">Cargando...</span>
        </div>
        <p class="mt-2">Detectando personal faltante...</p>
      </div>
    `);
    
    $.ajax({
      url: `/api/dashboard/runs/${runId}/missing-personnel`,
      method: 'GET',
      success: function(response) {
        if (response.ok && response.data) {
          renderMissingPersonnel(response.data, zoneName);
        } else {
          $('#replace-missing-content').html(`
            <div class="alert alert-info">
              <i class="fas fa-info-circle"></i> No se encontró personal faltante para este recorrido.
            </div>
          `);
          $('#btn-confirm-replace').hide();
        }
      },
      error: function(xhr) {
        const msg = xhr.responseJSON?.msg || 'Error al detectar personal faltante';
        $('#replace-missing-content').html(`
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> ${msg}
          </div>
        `);
        $('#btn-confirm-replace').hide();
      }
    });
    
    $('#replaceMissingModal').modal('show');
  });

  let currentRunIdForReplace = null;

  // Renderizar personal faltante
  function renderMissingPersonnel(data, zoneName) {
    if (!data.missing_personnel || data.missing_personnel.length === 0) {
      $('#replace-missing-content').html(`
        <div class="alert alert-success">
          <i class="fas fa-check-circle"></i> Todos los miembros del personal tienen asistencia registrada.
        </div>
      `);
      $('#btn-confirm-replace').hide();
      return;
    }

    let html = `
      <div class="alert alert-warning">
        <h6><i class="fas fa-exclamation-triangle"></i> Personal Faltante Detectado</h6>
        <p class="mb-0">Recorrido #${data.run_id} - Zona: ${zoneName} - Fecha: ${data.run_date}</p>
        <p class="mb-0"><strong>Total faltantes: ${data.total_missing}</strong></p>
      </div>
      <div class="table-responsive">
        <table class="table table-bordered">
          <thead class="thead-dark">
            <tr>
              <th>Personal Faltante</th>
              <th>Rol</th>
              <th>Motivo</th>
              <th>Reemplazo</th>
              <th>Motivo del Cambio</th>
            </tr>
          </thead>
          <tbody>
    `;

    data.missing_personnel.forEach(function(missing) {
      const replacementId = `replacement_${missing.personnel_id}`;
      const reasonId = `reason_${missing.personnel_id}`;
      const customReasonId = `custom_reason_${missing.personnel_id}`;
      
      html += `
        <tr>
          <td>
            <strong>${missing.employee_name}</strong><br>
            <small class="text-muted">DNI: ${missing.dni}</small>
          </td>
          <td><span class="badge badge-primary">${missing.role_label}</span></td>
          <td><small class="text-danger">${missing.reason}</small></td>
          <td>
            <select class="form-control form-control-sm replacement-select" 
                    id="${replacementId}" 
                    data-personnel-id="${missing.personnel_id}"
                    data-role="${missing.role}"
                    required>
              <option value="">Seleccione reemplazo...</option>
      `;
      
      if (missing.available_replacements && missing.available_replacements.length > 0) {
        missing.available_replacements.forEach(function(replacement) {
          html += `<option value="${replacement.id}">${replacement.name} (DNI: ${replacement.dni})</option>`;
        });
      } else {
        html += `<option value="" disabled>No hay reemplazos disponibles</option>`;
      }
      
      html += `
            </select>
          </td>
          <td>
            <select class="form-control form-control-sm change-reason-select" 
                    id="${reasonId}" 
                    data-personnel-id="${missing.personnel_id}">
              <option value="">Seleccione motivo...</option>
            </select>
            <textarea class="form-control form-control-sm mt-1 custom-reason-text" 
                      id="${customReasonId}" 
                      data-personnel-id="${missing.personnel_id}"
                      placeholder="O escriba un motivo personalizado..."
                      style="display: none;"
                      maxlength="500"></textarea>
          </td>
        </tr>
      `;
    });

    html += `
          </tbody>
        </table>
      </div>
    `;

    $('#replace-missing-content').html(html);
    $('#btn-confirm-replace').show();
    
    // Cargar motivos de cambio
    loadChangeReasons();
    
    // Manejar selección de motivo personalizado
    $('.change-reason-select').on('change', function() {
      const personnelId = $(this).data('personnel-id');
      const customReasonText = $(`#custom_reason_${personnelId}`);
      
      if ($(this).val() === 'custom') {
        customReasonText.show().prop('required', true);
      } else {
        customReasonText.hide().prop('required', false).val('');
      }
    });
  }

  // Cargar motivos de cambio
  function loadChangeReasons() {
    $.get('/api/change-reasons/active', function(reasons) {
      $('.change-reason-select').each(function() {
        const $select = $(this);
        if (reasons && reasons.length > 0) {
          reasons.forEach(function(reason) {
            $select.append(`<option value="${reason.id}">${reason.name}</option>`);
          });
        }
        $select.append('<option value="custom">-- Escribir motivo personalizado --</option>');
      });
    }).fail(function() {
      // Si no hay API, agregar opción de personalizado
      $('.change-reason-select').append('<option value="custom">-- Escribir motivo personalizado --</option>');
    });
  }

  // Confirmar reemplazos
  $('#btn-confirm-replace').on('click', function() {
    if (!currentRunIdForReplace) {
      Swal.fire('Error', 'No se ha seleccionado un recorrido', 'error');
      return;
    }
    
    const replacements = [];
    let hasErrors = false;
    
    $('.replacement-select').each(function() {
      const $select = $(this);
      const personnelId = $select.data('personnel-id');
      const newEmployeeId = $select.val();
      const reasonSelect = $(`#reason_${personnelId}`);
      const customReason = $(`#custom_reason_${personnelId}`).val();
      
      if (!newEmployeeId) {
        hasErrors = true;
        $select.addClass('is-invalid');
        return;
      }
      
      $select.removeClass('is-invalid');
      
      const replacement = {
        personnel_id: parseInt(personnelId),
        new_employee_id: parseInt(newEmployeeId),
      };
      
      if (reasonSelect.val() === 'custom' && customReason) {
        replacement.custom_reason = customReason;
      } else if (reasonSelect.val() && reasonSelect.val() !== 'custom') {
        replacement.reason_id = parseInt(reasonSelect.val());
      }
      
      replacements.push(replacement);
    });
    
    if (hasErrors) {
      Swal.fire('Error', 'Por favor seleccione un reemplazo para todos los miembros faltantes', 'error');
      return;
    }
    
    if (replacements.length === 0) {
      Swal.fire('Error', 'No hay reemplazos para procesar', 'error');
      return;
    }
    
    Swal.fire({
      title: '¿Confirmar reemplazos?',
      text: `Se reemplazarán ${replacements.length} miembro(s) del personal`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Sí, reemplazar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: '{{ route("api.dashboard.replace-missing-personnel") }}',
          method: 'POST',
          data: {
            run_id: currentRunIdForReplace,
            replacements: replacements,
            _token: '{{ csrf_token() }}'
          },
          success: function(response) {
            if (response.ok) {
              Swal.fire('Éxito', response.msg, 'success').then(() => {
                $('#replaceMissingModal').modal('hide');
                loadZonesStatus();
                currentRunIdForReplace = null;
              });
            } else {
              let errorMsg = response.msg || 'Error al reemplazar el personal';
              if (response.errors && response.errors.length > 0) {
                errorMsg += '<br><ul>';
                response.errors.forEach(function(err) {
                  errorMsg += '<li>' + err + '</li>';
                });
                errorMsg += '</ul>';
              }
              Swal.fire({
                icon: 'error',
                title: 'Error',
                html: errorMsg
              });
            }
          },
          error: function(xhr) {
            const msg = xhr.responseJSON?.msg || 'Error al reemplazar el personal';
            Swal.fire('Error', msg, 'error');
          }
        });
      }
    });
  });
});
</script>
@stop
