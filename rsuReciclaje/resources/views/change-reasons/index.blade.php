@extends('adminlte::page')

@section('title', 'Gestión de Motivos de Cambio')

@section('plugins.Sweetalert2', true)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-list-alt"></i> Gestión de Motivos de Cambio</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <button class="btn btn-success mb-3" data-toggle="modal" data-target="#createModal">
                            <i class="fas fa-plus"></i> Nuevo Motivo de Cambio
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($changeReasons as $reason)
                                    <tr>
                                        <td>{{ $reason->id }}</td>
                                        <td>
                                            <strong>{{ $reason->name }}</strong>
                                            @if($reason->is_predefined)
                                                <span class="badge badge-info ml-2">Predefinido</span>
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($reason->description ?? 'Sin descripción', 60) }}</td>
                                        <td>
                                            @if($reason->is_predefined)
                                                <span class="badge badge-primary">Sistema</span>
                                            @else
                                                <span class="badge badge-secondary">Personalizado</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($reason->active)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-danger">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!$reason->is_predefined)
                                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editModal{{ $reason->id }}">
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>
                                                <button class="btn btn-danger btn-sm" onclick="confirmDelete({{ $reason->id }}, '{{ $reason->name }}')">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            @else
                                                <span class="text-muted">No editable</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No hay motivos de cambio registrados</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $changeReasons->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Crear -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Nuevo Motivo de Cambio</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('change-reasons.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" 
                               placeholder="Ej: Permiso por enfermedad" required maxlength="120">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="description">Descripción</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="Descripción opcional del motivo...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="active" name="active" value="1" checked>
                            <label class="form-check-label" for="active">
                                Activo
                            </label>
                        </div>
                        <small class="form-text text-muted">Los motivos inactivos no aparecerán en los formularios de cambio</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modales de Editar -->
@foreach($changeReasons as $reason)
@if(!$reason->is_predefined)
<div class="modal fade" id="editModal{{ $reason->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Editar Motivo de Cambio #{{ $reason->id }}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('change-reasons.update', $reason) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name{{ $reason->id }}">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name{{ $reason->id }}" name="name" value="{{ old('name', $reason->name) }}" 
                               required maxlength="120">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="description{{ $reason->id }}">Descripción</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description{{ $reason->id }}" name="description" rows="3">{{ old('description', $reason->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="active{{ $reason->id }}" 
                                   name="active" value="1" {{ old('active', $reason->active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="active{{ $reason->id }}">
                                Activo
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

<!-- Formulario oculto para eliminar -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
function confirmDelete(id, name) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Se eliminará el motivo "${name}"`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('deleteForm');
            form.action = `/change-reasons/${id}`;
            form.submit();
        }
    });
}
</script>
@endpush

