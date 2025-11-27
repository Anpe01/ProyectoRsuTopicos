@extends('adminlte::page')

@section('title','Asistencias')

@section('plugins.Datatables', true)

@section('content_header')
  <h1>Gestión de Asistencias</h1>
@stop

@section('content')
<div class="container-fluid">
  <h3 class="mb-3">Gestión de Asistencias</h3>

  <div class="d-flex gap-2 mb-3 flex-wrap align-items-center">
    <div>
      <label class="form-label mb-1">Fecha de inicio *</label>
      <input type="date" id="f-start" class="form-control" value="{{ $start }}">
    </div>
    <div>
      <label class="form-label mb-1">Fecha de fin</label>
      <input type="date" id="f-end" class="form-control" value="{{ $end }}">
    </div>
    <div class="align-self-end">
      <button id="btn-filter" class="btn btn-outline-secondary">
        <i class="fas fa-filter"></i> Filtrar
      </button>
    </div>
    <div class="align-self-end">
      <small class="text-muted">
        <i class="fas fa-sync-alt fa-spin"></i> Actualización automática cada 15 segundos
      </small>
    </div>
    <div class="ms-auto d-flex gap-2 align-self-end">
      <a href="{{ route('attendances.kiosk') }}" class="btn btn-success" target="_blank">
        <i class="fas fa-external-link-alt"></i> Ir al Kiosco
      </a>
      <button class="btn btn-primary" data-toggle="modal" data-target="#attModal" id="btn-new-attendance">
        <i class="fas fa-plus"></i> Agregar Nueva Asistencia
      </button>
    </div>
  </div>

  <table id="tbl-attendances" class="table table-striped w-100">
    <thead>
      <tr>
        <th>DNI</th>
        <th>Empleado</th>
        <th>Fecha</th>
        <th>Tipo</th>
        <th>Estado</th>
        <th>Notas</th>
        <th>Acción</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

