@extends('adminlte::page')

@section('title', 'Actualización Masiva de Programación')

@section('plugins.Sweetalert2', true)

@section('content_header')
  <div class="card card-outline card-warning">
    <div class="card-header">
      <div class="d-flex justify-content-between align-items-center w-100">
        <h1 class="mb-0" style="font-size: 1.5rem; font-weight: 600;">
          <i class="fas fa-edit"></i> Actualización Masiva de Programación
        </h1>
        <a href="{{ route('programaciones.index') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Volver
        </a>
      </div>
    </div>
  </div>
@stop

@section('content')
<div class="container-fluid">
  <form id="bulk-update-form">
    @csrf
    <div class="card">
      <div class="card-header bg-primary">
        <h3 class="card-title">
          <i class="fas fa-filter"></i> Criterios de Selección
        </h3>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- Rango de Fechas -->
          <div class="col-md-6">
            <div class="form-group">
              <label>Fecha de Inicio <span class="text-danger">*</span></label>
              <input type="date" name="start_date" id="start_date" class="form-control" required>
              @error('start_date')
                <small class="text-danger d-block">{{ $message }}</small>
              @enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Fecha de Fin <span class="text-danger">*</span></label>
              <input type="date" name="end_date" id="end_date" class="form-control" required>
              @error('end_date')
                <small class="text-danger d-block">{{ $message }}</small>
              @enderror
            </div>
          </div>
        </div>

        <div class="row">
          <!-- Zona -->
          <div class="col-md-6">
            <div class="form-group">
              <label>Zona <span class="text-danger">*</span></label>
              <select name="zone_id" id="zone_id" class="form-control" required>
                <option value="">Seleccione una zona</option>
                @foreach($zones as $zone)
                  <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                @endforeach
              </select>
              @error('zone_id')
                <small class="text-danger d-block">{{ $message }}</small>
              @enderror
            </div>
          </div>

          <!-- Tipo de Cambio -->
          <div class="col-md-6">
            <div class="form-group">
              <label>Tipo de Cambio <span class="text-danger">*</span></label>
              <select name="change_type" id="change_type" class="form-control" required>
                <option value="">Seleccione el tipo de cambio</option>
                <option value="conductor">Conductor</option>
                <option value="ocupante">Ocupante</option>
                <option value="turno">Turno</option>
                <option value="vehiculo">Vehículo</option>
              </select>
              <small class="form-text text-muted">Seleccione el tipo de cambio que desea aplicar masivamente</small>
              @error('change_type')
                <small class="text-danger d-block">{{ $message }}</small>
              @enderror
            </div>
          </div>
        </div>

        <!-- Selector de Rol para Ocupante -->
        <div class="row" id="ocupante_role_row" style="display: none;">
          <div class="col-md-6">
            <div class="form-group">
              <label>Rol del Ocupante <span class="text-danger">*</span></label>
              <select name="ocupante_role" id="ocupante_role" class="form-control">
                <option value="">Seleccione el rol</option>
                <option value="ayudante1">Ayudante 1</option>
                <option value="ayudante2">Ayudante 2</option>
              </select>
              @error('ocupante_role')
                <small class="text-danger d-block">{{ $message }}</small>
              @enderror
            </div>
          </div>
        </div>

        <!-- Selector del Nuevo Valor -->
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label id="new_value_label">Nuevo Valor <span class="text-danger">*</span></label>
              <select name="new_value_id" id="new_value_id" class="form-control" required>
                <option value="">Seleccione...</option>
              </select>
              @error('new_value_id')
                <small class="text-danger d-block">{{ $message }}</small>
              @enderror
            </div>
          </div>
        </div>

        <!-- Motivo -->
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label>Motivo del Cambio <span class="text-danger">*</span></label>
              <select name="change_reason_id" id="change_reason_id" class="form-control">
                <option value="">Seleccione un motivo predefinido o escriba uno personalizado</option>
                @foreach($changeReasons as $reason)
                  <option value="{{ $reason->id }}" data-description="{{ $reason->description ?? '' }}">
                    {{ $reason->name }}
                    @if($reason->is_predefined)
                      <span class="badge badge-info">Predefinido</span>
                    @endif
                  </option>
                @endforeach
                <option value="custom">-- Escribir motivo personalizado --</option>
              </select>
              <small class="form-text text-muted">Puede seleccionar un motivo predefinido o escribir uno personalizado</small>
            </div>
          </div>
        </div>
        
        <!-- Campo de motivo personalizado (oculto inicialmente) -->
        <div class="row" id="custom_reason_row" style="display: none;">
          <div class="col-md-12">
            <div class="form-group">
              <label>Motivo Personalizado <span class="text-danger">*</span></label>
              <textarea name="reason" id="reason" class="form-control" rows="3" 
                        placeholder="Describa el motivo de la actualización masiva..." maxlength="500"></textarea>
              <small class="text-muted">Máximo 500 caracteres</small>
              @error('reason')
                <small class="text-danger d-block">{{ $message }}</small>
              @enderror
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-footer">
        <button type="submit" class="btn btn-primary btn-lg">
          <i class="fas fa-save"></i> Aplicar Actualización Masiva
        </button>
        <a href="{{ route('programaciones.index') }}" class="btn btn-secondary btn-lg">
          <i class="fas fa-times"></i> Cancelar
        </a>
      </div>
    </div>
  </form>
