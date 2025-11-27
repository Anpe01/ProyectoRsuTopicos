@extends('adminlte::page')

@section('title', 'Mantenimiento de Vehículos')

@section('content_header')
  <h1>Mantenimiento de Vehículos</h1>
  <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content')
  <div id="alert-container"></div>

  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Mantenimientos</h3>
      <div class="card-tools">
        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalCreateMaintenance">
          <i class="fas fa-plus"></i> Nuevo Mantenimiento
        </button>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-striped" id="tbl-maintenances">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Fecha Inicio</th>
              <th>Fecha Fin</th>
              <th>Horarios</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach($maintenances as $maintenance)
              <tr data-maintenance-id="{{ $maintenance->id }}">
                <td>{{ $maintenance->name }}</td>
                <td>{{ $maintenance->start_date->format('d/m/Y') }}</td>
                <td>{{ $maintenance->end_date->format('d/m/Y') }}</td>
                <td>
                  <span class="badge badge-info">{{ $maintenance->schedules->count() }} horario(s)</span>
                </td>
                <td class="text-nowrap">
                  <button class="btn btn-info btn-sm btn-view-schedules" 
                          data-maintenance-id="{{ $maintenance->id }}"
                          data-maintenance-name="{{ $maintenance->name }}"
                          title="Ver horarios de mantenimiento">
                    <i class="fas fa-truck"></i> Ver Vehículo
                  </button>
                  <button class="btn btn-primary btn-sm btn-edit-maintenance" 
                          data-maintenance-id="{{ $maintenance->id }}"
                          data-name="{{ $maintenance->name }}"
                          data-start-date="{{ $maintenance->start_date->format('Y-m-d') }}"
                          data-end-date="{{ $maintenance->end_date->format('Y-m-d') }}"
                          title="Editar mantenimiento">
                    <i class="fas fa-edit"></i> Editar
                  </button>
                  <button class="btn btn-danger btn-sm btn-delete-maintenance" 
                          data-maintenance-id="{{ $maintenance->id }}"
                          data-maintenance-name="{{ $maintenance->name }}"
                          title="Eliminar mantenimiento">
                    <i class="fas fa-trash"></i> Eliminar
                  </button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Modal Crear Mantenimiento --}}
  <div class="modal fade" id="modalCreateMaintenance" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-success">
          <h5 class="modal-title">Nuevo Mantenimiento</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <form id="formCreateMaintenance">
          @csrf
          <div class="modal-body">
            <div class="form-group">
              <label for="name">Nombre del Mantenimiento <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="name" name="name" required>
              <div class="invalid-feedback"></div>
            </div>
            <div class="form-group">
              <label for="start_date">Fecha de Inicio <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="start_date" name="start_date" required>
              <div class="invalid-feedback"></div>
            </div>
            <div class="form-group">
              <label for="end_date">Fecha de Fin <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="end_date" name="end_date" required>
              <div class="invalid-feedback"></div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Modal Editar Mantenimiento --}}
  <div class="modal fade" id="modalEditMaintenance" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h5 class="modal-title">Editar Mantenimiento</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <form id="formEditMaintenance">
          <input type="hidden" id="edit_maintenance_id" name="maintenance_id">
          <div class="modal-body">
            <div class="form-group">
              <label for="edit_name">Nombre del Mantenimiento <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="edit_name" name="name" required>
              <div class="invalid-feedback"></div>
            </div>
            <div class="form-group">
              <label for="edit_start_date">Fecha de Inicio <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
              <div class="invalid-feedback"></div>
            </div>
            <div class="form-group">
              <label for="edit_end_date">Fecha de Fin <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
              <div class="invalid-feedback"></div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Modal Ver Horarios --}}
  <div class="modal fade" id="modalViewSchedules" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header bg-info">
          <h5 class="modal-title">
            <i class="fas fa-calendar-alt"></i> Horarios de Mantenimiento: <strong id="schedule_maintenance_name"></strong>
          </h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <button class="btn btn-success btn-sm" id="btnAddSchedule">
              <i class="fas fa-plus"></i> Agregar Horario
            </button>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="tbl-schedules">
              <thead class="thead-light">
                <tr>
                  <th>Vehículo</th>
                  <th>Responsable</th>
                  <th>Tipo de Mantenimiento</th>
                  <th>Hora de Inicio</th>
                  <th>Hora de Fin</th>
                  <th>Día de la Semana</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody id="schedules-tbody">
                <!-- Se llena dinámicamente -->
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  {{-- Modal Crear/Editar Horario --}}
  <div class="modal fade" id="modalSchedule" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-success">
          <h5 class="modal-title" id="modalScheduleTitle">Nuevo Horario</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <form id="formSchedule">
          <input type="hidden" id="schedule_maintenance_id" name="maintenance_id">
          <input type="hidden" id="schedule_id" name="schedule_id">
          <div class="modal-body">
            <div class="form-group">
              <label for="schedule_vehicle_id">Vehículo <span class="text-danger">*</span></label>
              <select class="form-control" id="schedule_vehicle_id" name="vehicle_id" required>
                <option value="">Cargando...</option>
              </select>
              <div class="invalid-feedback"></div>
            </div>
            <div class="form-group">
              <label for="schedule_maintenance_type">Tipo de Mantenimiento <span class="text-danger">*</span></label>
              <select class="form-control" id="schedule_maintenance_type" name="maintenance_type" required>
                <option value="">Seleccione...</option>
                <option value="Preventivo">Preventivo</option>
                <option value="Limpieza">Limpieza</option>
                <option value="Reparación">Reparación</option>
              </select>
              <div class="invalid-feedback"></div>
            </div>
            <div class="form-group">
              <label for="schedule_day_of_week">Día de la Semana <span class="text-danger">*</span></label>
              <select class="form-control" id="schedule_day_of_week" name="day_of_week" required>
                <option value="">Seleccione...</option>
                <option value="Lunes">Lunes</option>
                <option value="Martes">Martes</option>
                <option value="Miércoles">Miércoles</option>
                <option value="Jueves">Jueves</option>
                <option value="Viernes">Viernes</option>
                <option value="Sábado">Sábado</option>
                <option value="Domingo">Domingo</option>
              </select>
              <div class="invalid-feedback"></div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="schedule_start_time">Hora de Inicio <span class="text-danger">*</span></label>
                  <input type="time" class="form-control" id="schedule_start_time" name="start_time" required>
                  <div class="invalid-feedback"></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="schedule_end_time">Hora de Fin <span class="text-danger">*</span></label>
                  <input type="time" class="form-control" id="schedule_end_time" name="end_time" required>
                  <div class="invalid-feedback"></div>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="schedule_responsible_id">Responsable <span class="text-danger">*</span></label>
              <select class="form-control" id="schedule_responsible_id" name="responsible_id" required>
                <option value="">Cargando...</option>
              </select>
              <div class="invalid-feedback"></div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Modal Ver Días Generados --}}
  <div class="modal fade" id="modalViewDays" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header bg-info">
          <h5 class="modal-title">
            <i class="fas fa-calendar-check"></i> Detalles de los Días Generados para el Mantenimiento
          </h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="tbl-days">
              <thead class="thead-light">
                <tr>
                  <th>Fecha</th>
                  <th>Observación</th>
                  <th>Imagen</th>
                  <th>Estado de Ejecución</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody id="days-tbody">
                <!-- Se llena dinámicamente -->
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  {{-- Modal Editar Día --}}
  <div class="modal fade" id="modalEditDay" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h5 class="modal-title">Editar Día de Mantenimiento</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <form id="formEditDay" enctype="multipart/form-data">
          <input type="hidden" id="edit_day_id" name="day_id">
          <div class="modal-body">
            <div class="form-group">
              <label for="edit_day_date">Fecha</label>
              <input type="date" class="form-control" id="edit_day_date" name="date" readonly>
            </div>
            <div class="form-group">
              <label for="edit_day_observation">Observación</label>
              <textarea class="form-control" id="edit_day_observation" name="observation" rows="3"></textarea>
            </div>
            <div class="form-group">
              <label for="edit_day_image">Imagen</label>
              <input type="file" class="form-control-file" id="edit_day_image" name="image" accept="image/*">
              <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB</small>
              <div id="current_image_preview" class="mt-2"></div>
            </div>
            <div class="form-group">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="edit_day_executed" name="executed" value="1">
                <label class="form-check-label" for="edit_day_executed">
                  Mantenimiento realizado
                </label>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

