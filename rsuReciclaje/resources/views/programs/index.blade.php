@extends('adminlte::page')

@section('title','Gestión de Programación')
@section('content_header')
  <h1>Gestión de Programación</h1>
@stop

@section('content')
  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if(session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif

  <button class="btn btn-success mb-3" data-toggle="modal" data-target="#modalCreateProgram">+ Nueva Programación</button>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Zona</th><th>Vehículo</th><th>Turno</th><th>Fecha de Inicio</th><th>Fecha de Fin</th><th>Días</th><th>Estado</th><th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      @forelse($programs as $program)
        <tr>
          <td>{{ $program->zone->name ?? 'N/A' }}</td>
          <td>{{ $program->vehicle->name ?? 'N/A' }} ({{ $program->vehicle->brand->name ?? '' }} {{ $program->vehicle->model->name ?? '' }})</td>
          <td>{{ $program->shift->name ?? 'N/A' }} ({{ $program->shift->start_time ?? '' }} - {{ $program->shift->end_time ?? '' }})</td>
          <td>{{ $program->start_date->format('d/m/Y') }}</td>
          <td>{{ $program->end_date->format('d/m/Y') }}</td>
          <td>
            @php $daysMap = [1=>'L',2=>'M',3=>'X',4=>'J',5=>'V',6=>'S',7=>'D']; @endphp
            @foreach($program->days_of_week ?? [] as $day)
              {{ $daysMap[$day] ?? $day }}@if(!$loop->last), @endif
            @endforeach
          </td>
          <td>
            @php
              // Si no existe la columna status, considerar todas como activas
              $status = isset($program->status) ? $program->status : 'active';
            @endphp
            @if($status === 'active')
              <span class="badge badge-success">Activo</span>
            @else
              <span class="badge badge-danger">Inactivo</span>
            @endif
          </td>
          <td>
            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalEdit{{ $program->id }}">Editar</button>
            <button class="btn btn-info btn-sm btn-view-runs" data-program-id="{{ $program->id }}" data-toggle="modal" data-target="#modalRuns{{ $program->id }}">Ver Recorridos</button>
            <form action="{{ route('programs.destroy',$program) }}" method="POST" class="d-inline">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar esta programación?')">Eliminar</button>
            </form>
          </td>
        </tr>

        {{-- Modal ver recorridos --}}
        <div class="modal fade" id="modalRuns{{ $program->id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-xl">
            <div class="modal-content">
              <div class="modal-header bg-info">
                <h5 class="modal-title">Recorridos de la Programación #{{ $program->id }}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
              </div>
              <div class="modal-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped table-hover" id="tbl-runs-{{ $program->id }}">
                    <thead>
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
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>

        {{-- Modal editar --}}
        <div class="modal fade" id="modalEdit{{ $program->id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg"><div class="modal-content">
            <div class="modal-header bg-primary">
              <h5 class="modal-title">Editar programación</h5>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="{{ route('programs.update',$program) }}">
              @csrf @method('PUT')
              <div class="modal-body">
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Zona <span class="text-danger">*</span></label>
                      <select name="zone_id" class="form-control" required>
                        <option value="">Seleccione...</option>
                        @foreach($zones as $zone)
                          <option value="{{ $zone->id }}" {{ $program->zone_id == $zone->id ? 'selected' : '' }}>
                            {{ $zone->name }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Vehículo <span class="text-danger">*</span></label>
                      <select name="vehicle_id" class="form-control" required>
                        <option value="">Seleccione...</option>
                        @foreach($vehicles as $vehicle)
                          <option value="{{ $vehicle->id }}" {{ $program->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->name }} ({{ $vehicle->brand->name ?? '' }} {{ $vehicle->model->name ?? '' }})
                          </option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Turno <span class="text-danger">*</span></label>
                      <select name="shift_id" class="form-control" required>
                        <option value="">Seleccione...</option>
                        @foreach($shifts as $shift)
                          <option value="{{ $shift->id }}" {{ $program->shift_id == $shift->id ? 'selected' : '' }}>
                            {{ $shift->name }} ({{ $shift->start_time }} - {{ $shift->end_time }})
                          </option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Fecha Inicio <span class="text-danger">*</span></label>
                      <input name="start_date" value="{{ $program->start_date->format('Y-m-d') }}" class="form-control" type="date" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Fecha Fin <span class="text-danger">*</span></label>
                      <input name="end_date" value="{{ $program->end_date->format('Y-m-d') }}" class="form-control" type="date" required>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label>Días de la semana</label><br>
                  @php $daysMap = [1=>'L',2=>'M',3=>'X',4=>'J',5=>'V',6=>'S',7=>'D']; @endphp
                  @foreach($daysMap as $num=>$label)
                    <label class="mr-2">
                      <input type="checkbox" name="days[]" value="{{ $num }}"
                        {{ isset($program) && in_array($num, $program->days_of_week ?? []) ? 'checked' : '' }}>
                      {{ $label }}
                    </label>
                  @endforeach
                </div>
                <div class="row">
                  <div class="col-md-6">
                    {{-- Estado removido ya que la tabla no tiene esta columna --}}
                  </div>
                </div>
                <div class="form-group">
                  <label>Notas</label>
                  <textarea name="notes" class="form-control" rows="3">{{ $program->notes }}</textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary">Guardar</button>
              </div>
            </form>
          </div></div>
        </div>
      @empty
        <tr><td colspan="8" class="text-center text-muted">Sin registros</td></tr>
      @endforelse
    </tbody>
  </table>

  {{ $programs->links() }}

  {{-- Modal crear --}}
  <div class="modal fade" id="modalCreateProgram" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content">
      <div class="modal-header bg-success">
        <h5 class="modal-title">Nueva programación</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form id="formCreateProgram" method="POST" action="{{ route('programs.store') }}">
        @csrf
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>Grupo de Personal <span class="text-danger">*</span></label>
                <select name="workgroup_id" id="workgroup_id" class="form-control" required>
                  <option value="">Seleccione un grupo...</option>
                  @if(isset($workgroups) && $workgroups->count() > 0)
                    @foreach($workgroups as $workgroup)
                      <option value="{{ $workgroup->id }}">{{ $workgroup->name }}</option>
                    @endforeach
                  @endif
                </select>
                <small class="form-text text-muted">Al seleccionar un grupo, se cargarán automáticamente la zona, vehículo, turno y días de trabajo.</small>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Zona <span class="text-danger">*</span></label>
                <select name="zone_id" id="zone_id" class="form-control" required>
                  <option value="">Seleccione...</option>
                  @foreach($zones as $zone)
                    <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Vehículo <span class="text-danger">*</span></label>
                <select name="vehicle_id" id="vehicle_id" class="form-control" required>
                  <option value="">Seleccione...</option>
                  @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}">{{ $vehicle->name }} ({{ $vehicle->brand->name ?? '' }} {{ $vehicle->model->name ?? '' }})</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Turno <span class="text-danger">*</span></label>
                <select name="shift_id" id="shift_id" class="form-control" required>
                  <option value="">Seleccione...</option>
                  @foreach($shifts as $shift)
                    <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->start_time }} - {{ $shift->end_time }})</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Fecha Inicio <span class="text-danger">*</span></label>
                <input name="start_date" class="form-control" type="date" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Fecha Fin <span class="text-danger">*</span></label>
                <input name="end_date" class="form-control" type="date" required>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Días de la semana</label><br>
            @php $daysMap = [1=>'L',2=>'M',3=>'X',4=>'J',5=>'V',6=>'S',7=>'D']; @endphp
            @foreach($daysMap as $num=>$label)
              <label class="mr-2">
                <input type="checkbox" name="days[]" id="day_{{ $num }}" class="day-checkbox" value="{{ $num }}" data-day="{{ $num }}">
                {{ $label }}
              </label>
            @endforeach
          </div>
          {{-- Estado y Notas removidos ya que la tabla no tiene estas columnas --}}
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success" id="btnSaveProgram">Guardar</button>
        </div>
      </form>
    </div></div>
  </div>
