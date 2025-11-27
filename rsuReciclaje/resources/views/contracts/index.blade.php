@extends('adminlte::page')
@section('title','Contratos')

@section('content_header')
  <h1>Gestión de Contratos</h1>
@stop

@section('content')
  @if(session('success')) <x-adminlte-alert theme="success" title="Ok">{{ session('success') }}</x-adminlte-alert> @endif
  @if(session('error'))   <x-adminlte-alert theme="danger"  title="Error">{{ session('error') }}</x-adminlte-alert> @endif

  <button class="btn btn-success mb-3" data-toggle="modal" data-target="#modalCreate">+ Nuevo</button>

  <table class="table table-bordered table-striped" id="tbl-contracts" style="width:100%">
    <thead>
      <tr>
        <th>Empleado</th>
        <th>Tipo</th>
        <th>Fechas</th>
        <th>Salario</th>
        <th>Departamento</th>
        <th>Activo</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      @foreach($contracts as $c)
      <tr>
        <td>{{ $c->employee?->last_name }}, {{ $c->employee?->first_name }}</td>
        <td>
          @if($c->type == 'temporal')
            <span class="badge badge-warning">Temporal</span>
          @elseif($c->type == 'nombrado')
            <span class="badge badge-success">Nombrado</span>
          @elseif($c->type == 'a tiempo completo')
            <span class="badge badge-info">A Tiempo Completo</span>
          @else
            {{ $c->type }}
          @endif
        </td>
        <td>{{ $c->start_date->format('d/m/Y') }} @if($c->end_date) — {{ $c->end_date->format('d/m/Y') }} @else — Indefinido @endif</td>
        <td>S/ {{ number_format($c->salary ?? 0,2) }}</td>
        <td>{{ $c->department->name ?? '-' }}</td>
        <td>
          @if($c->active ?? true)
            <span class="badge badge-success">Activo</span>
          @else
            <span class="badge badge-secondary">No activo</span>
          @endif
        </td>
        <td class="text-nowrap">
          <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalEdit{{ $c->id }}">Editar</button>
          <form action="{{ route('contracts.destroy',$c) }}" method="POST" class="d-inline frm-delete">
            @csrf @method('DELETE')
            <button class="btn btn-danger btn-sm" data-delete>Eliminar</button>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{-- MODAL CREAR --}}
  <div class="modal fade" id="modalCreate" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"><div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title">Nuevo Contrato</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form method="POST" action="{{ route('contracts.store') }}" id="formCreateContract">
        @csrf
        <input type="hidden" name="current_modal" value="modalCreate">
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group col-md-12">
              <label>Empleado: <span class="text-danger">*</span></label>
              <select name="employee_id" id="emp_create" class="form-control" required>
                <option value="">Seleccione empleado</option>
                @foreach($employees as $e)
                  <option value="{{ $e->id }}">{{ $e->last_name }}, {{ $e->first_name }}</option>
                @endforeach
              </select>
              @error('employee_id')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-12">
              <label>Tipo de Contrato: <span class="text-danger">*</span></label>
              <select name="type" id="type_create" class="form-control" required>
                <option value="">Seleccione un tipo</option>
                <option value="temporal">Temporal</option>
                <option value="nombrado">Nombrado</option>
                <option value="a tiempo completo">A Tiempo Completo</option>
              </select>
              @error('type')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Fecha de Inicio: <span class="text-danger">*</span></label>
              <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
              @error('start_date')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
            <div class="form-group col-md-6">
              <label>Fecha de Finalización:</label>
              <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
              <small class="text-muted">Dejar en blanco si el contrato no tiene fecha de fin</small>
              @error('end_date')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Salario: <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0" name="salary" class="form-control" value="{{ old('salary') }}" required>
              @error('salary')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
            <div class="form-group col-md-6">
              <label>Departamento: <span class="text-danger">*</span></label>
              <select name="department_id" id="dept_create" class="form-control" required>
                <option value="">Seleccione un departamento</option>
                @foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
              </select>
              @error('department_id')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Período de Prueba (meses): <span class="text-danger">*</span></label>
              <input type="会计师事务所ber" name="probation_months" min="0" max="12" class="form-control" value="{{ old('probation_months') }}" required>
              @error('probation_months')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
            <div class="form-group col-md-6">
              <label>¿Contrato Activo?</label><br>
              <label class="form-check-label">
                <input type="checkbox" name="active" value="1" checked>
              </label>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-12">
              <label>Motivo de Terminación:</label>
              <textarea name="termination_reason" class="form-control" rows="2" placeholder="Solo aplica si el contrato no está activo">{{ old('termination_reason') }}</textarea>
              @error('termination_reason')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button class="btn btn-success" type="submit">Guardar</button>
        </div>
      </form>
    </div></div>
  </div>

  {{-- MODALES EDITAR --}}
  @foreach($contracts as $c)
  <div class="modal fade" id="modalEdit{{ $c->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"><div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title">Editar Contrato</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form method="POST" action="{{ route('contracts.update',$c) }}">
        @csrf @method('PUT')
        <input type="hidden" name="current_modal" value="modalEdit{{ $c->id }}">
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group col-md-12">
              <label>Empleado: <span class="text-danger">*</span></label>
              <select name="employee_id" id="emp_edit_{{ $c->id }}" class="form-control" required>
                @foreach($employees as $e)
                  <option value="{{ $e->id }}" {{ $c->employee_id==$e->id?'selected':'' }}>
                    {{ $e->last_name }}, {{ $e->first_name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-12">
              <label>Tipo de Contrato: <span class="text-danger">*</span></label>
              <select name="type" id="type_edit_{{ $c->id }}" class="form-control" required>
                <option value="">Seleccione un tipo</option>
                <option value="temporal" {{ $c->type=='temporal'?'selected':'' }}>Temporal</option>
                <option value="nombrado" {{ $c->type=='nombrado'?'selected':'' }}>Nombrado</option>
                <option value="a tiempo completo" {{ $c->type=='a tiempo completo'?'selected':'' }}>A Tiempo Completo</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Fecha de Inicio: <span class="text-danger">*</span></label>
              <input type="date" name="start_date" value="{{ old('start_date',$c->start_date->format('Y-m-d')) }}" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
              <label>Fecha de Finalización:</label>
              <input type="date" name="end_date" value="{{ old('end_date',$c->end_date?->format('Y-m-d')) }}" class="form-control">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Salario: <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0" name="salary" value="{{ old('salary',$c->salary ?? 0) }}" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
              <label>Departamento: <span class="text-danger">*</span></label>
              <select name="department_id" id="dept_edit_{{ $c->id }}" class="form-control" required>
                @foreach($departments as $d)
                  <option value="{{ $d->id }}" {{ ($c->department_id ?? 0)==$d->id?'selected':'' }}>{{ $d->name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Período de Prueba (meses): <span class="text-danger">*</span></label>
              <input type="number" name="probation_months" min="0" max="12" value="{{ old('probation_months',$c->probation_months ?? 0) }}" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
              <label>¿Contrato Activo?</label><br>
              <input type="checkbox" name="active" value="1" {{ ($c->active ?? true) ? 'checked' : '' }}>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-12">
              <label>Motivo de Terminación:</label>
              <textarea name="termination_reason" class="form-control" rows="2">{{ old('termination_reason',$c->termination_reason ?? '') }}</textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button class="btn btn-primary" type="submit">Guardar</button>
        </div>
      </form>
    </div></div>
  </div>
  @endforeach
@stop

@section('js')
<script>
$(function(){
  $('#tbl-contracts').DataTable({
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json',
                emptyTable: 'Sin registros' },
    autoWidth: false,
    responsive: true,
    columnDefs: [{ targets: -1, orderable:false, searchable:false }]
  });

  // Función para inicializar Select2 en el modal de crear
  function initCreateSelect2() {
    if (!$.fn.select2) {
      console.warn('Select2 no está disponible');
      return;
    }
    
    var $modal = $('#modalCreate');
    var $deptSelect = $('#dept_create');
    
    // Verificar si hay departamentos en el HTML
    var optionCount = $deptSelect.find('option').length;
    console.log('Opciones de departamentos en HTML:', optionCount);
    
    // Si no hay departamentos en el HTML, cargarlos desde la API
    if (optionCount <= 1) {
      console.log('Cargando departamentos desde API...');
      $.ajax({
        url: '/api/departments',
        method: 'GET',
        dataType: 'json',
        success: function(departments) {
          console.log('Departamentos cargados desde API:', departments);
          if (departments && departments.length > 0) {
            $deptSelect.empty().append('<option value="">Seleccione un departamento</option>');
            $.each(departments, function(i, dept) {
              $deptSelect.append($('<option>', {
                value: dept.id,
                text: dept.name
              }));
            });
            // Inicializar Select2 después de cargar
            initAllSelect2($modal);
          }
        },
        error: function(xhr, status, error) {
          console.error('Error al cargar departamentos:', error, xhr);
          // Inicializar Select2 de todos modos
          initAllSelect2($modal);
        }
      });
    } else {
      // Si ya hay departamentos en el HTML, inicializar Select2 directamente
      console.log('Usando departamentos del HTML');
      initAllSelect2($modal);
    }
  }
  
  // Función para inicializar todos los Select2
  function initAllSelect2($modal) {
    // Destruir Select2 si ya existe para evitar duplicados
    ['emp_create', 'type_create', 'dept_create'].forEach(function(id) {
      var $select = $('#' + id);
      if ($select.hasClass('select2-hidden-accessible')) {
        $select.select2('destroy');
      }
    });
    
    // Inicializar Select2 para todos los campos
    $('#emp_create').select2({ 
      width: '100%',
      dropdownParent: $modal
    });
    $('#type_create').select2({ 
      width: '100%',
      dropdownParent: $modal
    });
    $('#dept_create').select2({ 
      width: '100%',
      dropdownParent: $modal,
      placeholder: 'Seleccione un departamento',
      allowClear: true
    });
    console.log('Select2 inicializado para todos los campos');
  }

  // Inicializar Select2 cuando se abre el modal de crear
  $('#modalCreate').on('shown.bs.modal', function() {
    console.log('Modal de crear abierto');
    // Pequeño delay para asegurar que el modal esté completamente visible
    setTimeout(function() {
      initCreateSelect2();
    }, 100);
  });

  // Asegurar que el valor de department_id se envíe correctamente al enviar el formulario
  $('#formCreateContract').on('submit', function(e) {
    var $deptSelect = $('#dept_create');
    if ($deptSelect.hasClass('select2-hidden-accessible')) {
      // Forzar que Select2 actualice el valor del select original
      $deptSelect.trigger('change');
    }
    // Verificar que se haya seleccionado un departamento
    if (!$deptSelect.val() || $deptSelect.val() === '') {
      e.preventDefault();
      if (window.Swal) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Por favor, seleccione un departamento.'
        });
      } else {
        alert('Por favor, seleccione un departamento.');
      }
      return false;
    }
  });

  // Inicializar Select2 en los modales de editar
  @foreach($contracts as $c)
    $('#modalEdit{{ $c->id }}').on('shown.bs.modal', function() {
      if ($.fn.select2) {
        var $modal = $(this);
        // Destruir Select2 si ya existe
        ['emp_edit_{{ $c->id }}', 'type_edit_{{ $c->id }}', 'dept_edit_{{ $c->id }}'].forEach(function(id) {
          var $select = $('#' + id);
          if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
          }
          $select.select2({ 
            width: '100%',
            dropdownParent: $modal
          });
        });
      }
    });
  @endforeach

  $(document).on('click','[data-delete]', function(e){
    e.preventDefault();
    const form = $(this).closest('form')[0];
    if (window.Swal) {
      Swal.fire({title:'¿Eliminar contrato?', icon:'warning', showCancelButton:true,
                 confirmButtonText:'Sí, eliminar', cancelButtonText:'Cancelar'})
          .then(r => { if(r.isConfirmed) form.submit(); });
    } else {
      if (confirm('¿Eliminar contrato?')) form.submit();
    }
  });

  @if ($errors->any() && old('current_modal'))
    $('#{{ old('current_modal') }}').on('shown.bs.modal', function() {
      if ($.fn.select2) {
        initCreateSelect2();
      }
    });
    $('#{{ old('current_modal') }}').modal('show');
  @endif
});
</script>
@stop