@stop

@section('js')
<script>
// Obtener token CSRF
const csrfToken = '{{ csrf_token() }}';

// Agregar meta tag si no existe
if (typeof jQuery !== 'undefined') {
  jQuery(document).ready(function($) {
    if (!$('meta[name="csrf-token"]').length) {
      $('head').append('<meta name="csrf-token" content="' + csrfToken + '">');
    }
  });
}

$(document).ready(function() {
  // Configurar token CSRF para todas las peticiones AJAX
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || csrfToken
    }
  });

  // Variables globales
  let currentMaintenanceId = null;
  let vehicles = [];
  let employees = [];

  // Cargar datos iniciales
  loadVehicles();
  loadEmployees();

  // Función para mostrar alertas
  function showAlert(message, type = 'success') {
    const alertHtml = `
      <div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    `;
    $('#alert-container').html(alertHtml);
    setTimeout(() => {
      $('.alert').fadeOut();
    }, 5000);
  }

  // Cargar vehículos
  function loadVehicles() {
    $.get('{{ route("maintenances.get-vehicles") }}', function(data) {
      vehicles = data;
      let options = '<option value="">Seleccione...</option>';
      data.forEach(function(vehicle) {
        options += `<option value="${vehicle.id}">${vehicle.code} - ${vehicle.name}</option>`;
      });
      $('#schedule_vehicle_id').html(options);
    });
  }

  // Cargar empleados
  function loadEmployees() {
    $.get('{{ route("maintenances.get-employees") }}', function(data) {
      employees = data;
      let options = '<option value="">Seleccione...</option>';
      data.forEach(function(employee) {
        options += `<option value="${employee.id}">${employee.name}</option>`;
      });
      $('#schedule_responsible_id').html(options);
    });
  }

  // Validación de fechas en el frontend
  function validateDates(startDate, endDate) {
    if (!startDate || !endDate) {
      return { valid: false, message: 'Ambas fechas son requeridas.' };
    }
    
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    if (isNaN(start.getTime()) || isNaN(end.getTime())) {
      return { valid: false, message: 'Las fechas deben ser válidas.' };
    }
    
    if (end < start) {
      return { valid: false, message: 'La fecha de fin no puede ser menor que la fecha de inicio.' };
    }
    
    return { valid: true };
  }

  // Validación de horarios en el frontend
  function validateTimes(startTime, endTime) {
    if (!startTime || !endTime) {
      return { valid: false, message: 'Ambas horas son requeridas.' };
    }
    
    const start = startTime.split(':').map(Number);
    const end = endTime.split(':').map(Number);
    
    if (start.length !== 2 || end.length !== 2) {
      return { valid: false, message: 'El formato de las horas debe ser HH:mm.' };
    }
    
    const startMinutes = start[0] * 60 + start[1];
    const endMinutes = end[0] * 60 + end[1];
    
    if (endMinutes <= startMinutes) {
      return { valid: false, message: 'La hora de fin debe ser mayor que la hora de inicio.' };
    }
    
    return { valid: true };
  }

  // Crear mantenimiento
  $('#formCreateMaintenance').on('submit', function(e) {
    e.preventDefault();
    
    // Validar fechas en el frontend
    const startDate = $('#start_date').val();
    const endDate = $('#end_date').val();
    const dateValidation = validateDates(startDate, endDate);
    
    if (!dateValidation.valid) {
      showAlert(dateValidation.message, 'danger');
      $('#end_date').addClass('is-invalid');
      $('#end_date').siblings('.invalid-feedback').text(dateValidation.message);
      return;
    }
    
    // Limpiar validaciones previas
    $(this).find('.is-invalid').removeClass('is-invalid');
    $(this).find('.invalid-feedback').text('');
    
    const formData = $(this).serialize();
    const csrfToken = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
    
    $.ajax({
      url: '{{ route("maintenances.store") }}',
      method: 'POST',
      data: formData + '&_token=' + csrfToken,
      headers: {
        'X-CSRF-TOKEN': csrfToken
      },
      success: function(response) {
        if (response.success) {
          if (typeof Swal !== 'undefined') {
            Swal.fire('Éxito', response.message, 'success');
          } else {
            showAlert(response.message);
          }
          $('#modalCreateMaintenance').modal('hide');
          $('#formCreateMaintenance')[0].reset();
          setTimeout(() => location.reload(), 1000);
        }
      },
      error: function(xhr) {
        const errors = xhr.responseJSON?.errors || {};
        let errorMessage = 'Error al registrar el mantenimiento.';
        
        if (Object.keys(errors).length > 0) {
          Object.keys(errors).forEach(function(key) {
            const input = $(`#${key}`);
            input.addClass('is-invalid');
            input.siblings('.invalid-feedback').text(errors[key][0]);
            if (errors[key][0]) {
              errorMessage = errors[key][0];
            }
          });
          showAlert(errorMessage, 'danger');
        } else {
          showAlert(errorMessage, 'danger');
        }
      }
    });
  });

  // Editar mantenimiento (usar delegación de eventos)
  $(document).on('click', '.btn-edit-maintenance', function() {
    const maintenanceId = $(this).data('maintenance-id');
    const name = $(this).data('name');
    const startDate = $(this).data('start-date');
    const endDate = $(this).data('end-date');

    $('#edit_maintenance_id').val(maintenanceId);
    $('#edit_name').val(name);
    $('#edit_start_date').val(startDate);
    $('#edit_end_date').val(endDate);
    $('#modalEditMaintenance').modal('show');
  });

  $('#formEditMaintenance').on('submit', function(e) {
    e.preventDefault();
    
    // Validar fechas en el frontend
    const startDate = $('#edit_start_date').val();
    const endDate = $('#edit_end_date').val();
    const dateValidation = validateDates(startDate, endDate);
    
    if (!dateValidation.valid) {
      showAlert(dateValidation.message, 'danger');
      $('#edit_end_date').addClass('is-invalid');
      $('#edit_end_date').siblings('.invalid-feedback').text(dateValidation.message);
      return;
    }
    
    // Limpiar validaciones previas
    $(this).find('.is-invalid').removeClass('is-invalid');
    $(this).find('.invalid-feedback').text('');
    
    const maintenanceId = $('#edit_maintenance_id').val();
    const formData = $(this).serialize();
    const csrfToken = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
    
    $.ajax({
      url: `/maintenances/${maintenanceId}`,
      method: 'PUT',
      data: formData + '&_token=' + csrfToken,
      headers: {
        'X-CSRF-TOKEN': csrfToken
      },
      success: function(response) {
        if (response.success) {
          if (typeof Swal !== 'undefined') {
            Swal.fire('Éxito', response.message, 'success');
          } else {
            showAlert(response.message);
          }
          $('#modalEditMaintenance').modal('hide');
          setTimeout(() => location.reload(), 1000);
        }
      },
      error: function(xhr) {
        const errors = xhr.responseJSON?.errors || {};
        let errorMessage = 'Error al actualizar el mantenimiento.';
        
        if (Object.keys(errors).length > 0) {
          Object.keys(errors).forEach(function(key) {
            const input = $(`#edit_${key}`);
            input.addClass('is-invalid');
            input.siblings('.invalid-feedback').text(errors[key][0]);
            if (errors[key][0]) {
              errorMessage = errors[key][0];
            }
          });
          showAlert(errorMessage, 'danger');
        } else {
          showAlert(errorMessage, 'danger');
        }
      }
    });
  });

  // Eliminar mantenimiento
  $(document).on('click', '.btn-delete-maintenance', function() {
    const maintenanceId = $(this).data('maintenance-id');
    const maintenanceName = $(this).data('maintenance-name');

    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: '¿Eliminar mantenimiento?',
        text: `¿Está seguro de eliminar el mantenimiento "${maintenanceName}"? Esta acción también eliminará todos los horarios y días asociados.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          const csrfToken = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
          $.ajax({
            url: `/maintenances/${maintenanceId}`,
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': csrfToken
            },
            success: function(response) {
              if (response.success) {
                Swal.fire('Eliminado', response.message, 'success');
                setTimeout(() => location.reload(), 1000);
              }
            },
            error: function(xhr) {
              Swal.fire('Error', 'Error al eliminar el mantenimiento', 'error');
            }
          });
        }
      });
    } else {
      if (confirm(`¿Está seguro de eliminar el mantenimiento "${maintenanceName}"?`)) {
        const csrfToken = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
        $.ajax({
          url: `/maintenances/${maintenanceId}`,
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken
          },
          success: function(response) {
            if (response.success) {
              showAlert(response.message);
              location.reload();
            }
          },
          error: function(xhr) {
            showAlert('Error al eliminar el mantenimiento', 'danger');
          }
        });
      }
    }
  });

  // Ver horarios (usar delegación de eventos para elementos cargados dinámicamente)
  $(document).on('click', '.btn-view-schedules', function() {
    currentMaintenanceId = $(this).data('maintenance-id');
    const maintenanceName = $(this).data('maintenance-name');
    
    $('#schedule_maintenance_name').text(maintenanceName);
    $('#schedule_maintenance_id').val(currentMaintenanceId);
    loadSchedules(currentMaintenanceId);
    $('#modalViewSchedules').modal('show');
  });

  function loadSchedules(maintenanceId) {
    $.get(`{{ url('/maintenances') }}/${maintenanceId}/schedules`, function(schedules) {
      let tbody = '';
      if (schedules && schedules.length > 0) {
        schedules.forEach(function(schedule) {
          const vehicleName = schedule.vehicle ? (schedule.vehicle.code || '') + ' - ' + (schedule.vehicle.name || 'N/A') : 'N/A';
          const responsibleName = schedule.responsible ? (schedule.responsible.first_name || '') + ' ' + (schedule.responsible.last_name || '') : 'N/A';
          const startTime = schedule.start_time ? schedule.start_time.substring(0, 5) : '';
          const endTime = schedule.end_time ? schedule.end_time.substring(0, 5) : '';
          
          tbody += `
            <tr>
              <td><strong>${vehicleName}</strong></td>
              <td>${responsibleName}</td>
              <td><span class="badge badge-primary">${schedule.maintenance_type || ''}</span></td>
              <td>${startTime}</td>
              <td>${endTime}</td>
              <td>${schedule.day_of_week || ''}</td>
              <td class="text-nowrap">
                <button class="btn btn-info btn-sm btn-view-days" data-schedule-id="${schedule.id}" title="Ver días generados">
                  <i class="fas fa-eye"></i> Ver
                </button>
                <button class="btn btn-primary btn-sm btn-edit-schedule" 
                        data-schedule-id="${schedule.id}"
                        data-vehicle-id="${schedule.vehicle_id}"
                        data-maintenance-type="${schedule.maintenance_type}"
                        data-day-of-week="${schedule.day_of_week}"
                        data-start-time="${startTime}"
                        data-end-time="${endTime}"
                        data-responsible-id="${schedule.responsible_id}"
                        title="Editar horario">
                  <i class="fas fa-edit"></i> Editar
                </button>
                <button class="btn btn-danger btn-sm btn-delete-schedule" data-schedule-id="${schedule.id}" title="Eliminar horario">
                  <i class="fas fa-trash"></i> Eliminar
                </button>
              </td>
            </tr>
          `;
        });
      } else {
        tbody = '<tr><td colspan="7" class="text-center text-muted">No hay horarios registrados. Haga clic en "Agregar Horario" para crear uno.</td></tr>';
      }
      $('#schedules-tbody').html(tbody);
    }).fail(function() {
      $('#schedules-tbody').html('<tr><td colspan="7" class="text-center text-danger">Error al cargar los horarios</td></tr>');
    });
  }

  // Agregar horario
  $('#btnAddSchedule').on('click', function() {
    $('#modalScheduleTitle').text('Nuevo Horario');
    $('#formSchedule')[0].reset();
    $('#schedule_id').val('');
    $('#schedule_maintenance_id').val(currentMaintenanceId);
    $('#modalSchedule').modal('show');
  });

  // Editar horario
  $(document).on('click', '.btn-edit-schedule', function() {
    $('#modalScheduleTitle').text('Editar Horario');
    $('#schedule_id').val($(this).data('schedule-id'));
    $('#schedule_maintenance_id').val(currentMaintenanceId);
    $('#schedule_vehicle_id').val($(this).data('vehicle-id'));
    $('#schedule_maintenance_type').val($(this).data('maintenance-type'));
    $('#schedule_day_of_week').val($(this).data('day-of-week'));
    $('#schedule_start_time').val($(this).data('start-time'));
    $('#schedule_end_time').val($(this).data('end-time'));
    $('#schedule_responsible_id').val($(this).data('responsible-id'));
    $('#modalSchedule').modal('show');
  });

  // Guardar horario
  $('#formSchedule').on('submit', function(e) {
    e.preventDefault();
    
    // Validar horarios en el frontend
    const startTime = $('#schedule_start_time').val();
    const endTime = $('#schedule_end_time').val();
    const timeValidation = validateTimes(startTime, endTime);
    
    if (!timeValidation.valid) {
      showAlert(timeValidation.message, 'danger');
      $('#schedule_end_time').addClass('is-invalid');
      $('#schedule_end_time').siblings('.invalid-feedback').text(timeValidation.message);
      return;
    }
    
    // Validar que todos los campos requeridos estén llenos
    if (!$('#schedule_vehicle_id').val()) {
      showAlert('Debe seleccionar un vehículo.', 'danger');
      $('#schedule_vehicle_id').addClass('is-invalid');
      return;
    }
    
    if (!$('#schedule_responsible_id').val()) {
      showAlert('Debe seleccionar un responsable.', 'danger');
      $('#schedule_responsible_id').addClass('is-invalid');
      return;
    }
    
    // Limpiar validaciones previas
    $(this).find('.is-invalid').removeClass('is-invalid');
    $(this).find('.invalid-feedback').text('');
    
    const scheduleId = $('#schedule_id').val();
    const url = scheduleId 
      ? `/maintenances/schedules/${scheduleId}`
      : '{{ route("maintenances.schedules.store") }}';
    const method = scheduleId ? 'PUT' : 'POST';
    const csrfToken = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
    
    // Obtener todos los datos del formulario
    const formDataObj = {
      maintenance_id: $('#schedule_maintenance_id').val(),
      vehicle_id: $('#schedule_vehicle_id').val(),
      maintenance_type: $('#schedule_maintenance_type').val(),
      day_of_week: $('#schedule_day_of_week').val(),
      start_time: $('#schedule_start_time').val(),
      end_time: $('#schedule_end_time').val(),
      responsible_id: $('#schedule_responsible_id').val(),
      _token: csrfToken
    };
    
    if (method === 'PUT') {
      formDataObj._method = 'PUT';
    }
    
    console.log('Enviando datos del horario:', formDataObj);
    
    $.ajax({
      url: url,
      method: 'POST',
      data: formDataObj,
      headers: {
        'X-CSRF-TOKEN': csrfToken
      },
      success: function(response) {
        if (response.success) {
          if (typeof Swal !== 'undefined') {
            Swal.fire('Éxito', response.message, 'success');
          } else {
            showAlert(response.message);
          }
          $('#modalSchedule').modal('hide');
          $('#formSchedule')[0].reset();
          loadSchedules(currentMaintenanceId);
        }
      },
      error: function(xhr) {
        console.error('Error al guardar horario:', xhr);
        const response = xhr.responseJSON || {};
        const errors = response.errors || {};
        let errorMessage = response.message || 'Error al guardar el horario.';
        
        if (Object.keys(errors).length > 0) {
          // Mostrar todos los errores
          let errorMessages = [];
          Object.keys(errors).forEach(function(key) {
            const input = $(`#schedule_${key}`);
            input.addClass('is-invalid');
            const errorText = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
            input.siblings('.invalid-feedback').text(errorText);
            if (errorText) {
              errorMessages.push(errorText);
            }
          });
          errorMessage = errorMessages.join('. ') || errorMessage;
          showAlert(errorMessage, 'danger');
        } else {
          showAlert(errorMessage, 'danger');
        }
      }
    });
  });

  // Eliminar horario
  $(document).on('click', '.btn-delete-schedule', function() {
    const scheduleId = $(this).data('schedule-id');
    
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: '¿Eliminar horario?',
        text: 'Esta acción también eliminará todos los días generados automáticamente. ¿Está seguro?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          const csrfToken = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
          $.ajax({
            url: `/maintenances/schedules/${scheduleId}`,
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': csrfToken
            },
            success: function(response) {
              if (response.success) {
                Swal.fire('Eliminado', response.message, 'success');
                loadSchedules(currentMaintenanceId);
              }
            },
            error: function(xhr) {
              Swal.fire('Error', 'Error al eliminar el horario', 'error');
            }
          });
        }
      });
    } else {
      if (confirm('¿Está seguro de eliminar este horario? Esta acción también eliminará todos los días generados.')) {
        const csrfToken = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
        $.ajax({
          url: `/maintenances/schedules/${scheduleId}`,
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken
          },
          success: function(response) {
            if (response.success) {
              showAlert(response.message);
              loadSchedules(currentMaintenanceId);
            }
          },
          error: function(xhr) {
            showAlert('Error al eliminar el horario', 'danger');
          }
        });
      }
    }
  });

  // Ver días generados
  $(document).on('click', '.btn-view-days', function() {
    const scheduleId = $(this).data('schedule-id');
    // Marcar el botón como activo para poder recuperarlo después
    $('.btn-view-days').removeClass('active');
    $(this).addClass('active');
    loadDays(scheduleId);
    $('#modalViewDays').modal('show');
  });

  function loadDays(scheduleId) {
    $.get(`{{ url('/maintenances/schedules') }}/${scheduleId}/days`, function(response) {
      if (response.success) {
        let tbody = '';
        if (response.days && response.days.length > 0) {
          response.days.forEach(function(day) {
            // Formatear fecha de manera segura
            let formattedDate = day.date;
            try {
              // Si la fecha viene como string en formato YYYY-MM-DD
              if (typeof day.date === 'string') {
                const dateParts = day.date.split('T')[0].split('-');
                if (dateParts.length === 3) {
                  const date = new Date(parseInt(dateParts[0]), parseInt(dateParts[1]) - 1, parseInt(dateParts[2]));
                  if (!isNaN(date.getTime())) {
                    formattedDate = date.toLocaleDateString('es-ES', { 
                      weekday: 'long', 
                      year: 'numeric', 
                      month: 'long', 
                      day: 'numeric' 
                    });
                  }
                }
              } else {
                // Si viene como objeto Date o timestamp
                const date = new Date(day.date);
                if (!isNaN(date.getTime())) {
                  formattedDate = date.toLocaleDateString('es-ES', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                  });
                }
              }
            } catch (e) {
              console.error('Error al formatear fecha:', e, day.date);
              formattedDate = day.date || 'Fecha inválida';
            }
            let imageHtml = '<span class="text-muted">Sin imagen</span>';
            if (day.image_path || day.image_url) {
              // Priorizar image_url si está disponible (viene del backend con la URL correcta)
              let imageSrc = day.image_url;
              if (!imageSrc && day.image_path) {
                // Si no hay image_url, construir la URL usando la ruta de Laravel
                // Extraer el nombre del archivo del path
                const filename = day.image_path.split('/').pop();
                // Construir la URL manualmente
                imageSrc = `{{ url('/maintenances/images') }}/${filename}`;
              }
              // Agregar timestamp para evitar caché si es necesario
              if (imageSrc) {
                imageSrc += (imageSrc.indexOf('?') === -1 ? '?' : '&') + 't=' + Date.now();
                // Log para depuración (solo en desarrollo)
                if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                  console.log('Cargando imagen:', imageSrc, 'Path:', day.image_path, 'URL:', day.image_url);
                }
              }
              imageHtml = `<img src="${imageSrc}" alt="Imagen" style="max-width: 100px; max-height: 100px;" class="img-thumbnail" onerror="console.error('Error al cargar imagen:', '${imageSrc}'); this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='inline';"><span style="display:none;" class="text-muted">Imagen no disponible</span>`;
            }
            const executedBadge = day.executed 
              ? '<span class="badge badge-success">Realizado</span>'
              : '<span class="badge badge-warning">Pendiente</span>';
            
            tbody += `
              <tr>
                <td><strong>${formattedDate}</strong></td>
                <td>${day.observation || '<span class="text-muted">Sin observación</span>'}</td>
                <td>${imageHtml}</td>
                <td>${executedBadge}</td>
                <td class="text-nowrap">
                  <button class="btn btn-primary btn-sm btn-edit-day" 
                          data-day-id="${day.id}"
                          data-date="${day.date}"
                          data-observation="${day.observation || ''}"
                          data-image-path="${day.image_path || ''}"
                          data-image-url="${day.image_url || ''}"
                          data-executed="${day.executed}"
                          title="Editar día">
                    <i class="fas fa-edit"></i> Editar
                  </button>
                  <button class="btn btn-danger btn-sm btn-delete-day" data-day-id="${day.id}" title="Eliminar día">
                    <i class="fas fa-trash"></i> Eliminar
                  </button>
                </td>
              </tr>
            `;
          });
        } else {
          tbody = '<tr><td colspan="5" class="text-center">No hay días generados</td></tr>';
        }
        $('#days-tbody').html(tbody);
      }
    });
  }

  // Editar día
  $(document).on('click', '.btn-edit-day', function() {
    const dayId = $(this).data('day-id');
    const date = $(this).data('date');
    const observation = $(this).data('observation');
    const imagePath = $(this).data('image-path');
    const imageUrl = $(this).data('image-url');
    const executed = $(this).data('executed');

    $('#edit_day_id').val(dayId);
    $('#edit_day_date').val(date);
    $('#edit_day_observation').val(observation);
    $('#edit_day_executed').prop('checked', executed);
    
    // Mostrar imagen actual si existe (priorizar image_url si está disponible)
    if (imagePath || imageUrl) {
      let imgSrc = imageUrl;
      if (!imgSrc && imagePath) {
        // Construir la URL usando la ruta de Laravel
        const filename = imagePath.split('/').pop();
        // Construir la URL manualmente
        imgSrc = `{{ url('/maintenances/images') }}/${filename}`;
      }
      if (imgSrc) {
        // Agregar timestamp para evitar caché
        imgSrc += (imgSrc.indexOf('?') === -1 ? '?' : '&') + 't=' + Date.now();
        $('#current_image_preview').html(`
          <img src="${imgSrc}" alt="Imagen actual" style="max-width: 200px; max-height: 200px;" class="img-thumbnail" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='block';">
          <span style="display:none;" class="text-muted">Imagen no disponible</span>
          <br><small class="text-muted">Imagen actual</small>
        `);
      } else {
        $('#current_image_preview').html('');
      }
    } else {
      $('#current_image_preview').html('');
    }
    
    $('#modalEditDay').modal('show');
  });

  // Guardar día
  $('#formEditDay').on('submit', function(e) {
    e.preventDefault();
    const dayId = $('#edit_day_id').val();
    const formData = new FormData(this);
    const csrfToken = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
    formData.append('_method', 'PUT');
    formData.append('_token', csrfToken);
    
    $.ajax({
      url: `/maintenances/days/${dayId}`,
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      headers: {
        'X-CSRF-TOKEN': csrfToken
      },
      success: function(response) {
        if (response.success) {
          if (typeof Swal !== 'undefined') {
            Swal.fire('Éxito', response.message, 'success');
          } else {
            showAlert(response.message);
          }
          $('#modalEditDay').modal('hide');
          // Limpiar el formulario
          $('#formEditDay')[0].reset();
          $('#current_image_preview').html('');
          // Recargar días si el modal de días está abierto
          if ($('#modalViewDays').is(':visible')) {
            // Obtener el scheduleId del botón que abrió el modal de días
            const scheduleId = $('.btn-view-days.active').data('schedule-id') || 
                              $('.btn-view-days').first().data('schedule-id');
            if (scheduleId) {
              // Esperar un momento antes de recargar para asegurar que el servidor haya procesado
              setTimeout(function() {
                loadDays(scheduleId);
              }, 300);
            }
          }
        }
      },
      error: function(xhr) {
        console.error('Error al actualizar el día:', xhr);
        let errorMessage = 'Error al actualizar el día';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMessage = xhr.responseJSON.message;
        }
        showAlert(errorMessage, 'danger');
      }
    });
  });

  // Eliminar día
  $(document).on('click', '.btn-delete-day', function() {
    const dayId = $(this).data('day-id');
    
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: '¿Eliminar día?',
        text: '¿Está seguro de eliminar este registro?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          const csrfToken = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
          $.ajax({
            url: `/maintenances/days/${dayId}`,
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': csrfToken
            },
            success: function(response) {
              if (response.success) {
                Swal.fire('Eliminado', response.message, 'success');
                // Recargar días
                const scheduleId = $('.btn-view-days').first().data('schedule-id');
                if (scheduleId) loadDays(scheduleId);
              }
            },
            error: function(xhr) {
              Swal.fire('Error', 'Error al eliminar el día', 'error');
            }
          });
        }
      });
    } else {
      if (confirm('¿Está seguro de eliminar este registro?')) {
        const csrfToken = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
        $.ajax({
          url: `/maintenances/days/${dayId}`,
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken
          },
          success: function(response) {
            if (response.success) {
              showAlert(response.message);
              // Recargar días
              const scheduleId = $('.btn-view-days').first().data('schedule-id');
              if (scheduleId) loadDays(scheduleId);
            }
          },
          error: function(xhr) {
            showAlert('Error al eliminar el día', 'danger');
          }
        });
      }
    }
  });

  // Validación en tiempo real de fechas
  $('#start_date, #end_date').on('change', function() {
    const startDate = $('#start_date').val();
    const endDate = $('#end_date').val();
    
    if (startDate && endDate) {
      const validation = validateDates(startDate, endDate);
      if (!validation.valid) {
        $('#end_date').addClass('is-invalid');
        $('#end_date').siblings('.invalid-feedback').text(validation.message);
      } else {
        $('#end_date').removeClass('is-invalid');
        $('#end_date').siblings('.invalid-feedback').text('');
      }
    }
  });

  // Validación en tiempo real de fechas en edición
  $('#edit_start_date, #edit_end_date').on('change', function() {
    const startDate = $('#edit_start_date').val();
    const endDate = $('#edit_end_date').val();
    
    if (startDate && endDate) {
      const validation = validateDates(startDate, endDate);
      if (!validation.valid) {
        $('#edit_end_date').addClass('is-invalid');
        $('#edit_end_date').siblings('.invalid-feedback').text(validation.message);
      } else {
        $('#edit_end_date').removeClass('is-invalid');
        $('#edit_end_date').siblings('.invalid-feedback').text('');
      }
    }
  });

  // Validación en tiempo real de horarios
  $('#schedule_start_time, #schedule_end_time').on('change', function() {
    const startTime = $('#schedule_start_time').val();
    const endTime = $('#schedule_end_time').val();
    
    if (startTime && endTime) {
      const validation = validateTimes(startTime, endTime);
      if (!validation.valid) {
        $('#schedule_end_time').addClass('is-invalid');
        $('#schedule_end_time').siblings('.invalid-feedback').text(validation.message);
      } else {
        $('#schedule_end_time').removeClass('is-invalid');
        $('#schedule_end_time').siblings('.invalid-feedback').text('');
      }
    }
  });

  // Limpiar validaciones al cerrar modales
  $('.modal').on('hidden.bs.modal', function() {
    $(this).find('.is-invalid').removeClass('is-invalid');
    $(this).find('.invalid-feedback').text('');
    $(this).find('form')[0]?.reset();
  });
});
</script>
@stop

