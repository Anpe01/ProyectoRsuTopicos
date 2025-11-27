@extends('adminlte::page')
@section('title','Empleados')

@section('content_header')
  <h1>Gestión de Personal (Empleados)</h1>
@stop

@section('content')
  @if(session('success')) <x-adminlte-alert theme="success" title="Ok">{{ session('success') }}</x-adminlte-alert> @endif
  @if(session('error'))   <x-adminlte-alert theme="danger"  title="Error">{{ session('error') }}</x-adminlte-alert> @endif

  <button class="btn btn-success mb-3" data-toggle="modal" data-target="#modalCreate">+ Nuevo</button>

  <table class="table table-bordered table-striped" id="tbl-employees" style="width:100%">
    <thead>
      <tr>
        <th>DNI</th>
        <th>Nombres</th>
        <th>Email</th>
        <th>Teléfono</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      @foreach($employees as $e)
        <tr>
          <td>{{ $e->dni }}</td>
        <td>{{ $e->full_name }}</td>
          <td>{{ $e->email }}</td>
          <td>{{ $e->phone }}</td>
        <td class="text-nowrap">
            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalEdit{{ $e->id }}">Editar</button>
            <form action="{{ route('employees.destroy',$e) }}" method="POST" class="d-inline frm-delete">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm" data-delete>Eliminar</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{-- Modal CREAR --}}
  <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title">Nuevo Empleado</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="current_modal" value="modalCreate">
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group col-md-3">
              <label>DNI <span class="text-danger">*</span></label>
              <input type="text" name="dni" class="form-control" placeholder="12345678" value="{{ old('dni') }}" required>
              <small class="text-muted">8 dígitos únicos</small>
              @error('dni')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
            <div class="form-group col-md-4">
              <label>Tipo de Empleado <span class="text-danger">*</span></label>
              <select id="function_id_create" name="function_id" class="form-control" required></select>
              @error('function_id')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
            <div class="form-group col-md-5">
              <label>Nombres <span class="text-danger">*</span></label>
              <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
              @error('first_name')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-5">
              <label>Apellidos <span class="text-danger">*</span></label>
              <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
              @error('last_name')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
            <div class="form-group col-md-4">
              <label>Fecha de Nacimiento <span class="text-danger">*</span></label>
              <input type="date" name="birth_date" id="birth_create" class="form-control" value="{{ old('birth_date') }}" required>
              <small class="text-muted">Mayor de 18 años</small>
              @error('birth_date')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
            <div class="form-group col-md-3">
              <label>Teléfono</label>
              <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="987654321">
              <small class="text-muted">Opcional</small>
              @error('phone')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Email</label>
              <input type="email" name="email" id="email_create" class="form-control" value="{{ old('email') }}" placeholder="empleado@ejemplo.com">
              <small id="email_ok" class="text-success d-none">Formato válido ✓</small>
              @error('email')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
            <div class="form-group col-md-6">
              <label>Contraseña <span class="text-danger">*</span></label>
              <input type="password" name="password" id="pass_create" class="form-control" required>
              <small id="pass_help" class="text-danger d-none">Mínimo 8 caracteres</small>
              @error('password')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Fotografía</label>
              <input type="file" name="photo" class="form-control-file" accept="image/png,image/jpeg">
              <small class="text-muted">JPG/PNG máx. 2MB</small>
              @error('photo')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
            <div class="form-group col-md-6">
              <label>Dirección</label>
              <input type="text" name="address" class="form-control" value="{{ old('address') }}" placeholder="Av. Principal 123, Distrito, Ciudad">
              <small class="text-muted">Mínimo 20 caracteres</small>
              @error('address')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
          </div>

          <div class="form-group form-check">
            <input class="form-check-input" type="checkbox" id="active_create" name="active" value="1" checked>
            <label class="form-check-label" for="active_create">Empleado activo</label>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button class="btn btn-success">Guardar</button>
        </div>
      </form>
    </div></div>
  </div>

  {{-- Modales EDITAR --}}
  @foreach($employees as $e)
  <div class="modal fade" id="modalEdit{{ $e->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title">Editar Empleado</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form method="POST" action="{{ route('employees.update',$e) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <input type="hidden" name="current_modal" value="modalEdit{{ $e->id }}">
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group col-md-3">
              <label>DNI <span class="text-danger">*</span></label>
              <input type="text" name="dni" class="form-control" value="{{ old('dni',$e->dni) }}" required>
              <small class="text-muted">8 dígitos</small>
              @error('dni')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
            <div class="form-group col-md-4">
              <label>Tipo de Empleado <span class="text-danger">*</span></label>
              <select id="function_id_edit_{{ $e->id }}" name="function_id" class="form-control" required></select>
              @error('function_id')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
            <div class="form-group col-md-5">
              <label>Nombres <span class="text-danger">*</span></label>
              <input type="text" name="first_name" class="form-control" value="{{ old('first_name',$e->first_name) }}" required>
              @error('first_name')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-5">
              <label>Apellidos <span class="text-danger">*</span></label>
              <input type="text" name="last_name" class="form-control" value="{{ old('last_name',$e->last_name) }}" required>
              @error('last_name')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
            <div class="form-group col-md-4">
              <label>Fecha de Nacimiento <span class="text-danger">*</span></label>
              <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date',$e->birth_date?->format('Y-m-d')) }}" required>
              <small class="text-muted">Mayor de 18 años</small>
              @error('birth_date')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
            <div class="form-group col-md-3">
              <label>Teléfono</label>
              <input type="text" name="phone" class="form-control" value="{{ old('phone',$e->phone) }}">
              @error('phone')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Email</label>
              <input type="email" name="email" class="form-control" value="{{ old('email',$e->email) }}">
              <small class="text-muted">Opcional</small>
              @error('email')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
            <div class="form-group col-md-6">
              <label>Contraseña (dejar en blanco para no cambiar)</label>
              <input type="password" name="password" id="pass_edit_{{ $e->id }}" class="form-control">
              <small class="text-muted">Mínimo 8 caracteres si la cambias</small>
              @error('password')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Fotografía</label>
              <input type="file" name="photo" class="form-control-file" accept="image/png,image/jpeg">
              @if($e->photo_path)
                <small class="text-muted d-block mt-1">Actual: <a href="{{ asset('storage/'.$e->photo_path) }}" target="_blank">ver</a></small>
              @endif
              @error('photo')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
            <div class="form-group col-md-6">
              <label>Dirección</label>
              <input type="text" name="address" class="form-control" value="{{ old('address',$e->address) }}">
              @error('address')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
          </div>

          <div class="form-group form-check">
            <input class="form-check-input" type="checkbox" id="active_edit_{{ $e->id }}" name="active" value="1" {{ $e->active ? 'checked' : '' }}>
            <label class="form-check-label" for="active_edit_{{ $e->id }}">Empleado activo</label>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button class="btn btn-primary">Guardar</button>
        </div>
      </form>
    </div></div>
  </div>
  @endforeach
