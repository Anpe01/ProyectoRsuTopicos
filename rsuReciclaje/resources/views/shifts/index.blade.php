@extends('adminlte::page')

@section('title','Gestión de Turnos')

@section('content_header')
    <h1>Gestión de Turnos</h1>
@stop

@section('content')

@if(session('success'))
    <x-adminlte-alert theme="success" title="OK">{{ session('success') }}</x-adminlte-alert>
@endif

@if($errors->any())
    <x-adminlte-alert theme="danger" title="Errores de validación">
        <ul class="mb-0">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </x-adminlte-alert>
@endif

<div class="mb-3">
    <button class="btn btn-primary" data-toggle="modal" data-target="#modalShift" id="btn-new-shift">
        <i class="fas fa-plus"></i> Nuevo Turno
    </button>
    </div>

<div class="table-responsive">
<table id="tbl-shifts" class="table table-striped table-hover">
    <thead class="table-light">
        <tr>
            <th>Nombre</th>
            <th>Entrada</th>
            <th>Salida</th>
            <th>Descripción</th>
            <th>Activo</th>
            <th style="width:130px">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($shifts as $s)
        <tr>
            <td>{{ $s->name }}</td>
            <td>{{ \Illuminate\Support\Str::of($s->start_time)->limit(5,'') }}</td>
            <td>{{ \Illuminate\Support\Str::of($s->end_time)->limit(5,'') }}</td>
            <td>{{ $s->description }}</td>
            <td>
                @if($s->active)
                    <span class="badge bg-success">Sí</span>
                @else
                    <span class="badge bg-secondary">No</span>
                @endif
            </td>
            <td>
                <button class="btn btn-sm btn-warning btn-edit"
                        data-id="{{ $s->id }}"
                        data-name="{{ $s->name }}"
                        data-start="{{ \Illuminate\Support\Str::of($s->start_time)->limit(5,'') }}"
                        data-end="{{ \Illuminate\Support\Str::of($s->end_time)->limit(5,'') }}"
                        data-description="{{ $s->description }}"
                        data-active="{{ (int)$s->active }}">
                    <i class="fas fa-edit"></i>
                </button>

                <form action="{{ route('shifts.destroy',$s) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('¿Eliminar turno {{ $s->name }}?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
    </table>
    </div>

{{-- MODAL ÚNICO (crear/editar) --}}
<div class="modal fade" id="modalShift" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="shiftModalTitle">Nuevo Turno</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <form id="shiftForm" method="POST" action="{{ route('shifts.store') }}">
        @csrf
        <input type="hidden" name="__method" id="spoofMethod">
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Nombre del Turno *</label>
                <input type="text" name="name" id="shift_name" class="form-control" required maxlength="100">
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Hora de Entrada *</label>
                    <input type="time" name="start_time" id="shift_start" class="form-control" required step="60">
                    <small class="text-muted">Formato 24 horas</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Hora de Salida *</label>
                    <input type="time" name="end_time" id="shift_end" class="form-control" required step="60">
                    <small class="text-muted">Formato 24 horas</small>
                </div>
            </div>
            <div class="mb-3 mt-3">
                <label class="form-label">Descripción</label>
                <textarea name="description" id="shift_desc" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="shift_active" name="active" checked>
                <label class="form-check-label" for="shift_active">¿Turno activo?</label>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnSaveShift" form="shiftForm">Guardar</button>
        </div>
      </form>
    </div>
  </div>
  </div>

@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // DataTable: asegurar que #columns == thead th
    $('#tbl-shifts').DataTable({
        pageLength: 10,
        order: [[0,'asc']],
        autoWidth: false,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
            emptyTable: 'Sin turnos'
        },
        columns: [{},{},{},{},{},{}]
    });

    // Abrir modal crear
    document.getElementById('btn-new-shift').addEventListener('click', () => {
        const f = document.getElementById('shiftForm');
        f.reset();
        document.getElementById('shift_active').checked = true;
        document.getElementById('shiftModalTitle').innerText = 'Nuevo Turno';
        f.setAttribute('action', '{{ route('shifts.store') }}');
        const spoof = document.getElementById('spoofMethod');
        spoof.setAttribute('name','__method');
        spoof.value = '';
    });

    // Abrir modal editar con data-*
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const id    = btn.dataset.id;
            const name  = btn.dataset.name || '';
            const start = btn.dataset.start || '';
            const end   = btn.dataset.end || '';
            const desc  = btn.dataset.description || btn.dataset.desc || '';
            const act   = btn.dataset.active === '1';

            document.getElementById('shiftModalTitle').innerText = 'Editar Turno';
            document.getElementById('shift_name').value = name;
            document.getElementById('shift_start').value = start;
            document.getElementById('shift_end').value = end;
            document.getElementById('shift_desc').value = desc;
            document.getElementById('shift_active').checked = act;

            const f = document.getElementById('shiftForm');
            f.setAttribute('action', '{{ url('shifts') }}' + '/' + id);
            const spoof = document.getElementById('spoofMethod');
            spoof.setAttribute('name','_method');
            spoof.value = 'PUT';

            $('#modalShift').modal('show');
        });
    });
});
</script>
@stop