</div>

<!-- Modal de Confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">
          <i class="fas fa-exclamation-triangle"></i> Confirmar Actualización Masiva
        </h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p id="confirm-message"></p>
        <div class="alert alert-warning">
          <strong>Advertencia:</strong> Esta acción afectará a todas las programaciones que cumplan los criterios 
          seleccionados y registrará los cambios en el historial. Esta acción no se puede deshacer fácilmente.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="confirm-submit">
          <i class="fas fa-check"></i> Confirmar y Aplicar
        </button>
      </div>
    </div>
  </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
  const employees = @json($employees);
  const shifts = @json($shifts);
  const vehicles = @json($vehicles);
  const changeReasons = @json($changeReasons);

  // Manejar selección de motivo
  $('#change_reason_id').on('change', function() {
    const selectedValue = $(this).val();
    const $customRow = $('#custom_reason_row');
    const $reasonTextarea = $('#reason');
    
    if (selectedValue === 'custom') {
      $customRow.show();
      $reasonTextarea.prop('required', true);
      $('#change_reason_id').prop('required', false);
    } else if (selectedValue) {
      $customRow.hide();
      $reasonTextarea.prop('required', false);
      $reasonTextarea.val('');
      $('#change_reason_id').prop('required', true);
    } else {
      $customRow.hide();
      $reasonTextarea.prop('required', false);
      $reasonTextarea.val('');
    }
  });

  // Mostrar/ocultar selector de rol para ocupante
  $('#change_type').on('change', function() {
    const changeType = $(this).val();
    const $ocupanteRow = $('#ocupante_role_row');
    const $newValueSelect = $('#new_value_id');
    const $newValueLabel = $('#new_value_label');
    
    // Mostrar selector de rol solo para ocupante
    if (changeType === 'ocupante') {
      $ocupanteRow.show();
      $('#ocupante_role').prop('required', true);
    } else {
      $ocupanteRow.hide();
      $('#ocupante_role').prop('required', false);
    }

    // Actualizar opciones según el tipo de cambio
    $newValueSelect.empty().append('<option value="">Seleccione...</option>');
    
    switch(changeType) {
      case 'conductor':
      case 'ocupante':
        $newValueLabel.text('Nuevo Empleado *');
        employees.forEach(function(emp) {
          const name = emp.first_name + ' ' + emp.last_name + ' (DNI: ' + emp.dni + ')';
          $newValueSelect.append(`<option value="${emp.id}">${name}</option>`);
        });
        break;
      case 'turno':
        $newValueLabel.text('Nuevo Turno *');
        shifts.forEach(function(shift) {
          $newValueSelect.append(`<option value="${shift.id}">${shift.name}</option>`);
        });
        break;
      case 'vehiculo':
        $newValueLabel.text('Nuevo Vehículo *');
        vehicles.forEach(function(vehicle) {
          const code = vehicle.code || vehicle.name || 'N/A';
          $newValueSelect.append(`<option value="${vehicle.id}">${code} - ${vehicle.name}</option>`);
        });
        break;
      default:
        $newValueLabel.text('Nuevo Valor *');
    }
  });

  // Manejar envío del formulario
  $('#bulk-update-form').on('submit', function(e) {
    e.preventDefault();
    
    // Validar campos requeridos
    const changeType = $('#change_type').val();
    if (changeType === 'ocupante' && !$('#ocupante_role').val()) {
      alert('Por favor seleccione el rol del ocupante.');
      return;
    }

    // Mostrar modal de confirmación
    const startDate = $('#start_date').val();
    const endDate = $('#end_date').val();
    const zoneName = $('#zone_id option:selected').text();
    const changeTypeName = $('#change_type option:selected').text();
    const newValueName = $('#new_value_id option:selected').text();
    
    $('#confirm-message').html(`
      <p><strong>Resumen de la actualización:</strong></p>
      <ul>
        <li><strong>Rango de fechas:</strong> ${startDate} a ${endDate}</li>
        <li><strong>Zona:</strong> ${zoneName}</li>
        <li><strong>Tipo de cambio:</strong> ${changeTypeName}</li>
        <li><strong>Nuevo valor:</strong> ${newValueName}</li>
      </ul>
      <p>Se actualizarán todas las programaciones que cumplan estos criterios.</p>
    `);
    
    $('#confirmModal').modal('show');
  });

  // Confirmar y enviar
  $('#confirm-submit').on('click', function() {
    const $btn = $(this);
    const $originalText = $btn.html();
    
    // Recopilar datos del formulario y convertir tipos
    const zoneIdVal = $('#zone_id').val();
    const newValueIdVal = $('#new_value_id').val();
    
    // Obtener motivo (predefinido o personalizado)
    const changeReasonId = $('#change_reason_id').val();
    const customReason = $('#reason').val().trim();
    
    const formData = {
      _token: $('input[name="_token"]').val(),
      start_date: $('#start_date').val(),
      end_date: $('#end_date').val(),
      zone_id: zoneIdVal ? parseInt(zoneIdVal) : '',
      change_type: $('#change_type').val(),
      new_value_id: newValueIdVal ? parseInt(newValueIdVal) : '',
    };
    
    // Agregar motivo (predefinido o personalizado)
    if (changeReasonId && changeReasonId !== 'custom') {
      formData.change_reason_id = changeReasonId;
    } else if (customReason) {
      formData.reason = customReason;
    }
    
    // Agregar ocupante_role solo si el tipo de cambio es ocupante
    if (formData.change_type === 'ocupante') {
      formData.ocupante_role = $('#ocupante_role').val();
    }
    
    // Validar que todos los campos requeridos estén presentes
    if (!formData.start_date || !formData.end_date || !formData.zone_id || 
        !formData.change_type || !formData.new_value_id || 
        (!formData.change_reason_id && !formData.reason)) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Por favor complete todos los campos requeridos'
      });
      $btn.prop('disabled', false).html($originalText);
      return;
    }
    
    if (formData.change_type === 'ocupante' && !formData.ocupante_role) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Por favor seleccione el rol del ocupante'
      });
      $btn.prop('disabled', false).html($originalText);
      return;
    }
    
    // Validar que los IDs sean números válidos
    if (isNaN(formData.zone_id) || isNaN(formData.new_value_id)) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Los valores seleccionados no son válidos'
      });
      $btn.prop('disabled', false).html($originalText);
      return;
    }
    
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
    
    $.ajax({
      url: '{{ route("programaciones.bulk-update.store") }}',
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': formData._token,
        'Accept': 'application/json',
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      data: formData,
      success: function(response) {
        $('#confirmModal').modal('hide');
        
        if (response.ok) {
          Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: response.msg,
            confirmButtonText: 'Aceptar'
          }).then(() => {
            window.location.href = '{{ route("programaciones.index") }}';
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: response.msg
          });
        }
      },
      error: function(xhr) {
        $('#confirmModal').modal('hide');
        let message = 'Error al procesar la actualización masiva';
        
        if (xhr.responseJSON) {
          // Si hay errores de validación
          if (xhr.responseJSON.errors) {
            const errors = Object.values(xhr.responseJSON.errors).flat();
            message = 'Errores de validación:\n' + errors.join('\n');
          } else if (xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
          } else if (xhr.responseJSON.msg) {
            message = xhr.responseJSON.msg;
          }
        }
        
        Swal.fire({
          icon: 'error',
          title: 'Error',
          html: message.replace(/\n/g, '<br>'),
          width: '600px'
        });
      },
      complete: function() {
        $btn.prop('disabled', false).html($originalText);
      }
    });
  });

  // Establecer fechas por defecto (mes actual)
  const today = new Date();
  const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
  const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
  
  $('#start_date').val(firstDay.toISOString().split('T')[0]);
  $('#end_date').val(lastDay.toISOString().split('T')[0]);
});
</script>
@stop

