@extends('adminlte::page')

@section('title', 'Grupo de Personal')

@section('plugins.Datatables', true)

@section('content_header')
  <div class="d-flex justify-content-between align-items-center">
    <h1 class="mb-0">Módulo: GRUPO DE PERSONAL</h1>
    <button id="btn-new-group" class="btn btn-primary">
      <i class="fas fa-plus"></i> Nuevo Grupo de Personal
    </button>
  </div>
@stop

@section('content')
<div class="container-fluid">
  <div class="card">
    <div class="card-body">
      <table id="tbl-workgroups" class="table table-striped table-hover w-100">
        <thead class="table-light">
          <tr>
            <th>Nombre</th>
            <th>Zona</th>
            <th>Turno</th>
            <th>Vehículo</th>
            <th>Días de Trabajo</th>
            <th style="width:120px">Acciones</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

{{-- Modal Nuevo Grupo de Personal --}}
<div class="modal fade" id="modalWorkgroup" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #007bff; color: white;">
        <h5 class="modal-title" id="modal-title-workgroup">Nuevo Grupo de Personal</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" style="opacity: 1;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="frm-workgroup">
          @csrf
          <input type="hidden" name="id" id="workgroup_id">
          
          {{-- Bloque 1: Datos obligatorios --}}
          <div class="mb-4">
            <h6 class="font-weight-bold mb-3 text-primary">Datos del Grupo <span class="text-danger">*</span></h6>
            
            <div class="row">
              {{-- Nombre del grupo --}}
              <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Nombre del grupo <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Ej: GRUPO ZONA A" required>
              </div>
              
              {{-- Zona --}}
              <div class="col-md-6 mb-3">
                <label for="zone_id" class="form-label">Zona <span class="text-danger">*</span></label>
                <select class="form-control" id="zone_id" name="zone_id" required>
                  <option value="">Seleccione una zona...</option>
                </select>
              </div>
            </div>
            
            <div class="row">
              {{-- Turno --}}
              <div class="col-md-6 mb-3">
                <label for="shift_id" class="form-label">Turno <span class="text-danger">*</span></label>
                <select class="form-control" id="shift_id" name="shift_id" required>
                  <option value="">Seleccione un turno...</option>
                </select>
              </div>
              
              {{-- Vehículo --}}
              <div class="col-md-6 mb-3">
                <label for="vehicle_id" class="form-label">Vehículo <span class="text-danger">*</span></label>
                <select class="form-control" id="vehicle_id" name="vehicle_id" required>
                  <option value="">Seleccione un vehículo...</option>
                </select>
              </div>
            </div>
            
            {{-- Días de trabajo --}}
            <div class="mb-3">
              <label class="form-label d-block">Días de trabajo <span class="text-danger">*</span></label>
              <div class="d-flex flex-wrap gap-3">
                <div class="form-check">
                  <input class="form-check-input day-checkbox" type="checkbox" id="day_1" name="days[]" value="1">
                  <label class="form-check-label" for="day_1">Lunes</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input day-checkbox" type="checkbox" id="day_2" name="days[]" value="2">
                  <label class="form-check-label" for="day_2">Martes</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input day-checkbox" type="checkbox" id="day_3" name="days[]" value="3">
                  <label class="form-check-label" for="day_3">Miércoles</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input day-checkbox" type="checkbox" id="day_4" name="days[]" value="4">
                  <label class="form-check-label" for="day_4">Jueves</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input day-checkbox" type="checkbox" id="day_5" name="days[]" value="5">
                  <label class="form-check-label" for="day_5">Viernes</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input day-checkbox" type="checkbox" id="day_6" name="days[]" value="6">
                  <label class="form-check-label" for="day_6">Sábado</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input day-checkbox" type="checkbox" id="day_7" name="days[]" value="7">
                  <label class="form-check-label" for="day_7">Domingo</label>
                </div>
              </div>
            </div>
          </div>
          
          {{-- Nota intermedia --}}
          <div class="alert alert-info mb-4" role="alert">
            <i class="fas fa-info-circle"></i> Estos datos son para pre configuración, no son obligatorios.
          </div>
          
          {{-- Bloque 2: Asignación de personal (opcional) --}}
          <div class="mb-4">
            <h6 class="font-weight-bold mb-3 text-secondary">Asignación de Personal</h6>
            
            {{-- Conductor --}}
            <div class="mb-3">
              <label for="conductor_id" class="form-label">Conductor</label>
              <select class="form-control" id="conductor_id" name="conductor_id">
                <option value="">Seleccione un conductor...</option>
              </select>
            </div>
            
            <div class="row">
              {{-- Ayudante 1 --}}
              <div class="col-md-6 mb-3">
                <label for="ayudante1_id" class="form-label">Ayudante 1</label>
                <select class="form-control" id="ayudante1_id" name="ayudante1_id">
                  <option value="">Seleccione un ayudante...</option>
                </select>
              </div>
              
              {{-- Ayudante 2 --}}
              <div class="col-md-6 mb-3">
                <label for="ayudante2_id" class="form-label">Ayudante 2</label>
                <select class="form-control" id="ayudante2_id" name="ayudante2_id">
                  <option value="">Seleccione un ayudante...</option>
                </select>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
        <button type="button" id="btn-save-workgroup" class="btn btn-primary">
          <i class="fas fa-save"></i> Guardar
        </button>
      </div>
    </div>
  </div>