{{-- Modal crear/editar --}}
<div class="modal fade" id="attModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modal-title">Nueva asistencia</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <form id="frm-att">
        <div class="modal-body row g-3">
          @csrf
          <input type="hidden" name="attendance_id" id="attendance_id">
          <input type="hidden" name="_method" id="method_field" value="POST">
          <div class="col-md-6">
            <label class="form-label">Empleado *</label>
            <select name="employee_id" id="employee_id" class="form-control" required>
              @foreach(\App\Models\Employee::orderBy('last_name')->get() as $e)
                <option value="{{ $e->id }}">{{ $e->dni }} — {{ $e->fullname }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Fecha *</label>
            <input type="date" name="attendance_date" id="attendance_date" class="form-control" value="{{ now()->toDateString() }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Tipo *</label>
            <select name="period" id="period" class="form-control" required>
              <option value="1">Entrada</option>
              <option value="2">Salida</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Estado *</label>
            <select name="status" id="status" class="form-control" required>
              <option value="1">Presente</option>
              <option value="0">Falta</option>
              <option value="2">Justificado</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Notas</label>
            <textarea name="notes" id="notes" class="form-control" rows="2" maxlength="2000"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button class="btn btn-primary" type="submit">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>
@stop

@section('js')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script>
(function(){
  const table = $('#tbl-attendances').DataTable({
    ajax: {
      url: '{{ route('attendances.index') }}',
      data: function(d){
        d.start = $('#f-start').val();
        d.end   = $('#f-end').val();
      }
    },
    serverSide: false,
    processing: true,
    responsive: true,
    language: {
      url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json'
    },
    columns: [
      {data:'dni',      name:'dni'},
      {data:'employee', name:'employee'},
      {data:'date',     name:'date'},
      {data:'type',     name:'type'},
      {data:'status',   name:'status'},
      {data:'notes',    name:'notes', orderable:false, searchable:false},
      {data:'actions',  name:'actions', orderable:false, searchable:false},
    ],
    order: [[2, 'desc']]
  });

  // Auto-refresh cada 15 segundos
  let autoRefreshInterval = setInterval(function() {
    table.ajax.reload(null, false); // false = mantener la página actual
  }, 15000); // 15000ms = 15 segundos

  // Limpiar el intervalo cuando se cierre la página
  $(window).on('beforeunload', function() {
    clearInterval(autoRefreshInterval);
  });

  // Opcional: Detener auto-refresh cuando el usuario está filtrando manualmente
  let manualRefresh = false;
  $('#btn-filter').on('click', function(){
    manualRefresh = true;
    table.ajax.reload(null, false);
    // Reiniciar el contador después de un filtro manual
    setTimeout(function() {
      manualRefresh = false;
    }, 2000);
  });

  // Botón agregar nueva
  $('button[data-toggle="modal"][data-target="#attModal"]').on('click', function(){
    resetForm();
    $('#modal-title').text('Nueva asistencia');
    $('#method_field').val('POST');
  });

  // Editar asistencia
  $(document).on('click', '.btn-edit-attendance', function(){
    const id = $(this).data('id');
    resetForm();
    $('#modal-title').text('Editar asistencia');
    $('#method_field').val('PUT');
    
    $.get('{{ url('/attendances') }}/' + id + '/edit')
      .done(function(res){
        if (res && res.ok && res.data) {
          const d = res.data;
          $('#attendance_id').val(d.id);
          $('#employee_id').val(d.employee_id);
          $('#attendance_date').val(d.attendance_date);
          $('#period').val(d.period);
          $('#status').val(d.status);
          $('#notes').val(d.notes || '');
          $('#attModal').modal('show');
        }
      })
      .fail(function(xhr){
        const msg = xhr.responseJSON?.message || 'Error al cargar datos';
        if (window.toastr) {
          toastr.error(msg);
        } else {
          alert(msg);
        }
      });
  });

  // Eliminar asistencia
  $(document).on('click', '.btn-delete-attendance', function(){
    const id = $(this).data('id');
    const btn = $(this);
    
    if (!confirm('¿Está seguro de eliminar esta asistencia?')) {
      return;
    }
    
    btn.prop('disabled', true);
    
    $.ajax({
      url: '{{ url('/attendances') }}/' + id,
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(res){
        if (res && res.ok) {
          table.ajax.reload(null, false);
          if (window.toastr) {
            toastr.success(res.msg || 'Eliminado');
          } else {
            alert(res.msg || 'Eliminado');
          }
        }
      },
      error: function(xhr){
        const msg = xhr.responseJSON?.message || 'Error al eliminar';
        if (window.toastr) {
          toastr.error(msg);
        } else {
          alert(msg);
        }
      },
      complete: function(){
        btn.prop('disabled', false);
      }
    });
  });

  // Función para resetear formulario
  function resetForm(){
    $('#frm-att')[0].reset();
    $('#attendance_id').val('');
    $('#method_field').val('POST');
    $('#attendance_date').val('{{ now()->toDateString() }}');
  }

  // Submit del formulario
  $('#frm-att').on('submit', function(e){
    e.preventDefault();
    const $form = $(this);
    const $btn = $form.find('button[type="submit"]');
    const originalText = $btn.html();
    const method = $('#method_field').val();
    const id = $('#attendance_id').val();
    
    let url = '{{ route('attendances.store') }}';
    if (method === 'PUT' && id) {
      url = '{{ url('/attendances') }}/' + id;
    }
    
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');
    
    $.ajax({
      url: url,
      method: method === 'PUT' ? 'PUT' : 'POST',
      data: $form.serialize(),
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(res){
        if (res && res.ok) {
          $('#attModal').modal('hide');
          resetForm();
          table.ajax.reload(null,false);
          if (window.toastr) {
            toastr.success(res.msg || 'Guardado');
          } else {
            alert(res.msg || 'Guardado');
          }
        } else {
          alert(res.message || 'Error al guardar');
        }
      },
      error: function(xhr){
        let msg = 'Error al guardar';
        if (xhr.responseJSON) {
          if (xhr.responseJSON.message) {
            msg = xhr.responseJSON.message;
          } else if (xhr.responseJSON.errors) {
            msg = 'Errores de validación:\n' + Object.values(xhr.responseJSON.errors).flat().join('\n');
          }
        }
        if (window.toastr) {
          toastr.error(msg);
        } else {
          alert(msg);
        }
      },
      complete: function(){
        $btn.prop('disabled', false).html(originalText);
      }
    });
  });
})();
</script>
@stop
