@extends('adminlte::page')

@section('title','Recorridos')
@section('content_header')
  <h1>Gestión de Recorridos</h1>
@stop

@section('content')
  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if(session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif

  <button class="btn btn-success mb-3" data-toggle="modal" data-target="#modalCreateRun">+ Nuevo recorrido</button>

  <table class="table table-bordered table-striped" id="tbl-runs">
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Zona</th>
        <th>Vehículo</th>
        <th>Turno</th>
        <th>Estado</th>
        <th>Equipo</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      @forelse($runs as $r)
        <tr>
          <td>{{ $r->run_date->format('Y-m-d') }}</td>
          <td>{{ $r->program?->zone?->name }}</td>
          <td>{{ $r->program?->vehicle?->name }}</td>
          <td>{{ $r->program?->shift?->name }}</td>
          <td>
            @php $st = $r->status; @endphp
            <span class="badge badge-{{ $st==='done'?'success':($st==='in_progress'?'info':($st==='canceled'?'danger':'secondary')) }}">
              {{ $st }}
            </span>
          </td>
          <td>{{ $r->personnel->count() }}</td>
          <td>
            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalEdit{{ $r->id }}">Editar</button>
            <button class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#modalCrew{{ $r->id }}">Equipo</button>
            <form action="{{ route('runs.destroy',$r) }}" method="POST" class="d-inline frm-delete">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm" data-delete>Eliminar</button>
            </form>
          </td>
        </tr>

        {{-- Modal Editar --}}
        <div class="modal fade" id="modalEdit{{ $r->id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg"><div class="modal-content">
            <div class="modal-header bg-primary">
              <h5 class="modal-title">Editar recorrido</h5>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="{{ route('runs.update',$r) }}">
              @csrf @method('PUT')
              <div class="modal-body">
                <div class="row">
                  <div class="col-md-6">
                    <label>Programa *</label>
                    <select name="program_id" class="form-control" required>
                      @foreach($programs as $p)
                        <option value="{{ $p->id }}" {{ $r->program_id==$p->id?'selected':'' }}>
                          #{{ $p->id }} — Zona {{ $p->zone?->name }} — {{ $p->shift?->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label>Fecha *</label>
                    <input type="date" name="run_date" class="form-control" value="{{ $r->run_date->format('Y-m-d') }}" required>
                  </div>
                  <div class="col-md-3">
                    <label>Estado *</label>
                    <select name="status" class="form-control" required>
                      @foreach(['planned','in_progress','done','canceled'] as $st)
                        <option value="{{ $st }}" {{ $r->status===$st?'selected':'' }}>{{ $st }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-3 mt-2">
                    <label>Inicio</label>
                    <input type="time" name="start_time" class="form-control" value="{{ $r->start_time }}">
                  </div>
                  <div class="col-md-3 mt-2">
                    <label>Fin</label>
                    <input type="time" name="end_time" class="form-control" value="{{ $r->end_time }}">
                  </div>
                  <div class="col-md-12 mt-2">
                    <label>Notas</label>
                    <textarea name="notes" class="form-control">{{ $r->notes }}</textarea>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary">Guardar</button>
              </div>
            </form>
          </div></div>
        </div>

        {{-- Modal Equipo --}}
        <div class="modal fade" id="modalCrew{{ $r->id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg"><div class="modal-content">
            <div class="modal-header bg-dark text-white">
              <h5 class="modal-title">Equipo del recorrido #{{ $r->id }}</h5>
              <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
              <form method="POST" action="{{ route('runpersonnel.store') }}" class="mb-3">
                @csrf
                <input type="hidden" name="run_id" value="{{ $r->id }}">
                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label>Empleado *</label>
                    <select name="staff_id" class="form-control" required>
                      <option value="">Seleccione…</option>
                      @foreach($employees as $e)
                        <option value="{{ $e->id }}">{{ $e->last_name }}, {{ $e->first_name }} ({{ $e->dni }})</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-md-4">
                    <label>Función *</label>
                    <select name="function_id" class="form-control" required>
                      <option value="">Seleccione…</option>
                      @foreach($functions as $f)
                        <option value="{{ $f->id }}">{{ $f->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-md-2 d-flex align-items-end">
                    <button class="btn btn-outline-dark w-100">Agregar</button>
                  </div>
                </div>
              </form>
              <table class="table table-sm">
                <thead><tr><th>Empleado</th><th>Función</th><th></th></tr></thead>
                <tbody>
                  @forelse($r->personnel as $rp)
                    <tr>
                      <td>{{ $rp->employee?->last_name }}, {{ $rp->employee?->first_name }} ({{ $rp->employee?->dni }})</td>
                      <td>{{ $rp->function?->name }}</td>
                      <td class="text-right">
                        <form action="{{ route('runpersonnel.destroy',$rp) }}" method="POST">
                          @csrf @method('DELETE')
                          <button class="btn btn-sm btn-danger" onclick="return confirm('¿Quitar del recorrido?')">Quitar</button>
                        </form>
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="3" class="text-muted text-center">Sin personal asignado</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
          </div></div>
        </div>

      @empty
        <tr><td colspan="7" class="text-center text-muted">Sin recorridos</td></tr>
      @endforelse
    </tbody>
  </table>

  {{ $runs->links() }}

  {{-- Modal Crear --}}
  <div class="modal fade" id="modalCreateRun" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content">
      <div class="modal-header bg-success">
        <h5 class="modal-title">Nuevo recorrido</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form method="POST" action="{{ route('runs.store') }}">
        @csrf
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <label>Programa *</label>
              <select name="program_id" class="form-control" required>
                <option value="">Seleccione…</option>
                @foreach($programs as $p)
                  <option value="{{ $p->id }}">#{{ $p->id }} — Zona {{ $p->zone?->name }} — {{ $p->shift?->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label>Fecha *</label>
              <input type="date" name="run_date" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label>Estado *</label>
              <select name="status" class="form-control" required>
                <option value="planned">planned</option>
                <option value="in_progress">in_progress</option>
                <option value="done">done</option>
                <option value="canceled">canceled</option>
              </select>
            </div>
            <div class="col-md-3 mt-2">
              <label>Inicio</label>
              <input type="time" name="start_time" class="form-control">
            </div>
            <div class="col-md-3 mt-2">
              <label>Fin</label>
              <input type="time" name="end_time" class="form-control">
            </div>
            <div class="col-md-12 mt-2">
              <label>Notas</label>
              <textarea name="notes" class="form-control" placeholder="Observaciones"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button class="btn btn-success">Guardar</button>
        </div>
      </form>
    </div></div>
  </div>
@stop

@section('js')
<script>
// Reabrir el último modal tras validación fallida
@if ($errors->any() && old('current_modal'))
    $(function(){ $('#{{ old('current_modal') }}').modal('show'); });
@endif

// Confirmación elegante de borrado (SweetAlert2)
$(document).on('click','[data-delete]', function(e){
    e.preventDefault();
    const form = $(this).closest('form')[0];
    Swal.fire({title:'¿Eliminar recorrido?', icon:'warning', showCancelButton:true,
               confirmButtonText:'Sí, eliminar', cancelButtonText:'Cancelar'})
        .then(res => { if(res.isConfirmed) form.submit(); });
});

// DataTable opcional
$('#tbl-runs').DataTable({ pageLength: 10, ordering: true, responsive: true });
</script>
@stop