@stop

@section('js')
<script>
console.log('Script de programación iniciando...');

$(document).ready(function() {
  console.log('jQuery listo, registrando eventos...');
  
  // Usar delegación de eventos para que funcione incluso si el modal se carga después
  $(document).on('change', '#modalCreateProgram #workgroup_id', function() {
    console.log('=== EVENTO CHANGE DETECTADO ===');
    
    const groupId = $(this).val();
    const $modal = $('#modalCreateProgram');
    
    console.log('Grupo seleccionado:', groupId);
    console.log('Modal encontrado:', $modal.length > 0);
    
    if (!groupId) {
      // Si no hay grupo seleccionado, limpiar campos
      $modal.find('#zone_id').val('').trigger('change');
      $modal.find('#vehicle_id').val('').trigger('change');
      $modal.find('#shift_id').val('').trigger('change');
      $modal.find('input[name="days[]"]').prop('checked', false);
      return;
    }
    
    // Obtener datos del grupo
    console.log('Haciendo petición AJAX a /workgroups/' + groupId);
    $.get('/workgroups/' + groupId)
      .done(function(response) {
        console.log('=== RESPUESTA RECIBIDA ===');
        console.log('Respuesta completa:', response);
        
        if (response.ok && response.data) {
          const group = response.data;
          console.log('Datos del grupo:', group);
          console.log('Días de trabajo del grupo:', group.days_of_week);
          console.log('Tipo de days_of_week:', typeof group.days_of_week);
          console.log('Es array?', Array.isArray(group.days_of_week));
          
          // Cargar zona
          if (group.zone_id) {
            $modal.find('#zone_id').val(group.zone_id).trigger('change');
            console.log('✓ Zona cargada:', group.zone_id);
          }
          
          // Cargar vehículo
          if (group.vehicle_id) {
            $modal.find('#vehicle_id').val(group.vehicle_id).trigger('change');
            console.log('✓ Vehículo cargado:', group.vehicle_id);
          }
          
          // Cargar turno
          if (group.shift_id) {
            $modal.find('#shift_id').val(group.shift_id).trigger('change');
            console.log('✓ Turno cargado:', group.shift_id);
          }
          
          // Cargar días de trabajo
          const $checkboxes = $modal.find('input[name="days[]"]');
          console.log('Total checkboxes encontrados:', $checkboxes.length);
          $checkboxes.each(function(i) {
            console.log('Checkbox ' + i + ':', {
              id: $(this).attr('id'),
              value: $(this).val(),
              checked: $(this).prop('checked')
            });
          });
          
          // Primero desmarcar todos
          $checkboxes.prop('checked', false);
          console.log('✓ Todos los checkboxes desmarcados');
          
          // Luego marcar los días del grupo
          if (group.days_of_week && Array.isArray(group.days_of_week) && group.days_of_week.length > 0) {
            console.log('=== MARCANDO DÍAS ===');
            console.log('Días a marcar:', group.days_of_week);
            
            let markedCount = 0;
            group.days_of_week.forEach(function(day) {
              const dayNum = parseInt(day, 10);
              console.log('Procesando día:', day, '-> número:', dayNum);
              
              if (!isNaN(dayNum) && dayNum >= 1 && dayNum <= 7) {
                // Buscar checkbox por ID
                const checkbox = $modal.find('#day_' + dayNum);
                console.log('Buscando #day_' + dayNum + ':', checkbox.length, 'encontrado(s)');
                
                if (checkbox.length > 0) {
                  checkbox.prop('checked', true);
                  markedCount++;
                  console.log('✓✓✓ CHECKBOX MARCADO para día', dayNum);
                } else {
                  // Intentar por valor
                  const checkboxByValue = $modal.find('input[name="days[]"][value="' + dayNum + '"]');
                  console.log('Buscando por valor:', checkboxByValue.length, 'encontrado(s)');
                  if (checkboxByValue.length > 0) {
                    checkboxByValue.prop('checked', true);
                    markedCount++;
                    console.log('✓✓✓ CHECKBOX MARCADO (por valor) para día', dayNum);
                  } else {
                    console.error('✗✗✗ NO SE ENCONTRÓ checkbox para día', dayNum);
                  }
                }
              } else {
                console.warn('✗ Día inválido:', day);
              }
            });
            
            console.log('=== RESUMEN ===');
            console.log('Total marcados:', markedCount, 'de', group.days_of_week.length);
            
            // Verificar qué checkboxes quedaron marcados
            $checkboxes.each(function() {
              if ($(this).prop('checked')) {
                console.log('Checkbox marcado:', $(this).attr('id'), 'valor:', $(this).val());
              }
            });
          } else {
            console.log('No hay días de trabajo o el array está vacío');
          }
        } else {
          console.error('Respuesta inválida:', response);
        }
      })
      .fail(function(xhr, status, error) {
        console.error('=== ERROR EN PETICIÓN ===');
        console.error('Status:', status);
        console.error('Error:', error);
        console.error('Response:', xhr.responseText);
        alert('Error al cargar los datos del grupo seleccionado.');
      });
  });
  
  // Interceptar envío del formulario y enviarlo vía AJAX
  $('#formCreateProgram').on('submit', function(e) {
    e.preventDefault();
    
    const $form = $(this);
    const $btn = $('#btnSaveProgram');
    const originalHtml = $btn.html();
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
    
    // Recopilar datos del formulario
    const formData = {
      workgroup_id: $form.find('#workgroup_id').val(),
      zone_id: $form.find('#zone_id').val(),
      shift_id: $form.find('#shift_id').val(),
      vehicle_id: $form.find('#vehicle_id').val(),
      start_date: $form.find('input[name="start_date"]').val(),
      end_date: $form.find('input[name="end_date"]').val(),
      days_of_week: $form.find('input[name="days[]"]:checked').map(function() {
        return parseInt($(this).val());
      }).get(),
      _token: '{{ csrf_token() }}'
    };
    
    // Validar que se haya seleccionado al menos un día
    if (formData.days_of_week.length === 0) {
      alert('Debe seleccionar al menos un día de la semana');
      $btn.prop('disabled', false).html(originalHtml);
      return;
    }
    
    // Enviar vía AJAX
    $.ajax({
      url: $form.attr('action'),
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': formData._token,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      data: formData,
      success: function(response) {
        if (response.ok) {
          if (typeof Swal !== 'undefined') {
            Swal.fire('Éxito', response.msg || 'Programación creada correctamente', 'success')
              .then(() => {
                location.reload();
              });
          } else {
            alert(response.msg || 'Programación creada correctamente');
            location.reload();
          }
        } else {
          alert(response.msg || 'Error al crear la programación');
          $btn.prop('disabled', false).html(originalHtml);
        }
      },
      error: function(xhr) {
        let errorMsg = 'Error al crear la programación';
        if (xhr.responseJSON) {
          if (xhr.responseJSON.errors) {
            const errors = Object.values(xhr.responseJSON.errors).flat();
            errorMsg = errors.join('\n');
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
        $btn.prop('disabled', false).html(originalHtml);
      }
    });
  });
  
  // Limpiar campos cuando se cierra el modal
  $('#modalCreateProgram').on('hidden.bs.modal', function() {
    const $modal = $(this);
    $modal.find('#workgroup_id').val('');
    $modal.find('#zone_id').val('');
    $modal.find('#vehicle_id').val('');
    $modal.find('#shift_id').val('');
    $modal.find('input[name="days[]"]').prop('checked', false);
    $modal.find('#formCreateProgram')[0].reset();
  });
  
  // Cargar runs cuando se abre el modal
  $(document).on('shown.bs.modal', '[id^="modalRuns"]', function() {
    const modalId = $(this).attr('id');
    const programId = modalId.replace('modalRuns', '');
    const $tbody = $(this).find('tbody');
    
    // Mostrar loading
    $tbody.html('<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando recorridos...</td></tr>');
    
    // Cargar runs
    $.get('/programs/' + programId + '/runs')
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
                    <button class="btn btn-warning btn-sm btn-run-detail" data-run-id="${run.id}" title="Ver detalle">
                      <i class="fas fa-route"></i> Detalle
                    </button>
                    <button class="btn btn-danger btn-sm btn-run-delete" data-run-id="${run.id}" title="Eliminar">
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
        console.error('Error al cargar runs:', xhr);
        $tbody.html('<tr><td colspan="7" class="text-center text-danger">Error al cargar los recorridos</td></tr>');
      });
  });
  
  // Abrir modal de detalle de run (redirigir a schedules)
  $(document).on('click', '.btn-run-detail', function() {
    const runId = $(this).data('run-id');
    // Redirigir a la vista de schedules con el run_id para que se abra automáticamente el modal
    window.location.href = '/programaciones?run_id=' + runId;
  });
  
  // Eliminar run
  $(document).on('click', '.btn-run-delete', function(e) {
    e.preventDefault();
    const runId = $(this).data('run-id');
    const $row = $(this).closest('tr');
    const $modal = $(this).closest('.modal');
    
    if (confirm('¿Está seguro de eliminar este recorrido? Esta acción no se puede deshacer.')) {
      // Mostrar loading
      $row.find('td').html('<i class="fas fa-spinner fa-spin"></i> Eliminando...');
      
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
            // Eliminar fila de la tabla
            $row.fadeOut(300, function() {
              $(this).remove();
              // Si no quedan filas, mostrar mensaje
              if ($modal.find('tbody tr').length === 0) {
                $modal.find('tbody').html('<tr><td colspan="7" class="text-center text-muted">No hay recorridos registrados para esta programación</td></tr>');
              }
            });
            
            // Mostrar mensaje de éxito
            if (typeof Swal !== 'undefined') {
              Swal.fire('Éxito', response.msg || 'El recorrido ha sido eliminado correctamente', 'success');
            } else {
              alert('El recorrido ha sido eliminado correctamente');
            }
          } else {
            alert(response.msg || 'Error al eliminar el recorrido');
            // Restaurar botones
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
          // Restaurar botones recargando el modal
          const programId = $modal.attr('id').replace('modalRuns', '');
          $modal.trigger('shown.bs.modal');
        }
      });
    }
  });
  
  console.log('✓ Eventos registrados correctamente');
});
</script>
@stop