</div>
@stop

@section('js')
<script>
let DT; // Variable global para DataTable

$(document).ready(function() {
  // Inicializar DataTable
  DT = $('#tbl-workgroups').DataTable({
    ajax: {
      url: "{{ route('workgroups.index') }}",
      dataSrc: 'data'
    },
    columns: [
      { data: 'name' },
      { data: 'zone_name' },
      { data: 'shift_name' },
      { data: 'vehicle_name' },
      { data: 'days_display' },
      { 
        data: null,
        orderable: false,
        searchable: false,
        render: function(data, type, row) {
          const id = row.id || data.id;
          return `
            <div class="btn-group btn-group-sm">
              <button class="btn btn-primary btn-edit" data-id="${id}" title="Editar">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn btn-danger btn-del" data-id="${id}" title="Eliminar">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          `;
        }
      }
    ],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
    }
  });
  
  // Cargar datos para selectores
  loadSelectData();
  
  // Abrir modal para nuevo grupo
  $('#btn-new-group').on('click', function() {
    openWorkgroupModal();
  });
  
  // Inicializar SweetAlert2 si no está disponible
  if (typeof Swal === 'undefined') {
    console.warn('SweetAlert2 no está cargado. Asegúrate de incluirlo en el layout.');
  }
  
  // Delegación de eventos para editar/eliminar
  $('#tbl-workgroups tbody').on('click', '.btn-edit', function() {
    const workgroupId = $(this).data('id');
    editWorkgroup(workgroupId);
  });
  
  $('#tbl-workgroups tbody').on('click', '.btn-del', function() {
    const workgroupId = $(this).data('id');
    if (!confirm('¿Está seguro de eliminar este grupo de personal?')) return;
    
    $.ajax({
      url: `/workgroups/${workgroupId}`,
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      success: function(response) {
        if (response.ok) {
          DT.ajax.reload(null, false);
          Swal.fire('Éxito', 'Grupo eliminado correctamente', 'success');
        }
      },
      error: function(xhr) {
        Swal.fire('Error', 'No se pudo eliminar el grupo', 'error');
      }
    });
  });
  
  // Guardar grupo
  $('#btn-save-workgroup').on('click', function() {
    saveWorkgroup();
  });
  
  // Limpiar formulario al cerrar modal
  $('#modalWorkgroup').on('hidden.bs.modal', function() {
    $('#frm-workgroup')[0].reset();
    $('#workgroup_id').val('');
    $('.day-checkbox').prop('checked', false);
    loadSelectData(); // Recargar selects
  });
});

