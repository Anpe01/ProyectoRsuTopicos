@extends('adminlte::page')

@section('title','Funciones (Personal)')
@section('content_header')
  <h1>Funciones (Roles operativos)</h1>
@stop

@section('content')
  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if(session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif

  <button class="btn btn-success mb-3" data-toggle="modal" data-target="#modalCreate">+ Nueva función</button>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Descripción</th>
        <th>Protegida</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      @forelse($functions as $f)
        <tr>
          <td>{{ $f->name }}</td>
          <td>{{ $f->description ?? '-' }}</td>
          <td>
            @if($f->protected)
              <span class="badge badge-info">Sí</span>
            @else
              <span class="badge badge-secondary">No</span>
            @endif
          </td>
          <td>
            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalEdit{{ $f->id }}" {{ $f->protected ? 'disabled' : '' }}>Editar</button>
            <form action="{{ route('functions.destroy',$f) }}" method="POST" class="d-inline">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar esta función?')" {{ $f->protected ? 'disabled' : '' }}>Eliminar</button>
            </form>
          </td>
        </tr>

        {{-- Modal Editar --}}
        <div class="modal fade" id="modalEdit{{ $f->id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-md"><div class="modal-content">
            <div class="modal-header bg-primary">
              <h5 class="modal-title">Editar función</h5>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="{{ route('functions.update',$f) }}">
              @csrf @method('PUT')
              <div class="modal-body">
                <div class="form-group">
                  <label>Nombre *</label>
                  <input name="name" value="{{ $f->name }}" class="form-control" required maxlength="120">
                </div>
                <div class="form-group">
                  <label>Descripción</label>
                  <textarea name="description" class="form-control" rows="3" placeholder="Opcional">{{ $f->description }}</textarea>
                </div>
                <div class="form-group">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="protected" id="prot{{ $f->id }}" {{ $f->protected ? 'checked' : '' }}>
                    <label class="form-check-label" for="prot{{ $f->id }}">Protegida (no editable/eliminable)</label>
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
      @empty
        <tr><td colspan="4" class="text-center text-muted">Sin registros</td></tr>
      @endforelse
    </tbody>
  </table>

  {{ $functions->links() }}

  {{-- Modal Crear --}}
  <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md"><div class="modal-content">
      <div class="modal-header bg-success">
        <h5 class="modal-title">Nueva función</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form method="POST" action="{{ route('functions.store') }}">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label>Nombre *</label>
            <input name="name" class="form-control" required maxlength="120" placeholder="Conductor, Ayudante, etc.">
          </div>
          <div class="form-group">
            <label>Descripción</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Opcional"></textarea>
          </div>
          <div class="form-group">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="protected" id="protNew">
              <label class="form-check-label" for="protNew">Protegida (no editable/eliminable)</label>
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