@stop

@section('js')
<script>
$(function(){
  $('#tbl-employees').DataTable({
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json',
                emptyTable: 'Sin registros' },
    autoWidth: false,
    responsive: true,
    columnDefs: [{ targets: -1, orderable:false, searchable:false }]
  });

  // Inicializar Select2 con AJAX cuando el modal se muestre
  if ($.fn.select2) {
    // Modal de crear
    $('#modalCreate').on('shown.bs.modal', function() {
      const $sel = $('#function_id_create');
      if (!$sel.hasClass('select2-hidden-accessible')) {
        $sel.select2({
          placeholder: 'Seleccione un tipo',
          theme: 'bootstrap',
          allowClear: true,
          dropdownParent: $('#modalCreate'),
          ajax: {
            url: '{{ route('functions.options') }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
              return { q: params.term || '' };
            },
            processResults: function(data) {
              return data;
            },
            cache: true
          },
          minimumInputLength: 0
        });
      }
    });
    
    // Modales de editar
    @foreach($employees as $e)
    $('#modalEdit{{ $e->id }}').on('shown.bs.modal', function() {
      const $sel = $('#function_id_edit_{{ $e->id }}');
      if (!$sel.hasClass('select2-hidden-accessible')) {
        @php
          $initialId = $e->function_id ?? null;
          $initialText = optional($e->jobFunction)->name;
        @endphp

        $sel.select2({
          placeholder: 'Seleccione un tipo',
          theme: 'bootstrap',
          allowClear: true,
          dropdownParent: $('#modalEdit{{ $e->id }}'),
          ajax: {
            url: '{{ route('functions.options') }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
              return { q: params.term || '' };
            },
            processResults: function(data) {
              return data;
            },
            cache: true
          },
          minimumInputLength: 0
        });

        // Pre-seleccionar valor inicial si existe
        @if($initialId && $initialText)
          const initialId = {{ $initialId }};
          const initialText = @json($initialText);
          const opt = new Option(initialText, initialId, true, true);
          $sel.append(opt).trigger('change');
        @endif
      }
    });
    @endforeach
  }

  $('#email_create').on('input', function(){
    const ok = this.value && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value);
    $('#email_ok').toggleClass('d-none', !ok);
  });
  $('#pass_create').on('input', function(){
    $('#pass_help').toggleClass('d-none', this.value.length >= 8);
  });

  function isAdult(dateStr){
    if(!dateStr) return false;
    const d = new Date(dateStr), now = new Date();
    const adult = new Date(now.getFullYear()-18, now.getMonth(), now.getDate());
    return d <= adult;
  }
  $('#birth_create').on('change', function(){
    if (!isAdult(this.value)) alert('El empleado debe ser mayor de 18 años.');
  });

$(document).on('click','[data-delete]', function(e){
    e.preventDefault();
    const form = $(this).closest('form')[0];
    if (window.Swal) {
      Swal.fire({title:'¿Eliminar empleado?', icon:'warning', showCancelButton:true, confirmButtonText:'Sí, eliminar', cancelButtonText:'Cancelar'})
          .then(r => { if(r.isConfirmed) form.submit(); });
    } else {
      if (confirm('¿Eliminar empleado?')) form.submit();
    }
  });

  @if ($errors->any() && old('current_modal'))
    $('#{{ old('current_modal') }}').modal('show');
  @endif
});
</script>
@stop