function loadSelectData() {
  // Cargar zonas
  $.get('/api/zones/active').done(function(zones) {
    const $zoneSelect = $('#zone_id');
    $zoneSelect.html('<option value="">Seleccione una zona...</option>');
    if (zones && zones.length > 0) {
      zones.forEach(function(zone) {
        $zoneSelect.append(`<option value="${zone.id}">${zone.name}</option>`);
      });
    }
  }).fail(function(xhr, status, error) {
    console.error('Error al cargar zonas:', error);
  });
  
  // Cargar turnos
  $.get('/api/shifts/active').done(function(shifts) {
    const $shiftSelect = $('#shift_id');
    $shiftSelect.html('<option value="">Seleccione un turno...</option>');
    if (shifts && shifts.length > 0) {
      shifts.forEach(function(shift) {
        $shiftSelect.append(`<option value="${shift.id}">${shift.name}</option>`);
      });
    }
  }).fail(function(xhr, status, error) {
    console.error('Error al cargar turnos:', error);
  });
  
  // Cargar vehículos
  $.get('/api/vehicles/active').done(function(vehicles) {
    console.log('Vehículos cargados:', vehicles);
    const $vehicleSelect = $('#vehicle_id');
    $vehicleSelect.html('<option value="">Seleccione un vehículo...</option>');
    if (vehicles && vehicles.length > 0) {
      vehicles.forEach(function(vehicle) {
        const capacity = vehicle.people_capacity || 0;
        const vehicleName = vehicle.code || vehicle.name || 'Sin nombre';
        $vehicleSelect.append(`<option value="${vehicle.id}">${vehicleName} (Capacidad: ${capacity})</option>`);
      });
    } else {
      console.warn('No se encontraron vehículos activos');
      $vehicleSelect.append('<option value="">No hay vehículos disponibles</option>');
    }
  }).fail(function(xhr, status, error) {
    console.error('Error al cargar vehículos:', error, xhr);
    const $vehicleSelect = $('#vehicle_id');
    $vehicleSelect.html('<option value="">Error al cargar vehículos</option>');
  });
  
  // Cargar empleados (todos para conductor y ayudantes)
  $.get('/api/employees/active').done(function(employees) {
    const $conductorSelect = $('#conductor_id');
    const $ayudante1Select = $('#ayudante1_id');
    const $ayudante2Select = $('#ayudante2_id');
    
    $conductorSelect.html('<option value="">Seleccione un conductor...</option>');
    $ayudante1Select.html('<option value="">Seleccione un ayudante...</option>');
    $ayudante2Select.html('<option value="">Seleccione un ayudante...</option>');
    
    if (employees && employees.length > 0) {
      employees.forEach(function(emp) {
        const fullname = `${emp.first_name || ''} ${emp.last_name || ''}`.trim();
        const option = `<option value="${emp.id}">${fullname}</option>`;
        $conductorSelect.append(option);
        $ayudante1Select.append(option);
        $ayudante2Select.append(option);
      });
    }
  }).fail(function(xhr, status, error) {
    console.error('Error al cargar empleados:', error);
  });
}

function openWorkgroupModal(data = null) {
  $('#frm-workgroup')[0].reset();
  $('#workgroup_id').val(data?.id || '');
  $('#modal-title-workgroup').text(data ? 'Editar Grupo de Personal' : 'Nuevo Grupo de Personal');
  
  // Limpiar y recargar todos los selects
  $('#zone_id, #shift_id, #vehicle_id, #conductor_id, #ayudante1_id, #ayudante2_id').empty();
  
  // Recargar datos de los selects
  loadSelectData();
  
  // Esperar a que se carguen los datos antes de establecer valores
  setTimeout(function() {
    if (data) {
      $('#name').val(data.name || '');
      $('#zone_id').val(data.zone_id || '');
      $('#shift_id').val(data.shift_id || '');
      $('#vehicle_id').val(data.vehicle_id || '');
      
      // Cargar días de trabajo
      if (data.days_of_week && Array.isArray(data.days_of_week)) {
        data.days_of_week.forEach(function(day) {
          $(`#day_${day}`).prop('checked', true);
        });
      }
      
      // Cargar personal asignado
      if (data.personnel) {
        data.personnel.forEach(function(person) {
          if (person.role === 'conductor') {
            $('#conductor_id').val(person.staff_id || person.id);
          } else if (person.role === 'ayudante') {
            // Asignar a ayudante1 o ayudante2 según disponibilidad
            if (!$('#ayudante1_id').val()) {
              $('#ayudante1_id').val(person.staff_id || person.id);
            } else if (!$('#ayudante2_id').val()) {
              $('#ayudante2_id').val(person.staff_id || person.id);
            }
          }
        });
      }
    } else {
      // Valores por defecto: Lunes a Viernes marcados
      [1, 2, 3, 4, 5].forEach(function(day) {
        $(`#day_${day}`).prop('checked', true);
      });
    }
  }, 300);
  
  $('#modalWorkgroup').modal('show');
}

function editWorkgroup(id) {
  $.get(`/workgroups/${id}`).done(function(response) {
    if (response.ok && response.data) {
      openWorkgroupModal(response.data);
    }
  }).fail(function() {
    Swal.fire('Error', 'No se pudo cargar el grupo', 'error');
  });
}

function saveWorkgroup() {
  const form = $('#frm-workgroup')[0];
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }
  
  // Validar días de trabajo
  const selectedDays = $('.day-checkbox:checked').length;
  if (selectedDays === 0) {
    Swal.fire('Error', 'Debe seleccionar al menos un día de trabajo', 'error');
    return;
  }
  
  const workgroupId = $('#workgroup_id').val();
  const url = workgroupId ? `/workgroups/${workgroupId}` : '/workgroups';
  const method = workgroupId ? 'PUT' : 'POST';
  
  // Recopilar datos del formulario
  const formData = {
    name: $('#name').val(),
    zone_id: $('#zone_id').val(),
    shift_id: $('#shift_id').val(),
    vehicle_id: $('#vehicle_id').val(),
    days_of_week: $('.day-checkbox:checked').map(function() {
      return parseInt($(this).val());
    }).get(),
    conductor_id: $('#conductor_id').val() || null,
    ayudante1_id: $('#ayudante1_id').val() || null,
    ayudante2_id: $('#ayudante2_id').val() || null
  };
  
  // Mostrar loading
  const $btn = $('#btn-save-workgroup');
  const originalHtml = $btn.html();
  $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
  
  $.ajax({
    url: url,
    method: method,
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    data: JSON.stringify(formData),
    success: function(response) {
      if (response.ok) {
        $('#modalWorkgroup').modal('hide');
        // Recargar DataTable
        if (DT) {
          DT.ajax.reload(null, false);
        } else {
          // Si DT no está definido, recargar la página
          location.reload();
        }
        Swal.fire('Éxito', response.msg || 'Grupo guardado correctamente', 'success');
      } else {
        Swal.fire('Error', response.msg || 'No se pudo guardar el grupo', 'error');
      }
    },
    error: function(xhr) {
      let errorMsg = 'Error al guardar el grupo';
      
      if (xhr.responseJSON) {
        if (xhr.responseJSON.errors) {
          const errors = Object.values(xhr.responseJSON.errors).flat();
          errorMsg = errors.join('<br>');
        } else if (xhr.responseJSON.msg) {
          errorMsg = xhr.responseJSON.msg;
          if (xhr.responseJSON.exception) {
            console.error('Excepción:', xhr.responseJSON.exception, xhr.responseJSON.file);
          }
        } else if (xhr.responseJSON.message) {
          errorMsg = xhr.responseJSON.message;
        }
      }
      
      console.error('Error completo:', xhr.responseJSON || xhr.responseText);
      Swal.fire('Error', errorMsg, 'error');
    },
    complete: function() {
      $btn.prop('disabled', false).html(originalHtml);
    }
  });
}
</script>
@stop
